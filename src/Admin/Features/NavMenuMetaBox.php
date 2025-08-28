<?php
/**
 * GeoDirectory Nav Menu Meta Box Feature
 *
 * @package GeoDirectory\Admin\Features
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Features;

/**
 * Adds the "GeoDirectory endpoints" meta box to the Appearance > Menus screen.
 *
 * @since 3.0.0
 */
final class NavMenuMetaBox {
	/**
	 * Registers the necessary WordPress hooks for this feature.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// This specific hook runs only on the nav menus admin page.
		add_action( 'admin_head-nav-menus.php', [ $this, 'add_meta_box' ] );
	}

	/**
	 * Adds the WordPress meta box.
	 *
	 * @return void
	 */
	public function add_meta_box(): void {
		add_meta_box(
			'geodirectory_endpoints_nav_link',
			__( 'GeoDirectory endpoints', 'geodirectory' ),
			[ $this, 'render' ], // The render method will output the HTML.
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Renders the HTML content for the meta box.
	 *
	 * @return void
	 * @todo This HTML/PHP mix should be moved into a dedicated view file in `src/admin/views/`.
	 */
	public function render(): void {
		$endpoints = $this->get_endpoints();
		?>
		<div id="geodirectory-endpoints" class="posttypediv">
			<?php
			// We can simplify this rendering logic later.
			foreach ( $endpoints as $key => $list ) {
				if ( empty( $list ) ) {
					continue;
				}
				$title = ucwords( str_replace( '_', ' ', $key ) );
				echo '<h4>' . esc_html( $title ) . '</h4>';
				echo '<div class="tabs-panel tabs-panel-active"><ul class="categorychecklist form-no-clear">';
				$walker = new \Walker_Nav_Menu_Checklist( [] );
				echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $list ), 0, (object) [ 'walker' => $walker ] );
				echo '</ul></div>';
			}
			?>
			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php?page-tab=all&selectall=1#geodirectory-endpoints' ) ); ?>" class="select-all"><?php esc_html_e( 'Select all' ); ?></a>
				</span>
				<span class="add-to-menu">
					<input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-post-type-menu-item" id="submit-geodirectory-endpoints">
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Builds the array of nav menu endpoint items.
	 *
	 * @return array
	 * @todo Refactor to inject dependencies instead of using global functions.
	 */
	private function get_endpoints(): array {
		$items      = [
			'cpt_archives'    => [],
			'cpt_add_listing' => [],
			'pages'           => [],
		];
		$loop_index = 999; // A high number to avoid conflicts.

		if ( function_exists( 'geodir_location_page_id' ) && ( $id = (int) geodir_location_page_id() ) ) {
			$items['pages'][] = $this->nav_item_from_page( $id, __( 'Location page', 'geodirectory' ) );
		}
		// @todo Add other geodir_*_page_id() checks here.

		$post_types = function_exists( 'geodir_get_option' ) ? geodir_get_option( 'post_types' ) : [];
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $name => $cpt ) {
				// CPT Archive item
				$loop_index++;
				$archive_item                 = new \stdClass();
				$archive_item->ID             = $loop_index;
				$archive_item->object_id      = $loop_index;
				$archive_item->object         = $name;
				$archive_item->type           = 'post_type_archive';
				$archive_item->title          = __( $cpt['labels']['name'], 'geodirectory' );
				$archive_item->url            = get_post_type_archive_link( $name );
				$items['cpt_archives'][ $name ] = $archive_item;

				// Add Listing item
				if ( function_exists( 'geodir_add_listing_page_url' ) ) {
					$loop_index++;
					$add_listing_item                 = new \stdClass();
					$add_listing_item->ID             = $loop_index;
					$add_listing_item->object_id      = $loop_index;
					$add_listing_item->object         = 'page';
					$add_listing_item->type           = 'custom';
					$add_listing_item->title          = sprintf( __( 'Add %s', 'geodirectory' ), __( $cpt['labels']['singular_name'], 'geodirectory' ) );
					$add_listing_item->url            = geodir_add_listing_page_url( $name );
					$items['cpt_add_listing'][ $name ] = $add_listing_item;
				}
			}
		}

		return (array) apply_filters( 'geodirectory_nav_menu_items', $items, $loop_index );
	}

	/**
	 * Helper to create a nav menu item object from a WordPress page.
	 *
	 * @param int    $page_id The ID of the page.
	 * @param string $title   The desired title for the menu item.
	 *
	 * @return \stdClass The nav menu item object.
	 */
	private function nav_item_from_page( int $page_id, string $title ): \stdClass {
		$page_obj            = get_post( $page_id );
		$item                = new \stdClass();
		$item->ID            = $page_obj->ID;
		$item->object_id     = $page_obj->ID;
		$item->object        = 'page';
		$item->type          = 'post_type';
		$item->title         = $title;
		$item->url           = get_permalink( $page_obj->ID );

		// Add other properties to satisfy the nav menu walker.
		$item->db_id         = 0;
		$item->menu_item_parent = 0;
		$item->target        = '';
		$item->attr_title    = '';
		$item->classes       = [ 'gd-menu-item' ];
		$item->xfn           = '';

		return $item;
	}
}
