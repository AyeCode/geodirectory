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
			echo wpautop( __( 'These settings control the permalinks used specifically for GeoDirectory CPTs', 'geodirectory' ) );

			// Get shop page
			$base_slug    = geodir_get_ctp_slug( 'gd_place' );
			$default_location = geodir_get_default_location();
			//print_r($default_location);


			$structures = array(
				__( 'Default', 'geodirectory' )     => array(
					'value' => '',
					'example' => esc_html( home_url() ).'/'. esc_html( $base_slug ).'/sample-place/'
				),
				__( 'Full location', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%postname%/',
					'example' => esc_html( home_url() ) . '/' . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) . 'sample-place/'
				),
				__( 'Full location with category', 'geodirectory' )     => array(
					'value' => '/%country%/%region%/%city%/%category%/%postname%/',
					'example' => esc_html( home_url() ) . '/' . trailingslashit( $base_slug ) .
					             trailingslashit( $default_location->country_slug ).
					             trailingslashit( $default_location->region_slug ).
					             trailingslashit( $default_location->city_slug ) .
					             'attractions/' .
					             'sample-place/'
				)
			);

			$is_default = false;

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
								<?php echo esc_html( home_url() ); ?>/?gd_place=sample-place
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
						<code><?php echo esc_html( home_url() ).'/%cpt_slug%'; ?></code>
						<input name="geodirectory_permalink_structure" id="geodir_permalink_structure" type="text"
						       value="<?php echo esc_attr( $this->permalinks ? trailingslashit( $this->permalinks) : '' ); ?>"
						       class="regular-text code">
						<br /><br />
						<span class="description">
							<?php _e( 'Available tags:', 'geodirectory' ); ?>
						</span>
						<code>%country%</code> , <code>%region%</code> , <code>%city%</code> , <code>%category%</code> , <code>%postname%</code> , <code>%post_id%</code>
					</td>
				</tr>
				</tbody>
			</table>
			<script type="text/javascript">
				jQuery(function () {
					jQuery('input.gdtog').change(function () {
						jQuery('#geodir_permalink_structure').val(jQuery(this).val());
					});
					jQuery('.permalink-structure input').change(function () {
						jQuery('.gd-permalink-structure').find('code.non-default-example, code.default-example').hide();
						if (jQuery(this).val()) {
							jQuery('.gd-permalink-structure code.non-default-example').show();
							jQuery('.gd-permalink-structure input').removeAttr('disabled');
						} else {
							jQuery('.gd-permalink-structure code.default-example').show();
							jQuery('.gd-permalink-structure input:eq(0)').click();
							jQuery('.gd-permalink-structure input').attr('disabled', 'disabled');
						}
					});
					jQuery('.permalink-structure input:checked').change();
					jQuery('#geodir_permalink_structure').focus(function () {
						jQuery('#geodir_custom_selection').click();
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

			//print_r($_POST);

			// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
			if ( isset( $_POST['permalink_structure'] ) ) {
				if ( function_exists( 'switch_to_locale' ) ) {
					switch_to_locale( get_locale() );
				}

				$gd_permalink = trim($_POST['geodirectory_permalink_structure']); // @todo we should write a function to sanatize the permalink string
				geodir_update_option( 'permalink_structure', $gd_permalink );

				if ( function_exists( 'restore_current_locale' ) ) {
					restore_current_locale();
				}
			}
		}
	}

endif;

return new GeoDir_Admin_Permalink_Settings();
