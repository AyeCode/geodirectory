<?php
/**
 * Adds settings to the permalinks admin settings page
 *
 * @class       GeoDir_Admin_Permalink_Settings
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Admin_Permalink_Settings', false ) ) :

	/**
	 * GeoDir_Admin_Permalink_Settings Class.
	 */
	class GeoDir_Admin_Permalink_Settings {

		/**
		 * Permalink settings.
		 *
		 * @var string
		 */
		private $permalinks = '';

		/**
		 * Hook in tabs.
		 */
		public function __construct() {
			$this->settings_init();
			$this->settings_save();
		}

		/**
		 * Init our settings.
		 */
		public function settings_init() {
			// Add a section to the permalinks page
			add_settings_section( 'geodir-permalink', __( 'GeoDirectory permalinks', 'geodirectory' ), array(
				$this,
				'settings'
			), 'permalink' );


			$this->permalinks = geodir_get_permalink_structure();
		}


		/**
		 * Show the settings.
		 */
		public function settings() {
			global $geodirectory;

			echo wpautop( __( 'These settings control the permalinks used specifically for GeoDirectory CPTs', 'geodirectory' ) );

			$url_base = trailingslashit( home_url( '/' ) ); // Base url.
			$base_slug = geodir_get_ctp_slug( 'gd_place' ); // Base slug.
			$default_location = $geodirectory->location->get_default_location();

			$structures = array(
				__( 'Default', 'geodirectory' )     => array(
					'value' => '',
					'example' => esc_html( $url_base ) . esc_html( $base_slug ).'/sample-place/'
				),
				__( 'Full location', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%postname%/',
					'example' => esc_html( $url_base ) . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) . 'sample-place/'
				),
				__( 'Full location with category', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%category%/%postname%/',
					'example' => esc_html( $url_base ) . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) .
					             'attractions/' .
					             'sample-place/'
				)
			);

			$is_default = false;

			$available_tags = array(
				/* translators: %s: permalink structure tag */
				'country'     => __( '%s (Country slug of the post. Ex: united-states.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'region' => __( '%s (Region slug of the post. Ex: pennsylvania.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'city'      => __( '%s (City slug of the post. Ex: philadelphia.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'category' => __( '%s (Category slug. Nested sub-categories appear as nested directories in the URL.)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'postname' => __( '%s (The sanitized post title (slug).)', 'geodirectory' ),
				/* translators: %s: permalink structure tag */
				'post_id'  => __( '%s (The unique ID of the post. Ex: 423.)', 'geodirectory' ),
			);

			/**
			 * Filters the list of available permalink structure tags on the Permalinks settings page.
			 *
			 * @since 2.0.0
			 *
			 * @param array $available_tags A key => value pair of available permalink structure tags.
			 */
			$available_tags = apply_filters( 'geodir_available_permalink_structure_tags', $available_tags );

			/* translators: %s: permalink structure tag */
			$structure_tag_added = __( '%s added to permalink structure', 'geodirectory' );

			/* translators: %s: permalink structure tag */
			$structure_tag_already_used = __( '%s (already used in permalink structure)', 'geodirectory' );

			//print_r($structures);
			?>
			<table class="form-table gd-permalink-structure">
				<tbody>



				<?php foreach ($structures as $label => $structure ){
					if( $structure['value'] == $this->permalinks){$is_default = true;}
					?>
				<tr>
					<th>
						<label>
							<input name="geodirectory_permalink" type="radio"
					                  value="<?php echo esc_attr( $structure['value'] ); ?>"
					                  class="gdtog" <?php checked( $structure['value'] , $this->permalinks ); ?> />
							<?php echo $label; ?>
						</label>
					</th>
					<td>
						<?php if( $label == __( 'Default', 'geodirectory' ) ) {?>
							<code class="default-example">
								<?php echo esc_html( $url_base ); ?>?gd_place=sample-place
							</code>
							<code class="non-default-example">
								<?php echo $structure['example']; ?>
							</code>
						<?php }else{?>
						<code>
							<?php echo $structure['example']; ?>
						</code>
						<?php }?>
					</td>
				</tr>
				<?php }?>


				<tr>
					<th>
						<label>
							<input name="geodirectory_permalink" id="geodir_custom_selection" type="radio" value="custom"
					                  class="tog" <?php checked( $is_default , false ); ?> />
							<?php _e( 'Custom base', 'geodirectory' ); ?>
						</label>
					</th>
					<td>
						<code><?php echo esc_html( $url_base ) . '%cpt_slug%'; ?></code>
						<input name="geodirectory_permalink_structure" id="geodir_permalink_structure" type="text"
						       value="<?php echo esc_attr( $this->permalinks ?  $this->permalinks : '' ); ?>"
						       class="regular-text code">
						<br /><br />
						<div class="gd-available-structure-tags hide-if-no-js">
							<div id="gd_custom_selection_updated" aria-live="assertive" class="screen-reader-text"></div>
							<?php if ( ! empty( $available_tags ) ) { ?>
								<p><?php _e( 'Available tags:' ); ?></p>
								<ul role="list">
									<?php
									foreach ( $available_tags as $tag => $explanation ) {
										?>
										<li>
											<button type="button"
													class="button button-secondary"
													aria-label="<?php echo esc_attr( sprintf( $explanation, $tag ) ); ?>"
													data-added="<?php echo esc_attr( sprintf( $structure_tag_added, $tag ) ); ?>"
													data-used="<?php echo esc_attr( sprintf( $structure_tag_already_used, $tag ) ); ?>">
												<?php echo '%' . $tag . '%'; ?>
											</button>
										</li>
										<?php
									}
									?>
								</ul>
							<?php } ?>
						</div>
					</td>
				</tr>
				</tbody>
			</table>

			<h2 class="title"><?php _e('GeoDirectory Taxonomies','geodirectory'); ?></h2>
			<p><?php
				/* translators: %s: placeholder that must come at the start of the URL */
				printf( __( 'If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>%s/topics/attractions/</code>. Tags and category can not be blank.','geodirectory' ),  $url_base .  $base_slug  ); ?></p>

			<table class="form-table">
				<tr>
					<th><label for="geodirectory_category_base"><?php /* translators: prefix for category permalinks */ _e('Category base','geodirectory'); ?></label></th>
					<td><input name="geodirectory_category_base" id="geodirectory_category_base" type="text" value="<?php echo esc_attr( geodir_get_option('permalink_category_base','category') ); ?>" class="regular-text code" /></td>
				</tr>
				<tr>
					<th><label for="geodirectory_tag_base"><?php _e('Tag base','geodirectory' ); ?></label></th>
					<td><input name="geodirectory_tag_base" id="geodirectory_tag_base" type="text" value="<?php echo esc_attr(geodir_get_option('permalink_tag_base','tags')); ?>" class="regular-text code" /></td>
				</tr>
			</table>

			<?php

			// Check if any CTP has address et to not be required.
			$post_types = geodir_get_posttypes( 'array' );
			$show_missing_location_settings = false;
			if ( ! empty( $post_types ) ) {
				foreach ( $post_types as $post_type => $pt ) {
					// Check if address is required
					if ( ! geodir_cpt_requires_address( $post_type ) ) {
						$show_missing_location_settings = true;
						break;
					}
				}
			}

			// Only show the required settings if at least one CPT has the address set to not be required.
			if($show_missing_location_settings){
			?>
			<h2 class="title"><?php _e('GeoDirectory Location Base (when not set)','geodirectory'); ?></h2>
			<p><?php
				/* translators: %s: placeholder that must come at the start of the URL */
				printf( __( 'When the address field is NOT be required, the location URL slug must have a replacement. For example: <code>%s/%s/%s/CITY-BASE-SLUG/sample-place/</code>','geodirectory' ),  $url_base .  $base_slug,$default_location->country_slug,$default_location->region_slug );
				?></p>
			<p><code><?php _e('Other suggestions: find, online, virtual, go, see','geodirectory') ?></code></p>

			<table class="form-table">
				<tr>
					<th><label for="geodirectory_missing_country_base"><?php /* translators: prefix for country base permalinks */ _e('Country base','geodirectory'); ?></label></th>
					<td><input name="geodirectory_missing_country_base" id="geodirectory_missing_country_base" type="text" value="<?php echo esc_attr( geodir_get_option('permalink_missing_country_base','global') ); ?>" class="regular-text code" /></td>
				</tr>
				<tr>
					<th><label for="geodirectory_missing_region_base"><?php /* translators: prefix for region base permalinks */ _e('Region base','geodirectory'); ?></label></th>
					<td><input name="geodirectory_missing_region_base" id="geodirectory_missing_region_base" type="text" value="<?php echo esc_attr( geodir_get_option('permalink_missing_region_base','discover') ); ?>" class="regular-text code" /></td>
				</tr>
				<tr>
					<th><label for="geodirectory_missing_city_base"><?php /* translators: prefix for city base permalinks */ _e('City base','geodirectory'); ?></label></th>
					<td><input name="geodirectory_missing_city_base" id="geodirectory_missing_city_base" type="text" value="<?php echo esc_attr( geodir_get_option('permalink_missing_city_base','explore') ); ?>" class="regular-text code" /></td>
				</tr>

			</table>
			<?php } ?>


			<style>.form-table.gd-permalink-structure .gd-available-structure-tags li{float:left;margin-right:5px}</style>
			<script type="text/javascript">
			var gdPermalinkStructureFocused = false,
				$gdPermalinkStructure = jQuery('#geodir_permalink_structure'),
				$gdPermalinkStructureInputs = jQuery('.gd-permalink-structure input:radio'),
				$gdPermalinkCustomSelection = jQuery('#geodir_custom_selection'),
				$gdAvailableStructureTags = jQuery('.form-table.gd-permalink-structure .gd-available-structure-tags button');
			// Change permalink structure input when selecting one of the common structures.
			$gdPermalinkStructureInputs.on('change', function() {
				if ('custom' === this.value) {
					return;
				}
				$gdPermalinkStructure.val(this.value);
				// Update button states after selection.
				$gdAvailableStructureTags.each(function() {
					gdChangeStructureTagButtonState(jQuery(this));
				});
			});
			$gdPermalinkStructure.on('click input', function() {
				$gdPermalinkCustomSelection.prop('checked', true);
			});
			// Check if the permalink structure input field has had focus at least once.
			$gdPermalinkStructure.on('focus', function(event) {
				gdPermalinkStructureFocused = true;
				jQuery(this).off(event);
			});
			/**
			 * Enables or disables a structure tag button depending on its usage.
			 *
			 * If the structure is already used in the custom permalink structure,
			 * it will be disabled.
			 *
			 * @param {object} button Button jQuery object.
			 */
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
			// Check initial button state.
			$gdAvailableStructureTags.each(function() {
				gdChangeStructureTagButtonState(jQuery(this));
			});
			// Observe permalink structure field and disable buttons of tags that are already present.
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
				// Remove structure tag if already part of the structure.
				if (-1 !== permalinkStructureValue.indexOf(textToAppend)) {
					permalinkStructureValue = permalinkStructureValue.replace(textToAppend + '/', '');
					$gdPermalinkStructure.val('/' === permalinkStructureValue ? '' : permalinkStructureValue);
					// Announce change to screen readers.
					jQuery('#custom_selection_updated').text(textToAnnounce);
					// Disable button.
					gdChangeStructureTagButtonState(jQuery(this));
					return;
				}
				// Input field never had focus, move selection to end of input.
				if (!gdPermalinkStructureFocused && 0 === selectionStart && 0 === selectionEnd) {
					selectionStart = selectionEnd = permalinkStructureValue.length;
				}
				$gdPermalinkCustomSelection.prop('checked', true);
				// Prepend and append slashes if necessary.
				if ('/' !== permalinkStructureValue.substr(0, selectionStart).substr(-1)) {
					textToAppend = '/' + textToAppend;
				}
				if ('/' !== permalinkStructureValue.substr(selectionEnd, 1)) {
					textToAppend = textToAppend + '/';
				}
				// Insert structure tag at the specified position.
				$gdPermalinkStructure.val(permalinkStructureValue.substr(0, selectionStart) + textToAppend + permalinkStructureValue.substr(selectionEnd));
				// Announce change to screen readers.
				jQuery('#custom_selection_updated').text(textToAnnounce);
				// Disable button.
				gdChangeStructureTagButtonState(jQuery(this));
				// If input had focus give it back with cursor right after appended text.
				if (gdPermalinkStructureFocused && $gdPermalinkStructure[0].setSelectionRange) {
					newSelectionStart = (permalinkStructureValue.substr(0, selectionStart) + textToAppend).length;
					$gdPermalinkStructure[0].setSelectionRange(newSelectionStart, newSelectionStart);
					$gdPermalinkStructure.focus();
				}
			});
			jQuery(function($) {
				jQuery('.permalink-structure input').on("change",function() {
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
				jQuery('.permalink-structure input:checked').trigger("change",);

				jQuery('form[name="form"]').on("submit",function(e){
					$return = true;

					$permalink_structure = jQuery('#geodir_permalink_structure').val();

					// check permalinks contain post name or post id
					if($permalink_structure != ''){
						if(!$permalink_structure.includes("/%postname%") && !$permalink_structure.includes("/%post_id%")){
							alert("<?php _e('GeoDirectory permalinks must contain either `%postname%` or `%post_id%`, please check and try again.','geodirectory'); ?>");
							$return = false;
						}

					}

					// check tag base is not blank
					if(jQuery('#geodirectory_tag_base').val()==''){
						alert("<?php _e('GeoDirectory tag base can not be blank, please check and try again.','geodirectory'); ?>");
						$return = false;
					}

					// check category base
					if(jQuery('#geodirectory_category_base').val()==''){


						alert("<?php _e('GeoDirectory category base can not be blank, please check and try again.','geodirectory'); ?>");
						$return = false;

//						if($permalink_structure==''){
//							$return = false;
//						}else if($permalink_structure.split("/").length-1 < 3){
//							$return = false;
//						}
//
//						if(!$return){
//							alert("<?php //_e('GeoDirectory category base can only be blank if the GeoDirectory permalinks use more than one tag, please check and try again.','geodirectory'); ?>//");
//						}
					}

					return $return;
				});


			});
			</script>
			<?php
		}

		/**
		 * Save the settings.
		 */
		public function settings_save() {
			if ( ! is_admin() ) {
				return;
			}

			//print_r($_POST);exit;

			// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
			if ( isset( $_POST['permalink_structure'] ) ) {
				if ( function_exists( 'switch_to_locale' ) ) {
					switch_to_locale( get_locale() );
				}

				$permalink_structure = isset( $_POST['geodirectory_permalink_structure'] ) ? trim( $_POST['geodirectory_permalink_structure'] ) : '';
				if ( ! empty( $permalink_structure ) ) {
					$permalink_structure = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', $permalink_structure ) );
				}
				$permalink_structure = sanitize_option( 'permalink_structure', $permalink_structure );

				// Set permalink structure.
				geodir_set_permalink_structure( $permalink_structure );

				// taxonomy base
				if ( isset( $_POST['geodirectory_category_base'] ) ) {
					$category_base = sanitize_title_with_dashes($_POST['geodirectory_category_base']);
					geodir_update_option('permalink_category_base',$category_base);
				}
				if ( isset( $_POST['geodirectory_tag_base'] ) ) {
					$tag_base = !empty($_POST['geodirectory_tag_base']) ? sanitize_title_with_dashes($_POST['geodirectory_tag_base']) : 'tags';
					geodir_update_option('permalink_tag_base',$tag_base);
				}

				// Missing location base
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
		}
	}

endif;

return new GeoDir_Admin_Permalink_Settings();
