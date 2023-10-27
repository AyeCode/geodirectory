<?php
/**
 * GeoDirectory Settings Page/Tab
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Settings_Page', false ) ) :

/**
 * GeoDir_Settings_Page.
 */
abstract class GeoDir_Settings_Page {

	/**
	 * Setting page id.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Setting page label.
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
//		add_action( 'geodir_sections_' . $this->id, array( $this, 'output_toggle_advanced' ) );
		add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );

	}

    /**
     * Get font awesome selectbox.
     *
     * @since 2.0.0
	 * @deprecated 2.3.26
     */
	public function font_awesome_select(){
		?>
		<style>.gd-notification.lity-hide{display: none;}</style>
		<div  id="gd-font-awesome-select" class="gd-notification lity-hide noti-white">
		<select name="tab_icon" class="regular-text geodir-select" data-fa-icons="1"  tabindex="-1" aria-hidden="true" onchange="jQuery('.gd-tabs-sortable li #field_icon').filter(':visible').val(jQuery(this).val()).trigger('change');jQuery('.lity-close').trigger('click');">
			<?php
			if ( ! function_exists( 'geodir_font_awesome_array' ) ) {
				include_once( dirname( __FILE__ ) . '/../settings/data_fontawesome.php' );
			}
			echo "<option value=''>".__('None','geodirectory')."</option>";
			//$tab_icon = str_replace("fas ","",$tab->tab_icon);
			foreach ( geodir_font_awesome_array() as $key => $val ) {
				?>
				<option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php
				//selected( $tab_icon, $key );
				?>><?php echo $key ?></option>
				<?php
			}
			?>
		</select>
		</div>
		<?php

	}

	/**
	 * Get settings page ID.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get settings page label.
	 * @since 3.0.0
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Add this page to settings.
	 */
	public function add_settings_page( $pages ) {
		$pages[ $this->id ] = $this->label;

		return $pages;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		return apply_filters( 'geodir_get_settings_' . $this->id, array() );
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		return apply_filters( 'geodir_get_sections_' . $this->id, array() );
	}

	/**
	 * Detect if the advanced settings button should be shown or not.
	 *
	 * @return bool
	 */
	public function show_advanced() {
		global $current_section, $geodir_render_advanced;

		$post_type = isset( $_REQUEST['post_type'] ) ? sanitize_title( $_REQUEST['post_type'] ) : '';

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == $post_type . '-settings' ) {
			return true; // If on CPT settings then show.
		}

		$geodir_render_advanced = true;
		$show = false;
		$settings = $this->get_settings( $current_section );

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $setting ) {
				if ( isset( $setting['advanced'] ) && $setting['advanced'] ) {
					$show = true;
					break;
				}
			}
		}

		$geodir_render_advanced = false;

		return $show;
	}

	/**
	 * Output the toggle show/hide advanced settings.
	 */
	public function output_toggle_advanced(){
		global $hide_advanced_toggle;

		if($hide_advanced_toggle){ return;}

		// check if we need to show advanced or not
		if(!$this->show_advanced()){return;}


		$this->toggle_advanced_button();

	}

    /**
     * Toggle advanced button.
     *
     * @since 2.0.0
     */
	public static function toggle_advanced_button($btn_class = 'btn btn-sm btn-primary ms-auto ml-auto gd-advanced-toggle',$init = true){

		$show = geodir_get_option( 'admin_disable_advanced', false );

		if($show){return;} // don't show advanced toggle

		$text_show = __("Show Advanced","geodirectory");
		$text_hide = __("Hide Advanced","geodirectory");

		if(!$show){
			$css = "none";
			$text = $text_show;
			$toggle_CSS = '';
		}else{
			$css = "block";
			$text = $text_hide;
			$toggle_CSS = 'gda-hide';
		}
		?>
		<style>

			/*.gd-advanced-setting,#default_location_set_address_button{display: none;}*/
			/*.gd-advanced-setting.gda-show,#default_location_set_address_button.gda-show{display: block;}*/
			/*tr.gd-advanced-setting.gda-show{display: table-row;}*/
			/*li.gd-advanced-setting.gda-show{display: list-item;}*/

			/* Show Advanced */
			.gd-advanced-btn .gdat-text-show {display: block;}
			.gd-advanced-btn .gdat-text-hide {display: none;}

			/* Hide Advanced */
			.gd-advanced-btn.gda-hide .gdat-text-show {display: none;}
			.gd-advanced-btn.gda-hide .gdat-text-hide {display: block;}
		</style>

		<?php
		echo "<button class='".esc_attr( $btn_class )." gd-advanced-btn $toggle_CSS' type=\"button\"  >";
		echo "<span class='gdat-text-show'>$text_show</span>";
		echo "<span class='gdat-text-hide'>$text_hide</span>";
		echo "</button>";

		if( $init ) {
			?>
			<script>
				init_advanced_settings();
			</script>
			<?php
		}
	}

	/**
	 * Output sections.
	 */
	public function output_sections() {
		global $current_section;

		$output = '';

		$sections = $this->get_sections();

		if ( !empty( $sections ) && sizeof( $sections ) > 1 ) {
			$output .= '<ul class="subsubsub m-0 p-0	">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				$output .= '<li><a href="' . admin_url( 'admin.php?page=gd-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			$output .= '</ul>';
		}

		ob_start();

		$this->output_toggle_advanced();

		$output .= ob_get_clean();

		if ( $output ) {
			echo "<div class='clearfix d-flex align-content-center flex-wrap'>";
			echo $output;
			echo "</div>";
		}



	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		GeoDir_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings();
		GeoDir_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( 'geodir_update_options_' . $this->id . '_' . $current_section );
		}
	}
}

endif;
