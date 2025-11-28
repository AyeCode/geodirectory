<?php
/**
 * Permalink Settings Page Integration
 *
 * Adds GeoDirectory permalink settings to the WordPress Settings > Permalinks page.
 * Handles rendering the UI and saving permalink structure options.
 *
 * @package GeoDirectory\Admin\Settings
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Settings;

use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Services\Settings;

final class PermalinkSettings {
	private LocationsInterface $locations;
	private Settings $settings;
	private string $permalinks = '';

	public function __construct( LocationsInterface $locations, Settings $settings ) {
		$this->locations = $locations;
		$this->settings = $settings;
	}

	/**
	 * Initialize permalink settings section.
	 */
	public function settings_init(): void {
		// Add section to the permalinks page
		add_settings_section(
			'geodir-permalink',
			__( 'GeoDirectory permalinks', 'geodirectory' ),
			[ $this, 'render_settings' ],
			'permalink'
		);

		$this->permalinks = geodir_get_permalink_structure();

		// Save settings
		$this->save_settings();
	}

	/**
	 * Render permalink settings UI.
	 */
	public function render_settings(): void {
		echo wpautop( __( 'These settings control the permalinks used specifically for GeoDirectory CPTs', 'geodirectory' ) );

		$url_base = trailingslashit( home_url( '/' ) );
		$base_slug = geodir_get_ctp_slug( 'gd_place' );
		$default_location = $this->locations->get_default();

		$structures = [
			__( 'Default', 'geodirectory' ) => [
				'value'   => '',
				'example' => esc_html( $url_base ) . esc_html( $base_slug ) . '/sample-place/',
			],
			__( 'Full location', 'geodirectory' ) => [
				'value'   => '/%country%/%region%/%city%/%postname%/',
				'example' => esc_html( $url_base ) . trailingslashit( $base_slug ) .
				             trailingslashit( $default_location->country_slug ) .
				             trailingslashit( $default_location->region_slug ) .
				             trailingslashit( $default_location->city_slug ) . 'sample-place/',
			],
			__( 'Full location with category', 'geodirectory' ) => [
				'value'   => '/%country%/%region%/%city%/%category%/%postname%/',
				'example' => esc_html( $url_base ) . trailingslashit( $base_slug ) .
				             trailingslashit( $default_location->country_slug ) .
				             trailingslashit( $default_location->region_slug ) .
				             trailingslashit( $default_location->city_slug ) .
				             'attractions/' .
				             'sample-place/',
			],
		];

		$is_default = false;

		$available_tags = [
			'country'  => __( '%s (Country slug of the post. Ex: united-states.)', 'geodirectory' ),
			'region'   => __( '%s (Region slug of the post. Ex: pennsylvania.)', 'geodirectory' ),
			'city'     => __( '%s (City slug of the post. Ex: philadelphia.)', 'geodirectory' ),
			'category' => __( '%s (Category slug. Nested sub-categories appear as nested directories in the URL.)', 'geodirectory' ),
			'postname' => __( '%s (The sanitized post title (slug).)', 'geodirectory' ),
			'post_id'  => __( '%s (The unique ID of the post. Ex: 423.)', 'geodirectory' ),
		];

		$available_tags = apply_filters( 'geodir_available_permalink_structure_tags', $available_tags );

		$structure_tag_added = __( '%s added to permalink structure', 'geodirectory' );
		$structure_tag_already_used = __( '%s (already used in permalink structure)', 'geodirectory' );
		?>
		<table class="form-table gd-permalink-structure">
			<tbody>
			<?php foreach ( $structures as $label => $structure ) {
				if ( $structure['value'] === $this->permalinks ) {
					$is_default = true;
				}
				?>
				<tr>
					<th>
						<label>
							<input name="geodirectory_permalink" type="radio"
								   value="<?php echo esc_attr( $structure['value'] ); ?>"
								   class="gdtog" <?php checked( $structure['value'], $this->permalinks ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
					</th>
					<td>
						<?php if ( $label === __( 'Default', 'geodirectory' ) ) { ?>
							<code class="default-example">
								<?php echo esc_html( $url_base ); ?>?gd_place=sample-place
							</code>
							<code class="non-default-example">
								<?php echo esc_html( $structure['example'] ); ?>
							</code>
						<?php } else { ?>
							<code>
								<?php echo esc_html( $structure['example'] ); ?>
							</code>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>

			<tr>
				<th>
					<label>
						<input name="geodirectory_permalink" id="geodir_custom_selection" type="radio" value="custom"
							   class="tog" <?php checked( $is_default, false ); ?> />
						<?php esc_html_e( 'Custom base', 'geodirectory' ); ?>
					</label>
				</th>
				<td>
					<code><?php echo esc_html( $url_base ) . '%cpt_slug%'; ?></code>
					<input name="geodirectory_permalink_structure" id="geodir_permalink_structure" type="text"
						   value="<?php echo esc_attr( $this->permalinks ? $this->permalinks : '' ); ?>"
						   class="regular-text code">
					<br /><br />
					<div class="gd-available-structure-tags hide-if-no-js">
						<div id="gd_custom_selection_updated" aria-live="assertive" class="screen-reader-text"></div>
						<?php if ( ! empty( $available_tags ) ) { ?>
							<p><?php esc_html_e( 'Available tags:', 'geodirectory' ); ?></p>
							<ul role="list">
								<?php foreach ( $available_tags as $tag => $explanation ) { ?>
									<li>
										<button type="button"
												class="button button-secondary"
												aria-label="<?php echo esc_attr( sprintf( $explanation, $tag ) ); ?>"
												data-added="<?php echo esc_attr( sprintf( $structure_tag_added, $tag ) ); ?>"
												data-used="<?php echo esc_attr( sprintf( $structure_tag_already_used, $tag ) ); ?>">
											<?php echo '%' . esc_html( $tag ) . '%'; ?>
										</button>
									</li>
								<?php } ?>
							</ul>
						<?php } ?>
					</div>
				</td>
			</tr>
			</tbody>
		</table>

		<h2 class="title"><?php esc_html_e( 'GeoDirectory Taxonomies', 'geodirectory' ); ?></h2>
		<p><?php
			printf(
				__( 'If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>%s/topics/attractions/</code>. Tags and category can not be blank.', 'geodirectory' ),
				esc_html( $url_base . $base_slug )
			);
		?></p>

		<table class="form-table">
			<tr>
				<th><label for="geodirectory_category_base"><?php esc_html_e( 'Category base', 'geodirectory' ); ?></label></th>
				<td><input name="geodirectory_category_base" id="geodirectory_category_base" type="text"
						   value="<?php echo esc_attr( geodir_get_option( 'permalink_category_base', 'category' ) ); ?>"
						   class="regular-text code" /></td>
			</tr>
			<tr>
				<th><label for="geodirectory_tag_base"><?php esc_html_e( 'Tag base', 'geodirectory' ); ?></label></th>
				<td><input name="geodirectory_tag_base" id="geodirectory_tag_base" type="text"
						   value="<?php echo esc_attr( geodir_get_option( 'permalink_tag_base', 'tags' ) ); ?>"
						   class="regular-text code" /></td>
			</tr>
		</table>

		<?php
		// Show missing location settings if any CPT doesn't require address
		if ( $this->show_missing_location_settings() ) {
			?>
			<h2 class="title"><?php esc_html_e( 'GeoDirectory Location Base (when not set)', 'geodirectory' ); ?></h2>
			<p><?php
				printf(
					__( 'When the address field is NOT be required, the location URL slug must have a replacement. For example: <code>%s/%s/%s/CITY-BASE-SLUG/sample-place/</code>', 'geodirectory' ),
					esc_html( $url_base . $base_slug ),
					esc_html( $default_location->country_slug ),
					esc_html( $default_location->region_slug )
				);
			?></p>
			<p><code><?php esc_html_e( 'Other suggestions: find, online, virtual, go, see', 'geodirectory' ); ?></code></p>

			<table class="form-table">
				<tr>
					<th><label for="geodirectory_missing_country_base"><?php esc_html_e( 'Country base', 'geodirectory' ); ?></label></th>
					<td><input name="geodirectory_missing_country_base" id="geodirectory_missing_country_base" type="text"
							   value="<?php echo esc_attr( geodir_get_option( 'permalink_missing_country_base', 'global' ) ); ?>"
							   class="regular-text code" /></td>
				</tr>
				<tr>
					<th><label for="geodirectory_missing_region_base"><?php esc_html_e( 'Region base', 'geodirectory' ); ?></label></th>
					<td><input name="geodirectory_missing_region_base" id="geodirectory_missing_region_base" type="text"
							   value="<?php echo esc_attr( geodir_get_option( 'permalink_missing_region_base', 'discover' ) ); ?>"
							   class="regular-text code" /></td>
				</tr>
				<tr>
					<th><label for="geodirectory_missing_city_base"><?php esc_html_e( 'City base', 'geodirectory' ); ?></label></th>
					<td><input name="geodirectory_missing_city_base" id="geodirectory_missing_city_base" type="text"
							   value="<?php echo esc_attr( geodir_get_option( 'permalink_missing_city_base', 'explore' ) ); ?>"
							   class="regular-text code" /></td>
				</tr>
			</table>
		<?php } ?>

		<style>.form-table.gd-permalink-structure .gd-available-structure-tags li{float:left;margin-right:5px}</style>
		<?php $this->render_javascript(); ?>
		<?php
	}

	/**
	 * Render JavaScript for permalink UI interactions.
	 */
	private function render_javascript(): void {
		?>
		<script type="text/javascript">
		var gdPermalinkStructureFocused = false,
			$gdPermalinkStructure = jQuery('#geodir_permalink_structure'),
			$gdPermalinkStructureInputs = jQuery('.gd-permalink-structure input:radio'),
			$gdPermalinkCustomSelection = jQuery('#geodir_custom_selection'),
			$gdAvailableStructureTags = jQuery('.form-table.gd-permalink-structure .gd-available-structure-tags button');

		$gdPermalinkStructureInputs.on('change', function() {
			if ('custom' === this.value) {
				return;
			}
			$gdPermalinkStructure.val(this.value);
			$gdAvailableStructureTags.each(function() {
				gdChangeStructureTagButtonState(jQuery(this));
			});
		});

		$gdPermalinkStructure.on('click input', function() {
			$gdPermalinkCustomSelection.prop('checked', true);
		});

		$gdPermalinkStructure.on('focus', function(event) {
			gdPermalinkStructureFocused = true;
			jQuery(this).off(event);
		});

		function gdChangeStructureTagButtonState(button) {
			if (-1 !== $gdPermalinkStructure.val().indexOf(button.text().trim())) {
				button.attr('data-label', button.attr('aria-label'));
				button.attr('aria-label', button.attr('data-used'));
				button.attr('aria-pressed', true);
				button.addClass('active');
			} else if (button.attr('data-label')) {
				button.attr('aria-label', button.attr('data-label'));
				button.attr('aria-pressed', false);
				button.removeClass('active');
			}
		}

		$gdAvailableStructureTags.each(function() {
			gdChangeStructureTagButtonState(jQuery(this));
		});

		$gdPermalinkStructure.on('change', function() {
			$gdAvailableStructureTags.each(function() {
				gdChangeStructureTagButtonState(jQuery(this));
			});
		});

		$gdAvailableStructureTags.on('click', function() {
			var permalinkStructureValue = $gdPermalinkStructure.val(),
				selectionStart = $gdPermalinkStructure[0].selectionStart,
				selectionEnd = $gdPermalinkStructure[0].selectionEnd,
				textToAppend = jQuery(this).text().trim(),
				textToAnnounce = jQuery(this).attr('data-added'),
				newSelectionStart;

			if (-1 !== permalinkStructureValue.indexOf(textToAppend)) {
				permalinkStructureValue = permalinkStructureValue.replace(textToAppend + '/', '');
				$gdPermalinkStructure.val('/' === permalinkStructureValue ? '' : permalinkStructureValue);
				jQuery('#custom_selection_updated').text(textToAnnounce);
				gdChangeStructureTagButtonState(jQuery(this));
				return;
			}

			if (!gdPermalinkStructureFocused && 0 === selectionStart && 0 === selectionEnd) {
				selectionStart = selectionEnd = permalinkStructureValue.length;
			}

			$gdPermalinkCustomSelection.prop('checked', true);

			if ('/' !== permalinkStructureValue.substr(0, selectionStart).substr(-1)) {
				textToAppend = '/' + textToAppend;
			}
			if ('/' !== permalinkStructureValue.substr(selectionEnd, 1)) {
				textToAppend = textToAppend + '/';
			}

			$gdPermalinkStructure.val(permalinkStructureValue.substr(0, selectionStart) + textToAppend + permalinkStructureValue.substr(selectionEnd));
			jQuery('#custom_selection_updated').text(textToAnnounce);
			gdChangeStructureTagButtonState(jQuery(this));

			if (gdPermalinkStructureFocused && $gdPermalinkStructure[0].setSelectionRange) {
				newSelectionStart = (permalinkStructureValue.substr(0, selectionStart) + textToAppend).length;
				$gdPermalinkStructure[0].setSelectionRange(newSelectionStart, newSelectionStart);
				$gdPermalinkStructure.focus();
			}
		});

		jQuery(function($) {
			jQuery('.permalink-structure input').on("change", function() {
				jQuery('.gd-permalink-structure').find('code.non-default-example, code.default-example').hide();
				if (jQuery(this).val()) {
					jQuery('.gd-permalink-structure code.non-default-example').show();
					jQuery('.gd-permalink-structure input').removeAttr('disabled');
					jQuery('.gd-available-structure-tags li button').removeAttr('disabled');
				} else {
					jQuery('.gd-permalink-structure code.default-example').show();
					jQuery('.gd-permalink-structure input:eq(0)').trigger("click");
					jQuery('.gd-permalink-structure input').attr('disabled', 'disabled');
					jQuery('.gd-available-structure-tags li button').attr('disabled', 'disabled');
				}
			});
			jQuery('.permalink-structure input:checked').trigger("change");

			jQuery('form[name="form"]').on("submit", function(e) {
				var $return = true;
				var $permalink_structure = jQuery('#geodir_permalink_structure').val();

				if ($permalink_structure != '') {
					if (!$permalink_structure.includes("/%postname%") && !$permalink_structure.includes("/%post_id%")) {
						alert("<?php esc_html_e( 'GeoDirectory permalinks must contain either `%postname%` or `%post_id%`, please check and try again.', 'geodirectory' ); ?>");
						$return = false;
					}
				}

				if (jQuery('#geodirectory_tag_base').val() == '') {
					alert("<?php esc_html_e( 'GeoDirectory tag base can not be blank, please check and try again.', 'geodirectory' ); ?>");
					$return = false;
				}

				if (jQuery('#geodirectory_category_base').val() == '') {
					alert("<?php esc_html_e( 'GeoDirectory category base can not be blank, please check and try again.', 'geodirectory' ); ?>");
					$return = false;
				}

				return $return;
			});
		});
		</script>
		<?php
	}

	/**
	 * Save permalink settings.
	 */
	private function save_settings(): void {
		if ( ! is_admin() || ! isset( $_POST['permalink_structure'] ) ) {
			return;
		}

		if ( function_exists( 'switch_to_locale' ) ) {
			switch_to_locale( get_locale() );
		}

		// Permalink structure
		$permalink_structure = isset( $_POST['geodirectory_permalink_structure'] ) ? trim( $_POST['geodirectory_permalink_structure'] ) : '';
		if ( ! empty( $permalink_structure ) ) {
			$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );
		}
		$permalink_structure = sanitize_option( 'permalink_structure', $permalink_structure );
		geodir_set_permalink_structure( $permalink_structure );

		// Category base
		if ( isset( $_POST['geodirectory_category_base'] ) ) {
			$category_base = sanitize_title_with_dashes( $_POST['geodirectory_category_base'] );
			geodir_update_option( 'permalink_category_base', $category_base );
		}

		// Tag base
		if ( isset( $_POST['geodirectory_tag_base'] ) ) {
			$tag_base = ! empty( $_POST['geodirectory_tag_base'] ) ? sanitize_title_with_dashes( $_POST['geodirectory_tag_base'] ) : 'tags';
			geodir_update_option( 'permalink_tag_base', $tag_base );
		}

		// Missing location bases
		if ( isset( $_POST['geodirectory_missing_country_base'] ) ) {
			geodir_update_option( 'permalink_missing_country_base', sanitize_title_with_dashes( $_POST['geodirectory_missing_country_base'] ) );
		}
		if ( isset( $_POST['geodirectory_missing_region_base'] ) ) {
			geodir_update_option( 'permalink_missing_region_base', sanitize_title_with_dashes( $_POST['geodirectory_missing_region_base'] ) );
		}
		if ( isset( $_POST['geodirectory_missing_city_base'] ) ) {
			geodir_update_option( 'permalink_missing_city_base', sanitize_title_with_dashes( $_POST['geodirectory_missing_city_base'] ) );
		}

		if ( function_exists( 'restore_current_locale' ) ) {
			restore_current_locale();
		}
	}

	/**
	 * Check if we should show missing location settings.
	 *
	 * @return bool True if at least one CPT doesn't require address.
	 */
	private function show_missing_location_settings(): bool {
		$post_types = geodir_get_posttypes( 'array' );

		if ( empty( $post_types ) ) {
			return false;
		}

		foreach ( $post_types as $post_type => $pt ) {
			if ( ! geodir_cpt_requires_address( $post_type ) ) {
				return true;
			}
		}

		return false;
	}
}
