<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;
use AyeCode_UI_Settings;

/**
 * Class BusinessHoursField
 *
 * Handles the complex Business Hours input table and JS initialization.
 * Replaces geodir_cfi_business_hours.
 */
class BusinessHoursField extends AbstractFieldType {

	use BusinessHoursFieldOutputTrait;

	public function render_input() {
		$args         = $this->get_aui_args();
		$htmlvar_name = $this->field_data['htmlvar_name'];
		$value        = $this->value;

		// Get settings
		$locale          = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$timezone_string = geodirectory()->formatter->timezone_string();
		$weekdays        = geodirectory()->business_hours->get_short_weekdays();

		// Process existing value or use defaults
		$hours = array();
		$active = false;

		if ( ! empty( $value ) ) {
			$active = true;
			$periods = geodirectory()->business_hours->schema_to_array( $value, '' );

			if ( ! empty( $periods['hours'] ) ) {
				$hours = $periods['hours'];
			}
			if ( ! empty( $periods['timezone_string'] ) ) {
				$timezone_string = $periods['timezone_string'];
			}
		} else {
			// Use default values from geodir_bh_default_values()
			$hours = geodir_bh_default_values();
		}

		$horizontal = ( $args['label_type'] === 'horizontal' );

		// Generate time options (5 minute intervals)
		$time_options = $this->generate_time_options();

		ob_start();
		?>

		<div id="<?php echo esc_attr( $args['id'] ); ?>_row"
		     class="gd-bh-row mb-3 <?php echo $horizontal ? 'row' : ''; ?>"
		     <?php echo isset($args['wrap_attributes']) ? $args['wrap_attributes'] : ''; ?>
		     x-data="geodirBusinessHours_<?php echo esc_js( $htmlvar_name ); ?>()"
		     x-init="init()">

			<?php if ( $horizontal ) : ?>
			<label class="pt-0 col-sm-2 col-form-label">
				<?php echo $args['label']; ?>
			</label>
			<div class="col-sm-10">
			<?php else : ?>
			<label class="form-label">
				<?php echo $args['label']; ?>
			</label>
			<?php endif; ?>

				<!-- Bootstrap Toggle Switch -->
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="form-check form-switch">
						<input class="form-check-input"
						       type="checkbox"
						       role="switch"
						       id="<?php echo esc_attr( $htmlvar_name ); ?>_f_active"
						       x-model="active"
						       @change="updateValue()">
						<label class="form-check-label" for="<?php echo esc_attr( $htmlvar_name ); ?>_f_active">
							<span x-text="active ? '<?php echo esc_js( __( 'Enabled', 'geodirectory' ) ); ?>' : '<?php echo esc_js( __( 'Disabled', 'geodirectory' ) ); ?>'"></span>
						</label>
					</div>
					<button type="button" class="btn btn-link btn-sm text-decoration-none" @click="clearAll()" x-show="active" x-cloak>
						<?php _e( 'Clear All Days', 'geodirectory' ); ?>
					</button>
				</div>

				<!-- Business Hours Table -->
				<div x-show="active" x-cloak>
					<div class="border rounded">
						<?php
						$day_index = 0;
						foreach ( $weekdays as $day_code => $day_name ) :
							$day_hours = isset( $hours[ $day_code ] ) ? $hours[ $day_code ] : array();
							$day_state = 'closed';
							$day_slots = array();

							if ( ! empty( $day_hours ) ) {
								// Check for 24-hour mode (00:00-00:00)
								if ( count( $day_hours ) === 1 &&
								     isset( $day_hours[0]['opens'] ) && $day_hours[0]['opens'] === '00:00' &&
								     isset( $day_hours[0]['closes'] ) && $day_hours[0]['closes'] === '00:00' ) {
									$day_state = '24h';
								} else {
									$day_state = 'open';
									foreach ( $day_hours as $slot ) {
										$day_slots[] = array(
											'open' => isset( $slot['opens'] ) ? $slot['opens'] : '09:00',
											'close' => isset( $slot['closes'] ) ? $slot['closes'] : '17:00'
										);
									}
								}
							}

							$day_slots_json = ! empty( $day_slots ) ? json_encode( $day_slots ) : '[]';
							$border_class = $day_index > 0 ? 'border-top' : '';
							$day_index++;
							?>
							<div class="p-3 <?php echo $border_class; ?>">
								<div class="d-flex flex-wrap gap-2 align-items-start  align-items-center">
									<!-- Day Name -->
									<div class="fw-bold" style="min-width: 60px;">
										<?php echo esc_html( strtoupper( $day_name ) ); ?>
									</div>

									<!-- State Toggle (Pill Style Radio Buttons) -->
									<div class="d-inline-flex bg-body-secondary rounded p-1 shadow-sm" role="group" aria-label="<?php echo esc_attr( sprintf( __( '%s status', 'geodirectory' ), $day_name ) ); ?>">
										<!-- Closed -->
										<input type="radio"
										       class="btn-check"
										       :name="'state_<?php echo esc_js( $day_code ); ?>'"
										       :id="'btn_closed_<?php echo esc_js( $day_code ); ?>'"
										       autocomplete="off"
										       :checked="days['<?php echo esc_js( $day_code ); ?>'].state === 'closed'"
										       @change="setDayState('<?php echo esc_js( $day_code ); ?>', 'closed')">
										<label class="btn btn-sm btn-outline-light border-0 btn-icon"
										       :for="'btn_closed_<?php echo esc_js( $day_code ); ?>'"
										       :class="days['<?php echo esc_js( $day_code ); ?>'].state === 'closed' ? 'active text-primary' : 'text-secondary'"
											   data-bs-title="<?php esc_attr_e( 'Closed', 'geodirectory' ); ?>" data-bs-toggle="tooltip">
											<i class="fa-regular fa-moon fa-lg"></i>
										</label>

										<!-- Open with Hours -->
										<input type="radio"
										       class="btn-check"
										       :name="'state_<?php echo esc_js( $day_code ); ?>'"
										       :id="'btn_open_<?php echo esc_js( $day_code ); ?>'"
										       autocomplete="off"
										       :checked="days['<?php echo esc_js( $day_code ); ?>'].state === 'open'"
										       @change="setDayState('<?php echo esc_js( $day_code ); ?>', 'open')">
										<label class="btn btn-sm btn-outline-light border-0 btn-icon mx-1"
										       :for="'btn_open_<?php echo esc_js( $day_code ); ?>'"
										       :class="days['<?php echo esc_js( $day_code ); ?>'].state === 'open' ? 'active text-primary' : 'text-secondary'"
											   data-bs-title="<?php esc_attr_e( 'Open with hours', 'geodirectory' ); ?>" data-bs-toggle="tooltip">
											<i class="fa-regular fa-clock fa-lg"></i>
										</label>

										<!-- 24 Hours -->
										<input type="radio"
										       class="btn-check"
										       :name="'state_<?php echo esc_js( $day_code ); ?>'"
										       :id="'btn_24h_<?php echo esc_js( $day_code ); ?>'"
										       autocomplete="off"
										       :checked="days['<?php echo esc_js( $day_code ); ?>'].state === '24h'"
										       @change="setDayState('<?php echo esc_js( $day_code ); ?>', '24h')">
										<label class="btn btn-sm btn-outline-light border-0 btn-icon"
										       :for="'btn_24h_<?php echo esc_js( $day_code ); ?>'"
										       :class="days['<?php echo esc_js( $day_code ); ?>'].state === '24h' ? 'active text-primary' : 'text-secondary'"
											   data-bs-title="<?php esc_attr_e( 'Open 24 Hours', 'geodirectory' ); ?>" data-bs-toggle="tooltip">
											<i class="fa-regular fa-sun fa-lg"></i>
										</label>
									</div>

									<!-- Time Slots or State Text -->
									<div class="flex-fill">
										<!-- Closed State -->
										<template x-if="days['<?php echo esc_js( $day_code ); ?>'].state === 'closed'">
											<span class="badge text-danger bg-danger-subtle fs-sm fw-normal"><?php _e( 'Closed', 'geodirectory' ); ?></span>
										</template>

										<!-- 24 Hour State -->
										<template x-if="days['<?php echo esc_js( $day_code ); ?>'].state === '24h'">
											<span class="badge text-success bg-success-subtle fs-sm fw-normal"><i class="fas fa-sun"></i> <?php _e( 'Open 24 Hours', 'geodirectory' ); ?></span>
										</template>

										<!-- Open with Time Slots -->
										<template x-if="days['<?php echo esc_js( $day_code ); ?>'].state === 'open'">
											<div class="d-flex flex-column gap-2">
												<template x-for="(slot, index) in days['<?php echo esc_js( $day_code ); ?>'].slots" :key="index">
													<div class="d-flex flex-wrap align-items-center gap-2">
														<select class="form-select form-select-sm" style="width: auto; min-width: 100px;" x-model="slot.open" @change="updateValue()">
															<?php echo $time_options; ?>
														</select>
														<span>-</span>
														<select class="form-select form-select-sm" style="width: auto; min-width: 100px;" x-model="slot.close" @change="updateValue()">
															<?php echo $time_options; ?>
														</select>
														<!-- First row: + icon, Other rows: trash icon -->
														<button type="button"
														        class="btn btn-sm btn-link text-decoration-none"
														        :class="index === 0 ? 'text-primary' : 'text-danger'"
														        @click="index === 0 ? addSlot('<?php echo esc_js( $day_code ); ?>') : removeSlot('<?php echo esc_js( $day_code ); ?>', index)"
														        data-bs-toggle="tooltip"
														        data-bs-placement="top"
														        :data-bs-title="index === 0 ? '<?php esc_attr_e( 'Add hours', 'geodirectory' ); ?>' : '<?php esc_attr_e( 'Remove hours', 'geodirectory' ); ?>'">
															<i :class="index === 0 ? 'fas fa-plus' : 'fas fa-trash'"></i>
														</button>
													</div>
												</template>
											</div>
										</template>
									</div>

									<!-- Copy Button -->
									<button type="button"
									        class="btn btn-sm btn-outline-secondary btn-icon"
									        @click="copyToAll('<?php echo esc_js( $day_code ); ?>')"
									        data-bs-title="<?php esc_attr_e( 'Copy to all days', 'geodirectory' ); ?>" data-bs-toggle="tooltip">
										<i class="far fa-clone"></i>
									</button>
								</div>
							</div>
						<?php endforeach; ?>

						<!-- Timezone Selector -->
						<div class="p-3 border-top bg-light">
							<div class="row align-items-center g-2">
								<div class="col-sm-3">
									<label for="<?php echo esc_attr( $htmlvar_name ); ?>_f_timezone_string" class="form-label mb-0">
										<?php _e( 'Timezone:', 'geodirectory' ); ?>
									</label>
								</div>
								<div class="col-sm-9">
									<select id="<?php echo esc_attr( $htmlvar_name ); ?>_f_timezone_string"
									        class="form-select form-select-sm aui-select2"
									        x-model="timezone"
									        @change="updateValue()"
									        data-placeholder="<?php esc_attr_e( 'Select a city/timezone&hellip;', 'geodirectory' ); ?>"
									        data-allow-clear="1">
										<?php echo geodir_timezone_choice( $timezone_string, $locale ); ?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Hidden Input with Schema Value -->
				<input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>" x-model="schemaValue">

				<?php if ( $horizontal && ! empty( $args['help_text'] ) ) : ?>
					<small class="form-text text-muted d-block mt-2"><?php echo $args['help_text']; ?></small>
				<?php endif; ?>

			<?php if ( $horizontal ) : ?>
			</div>
			<?php endif; ?>

		</div>

		<script>
		function geodirBusinessHours_<?php echo esc_js( $htmlvar_name ); ?>() {
			return {
				active: <?php echo $active ? 'true' : 'false'; ?>,
				timezone: '<?php echo esc_js( $timezone_string ); ?>',
				schemaValue: '<?php echo esc_js( $value ); ?>',
				days: {
					<?php
					foreach ( $weekdays as $day_code => $day_name ) :
						$day_hours = isset( $hours[ $day_code ] ) ? $hours[ $day_code ] : array();
						$day_state = 'closed';
						$day_slots = array();

						if ( ! empty( $day_hours ) ) {
							// Check for 24-hour mode (00:00-00:00)
							if ( count( $day_hours ) === 1 &&
							     isset( $day_hours[0]['opens'] ) && $day_hours[0]['opens'] === '00:00' &&
							     isset( $day_hours[0]['closes'] ) && $day_hours[0]['closes'] === '00:00' ) {
								$day_state = '24h';
							} else {
								$day_state = 'open';
								foreach ( $day_hours as $slot ) {
									$day_slots[] = array(
										'open' => isset( $slot['opens'] ) ? $slot['opens'] : '09:00',
										'close' => isset( $slot['closes'] ) ? $slot['closes'] : '17:00'
									);
								}
							}
						}
						?>
						'<?php echo esc_js( $day_code ); ?>': {
							state: '<?php echo esc_js( $day_state ); ?>',
							slots: <?php echo json_encode( $day_slots ); ?>
						},
					<?php endforeach; ?>
				},

				init() {
					this.updateValue();
					// Initialize tooltips after Alpine renders
					this.$nextTick(() => {
						if (typeof aui_init === 'function') {
							aui_init();
						}
					});
				},

				reinitTooltips() {
					this.$nextTick(() => {
						// Dispose all existing tooltips first
						const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
						tooltipElements.forEach(el => {
							const tooltip = bootstrap.Tooltip.getInstance(el);
							if (tooltip) {
								tooltip.dispose();
							}
						});
						// Reinitialize tooltips
						if (typeof aui_init === 'function') {
							aui_init();
						}
					});
				},

				setDayState(dayCode, state) {
					this.days[dayCode].state = state;
					if (state === 'open' && this.days[dayCode].slots.length === 0) {
						this.days[dayCode].slots = [{open: '09:00', close: '17:00'}];
					} else if (state !== 'open') {
						this.days[dayCode].slots = [];
					}
					this.updateValue();
					// Reinitialize tooltips when state changes to 'open' (slots appear)
					if (state === 'open') {
						this.reinitTooltips();
					}
				},

				addSlot(dayCode) {
					this.days[dayCode].slots.push({open: '09:00', close: '17:00'});
					this.updateValue();
					this.reinitTooltips();
				},

				removeSlot(dayCode, index) {
					// Hide all tooltips immediately before removing the element
					const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
					tooltipElements.forEach(el => {
						const tooltip = bootstrap.Tooltip.getInstance(el);
						if (tooltip) {
							tooltip.hide();
						}
					});

					this.days[dayCode].slots.splice(index, 1);
					if (this.days[dayCode].slots.length === 0) {
						this.days[dayCode].state = 'closed';
					}
					this.updateValue();
					this.reinitTooltips();
				},

				toSchema() {
					if ( ! this.active ) return '';

					const periods = [];
					const dayCodes = <?php echo json_encode( array_keys( $weekdays ) ); ?>;

					dayCodes.forEach(dayCode => {
						const day = this.days[dayCode];

						if (day.state === '24h') {
							periods.push(`${dayCode} 00:00-00:00`);
						} else if (day.state === 'open' && day.slots.length > 0) {
							const hours = day.slots
								.filter(s => s.open && s.close)
								.map(s => `${s.open}-${s.close}`)
								.join(',');
							if (hours) periods.push(`${dayCode} ${hours}`);
						}
					});

					// Get timezone offset from select option
					const tzSelect = document.getElementById('<?php echo esc_js( $htmlvar_name ); ?>_f_timezone_string');
					const selectedOption = tzSelect?.options[tzSelect.selectedIndex];
					const offset = selectedOption?.dataset?.offset || '+0:00';

					// Build schema string manually to match old format: ["Mo 09:00-17:00","Tu 09:00-17:00"],["UTC":"+0","Timezone":"UTC"]
					const periodsStr = periods.map(p => `"${p}"`).join(',');
					return `[${periodsStr}],[\"UTC\":\"${offset}\",\"Timezone\":\"${this.timezone}\"]`;
				},

				updateValue() {
					this.$nextTick(() => {
						this.schemaValue = this.toSchema();
					});
				},

				copyToAll(sourceCode) {
					// Dispose all tooltips immediately before DOM changes
					const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
					tooltipElements.forEach(el => {
						const tooltip = bootstrap.Tooltip.getInstance(el);
						if (tooltip) {
							tooltip.dispose();
						}
					});

					const sourceDay = this.days[sourceCode];
					Object.keys(this.days).forEach(dayCode => {
						if (dayCode !== sourceCode) {
							this.days[dayCode].state = sourceDay.state;
							this.days[dayCode].slots = JSON.parse(JSON.stringify(sourceDay.slots));
						}
					});
					this.updateValue();
					this.reinitTooltips();
				},

				clearAll() {
					Object.keys(this.days).forEach(dayCode => {
						this.days[dayCode].state = 'closed';
						this.days[dayCode].slots = [];
					});
					this.updateValue();
				}
			}
		}
		</script>
		<?php

		return ob_get_clean();
	}

	/**
	 * Generate time options for select dropdowns (5 minute intervals).
	 *
	 * @return string HTML options
	 */
	private function generate_time_options() {
		$options = '';
		for ( $h = 0; $h < 24; $h++ ) {
			for ( $m = 0; $m < 60; $m += 5 ) {
				$time = sprintf( '%02d:%02d', $h, $m );
				$options .= sprintf( '<option value="%s">%s</option>', esc_attr( $time ), esc_html( $time ) );
			}
		}
		return $options;
	}

	/**
	 * Sanitize: Ensure valid JSON structure or schema string.
	 */
	public function sanitize( $value ) {
		// Often the frontend sends a stringified JSON or a specific array structure.
		// Validation logic from geodir_save_post logic can go here.
		if ( is_array( $value ) ) {
			return wp_json_encode( $value );
		}
		return sanitize_text_field( $value );
	}
}
