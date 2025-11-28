<?php
/**
 * Permalink Rewrite Manager
 *
 * Manages all GeoDirectory rewrite rules and URL generation.
 * Handles post permalinks, location pages, search, author pages, and taxonomy URLs.
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Support\Hookable;

final class PermalinkRewriteManager {
	use Hookable;

	private LocationsInterface $locations;
	private Settings $settings;
	private array $rewrite_rules = [];
	public string $rewrite_rule_problem = '';

	public function __construct( LocationsInterface $locations, Settings $settings ) {
		$this->locations = $locations;
		$this->settings = $settings;
	}

	/**
	 * Register all permalink hooks.
	 */
	public function hook(): void {
		$this->on( 'init', [ $this, 'register_rewrite_tags' ], 10 );
		$this->on( 'init', [ $this, 'register_post_rewrite_rules' ], 10 );
		$this->on( 'init', [ $this, 'register_location_rewrite_rules' ], 11 );
		$this->on( 'init', [ $this, 'register_add_listing_rewrite_rules' ], 11 );
		$this->on( 'init', [ $this, 'register_search_rewrite_rules' ], 11 );
		$this->on( 'init', [ $this, 'register_author_rewrite_rules' ] );
		$this->on( 'init', [ $this, 'insert_rewrite_rules' ], 20 );
		$this->on( 'init', [ self::class, 'flush_rewrite_rules' ], 99 );
		$this->filter( 'post_type_link', [ $this, 'filter_post_url' ], 0, 4 );
		$this->filter( 'term_link', [ $this, 'filter_term_url' ], 9, 3 );
		$this->filter( 'rewrite_rules_array', [ self::class, 'arrange_rewrite_rules' ], 999, 1 );
		$this->filter( 'wp_setup_nav_menu_item', [ self::class, 'wp_setup_nav_menu_item' ], 10, 1 );
	}

	/**
	 * Register custom rewrite tags for GeoDirectory.
	 */
	public function register_rewrite_tags(): void {
		add_rewrite_tag( '%country%', '([^&]+)' );
		add_rewrite_tag( '%region%', '([^&]+)' );
		add_rewrite_tag( '%city%', '([^&]+)' );
		add_rewrite_tag( '%gd_favs%', '([^&]+)' );
		add_rewrite_tag( '%sort_by%', '([^&]+)' );
		add_rewrite_tag( '%latlon%', '((\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?))' );
		add_rewrite_tag( '%dist%', '((?=.+)(?:[1-9]\d*|0)?(?:\.\d+))' );
	}

	/**
	 * Register rewrite rules for single GeoDirectory posts.
	 */
	public function register_post_rewrite_rules(): void {
		global $wp_rewrite;

		$permalink_structure = $this->get_permalink_structure();
		$post_types = geodir_get_posttypes( 'array' );

		if ( empty( $post_types ) ) {
			return;
		}

		if ( empty( $permalink_structure ) ) {
			$permalink_structure = '/%postname%/';
		}

		$permalink_arr = explode( '/', trim( $permalink_structure, '/' ) );

		foreach ( $post_types as $cpt => $post_type ) {
			$cpt_permalink_arr = $permalink_arr;

			// Replace generic %category% with CPT-specific category
			foreach ( $cpt_permalink_arr as $key => $val ) {
				if ( $val === '%category%' ) {
					$cpt_permalink_arr[ $key ] = '%' . $cpt . 'category%';
				}
			}

			$cpt_permalink_arr = apply_filters( 'geodir_post_permalink_structure_params', $cpt_permalink_arr, $cpt, $post_type );

			// Build the regex pattern and query string
			$regex_part = '/';
			foreach ( $cpt_permalink_arr as $rvalue ) {
				if ( strpos( trim( $rvalue ), '%' ) === 0 ) {
					if ( $rvalue === '%post_id%' ) {
						$regex_part .= '([0-9]+)/';
					} else {
						$regex_part .= '([^/]*)/';
					}
				} else {
					$regex_part .= $rvalue . '/';
				}
			}
			$regex_part .= '?';

			$regex = '^' . $post_type['rewrite']['slug'] . $regex_part;
			$redirect = 'index.php?';
			$match = 1;
			$query_vars = [];

			foreach ( $cpt_permalink_arr as $tag ) {
				// Skip custom tags
				if ( strpos( trim( $tag ), '%' ) !== 0 ) {
					continue;
				}

				$tag = trim( $tag, '%' );
				if ( $tag === 'postname' ) {
					$query_vars[] = $cpt . '=$matches[' . $match . ']';
				} else {
					$query_vars[] = $tag . '=$matches[' . $match . ']';
				}
				$match++;
			}

			if ( ! empty( $query_vars ) ) {
				$redirect .= implode( '&', $query_vars );
			}

			$after = $permalink_structure === '/%postname%/' ? 'bottom' : 'top';

			// Add rewrite rule for /attachment/
			$this->add_rewrite_rule( trim( $regex, '?^' ) . 'attachment/([^/]+)/?$', $redirect . '&attachment=$matches[' . $match . ']', 'top' );

			// Add rewrite rule for /comment-page-xx/
			$comment_regex = trim( $regex, '?^' ) . $wp_rewrite->comments_pagination_base . '-([0-9]{1,})/?$';
			$comment_redirect = $redirect . '&cpage=$matches[' . $match . ']';
			$this->add_rewrite_rule( $comment_regex, $comment_redirect, 'top' );

			// Force 404 for unnecessary slugs after post slug
			if ( substr( $regex, -3 ) === ')/?' ) {
				$regex = str_replace( '*)/?', '*)?/?$', $regex );
			}

			$this->add_rewrite_rule( $regex, $redirect, $after );

			// Allow translation/modification
			do_action( 'geodir_permalinks_post_rewrite_rule', $cpt, $post_type, $this, $regex_part, $redirect, $after );
		}
	}

	/**
	 * Register rewrite rules for location pages.
	 */
	public function register_location_rewrite_rules(): void {
		$location_slug = $this->get_location_slug();

		$this->add_rewrite_rule(
			'^' . $location_slug . '/([^/]+)/([^/]+)/([^/]+)/?',
			'index.php?pagename=' . $location_slug . '&country=$matches[1]&region=$matches[2]&city=$matches[3]',
			'top'
		);

		$this->add_rewrite_rule(
			'^' . $location_slug . '/([^/]+)/([^/]+)/?',
			'index.php?pagename=' . $location_slug . '&country=$matches[1]&region=$matches[2]',
			'top'
		);

		$this->add_rewrite_rule(
			'^' . $location_slug . '/([^/]+)/?',
			'index.php?pagename=' . $location_slug . '&country=$matches[1]',
			'top'
		);
	}

	/**
	 * Register rewrite rules for search page.
	 */
	public function register_search_rewrite_rules(): void {
		$search_slug = $this->get_search_slug();

		$this->add_rewrite_rule(
			'^' . $search_slug . '/page/([^/]+)/?',
			'index.php?pagename=' . $search_slug . '&paged=$matches[1]',
			'top'
		);
	}

	/**
	 * Register rewrite rules for add listing pages.
	 */
	public function register_add_listing_rewrite_rules(): void {
		$post_types = geodir_get_posttypes( 'array' );

		if ( empty( $post_types ) ) {
			return;
		}

		$page_slug = $this->get_add_listing_slug();
		$rules = [];

		foreach ( $post_types as $post_type => $cpt ) {
			if ( empty( $cpt['rewrite']['slug'] ) ) {
				continue;
			}

			$cpt_slug = $cpt['rewrite']['slug'];

			$rules[ '^' . $page_slug . '/' . $cpt_slug . '/?$' ] = 'index.php?pagename=' . $page_slug . '&listing_type=' . $post_type;
			$rules[ '^' . $page_slug . '/' . $cpt_slug . '/?([0-9]{1,})/?$' ] = 'index.php?pagename=' . $page_slug . '&listing_type=' . $post_type . '&pid=$matches[1]';

			$cpt_page_slug = $this->get_add_listing_slug( $post_type );
			if ( $cpt_page_slug !== $cpt_slug ) {
				$rules[ '^' . $cpt_page_slug . '/' . $cpt_slug . '/?$' ] = 'index.php?pagename=' . $cpt_page_slug . '&listing_type=' . $post_type;
				$rules[ '^' . $cpt_page_slug . '/' . $cpt_slug . '/?([0-9]{1,})/?$' ] = 'index.php?pagename=' . $cpt_page_slug . '&listing_type=' . $post_type . '&pid=$matches[1]';
			}
		}

		$rules = apply_filters( 'geodir_get_add_listing_rewrite_rules', $rules );

		if ( ! empty( $rules ) ) {
			foreach ( $rules as $regex => $redirect ) {
				$this->add_rewrite_rule( $regex, $redirect, 'top' );
			}
		}
	}

	/**
	 * Register rewrite rules for author pages.
	 */
	public function register_author_rewrite_rules(): void {
		global $wp_rewrite;

		$post_types = geodir_get_posttypes( 'array' );
		$saves_slug_arr = [];

		if ( empty( $post_types ) ) {
			return;
		}

		$author_rewrite_base = $wp_rewrite->author_base . '/([^/]+)';

		// Get the author permalink structure
		$author_permastruct = $wp_rewrite->get_author_permastruct();
		if ( ! empty( $author_permastruct ) ) {
			$author_rewrite_base = trim( str_replace( '%author%', '([^/]+)', $author_permastruct ), '/' );
		}

		foreach ( $post_types as $post_type => $cpt ) {
			$cpt_slug = $cpt['rewrite']['slug'] ?? '';
			$saves_slug = $this->get_favs_slug( $cpt_slug );

			// Add CPT author rewrite rules
			$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $cpt_slug . '/?$', 'index.php?author_name=$matches[1]&post_type=' . $post_type, 'top' );
			$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $cpt_slug . '/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&post_type=' . $post_type . '&paged=$matches[2]', 'top' );

			// Favorites rules (only add once per unique slug)
			if ( ! isset( $saves_slug_arr[ $saves_slug ] ) ) {
				$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $saves_slug . '/?$', 'index.php?author_name=$matches[1]&gd_favs=1' );
				$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $saves_slug . '/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&gd_favs=1&paged=$matches[2]', 'top' );
			}

			$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $saves_slug . '/' . $cpt_slug . '/?$', 'index.php?author_name=$matches[1]&gd_favs=1&post_type=' . $post_type, 'top' );
			$this->add_rewrite_rule( '^' . $author_rewrite_base . '/' . $saves_slug . '/' . $cpt_slug . '/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&gd_favs=1&post_type=' . $post_type . '&paged=$matches[2]', 'top' );

			// Allow translation/modification
			do_action( 'geodir_permalinks_author_rewrite_rule', $post_type, $cpt, $this, $cpt_slug, $saves_slug, $saves_slug_arr );

			$saves_slug_arr[ $saves_slug ] = $saves_slug;
		}
	}

	/**
	 * Insert all accumulated rewrite rules into WordPress.
	 */
	public function insert_rewrite_rules(): void {
		if ( empty( $this->rewrite_rules ) ) {
			return;
		}

		// Sort rules by priority
		usort( $this->rewrite_rules, [ $this, 'sort_rewrites' ] );

		foreach ( $this->rewrite_rules as $rule ) {
			add_rewrite_rule( $rule['regex'], $rule['redirect'], $rule['after'] );
		}
	}

	/**
	 * Sort rewrite rules by priority (most specific first).
	 *
	 * @param array $b First rule.
	 * @param array $a Second rule.
	 * @return int Sort order.
	 */
	private function sort_rewrites( array $b, array $a ): int {
		if ( $a['count'] === $b['count'] ) {
			return 0;
		}
		return ( $a['count'] < $b['count'] ) ? -1 : 1;
	}

	/**
	 * Add a rewrite rule to the queue.
	 *
	 * @param string $regex Regular expression to match.
	 * @param string $redirect Query string redirect.
	 * @param string $after Priority (top/bottom).
	 */
	public function add_rewrite_rule( string $regex, string $redirect, string $after = '' ): void {
		// Check for duplicate rules
		if ( isset( $this->rewrite_rules[ $regex ] ) ) {
			$parts = explode( '/([^/]*)/?$', $regex );

			if ( count( $parts ) === 2 && $this->get_permalink_structure() === '' ) {
				// Allow duplicate for empty permalink structure
			} else {
				$this->rewrite_rule_problem = $regex;
				add_action( 'admin_notices', [ $this, 'rewrite_rule_problem_notice' ] );
			}
		}

		// Calculate priority score
		$static_sections = 0;
		$sections = explode( '/', str_replace( '^/', '', $regex ) );

		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( substr( $section, 0, 1 ) !== '(' ) {
					$static_sections++;
				}
			}
		}

		$count = ( 10 * count( explode( '/', str_replace( [ '([^/]+)', '([^/]*)' ], '', $regex ) ) ) )
		         - ( substr_count( $regex, '([^/]+)' ) + substr_count( $regex, '([^/]*)' ) )
		         + ( $static_sections * 11 )
		         + ( substr( $regex, -3 ) === '/?$' ? 1 : 0 );

		$this->rewrite_rules[ $regex ] = [
			'regex'    => $regex,
			'redirect' => $redirect,
			'after'    => $after,
			'count'    => $count,
		];
	}

	/**
	 * Display admin notice for rewrite rule problems.
	 */
	public function rewrite_rule_problem_notice(): void {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'GeoDirectory permalink error: the following rule appears twice:', 'geodirectory' ); echo ' ' . esc_html( $this->rewrite_rule_problem ); ?></p>
			<p><?php esc_html_e( 'Try making the GeoDirectory permalinks more unique.', 'geodirectory' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Filter post URLs to use GeoDirectory permalink structure.
	 *
	 * @param string  $post_link Post URL.
	 * @param object  $post_obj  Post object.
	 * @param bool    $leavename Whether to leave postname placeholder.
	 * @param bool    $sample    Whether this is a sample permalink.
	 * @return string Filtered post URL.
	 */
	public function filter_post_url( string $post_link, object $post_obj, bool $leavename, bool $sample ): string {
		global $gd_post;

		// Only modify GeoDirectory post types
		if ( ! geodir_is_gd_post_type( $post_obj->post_type ) ) {
			return $post_link;
		}

		// Don't modify draft/pending posts
		if ( isset( $post_obj->post_status ) && in_array( $post_obj->post_status, [ 'draft', 'pending', 'auto-draft', 'future' ] ) ) {
			return $post_link;
		}

		// Check if pretty permalinks are enabled
		$permalink_structure = apply_filters( 'geodir_post_permalink_structure', $this->get_permalink_structure(), $post_obj->post_type );
		if ( strpos( $permalink_structure, '%postname%' ) === false || empty( $permalink_structure ) ) {
			return $post_link;
		}

		// Update gd_post if needed
		$correct_post = isset( $gd_post->ID ) && $post_obj->ID === $gd_post->ID;
		if ( ! $correct_post ) {
			$orig_post = $gd_post;
			$gd_post = geodir_get_post_info( $post_obj->ID );
		}

		if ( empty( $gd_post ) ) {
			if ( isset( $orig_post ) ) {
				$gd_post = $orig_post;
			}
			return $post_link;
		}

		// Get full GD post info if needed
		if ( ! isset( $gd_post->default_category ) ) {
			$gd_post = geodir_get_post_info( $gd_post->ID );
		}

		if ( empty( $gd_post ) ) {
			if ( isset( $orig_post ) ) {
				$gd_post = $orig_post;
			}
			return $post_link;
		}

		// Handle revisions
		if ( $gd_post->post_type === 'revision' ) {
			$gd_post->post_type = get_post_type( wp_get_post_parent_id( $gd_post->ID ) );
		}

		// Get base URL without location filters
		if ( function_exists( 'geodir_location_geo_home_link' ) ) {
			remove_filter( 'home_url', 'geodir_location_geo_home_link', 100000 );
		}

		$permalink = trailingslashit( home_url() );

		if ( function_exists( 'geodir_location_geo_home_link' ) ) {
			add_filter( 'home_url', 'geodir_location_geo_home_link', 100000, 2 );
		}

		// Add CPT slug
		$post_types = geodir_get_posttypes( 'array' );
		$cpt_slug = $post_types[ $gd_post->post_type ]['rewrite']['slug'];
		$cpt_slug = apply_filters( 'geodir_post_permalink_structure_cpt_slug', $cpt_slug, $gd_post, $post_link );

		$permalink .= $cpt_slug . $permalink_structure;

		// Replace location tags
		if ( strpos( $permalink, '%country%' ) !== false ) {
			$locations = $this->get_post_location_slugs( $gd_post );
			$permalink = str_replace( '%country%', $locations->country_slug ?? geodir_get_option( 'permalink_missing_country_base', 'global' ), $permalink );
		}

		if ( strpos( $permalink, '%region%' ) !== false ) {
			$locations = $locations ?? $this->get_post_location_slugs( $gd_post );
			$permalink = str_replace( '%region%', $locations->region_slug ?? geodir_get_option( 'permalink_missing_region_base', 'discover' ), $permalink );
		}

		if ( strpos( $permalink, '%city%' ) !== false ) {
			$locations = $locations ?? $this->get_post_location_slugs( $gd_post );
			$permalink = str_replace( '%city%', $locations->city_slug ?? geodir_get_option( 'permalink_missing_city_base', 'explore' ), $permalink );
		}

		// Replace category tag
		if ( strpos( $permalink, '%category%' ) !== false ) {
			$term = null;

			if ( is_admin() && isset( $_POST['default_category'] ) && $_POST['default_category'] ) {
				$term = get_term_by( 'id', absint( $_POST['default_category'] ), $gd_post->post_type . 'category' );
			} elseif ( isset( $gd_post->default_category ) && $gd_post->default_category ) {
				$term = get_term_by( 'id', absint( $gd_post->default_category ), $gd_post->post_type . 'category' );
			} elseif ( isset( $gd_post->post_category ) && $gd_post->post_category ) {
				$cat_id = explode( ',', trim( $gd_post->post_category, ',' ) );
				$cat_id = ! empty( $cat_id ) ? absint( $cat_id[0] ) : 0;

				if ( $cat_id ) {
					$term = get_term_by( 'id', $cat_id, $gd_post->post_type . 'category' );
				}
			}

			if ( ! empty( $term ) && $term->slug ) {
				$term = apply_filters( 'geodir_post_url_filter_term', $term, $gd_post );
				$permalink = str_replace( '%category%', $term->slug, $permalink );
			}
		}

		// Replace postname tag
		if ( ! $leavename && strpos( $permalink, '%postname%' ) !== false ) {
			$permalink = str_replace( '%postname%', $gd_post->post_name, $permalink );
		}

		// Replace post_id tag
		if ( strpos( $permalink, '%post_id%' ) !== false ) {
			$permalink = str_replace( '%post_id%', (string) $gd_post->ID, $permalink );
		}

		// Restore original gd_post if needed
		if ( isset( $orig_post ) ) {
			$gd_post = $orig_post;
		}

		return $permalink;
	}

	/**
	 * Remove parent category slug from term URLs.
	 *
	 * @param string $termlink Term URL.
	 * @param object $term     Term object.
	 * @param string $taxonomy Taxonomy name.
	 * @return string Filtered term URL.
	 */
	public function filter_term_url( string $termlink, object $term, string $taxonomy ): string {
		if ( ! geodir_is_gd_taxonomy( $taxonomy ) ) {
			return $termlink;
		}

		// Ensure we have full term object
		if ( ! empty( $term ) && is_object( $term ) && ! isset( $term->parent ) && ! empty( $term->term_id ) ) {
			$_term = get_term( $term->term_id );

			if ( ! empty( $_term ) && ! is_wp_error( $_term ) ) {
				$term = $_term;
			}
		}

		// Remove parent slug if present
		if ( ! empty( $term->parent ) ) {
			$parent = $this->get_term_parent_info( $term->parent, $taxonomy );
			$parent_slug = $parent->slug ?? '';

			if ( $parent_slug ) {
				$termlink = str_replace( '/' . $parent_slug . '/', '/', $termlink );
			}
		}

		return $termlink;
	}

	/**
	 * Get parent term info recursively.
	 *
	 * @param int    $term_id  Term ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $slug     Accumulated slug.
	 * @return object|null Parent term object.
	 */
	private function get_term_parent_info( int $term_id, string $taxonomy, string $slug = '' ): ?object {
		$parent = get_term( $term_id, $taxonomy );

		if ( empty( $parent ) || is_wp_error( $parent ) ) {
			return null;
		}

		if ( $slug ) {
			$parent->slug = $parent->slug . '/' . $slug;
		}

		if ( ! empty( $parent->parent ) ) {
			return $this->get_term_parent_info( $parent->parent, $taxonomy, $parent->slug );
		}

		return $parent;
	}

	/**
	 * Get post location slugs.
	 *
	 * @param object $gd_post GeoDirectory post object with location data.
	 * @return LocationData Location data object.
	 */
	private function get_post_location_slugs( object $gd_post ): object {
		return apply_filters( 'geodir_post_permalinks', $this->locations->get_for_post( $gd_post ), $gd_post );
	}

	/**
	 * Arrange rewrite rules to fix paged, feed permalinks.
	 *
	 * @param array $rules Rewrite rules array.
	 * @return array Modified rewrite rules.
	 */
	public static function arrange_rewrite_rules( array $rules ): array {
		$post_types = geodir_get_posttypes( 'names' );
		$post_type_slugs = [];

		// Get post type slugs
		foreach ( $rules as $regex => $query ) {
			if ( strpos( $query, 'index.php?post_type=' ) !== 0 ) {
				continue;
			}

			foreach ( $post_types as $post_type ) {
				if ( strpos( $query, 'index.php?post_type=' . $post_type . '&' ) === 0 ) {
					$_regex = explode( '/', $regex );
					$slug = ! empty( $_regex[0] ) ? str_replace( '^', '', $_regex[0] ) : '';

					if ( ! empty( $slug ) && ! in_array( $slug, $post_type_slugs ) ) {
						$post_type_slugs[ $post_type ] = $_regex[0];
					}
				}
			}
		}

		if ( empty( $post_type_slugs ) ) {
			return $rules;
		}

		$_post_type_slugs = array_flip( $post_type_slugs );
		$post_type_slugs = array_unique( array_values( $post_type_slugs ) );

		$_rules = $rules;
		foreach ( $rules as $regex => $query ) {
			// Skip API rules
			if ( isset( $_rules[ $regex ] ) && strpos( $query, '&geodir-api=' ) !== false ) {
				unset( $_rules[ $regex ] );
				continue;
			}

			// Remove CPT rules for attachments/embeds that don't match the CPT
			foreach ( $post_type_slugs as $slug ) {
				if ( isset( $_rules[ $regex ] ) && ( strpos( $regex, '^' . $slug . '/' ) === 0 || strpos( $regex, $slug . '/' ) === 0 ) && ( strpos( $query, '?attachment=' ) !== false || strpos( $query, '&attachment=' ) !== false || strpos( $query, '&tb=1' ) !== false || strpos( $query, '&embed=true' ) !== false ) ) {
					if ( strpos( $query, '&' . $_post_type_slugs[ $slug ] . '=' ) === false && strpos( $query, '?' . $_post_type_slugs[ $slug ] . '=' ) === false ) {
						unset( $_rules[ $regex ] );
					}
				}
			}
		}

		// Force static structures first
		$ordered_rules_first = [];
		$ordered_rules_second = [];

		foreach ( $_rules as $key => $value ) {
			$parts = explode( '/', $key );

			if ( count( $parts ) > 1 && strpos( $parts[0], '.' ) === false && strpos( $parts[0], '[' ) === false && strpos( $parts[1], '[' ) === false && strpos( $parts[1], '?' ) === false ) {
				$ordered_rules_first[ $key ] = $value;
			} else {
				$ordered_rules_second[ $key ] = $value;
			}
		}

		return $ordered_rules_first + $ordered_rules_second;
	}

	/**
	 * Check & flush rewrite rules if needed.
	 */
	public static function flush_rewrite_rules(): void {
		// Rank Math flush rewrite rules to generate sitemaps
		if ( class_exists( 'RankMath\\Helper' ) ) {
			if ( ! wp_doing_ajax() && ! wp_doing_cron() && get_option( 'geodir_rank_math_flush_rewrite' ) ) {
				flush_rewrite_rules();
				delete_option( 'geodir_rank_math_flush_rewrite' );
			}
		}
	}

	/**
	 * Get GeoDirectory permalink structure.
	 *
	 * @return string Permalink structure.
	 */
	private function get_permalink_structure(): string {
		return geodir_get_permalink_structure();
	}

	/**
	 * Get location page slug.
	 *
	 * @return string Location page slug.
	 */
	private function get_location_slug(): string {
		$location_slug = 'location';

		if ( $page_id = geodir_location_page_id() ) {
			if ( $slug = get_post_field( 'post_name', $page_id ) ) {
				$location_slug = $slug;
			}
		}

		return apply_filters( 'geodir_rewrite_location_slug', $location_slug );
	}

	/**
	 * Get search page slug.
	 *
	 * @return string Search page slug.
	 */
	private function get_search_slug(): string {
		$search_slug = 'search';

		if ( $page_id = geodir_search_page_id() ) {
			if ( $slug = get_post_field( 'post_name', $page_id ) ) {
				$search_slug = $slug;
			}
		}

		return apply_filters( 'geodir_rewrite_search_slug', $search_slug );
	}

	/**
	 * Get add listing page slug.
	 *
	 * @param string $post_type Post type. Default empty.
	 * @param bool   $page_uri  True to build the URI path for a page.
	 * @return string Add listing page slug.
	 */
	private function get_add_listing_slug( string $post_type = '', bool $page_uri = true ): string {
		$slug = 'add-listing';
		$_slug = '';

		if ( $post_type && ( $page_id = (int) geodir_add_listing_page_id( $post_type ) ) ) {
			$_slug = $page_uri ? get_page_uri( $page_id ) : get_post_field( 'post_name', $page_id );
		}

		if ( ! $_slug && ( $page_id = (int) geodir_add_listing_page_id() ) ) {
			$_slug = $page_uri ? get_page_uri( $page_id ) : get_post_field( 'post_name', $page_id );
		}

		if ( $_slug ) {
			$slug = strpos( $_slug, '%' ) !== false ? urldecode( $_slug ) : $_slug;
		}

		return apply_filters( 'geodir_rewrite_add_listing_slug', $slug, $post_type, $page_uri );
	}

	/**
	 * Get favorites/saves slug.
	 *
	 * @param string $cpt_slug CPT slug.
	 * @return string Favorites slug.
	 */
	private function get_favs_slug( string $cpt_slug = '' ): string {
		return apply_filters( 'geodir_rewrite_favs_slug', 'favs', $cpt_slug );
	}

	/**
	 * Filter navigation menu items to update old add listing URLs.
	 *
	 * @param object $menu_item Menu item object.
	 * @return object Filtered menu item.
	 */
	public static function wp_setup_nav_menu_item( object $menu_item ): object {
		if ( ! empty( $menu_item->type ) && $menu_item->type === 'custom' && ! empty( $menu_item->url ) && ( strpos( $menu_item->url, '?listing_type=gd_' ) || strpos( $menu_item->url, '&listing_type=gd_' ) ) && get_option( 'permalink_structure' ) ) {
			$parse_url = parse_url( $menu_item->url );

			if ( ! empty( $parse_url['query'] ) ) {
				$query_params = wp_parse_args( $parse_url['query'] );

				if ( ! empty( $query_params['listing_type'] ) ) {
					$url = geodir_add_listing_page_url( $query_params['listing_type'] );
					$args = [];

					foreach ( $query_params as $key => $value ) {
						if ( in_array( $key, [ 'listing_type', 'pid' ] ) ) {
							continue;
						}
						$args[ $key ] = $value;
					}

					if ( ! empty( $args ) ) {
						$url = add_query_arg( $args, $url );
					}

					$menu_item->url = $url;
				}
			}
		}

		return $menu_item;
	}
}
