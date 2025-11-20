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

	public function render_input() {
		// Enqueue required scripts (Flatpickr via AUI)
		$aui_settings = AyeCode_UI_Settings::instance();
		$aui_settings->enqueue_flatpickr();

		$args         = $this->get_aui_args();
		$htmlvar_name = $this->field_data['htmlvar_name'];
		$value        = $this->value;

		// Dependencies (Assuming these are available via global helpers or service container)
		// We use the new V3 services where possible.
		$locale            = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$time_format       = geodirectory()->business_hours->input_time_format(); // e.g. 'H:i' or 'g:i A'
		$timepicker_format = geodirectory()->business_hours->input_time_format( true ); // jQuery/Flatpickr format
		$timezone_string   = geodirectory()->formatter->timezone_string();
		$weekdays          = geodirectory()->business_hours->get_short_weekdays();

		// Determine 24h format for JS attributes
		$time_24hr = strpos( $timepicker_format, 'H' ) !== false;
		// Escape check (e.g. \H)
		if ( $time_24hr && strpos( $timepicker_format, '\H' ) !== false ) {
			if ( strpos( $timepicker_format, '\H' ) + 1 === strpos( $timepicker_format, 'H' ) ) {
				$time_24hr = false;
			}
		}

		// Process existing value
		$hours = geodirectory()->business_hours->default_values();
		$display_hours = 'none';

		if ( ! empty( $value ) ) {
			$display_hours = '';
			// Assuming geodir_schema_to_array is wrapper or logic moved to BusinessHours service
			$periods = geodirectory()->business_hours->schema_to_array( $value, '' ); // Country arg empty for now

			if ( ! empty( $periods['hours'] ) ) {
				$hours = $periods['hours'];
			}
			if ( ! empty( $periods['timezone_string'] ) ) {
				$timezone_string = $periods['timezone_string'];
			}
		}

		$timezone_data = geodirectory()->business_hours->timezone_data( $timezone_string );
		$horizontal    = ( $args['label_type'] === 'horizontal' );
		$bs5           = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];

		ob_start();
		?>
		<script type="text/javascript">
			jQuery(function($){
				if (typeof GeoDir_Business_Hours !== 'undefined') {
					GeoDir_Business_Hours.init({
						'field': '<?php echo esc_js( $htmlvar_name ); ?>',
						'value': '<?php echo esc_js( $value ); ?>',
						'json': '<?php echo esc_js( json_encode( $value ) ); ?>',
						'offset': <?php echo (int) $timezone_data['offset']; ?>,
						'utc_offset': '<?php echo esc_js( $timezone_data['utc_offset'] ); ?>',
						'offset_dst': <?php echo (int) $timezone_data['offset_dst']; ?>,
						'utc_offset_dst': '<?php echo esc_js( $timezone_data['utc_offset_dst'] ); ?>',
						'has_dst': <?php echo (int) $timezone_data['has_dst']; ?>,
						'is_dst': <?php echo (int) $timezone_data['is_dst']; ?>
					});
				}
			});
		</script>

		<div id="<?php echo esc_attr( $args['id'] ); ?>_row" class="gd-bh-row <?php echo $horizontal ? 'row' : ''; ?> <?php echo $bs5 ? 'mb-3' : 'form-group'; ?>" <?php echo isset($args['wrap_attributes']) ? $args['wrap_attributes'] : ''; ?>>

			<label class="<?php echo $horizontal ? 'pt-0 col-sm-2 col-form-label' : ( $bs5 ? 'form-label' : '' ); ?>">
				<?php echo $args['label']; ?>
			</label>

			<div class="gd-bh-field <?php echo $horizontal ? 'col-sm-10' : ''; ?>" data-field-name="<?php echo esc_attr( $htmlvar_name ); ?>" role="radiogroup">
				<?php
				// Active Toggle (Yes/No)
				echo aui()->radio([
					'id' => $htmlvar_name . '_f_active',
					'name' => $htmlvar_name . '_f_active',
					'required' => true,
					'label_type' => 'vertical', // Hidden/Vertical
					'value' => ( $value ? 1 : 0 ),
					'options' => [ '1' => __( 'Yes','geodirectory' ), '0' => __( 'No','geodirectory' ) ],
					'extra_attributes' => [ 'data-field' => 'active', 'data-no-rule' => 1 ]
				]);
				?>

				<div class="gd-bh-items" style="display:<?php echo esc_attr( $display_hours ); ?>;" data-12am="<?php echo esc_attr( strtoupper( date_i18n( $time_format, strtotime( '00:00' ) ) ) ); ?>">
					<table class="table table-borderless table-striped">
						<thead class="<?php echo $bs5 ? 'table-light' : 'thead-light'; ?>">
						<tr>
							<th class="gd-bh-day"><?php _e( 'Day', 'geodirectory' ); ?></th>
							<th class="gd-bh-24hours text-nowrap"><?php _e( 'Open 24 hours', 'geodirectory' ); ?></th>
							<th class="gd-bh-time"><?php _e( 'Opening Hours', 'geodirectory' ); ?></th>
							<th class="gd-bh-act"><span class="sr-only visually-hidden"><?php _e( 'Add', 'geodirectory' ); ?></span></th>
						</tr>
						</thead>
						<tbody>
						<tr style="display:none!important">
							<td colspan="4" class="gd-bh-blank">
								<div class="gd-bh-hours row">
									<div class="col-10 p-0 mb-1"><div class="input-group">
											<div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo $time_24hr ? 'true' : 'false'; ?>" data-alt-input-class="gd-alt-open form-control text-center bg-white rounded-0 w-100 GD_UNIQUE_ID_oa" class="form-control text-center bg-white rounded-0 w-100" id="GD_UNIQUE_ID_o" data-field-alt="open" data-bh="time" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" value="09:00"></div>
											<div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo $time_24hr ? 'true' : 'false'; ?>" data-alt-input-class="gd-alt-close form-control text-center bg-white rounded-0 w-100 GD_UNIQUE_ID_oa" class="form-control text-center bg-white rounded-0 w-100" id="GD_UNIQUE_ID_c" data-field-alt="close" data-bh="time" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" value="17:00"></div>
										</div></div>
									<div class="col-2 text-left text-start gd-bh-remove"><i class="fas fa-minus-circle text-danger c-pointer mt-2" title="<?php esc_attr_e("Remove hours","geodirectory"); ?>" data-toggle="tooltip" aria-hidden="true"></i></div>
								</div>
							</td>
						</tr>

						<?php foreach ( $weekdays as $day_no => $day ) {
							$is_closed = empty( $hours[ $day_no ] );
							?>
							<tr class="gd-bh-item<?php echo $is_closed ? ' gd-bh-item-closed' : ''; ?>">
								<td class="gd-bh-day align-top"><?php echo esc_html( $day ); ?></td>
								<td class="gd-bh-24hours align-top"><div class="form-check mt-1"><input type="checkbox" value="1" class="form-check-input" <?php echo $is_closed ? 'style="display:none"' : ''; ?>></div></td>
								<td class="gd-bh-time" data-day="<?php echo esc_attr( $day_no ); ?>" data-field="<?php echo esc_attr( $htmlvar_name ); ?>_f[hours][<?php echo esc_attr( $day_no ); ?>]">
									<?php if ( ! $is_closed ) {
										foreach ( $hours[ $day_no ] as $slot ) {
											// ... [Slot Rendering Logic for Existing Values] ...
											// For brevity, replicating the logic from geodir_cfi_business_hours loop:
											$open = isset($slot['opens']) ? $slot['opens'] : '';
											$close = isset($slot['closes']) ? $slot['closes'] : '';
											$unique_id = uniqid( (string) rand() );

											$open_His = $open ? date_i18n( 'H:i:s', strtotime( $open ) ) : '';
											$close_His = $close ? date_i18n( 'H:i:s', strtotime( $close ) ) : '';

											?>
											<div class="gd-bh-hours<?php echo ( $open == '00:00' && $open == $close ) ? ' gd-bh-has24' : ''; ?> row">
												<div class="col-10 p-0 mb-1"><div class="input-group">
														<div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo $time_24hr ? 'true' : 'false'; ?>" data-alt-input-class="gd-alt-open form-control text-center bg-white rounded-0 w-100 <?php echo $unique_id; ?>_oa" data-aui-init="flatpickr" class="form-control text-center bg-white rounded-0 w-100" id="<?php echo $unique_id; ?>_o" data-field-alt="open" data-bh="time" value="<?php echo esc_attr( $open_His ); ?>" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" data-time="<?php echo esc_attr($open_His); ?>" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][open][]"></div>
														<div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo $time_24hr ? 'true' : 'false'; ?>" data-alt-input-class="gd-alt-close form-control text-center bg-white rounded-0 w-100 <?php echo $unique_id; ?>_oa" data-aui-init="flatpickr" class="form-control text-center bg-white rounded-0 w-100" id="<?php echo $unique_id; ?>_c" data-field-alt="close" data-bh="time" value="<?php echo esc_attr( $close_His ); ?>" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" data-time="<?php echo esc_attr($close_His); ?>" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][close][]"></div>
													</div></div>
												<div class="col-2 text-left text-start gd-bh-remove"><i class="fas fa-minus-circle text-danger c-pointer mt-2" title="<?php esc_attr_e( "Remove hours", "geodirectory" ); ?>" data-toggle="tooltip" aria-hidden="true"></i></div>
											</div>
											<?php
										}
									} else { ?>
										<div class="gd-bh-closed text-center"><?php _e( 'Closed', 'geodirectory' ); ?></div>
									<?php } ?>
								</td>
								<td class="gd-bh-act align-top"><span class="gd-bh-add c-pointer" title="<?php esc_attr_e("Add new set of hours","geodirectory"); ?>" data-toggle="tooltip"><i class="fas fa-plus-circle text-primary" aria-hidden="true"></i></span></td>
							</tr>
						<?php } ?>

						<tr class="gd-tz-item">
							<td colspan="4">
								<div class="row mb-0">
									<div class="col-sm-2 col-form-label">
										<label for="<?php echo esc_attr( $htmlvar_name ); ?>_f_timezone_string" class="mb-0"><?php _e( 'Timezone:', 'geodirectory' ); ?></label>
									</div>
									<div class="col-sm-10 pt-1">
										<select data-field="timezone_string" id="<?php echo esc_attr( $htmlvar_name ); ?>_f_timezone_string" class="<?php echo $bs5 ? 'form-select form-select-sm' : 'custom-select custom-select-sm'; ?> aui-select2" data-placeholder="<?php esc_attr_e( 'Select a city/timezone&hellip;', 'geodirectory' ); ?>" data-allow-clear="1" option-ajaxchosen="false" tabindex="-1" aria-hidden="true">
											<?php echo geodir_timezone_choice( $timezone_string, $locale ); ?>
										</select>
									</div>
								</div>
							</td>
						</tr>
						</tbody>
					</table>
				</div>

				<input type="hidden" name="<?php echo esc_attr( $htmlvar_name ); ?>" value="<?php echo esc_attr( is_array($value) ? json_encode($value) : $value ); ?>">

				<?php if ( $horizontal && ! empty( $args['help_text'] ) ) { ?>
					<small class="form-text text-muted d-block"><?php echo $args['help_text']; ?></small>
				<?php } ?>
			</div>
		</div>
		<?php

		return ob_get_clean();
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
