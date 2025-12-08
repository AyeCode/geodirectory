<?php
/**
 * Rank Math SEO Integration
 *
 * @package GeoDirectory\Integrations\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Integrations\Seo;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Seo\MetaManager;
use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Integrates GeoDirectory with Rank Math SEO plugin.
 */
final class RankMath implements SeoIntegrationInterface {
	private VariableReplacer $replacer;
	private string $gd_page = '';

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 * @param MetaManager $meta_manager The meta manager service.
	 */
	public function __construct(
		private Settings $settings,
		private MetaManager $meta_manager
	) {}

	/**
	 * {@inheritDoc}
	 */
	public function is_active(): bool {
		return defined( 'RANK_MATH_VERSION' ) && ! $this->settings->get( 'rank_math_disable' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;
		$this->gd_page = geodir_get_page_type();

		// Variable registration
		add_action( 'rank_math/vars/register_extra_replacements', [ $this, 'register_extra_replacements' ], 20 );

		// Title and description replacement
		add_filter( 'rank_math/frontend/title', [ $this->meta_manager, 'get_title' ], 10, 1 );
		add_filter( 'rank_math/frontend/description', [ $this, 'filter_description' ], 9, 1 );

		// Breadcrumb modifications
		add_filter( 'rank_math/frontend/breadcrumb/settings', [ $this, 'breadcrumb_settings' ], 20, 1 );
		add_filter( 'rank_math/frontend/breadcrumb/items', [ $this, 'breadcrumb_links' ], 10, 2 );
		add_filter( 'rank_math/frontend/breadcrumb/main_term', [ $this, 'breadcrumb_main_term' ], 20, 2 );

		// Page title filters
		add_filter( 'the_title', [ $this->meta_manager, 'output_title' ], 10, 2 );
		add_filter( 'get_the_archive_title', [ $this->meta_manager, 'output_title' ], 10 );
	}

	/**
	 * Registers GD variables for Rank Math SEO.
	 *
	 * @return void
	 */
	public function register_extra_replacements(): void {
		$pages = [ 'location', 'search', 'post_type', 'archive', 'add-listing', 'single' ];

		$variables = [];
		foreach ( $pages as $page ) {
			$_variables = VariableReplacer::get_variables( $page );

			if ( ! empty( $_variables ) ) {
				foreach ( $_variables as $var => $help ) {
					if ( empty( $variables[ $var ] ) ) {
						$variables[ $var ] = $help;
					}
				}
			}
		}

		// Custom fields
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				if ( empty( $variables[ '_' . $field['htmlvar_name'] ] ) ) {
					$variables[ '_' . $field['htmlvar_name'] ] = __( stripslashes( $field['admin_title'] ), 'geodirectory' );
				}
			}
		}

		// Advanced custom fields
		$advance_fields = geodir_post_meta_advance_fields();
		if ( ! empty( $advance_fields ) ) {
			foreach ( $advance_fields as $key => $field ) {
				if ( empty( $variables[ '_' . $key ] ) ) {
					$variables[ '_' . $key ] = __( stripslashes( $field['frontend_title'] ), 'geodirectory' );
				}
			}
		}

		$variables = apply_filters( 'geodir_rank_math_register_extra_replacements', $variables );

		foreach ( $variables as $var => $help ) {
			if ( is_string( $var ) && $var !== '' ) {
				$var = trim( $var, '%' );

				if ( ! empty( $var ) ) {
					$var = '_gd_' . $var; // Add prefix to prevent conflict with RankMath default vars

					rank_math_register_var_replacement(
						$var,
						[
							'name' => esc_html( $help ),
							'description' => esc_html( $help ),
							'variable' => $var
						]
					);

					add_filter( 'rank_math/vars/' . $var, [ $this, 'replacement' ], 20, 2 );
				}
			}
		}
	}

	/**
	 * Replaces GD variables for Rank Math.
	 *
	 * @param string $args Variable args.
	 * @param mixed $variable Variable model.
	 * @return string Variable value.
	 */
	public function replacement( $args, $variable = [] ): string {
		$var = ! empty( $variable ) ? $variable->get_id() : '';
		if ( empty( $var ) ) {
			return '';
		}

		$var = strpos( $var, '_gd_' ) === 0 ? substr( $var, 4 ) : $var;

		return $this->replacer->replace( '%%' . $var . '%%' );
	}

	/**
	 * Filters the description and replaces GD variables.
	 *
	 * @param string $description The description sentence.
	 * @return string Filtered description.
	 */
	public function filter_description( string $description ): string {
		if ( empty( $description ) ) {
			$description = $this->meta_manager->get_description();
		}

		return $this->replacer->replace( $description );
	}

	/**
	 * Filters Rank Math breadcrumb settings to hide ancestors.
	 *
	 * @param array $settings Breadcrumbs settings.
	 * @return array Filtered settings.
	 */
	public function breadcrumb_settings( array $settings ): array {
		if ( ! is_admin() && geodir_is_geodir_page() ) {
			$settings['show_ancestors'] = false;
			$settings['hide_tax_name'] = true;
		}

		return $settings;
	}

	/**
	 * Filters Rank Math breadcrumbs to add category to details page.
	 *
	 * @param array $crumbs The breadcrumb array.
	 * @param mixed $breadcrumbs Breadcrumbs object.
	 * @return array Filtered breadcrumbs.
	 */
	public function breadcrumb_links( array $crumbs, $breadcrumbs = [] ): array {
		global $wp_query, $gd_detail_breadcrumb;

		// Maybe add category link to single page
		if ( ( geodir_is_page( 'single' ) || geodir_is_page( 'archive' ) ) && ! $gd_detail_breadcrumb ) {
			$post_type = geodir_get_current_posttype();

			$breadcrumb = [];
			$adjust = 0;

			if ( is_tax() && ! is_post_type_archive() ) {
				$breadcrumb[] = [
					wp_strip_all_tags( geodir_post_type_name( $post_type, true ) ),
					get_post_type_archive_link( $post_type ),
					'hide_in_schema' => false
				];
				$adjust--;
			} else {
				$category = ! empty( $wp_query->query_vars[ $post_type . 'category' ] ) ? $wp_query->query_vars[ $post_type . 'category' ] : '';

				if ( $category ) {
					$term = get_term_by( 'slug', $category, $post_type . 'category' );

					if ( ! empty( $term ) ) {
						$breadcrumb[] = [ $term->name, get_term_link( $term->slug, $post_type . 'category' ) ];
					}
				}
			}

			if ( ! empty( $breadcrumb ) && count( $breadcrumb ) > 0 ) {
				$offset = \RankMath\Helper::get_settings( 'general.breadcrumbs_home' ) ? 2 : 1;
				$offset = apply_filters( 'rankmath_breadcrumb_links_offset', ( $offset + $adjust ), $breadcrumb, $crumbs );
				$length = apply_filters( 'rankmath_breadcrumb_links_length', 0, $breadcrumb, $crumbs );

				array_splice( $crumbs, $offset, $length, $breadcrumb );
			}
		}

		return $crumbs;
	}

	/**
	 * Filters Rank Math breadcrumb post main term.
	 *
	 * @param object|null $term Post main term.
	 * @param array $terms Post terms.
	 * @return object|null The post main term.
	 */
	public function breadcrumb_main_term( $term, array $terms = [] ) {
		global $gd_post, $gd_detail_breadcrumb;

		if ( ! empty( $terms ) && geodir_is_page( 'detail' ) && ! empty( $gd_post ) && ! empty( $gd_post->default_category ) ) {
			foreach ( $terms as $_term ) {
				if ( $_term->term_id == $gd_post->default_category ) {
					$term = $_term;
					$gd_detail_breadcrumb = true;
				}
			}
		}

		return $term;
	}
}
