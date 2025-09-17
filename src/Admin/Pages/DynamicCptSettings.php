<?php

namespace AyeCode\GeoDirectory\Admin\Pages;

use AyeCode\GeoDirectory\Admin\Settings\SettingsPersistenceManager;
use AyeCode\GeoDirectory\Admin\Utils\SortFieldFactory;
use AyeCode\GeoDirectory\Admin\Utils\TabFieldFactory;
use AyeCode\SettingsFramework\Settings_Framework;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dynamically builds and manages the settings page for a specific Custom Post Type.
 *
 * This class extends the Settings_Framework and is responsible for defining
 * the structure of the settings page and handling the persistence of those
 * settings by delegating to a dedicated manager.
 */
final class DynamicCptSettings extends Settings_Framework {

	/**
	 * @var \WP_Post_Type The WP_Post_Type object for the current CPT.
	 */
	private $cpt;

	/**
	 * @var string The slug of the current CPT (e.g., 'gd_place').
	 */
	private $cpt_slug;

	/**
	 * @var Settings_Persistence_Manager Manages the saving and retrieving of all settings data.
	 */
	private SettingsPersistenceManager $persistence_manager;

	public function __construct( $cpt_slug, $cpt_object ) {
		$this->cpt_slug = $cpt_slug;
		$this->cpt      = $cpt_object;
		$this->persistence_manager = new SettingsPersistenceManager();

		$this->option_name = 'geodir_' . $cpt_slug . '_settings';
		$this->parent_slug = 'edit.php?post_type=' . $cpt_slug;
		$this->page_slug   = $cpt_slug . '-settings';
		$this->page_title  = sprintf( __( '%s Settings', 'geodirectory' ), $this->cpt->labels->singular_name );
		$this->menu_title  = __( 'Settings', 'geodirectory' );
		$this->plugin_name = '<span class="fa-stack fa-1x me-1"><i class="fas fa-circle fa-stack-2x text-light"></i><i style="color: #ff8333 !important;" class="fas fa-globe-americas fa-stack-2x text-primary "></i></span> <span class="fw-normal fs-4"><span style="color: #ff8333 !important;">Geo</span>Directory</span>';

		parent::__construct();

		// add scripts
		add_action( 'admin_footer', [ $this, 'enqueue_sd_assets' ], 50 );
	}

	public function enqueue_sd_assets() {
		// shortcode builder
		\WP_Super_Duper::shortcode_insert_button_script();
		add_thickbox();
	}

	/**
	 * This method is completely UNCHANGED.
	 * @return array
	 */
	protected function get_config() {
		$settings_files = [
			'general' => 'config/cpt-settings/general.php',
//			'fields'  => 'config/cpt-settings/fields.php',
//			'sorting' => 'config/cpt-settings/sorting.php',
			//'tabs'    => 'config/cpt-settings/tabs.php',
		];

		$sections = [];
		$base_path = dirname( __FILE__ ) . '/../';

		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}

		// Dynamic sorting config
		$sections[] = $this->get_fields_config();

		// Dynamic sorting config
		$sections[] = $this->get_sorting_config();

		// Dynamic tabs config
		$sections[] = $this->get_tabs_config();


		return [ 'sections' => $sections ];
	}

	/**
	 * Overrides the parent framework's method to get all settings.
	 *
	 * This method now correctly returns a single, structured array containing
	 * all settings for the current CPT, which the framework will use to
	 * populate all fields across all tabs.
	 *
	 * @return array The complete settings array for the CPT.
	 */
	public function get_settings(): array {
		return $this->persistence_manager->get_all( $this->cpt_slug );
	}

	/**
	 * Overrides the parent framework's method for saving settings.
	 *
	 * The logic remains the same: delegate the save operation for a specific
	 * section to our persistence manager.
	 *
	 * @param string $section_id The ID of the tab/section being saved.
	 * @param array $data The sanitized form data for that section.
	 * @return bool True on success, false on failure.
	 */
	public function save_settings( $new_settings = array() ): bool {
		return $this->persistence_manager->save_all( $this->cpt_slug, $new_settings );
	}

	private function get_fields_config(): array {
		global $wpdb;
		$post_type = $this->cpt_slug;
		// Start with a base configuration. You can still keep this in a file if you want.
		$base_path = dirname( __FILE__ ) . '/../config/cpt-settings/fields.php';
		$config = include( $base_path );


//		$config['templates'][] =[
//			'group_title' =>  __( 'Custom Fields', 'geodirectory' ),
//			'options' => $custom_options
//		];


		/**
		 * Filters the final tabs configuration array for a specific post type.
		 * This allows addons to easily modify the tab builder settings.
		 *
		 * @param array  $config    The tabs configuration array.
		 * @param string $cpt_slug  The current post type slug.
		 */
		return apply_filters( 'geodir_cpt_settings_fields_config', $config, $this->cpt_slug );
	}


	/**
	 * Generates the dynamic configuration for the 'Tabs' settings section.
	 *
	 * This method builds the configuration array for the tab builder,
	 * allowing for different fields or defaults based on the current post type.
	 *
	 * @return array The configuration array for the 'tabs' section.
	 */
	private function get_sorting_config(): array {
		global $wpdb;
		$post_type = $this->cpt_slug;
		// Start with a base configuration. You can still keep this in a file if you want.
		$base_path = dirname( __FILE__ ) . '/../config/cpt-settings/sorting.php';
		$config = include( $base_path );
		$options = [];


		// preset fields

		$options[] = [
			'title' => esc_attr__('Random', 'geodirectory'),
			'id' => 'post_status',
			'icon' => 'fas fa-random',
			'description'    => __( 'Random sort (not recommended for large sites)', 'geodirectory' ),
			'fields' => SortFieldFactory::build([
				'name'=>['default' => esc_attr__('Random', 'geodirectory') ],
				'uid',
				'parent_id',
				'sort',
				'is_active',
				'field_type'=>['default' => 'random'],
				'type'=>['default' => 'post_status'],
			])
		];

		// datetime → post_date
		$options[] = [
			'title' => esc_attr__( 'Add date', 'geodirectory' ),
			'id' => 'post_date',
			'icon' => 'fas fa-calendar',
			'description' => __( 'Sort by date added', 'geodirectory' ),
			'fields' => SortFieldFactory::build([
				'name'       => ['default' => esc_attr__( 'Add date', 'geodirectory' )],
				'uid',
				'parent_id',
				'sort',
				'is_active',
				'field_type' => ['default' => 'datetime'],
				'type'       => ['default' => 'post_date'],
			]),
		];

		// bigint → comment_count
		$options[] = [
			'title' => esc_attr__( 'Review', 'geodirectory' ),
			'id' => 'comment_count',
			'icon' => 'far fa-comment-dots',
			'description' => __( 'Sort by the number of reviews', 'geodirectory' ),
			'fields' => SortFieldFactory::build([
				'name'       => ['default' => esc_attr__( 'Review', 'geodirectory' )],
				'uid',
				'parent_id',
				'sort',
				'is_active',
				'field_type' => ['default' => 'bigint'],
				'type'       => ['default' => 'comment_count'],
			]),
		];

		// float → overall_rating
		$options[] = [
			'title' => esc_attr__( 'Rating', 'geodirectory' ),
			'id' => 'overall_rating',
			'icon' => 'fas fa-star',
			'description' => __( 'Sort by the overall rating value', 'geodirectory' ),
			'fields' => SortFieldFactory::build([
				'name'       => ['default' => esc_attr__( 'Rating', 'geodirectory' )],
				'uid',
				'parent_id',
				'sort',
				'is_active',
				'field_type' => ['default' => 'float'],
				'type'       => ['default' => 'overall_rating'],
			]),
		];

		// text → post_title
		$options[] = [
			'title' => esc_attr__( 'Title', 'geodirectory' ),
			'id' => 'post_title',
			'icon' => 'fas fa-sort-alpha-up',
			'description' => __( 'Sort alphabetically by title', 'geodirectory' ),
			'fields' => SortFieldFactory::build([
				'name'       => ['default' => esc_attr__( 'Title', 'geodirectory' )],
				'uid',
				'parent_id',
				'sort',
				'is_active',
				'field_type' => ['default' => 'text'],
				'type'       => ['default' => 'post_title'],
			]),
		];

		// Standard Fields
		$table_name = geodirectory()->tables->get( 'custom_fields' );
		$standard = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE post_type = %s AND data_type != %s AND cat_sort = '1' ORDER BY sort_order ASC",
				$post_type,
				'TEXT'
			),
			ARRAY_A // Return as an array of associative arrays
		);

		if(!empty($standard)){
			foreach ($standard as $key => $result) {

				$options[] = [
					'title' => esc_attr($result['frontend_title']),
					'id' => esc_attr($result['htmlvar_name']),
					'icon' => esc_attr($result['field_icon']),
					'fields' => SortFieldFactory::build([
						'name'       => ['default' => esc_attr($result['frontend_title'])],
						'uid',
						'parent_id',
						'sort',
						'is_active',
						'data_type' => ['default' => esc_attr($result['data_type'])],
						'field_type' => ['default' => esc_attr($result['field_type'])],
						'type'       => ['default' => esc_attr($result['htmlvar_name'])],
					]),
				];
			}
		}

		if(!empty($options)){
			$config['templates'][] =[
//				'group_title' =>  __( 'Standard Fields', 'geodirectory' ), // title not needed as only one section
				'options' => $options
			];
		}



		/**
		 * Filters the final tabs configuration array for a specific post type.
		 * This allows addons to easily modify the tab builder settings.
		 *
		 * @param array  $config    The tabs configuration array.
		 * @param string $cpt_slug  The current post type slug.
		 */
		return apply_filters( 'geodir_cpt_settings_sort_config', $config, $this->cpt_slug );
	}


	/**
	 * Generates the dynamic configuration for the 'Tabs' settings section.
	 *
	 * This method builds the configuration array for the tab builder,
	 * allowing for different fields or defaults based on the current post type.
	 *
	 * @return array The configuration array for the 'tabs' section.
	 */
	private function get_tabs_config(): array {
		global $wpdb;
		$post_type = $this->cpt_slug;
		// Start with a base configuration. You can still keep this in a file if you want.
		$base_path = dirname( __FILE__ ) . '/../config/cpt-settings/tabs.php';
		$config = include( $base_path );



		// Standard Fields
		$standard_options = [];
		$table_name = geodirectory()->tables->get( 'custom_fields' );
		$standard = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE post_type = %s ORDER BY sort_order ASC",
				$post_type
			),
			ARRAY_A // Return as an array of associative arrays
		);

		if(!empty($standard)){
			foreach ($standard as $key => $result) {
				$standard_options[] = [
					'title' => esc_attr($result['admin_title']),
					'id' => esc_attr($result['htmlvar_name']),
					'icon' => esc_attr($result['field_icon']),
					'fields' => TabFieldFactory::build([
						'tab_name'=>['default' => esc_attr($result['admin_title'])],
						'tab_icon'=>['default' => esc_attr($result['field_icon'])],
						'tab_content_hidden',
						'uid',
						'parent_id',
						'tab_type'=>['default' => 'meta'],
						'type'=>['default' => esc_attr($result['htmlvar_name'])],
					])
				];
			}
		}

		if(!empty($standard_options)){
			$config['templates'][] =[
				'group_title' =>  __( 'Standard Fields', 'geodirectory' ),
				'options' => $standard_options
			];
		}


		// Predefined
		$predefined_options = [];
		// Fieldset
		$predefined_options[] = [
			'title' => esc_attr__('Fieldset (used as empty container)', 'geodirectory'),
			'id' => 'fieldset',
			'icon' => 'fas fa-minus',
			'fields' => TabFieldFactory::build([
				'tab_name'=>['default' => esc_attr__('Fieldset', 'geodirectory')],
				'tab_icon'=>['default' => 'fas fa-minus'],
				'tab_content_hidden',
				'uid',
				'tab_type'=>['default' => 'fieldset'],
				'type'=>['default' => 'fieldset'],
			])
		];
		// reviews
		$predefined_options[] = [
			'title' => esc_attr__('Reviews', 'geodirectory'),
			'id' => 'reviews',
			'icon' => 'fas fa-comments',
			'fields' => TabFieldFactory::build([
				'tab_name'=>['default' => esc_attr__('Reviews', 'geodirectory')],
				'tab_icon'=>['default' => 'fas fa-comments'],
				'tab_content_hidden',
				'uid',
				'tab_type'=>['default' => 'standard'],
				'type'=>['default' => 'reviews'],
			])
		];
		// Map
		$predefined_options[] = [
			'title' => esc_attr__('Map', 'geodirectory'),
			'id' => 'post_map',
			'icon' => 'fas fa-globe-americas',
			'fields' => TabFieldFactory::build([
				'tab_name'=>['default' => esc_attr__('Map', 'geodirectory')],
				'tab_icon'=>['default' => 'fas fa-globe-americas'],
				'tab_content_hidden' =>[
					'default' => '[gd_map width="100%" height="425px" maptype="ROADMAP" zoom="0" map_type="post" map_directions="1"]',
				],
				'uid',
				'tab_type'=>['default' => 'standard'],
				'type'=>['default' => 'post_map'], // tab_key
			])
		];
		// Photos
		$predefined_options[] = [
			'title' => esc_attr__('Photos', 'geodirectory'),
			'id' => 'post_images',
			'icon' => 'fas fa-image',
			'fields' => TabFieldFactory::build([
				'tab_name'=>['default' => esc_attr__('Photos', 'geodirectory')],
				'tab_icon'=>['default' => 'fas fa-image'],
				'tab_content_hidden' =>[
					'default' => '[gd_post_images type="gallery" ajax_load="1" slideshow="1" show_title="1" animation="slide" controlnav="1" link_to="lightbox"]',
				],
				'uid',
				'tab_type'=>['default' => 'standard'],
				'type'=>['default' => 'post_images'], // tab_key
			])
		];
		$config['templates'][] =[
			'group_title' =>  __( 'Predefined Fields', 'geodirectory' ),
			'options' => $predefined_options
		];



		// Custom
		//@todo we need to make the shortcode inserter work with the JS so it does not change the url #hash
//		tb_show(
//			'My Modal Title',          // Title
//			'#TB_inline?width=100%&amp;height=550&amp;inlineId=super-duper-content-ajaxed', // Content
//			false                      // Image group (for galleries)
//		);

		$custom_options[] = [
			'title' => esc_attr__('Shortcode', 'geodirectory'),
			'id' => 'shortcode',
			'icon' => 'fas fa-cubes',
			'fields' => TabFieldFactory::build([
				'tab_name'=>['default' => esc_attr__('Shortcode', 'geodirectory')],
				'tab_icon'=>['default' => 'fas fa-cubes'],
				'tab_content' =>[
					'label' => esc_attr__('Contentx', 'geodirectory').'<button class="btn btn-sm btn-primary" onclick="sd_ajax_get_picker(\'tab_content\')">Insert Shortcode</button>',//\WP_Super_Duper_Shortcode_Inserter::shortcode_button( $id = '', $search_for_id = '' ), //sd_ajax_get_picker
					'placeholder' => esc_attr__('Add shortcode or custom HTML here', 'geodirectory')
				],
				'uid',
				'tab_type'=>['default' => 'shortcode'],
				'type'=>['default' => 'shortcode'],
			])
		];
		$config['templates'][] =[
			'group_title' =>  __( 'Custom Fields', 'geodirectory' ),
			'options' => $custom_options
		];

		/**
		 * Filters the final tabs configuration array for a specific post type.
		 * This allows addons to easily modify the tab builder settings.
		 *
		 * @param array  $config    The tabs configuration array.
		 * @param string $cpt_slug  The current post type slug.
		 */
		return apply_filters( 'geodir_cpt_settings_tabs_config', $config, $this->cpt_slug );
	}
}
