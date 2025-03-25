<?php
/**
 * Setup menus in WP admin.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * GeoDir_Admin_Menus Class.
 */
class GeoDir_Admin_Menus {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'status_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'cpt_settings_menu' ), 10 );

		if ( apply_filters( 'geodirectory_show_addons_page', true ) ) {
			add_action( 'admin_menu', array( $this, 'addons_menu' ), 70 );
		}

		// Add endpoints custom URLs in Appearance > Menus > Pages.
		add_action( 'admin_head-nav-menus.php', array( $this, 'add_nav_menu_meta_boxes' ) );

		// Filters
		add_filter( 'add_menu_classes', array($this, 'bubble_count_number'));
	}


	/**
	 * Show the pending post counts on the CPT admin menu item.
	 *
	 * @since 2.0.0.49
	 * @param $menu
	 *
	 * @return mixed
	 */
	public function bubble_count_number( $menu )
	{

		$cpts = geodir_get_posttypes();
		$counts = array();

		// check cache
		$cache = wp_cache_get("geodir_post_counts");
		if($cache !== false){
			// we have cache so no need to count again
			$counts = $cache;
		}else{
			if(!empty($cpts )){
				foreach($cpts as $cpt){
					$post_counts = wp_count_posts($cpt, 'readable'); // let WP handel the caching
					$counts[$cpt] = isset($post_counts->pending) ? absint($post_counts->pending) : 0;
				}
			}

			// set cache
			wp_cache_set("geodir_post_counts" ,$counts);
		}



		if(!empty($counts) && !empty($menu)){
			foreach($menu as $menu_key => $menu_data){
				// check its probably a GD post type
				if(substr( $menu_data[2], 0, 22 ) === "edit.php?post_type=gd_"){
					$parts = explode("=",$menu_data[2]);
					if(!empty($parts[1]) && !empty($counts[$parts[1]])){
						$count = $counts[$parts[1]];
						if(in_array($parts[1],$cpts)){
							$menu[$menu_key][0] .= " <span class='awaiting-mod  count-$count' title='".__("Posts pending review","geodirectory")."'><span class='pending-count'>" . number_format_i18n($count) . '</span></span>';
						}
					}
				}
			}
		}

		return $menu;
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		global $menu;

		$capability = $this->admin_menu_capability( 'geodirectory' );

		// @todo we should change this to manage_geodirectory capability on install
		if ( current_user_can( $capability ) ) {
			$menu[] = array( '', 'read', 'separator-geodirectory', '', 'wp-menu-separator geodirectory' ); // WPCS: override ok.
		}

		$menu_count = apply_filters( 'geodir_admin_menu_count', '' ); //@todo this seems to make some things not work like the claim listing view link, fix before using

		add_menu_page( __( 'Geodirectory Dashboard', 'geodirectory' ), __( 'GeoDirectory', 'geodirectory' ) . $menu_count, $capability, 'geodirectory', array( $this, 'dashboard_page' ), 'dashicons-admin-site', '55.1984' );
		add_submenu_page( 'geodirectory', __( 'Geodirectory Dashboard', 'geodirectory' ), __( 'Dashboard', 'geodirectory' ), $capability, 'geodirectory', array( $this, 'dashboard_page' ) );
	}

	/**
	 * Dashboard page.
	 */
	public function dashboard_page(){
		$dashboard = GeoDir_Admin_Dashboard::instance();

		$dashboard->output();
	}

	/**
	 * Add CPT Settings menu.
	 */
	public function cpt_settings_menu(){
		// Add CPT setting to each GD CPT
		$post_types = geodir_get_option( 'post_types' );
		if(!empty($post_types)){
			foreach($post_types as $name => $cpt){
				add_submenu_page('edit.php?post_type='.$name, __('Settings', 'geodirectory'), __('Settings', 'geodirectory'), $this->admin_menu_capability( $name . '-settings' ), $name.'-settings', array( $this, 'settings_page' ) );
			}
		}
	}

	/**
	 * Add menu item.
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page( 'geodirectory', __( 'GeoDirectory settings', 'geodirectory' ),  __( 'Settings', 'geodirectory' ) , $this->admin_menu_capability( 'gd-settings' ), 'gd-settings', array( $this, 'settings_page' ) );
	}


	/**
	 * Add menu item.
	 */
	public function status_menu() {
		add_submenu_page( 'geodirectory', __( 'GeoDirectory status', 'geodirectory' ),  __( 'Status', 'geodirectory' ) , $this->admin_menu_capability( 'gd-status' ), 'gd-status', array( $this, 'status_page' ) );
	}

	/**
	 * Addons menu item.
	 */
	public function addons_menu() {
		add_submenu_page( 'geodirectory', __( 'GeoDirectory extensions', 'geodirectory' ),  __( 'Extensions', 'geodirectory' ) , $this->admin_menu_capability( 'gd-addons' ), 'gd-addons', array( $this, 'addons_page' ) );
	}




	/**
	 * Init the reports page.
	 */
	public function reports_page() {
		GeoDir_Admin_Reports::output();
	}

	/**
	 * Init the settings page.
	 */
	public function settings_page() {
		GeoDir_Admin_Settings::output();
	}



	/**
	 * Init the status page.
	 */
	public function status_page() {
		GeoDir_Admin_Status::output();
	}

	/**
	 * Init the addons page.
	 */
	public function addons_page() {
		//echo '### addons page';
		GeoDir_Admin_Addons::output();
	}

	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 */
	public function add_nav_menu_meta_boxes() {
		add_meta_box( 'geodirectory_endpoints_nav_link', __( 'GeoDirectory endpoints', 'geodirectory' ), array( $this, 'nav_menu_links' ), 'nav-menus', 'side', 'low' );
	}

	/**
	 * Output menu links.
	 */
	public function nav_menu_links() {
		// Get items from account menu.
		$endpoints = $this->get_endpoints();

		$endpoints = apply_filters( 'geodirectory_custom_nav_menu_items', $endpoints );
		?>
		<div id="geodirectory-endpoints" class="posttypediv">

			<?php

			if(!empty($endpoints['cpt_archives'])){
			?>
			<h4><?php _e('CPT Archives','geodirectory');?></h4>
			<div id="tabs-panel-geodirectory-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="geodirectory-endpoints-checklist" class="categorychecklist form-no-clear">
					<?php
					$walker = new Walker_Nav_Menu_Checklist(array());
					echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $endpoints['cpt_archives']), 0, (object) array('walker' => $walker));
					?>
				</ul>
			</div>
			<?php }


			if(!empty($endpoints['cpt_add_listing'])){
				?>
				<h4><?php _e('CPT Add Listing','geodirectory');?></h4>
				<div id="tabs-panel-geodirectory-endpoints" class="tabs-panel tabs-panel-active">
					<ul id="geodirectory-endpoints-checklist" class="categorychecklist form-no-clear">
						<?php
						$walker = new Walker_Nav_Menu_Checklist(array());
						echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $endpoints['cpt_add_listing']), 0, (object) array('walker' => $walker));
						?>
					</ul>
				</div>
			<?php }

			if(!empty($endpoints['pages'])){
				?>
				<h4><?php _e('GD Pages','geodirectory');?></h4>
				<div id="tabs-panel-geodirectory-endpoints" class="tabs-panel tabs-panel-active">
					<ul id="geodirectory-endpoints-checklist" class="categorychecklist form-no-clear">
						<?php
						$walker = new Walker_Nav_Menu_Checklist(array());
						echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $endpoints['pages']), 0, (object) array('walker' => $walker));
						?>
					</ul>
				</div>
			<?php }

			// custom additional
			foreach ($endpoints as $key => $endpoint){
				if($key != 'cpt_archives' && $key != 'cpt_add_listing' && $key != 'pages'){
					$title = ucwords( str_replace("_"," ",$key));
					?>
					<h4><?php esc_attr_e($title ,'geodirectory');?></h4>
					<div id="tabs-panel-geodirectory-endpoints" class="tabs-panel tabs-panel-active">
						<ul id="geodirectory-endpoints-checklist" class="categorychecklist form-no-clear">
							<?php
							$walker = new Walker_Nav_Menu_Checklist(array());
							echo walk_nav_menu_tree(array_map('wp_setup_nav_menu_item', $endpoints[$key]), 0, (object) array('walker' => $walker));
							?>
						</ul>
					</div>
				<?php }

			}



				?>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo admin_url( 'nav-menus.php?page-tab=all&selectall=1#geodirectory-endpoints' ); ?>" class="select-all"><?php _e( 'Select all', 'geodirectory' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'geodirectory' ); ?>" name="add-post-type-menu-item" id="submit-geodirectory-endpoints">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Get GD menu items.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_endpoints() {
		$items = array();
		$items['cpt_archives'] = array();
		$items['cpt_add_listing'] = array();
		$items['pages'] = array();
		$loop_index = 999;

		// Add the Location menu item
		$gd_location_page_id = geodir_location_page_id();
		if($gd_location_page_id){
			$item = new stdClass();
			$item->ID = $gd_location_page_id;
			$item->object_id = $gd_location_page_id;
			$item->db_id = 0;
			$item->object =  'page';
			$item->menu_item_parent = 0;
			$item->type = 'post_type';
			$item->title = __('Location page','geodirectory');
			$item->url = get_page_link($gd_location_page_id);
			$item->target = '';
			$item->attr_title = '';
			$item->classes = array('gd-menu-item');
			$item->xfn = '';

			$items['pages'][] = $item;
		}

		// Add the Search menu item
		$gd_search_page_id = geodir_search_page_id();
		if($gd_search_page_id){
			$item = new stdClass();
			$item->ID = $gd_search_page_id;
			$item->object_id = $gd_search_page_id;
			$item->db_id = 0;
			$item->object =  'page';
			$item->menu_item_parent = 0;
			$item->type = 'post_type';
			$item->title = __('Search page','geodirectory');
			$item->url = get_page_link($gd_search_page_id);
			$item->target = '';
			$item->attr_title = '';
			$item->classes = array('gd-menu-item');
			$item->xfn = '';

			$items['pages'][] = $item;
		}

		// Add the Search menu item
		$gd_tc_page_id = geodir_terms_and_conditions_page_id();
		if($gd_tc_page_id){
			$item = new stdClass();
			$item->ID = $gd_tc_page_id;
			$item->object_id = $gd_tc_page_id;
			$item->db_id = 0;
			$item->object =  'page';
			$item->menu_item_parent = 0;
			$item->type = 'post_type';
			$item->title = __('Terms and Conditions page','geodirectory');
			$item->url = get_page_link($gd_tc_page_id);
			$item->target = '';
			$item->attr_title = '';
			$item->classes = array('gd-menu-item');
			$item->xfn = '';

			$items['pages'][] = $item;
		}

		// Add CPT setting to each GD CPT
		$post_types = geodir_get_option( 'post_types' );
		if(!empty($post_types)){
			foreach($post_types as $name => $cpt){

				// item for archives
				$item = new stdClass();
				$loop_index++;

				$item->ID = $loop_index;
				$item->object_id = $loop_index;
				$item->db_id = 0;
				$item->object =  $name;
				$item->menu_item_parent = 0;
				$item->type = 'post_type_archive';
				$item->title = __($cpt['labels']['name'],'geodirectory');
				$item->url = get_post_type_archive_link($name);
				$item->target = '';
				$item->attr_title = '';
				$item->classes = array('gd-menu-item');
				$item->xfn = '';

				$items['cpt_archives'][$name] = $item;

				// Item for add listing
				$add_item = new stdClass();
				$loop_index++;
				$add_item->ID = $loop_index;
				$add_item->object_id = $loop_index;
				$add_item->db_id = 0;
				$add_item->object = 'page';
				$add_item->menu_item_parent = 0;
				$add_item->type = 'custom';
				$add_item->title = sprintf( __('Add %s', 'geodirectory'), __($cpt['labels']['singular_name'],'geodirectory') );
				$add_item->url = geodir_add_listing_page_url( $name );
				$add_item->target = '';
				$add_item->attr_title = '';
				$add_item->classes = array('gd-menu-item','geodir-location-switcher');
				$add_item->xfn = '';

				$items['cpt_add_listing'][$name] = $add_item;
			}
		}


		return apply_filters( 'geodirectory_menu_items', $items,$loop_index );
	}

	/*
	 * Get the capability required for GeoDirectory menu to be displayed to the user.
	 *
	 * @since 2.1.0.16
	 *
	 * @param  string $menu_slug The menu slug.
	 * @return string The capability.
	 */
	public function admin_menu_capability( $menu_slug = '' ) {
		$capability = 'manage_options';

		/*
		 * Filter the capability required for GeoDirectory menu to be displayed to the user.
		 *
		 * @since 2.1.0.16
		 *
		 * @param string $capability The capability required for GeoDirectory menu to be displayed to the user.
		 * @param string $menu_slug The menu slug.
		 */
		return apply_filters( 'geodirectory_admin_menu_capability', $capability, $menu_slug );
	}
}

return new GeoDir_Admin_Menus();
