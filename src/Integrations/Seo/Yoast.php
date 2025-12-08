<?php
/**
 * Yoast SEO Integration
 *
 * @package GeoDirectory\Integrations\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Integrations\Seo;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Seo\MetaManager;
use AyeCode\GeoDirectory\Core\Seo\CanonicalManager;
use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Integrates GeoDirectory with Yoast SEO plugin.
 */
final class Yoast implements SeoIntegrationInterface {
	private VariableReplacer $replacer;
	private bool|int $gd_has_filter_thumbnail_id = false;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 * @param MetaManager $meta_manager The meta manager service.
	 * @param CanonicalManager $canonical_manager The canonical manager service.
	 */
	public function __construct(
		private Settings $settings,
		private MetaManager $meta_manager,
		private CanonicalManager $canonical_manager
	) {}

	/**
	 * {@inheritDoc}
	 */
	public function is_active(): bool {
		return defined( 'WPSEO_VERSION' ) && ! $this->settings->get( 'wpseo_disable' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;

		// Check Yoast version for compatibility
		$is_yoast_14_plus = version_compare( WPSEO_VERSION, '14.0', '>=' );

		if ( $is_yoast_14_plus ) {
			// Yoast 14+ hooks
			add_filter( 'wpseo_twitter_title', [ $this, 'twitter_title' ], 10, 2 );
			add_filter( 'wpseo_title', [ $this, 'filter_title' ], 20, 2 );
			add_filter( 'wpseo_metadesc', [ $this, 'filter_metadesc' ], 20, 2 );

			add_filter( 'wpseo_opengraph_url', [ $this, 'opengraph_url' ], 20, 2 );
			add_filter( 'wpseo_add_opengraph_additional_images', [ $this, 'opengraph_image' ], 20, 1 );
			add_filter( 'wpseo_canonical', [ $this, 'canonical' ], 20, 2 );
			add_filter( 'wpseo_adjacent_rel_url', [ $this, 'adjacent_rel_url' ], 20, 3 );

			add_action( 'wpseo_register_extra_replacements', [ $this, 'register_extra_replacements' ], 20 );

			// OpenGraph meta setup
			add_action( 'template_redirect', [ $this, 'template_redirect' ], 9999 );
		} else {
			// Pre-14 Yoast hooks
			add_filter( 'wpseo_title', [ $this->meta_manager, 'get_title' ], 10, 1 );
			add_filter( 'wpseo_metadesc', [ $this->meta_manager, 'get_description' ], 10, 1 );

			add_filter( 'wpseo_twitter_title', [ $this->meta_manager, 'get_title' ], 10, 1 );
			add_filter( 'wpseo_twitter_description', [ $this->meta_manager, 'get_description' ], 10, 1 );
			add_filter( 'wpseo_opengraph_title', [ $this->meta_manager, 'get_title' ], 10, 1 );
			add_filter( 'wpseo_opengraph_desc', [ $this->meta_manager, 'get_description' ], 10, 1 );
			add_filter( 'wpseo_opengraph_url', [ $this, 'opengraph_url' ], 20, 2 );
			add_filter( 'wpseo_add_opengraph_additional_images', [ $this, 'opengraph_image' ], 20, 1 );
			add_filter( 'wpseo_canonical', [ $this, 'canonical' ], 20, 2 );
			add_filter( 'wpseo_adjacent_rel_url', [ $this, 'adjacent_rel_url' ], 20, 3 );
		}

		// Common hooks for all Yoast versions
		add_filter( 'wpseo_frontend_presentation', [ $this, 'frontend_presentation' ], 11, 2 );
		add_filter( 'wpseo_breadcrumb_links', [ $this, 'breadcrumb_links' ] );
		add_filter( 'wpseo_robots_array', [ $this, 'robots_array' ], 20, 2 );
		add_filter( 'get_post_metadata', [ $this, 'filter_post_metadata' ], 99, 5 );

		// Search page specific hooks
		add_action( 'wp_head', function() {
			if ( geodir_is_page( 'search' ) ) {
				add_filter( 'wpseo_frontend_page_type_simple_page_id', [ $this, 'frontend_page_type_simple_page_id' ], 10, 1 );
			}
		}, 0 );
		add_action( 'wp_head', function() {
			if ( geodir_is_page( 'search' ) ) {
				remove_filter( 'wpseo_frontend_page_type_simple_page_id', [ $this, 'frontend_page_type_simple_page_id' ], 10, 1 );
			}
		}, 99 );

		// Page title filters
		add_filter( 'the_title', [ $this->meta_manager, 'output_title' ], 10, 2 );
		add_filter( 'get_the_archive_title', [ $this->meta_manager, 'output_title' ], 10 );
	}

	/**
	 * Filters the Yoast title and replaces our variables.
	 *
	 * @param string $title The original title from Yoast.
	 * @param mixed $presentation The presentation object.
	 * @return string The filtered title.
	 */
	public function filter_title( string $title, $presentation = [] ): string {
		if ( ! empty( $title ) || ! geodir_is_geodir_page() ) {
			return $title;
		}

		if ( geodir_is_page( 'archive' ) ) {
			$queried_object = get_queried_object();

			if ( ! empty( $queried_object->term_id ) && ! empty( $queried_object->taxonomy ) && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				if ( $_title = \WPSEO_Taxonomy_Meta::get_term_meta( $queried_object->term_id, $queried_object->taxonomy, 'title' ) ) {
					$title = $_title;
				} elseif ( $_title = \WPSEO_Options::get( 'title-tax-' . $queried_object->taxonomy ) ) {
					$title = $_title;
				}

				if ( strpos( $title, '%%' ) !== false ) {
					$title = wpseo_replace_vars( $title, $queried_object );
				}

				if ( strpos( $title, '%%' ) !== false ) {
					$title = $this->replacer->replace( $title );
				}
			}
		}

		return $title;
	}

	/**
	 * Filters the Yoast meta description and replaces our variables.
	 *
	 * @param string $metadesc The original meta description from Yoast.
	 * @param mixed $presentation The presentation object.
	 * @return string The filtered meta description.
	 */
	public function filter_metadesc( string $metadesc, $presentation = [] ): string {
		if ( ! empty( $metadesc ) || ! geodir_is_geodir_page() ) {
			return $metadesc;
		}

		if ( geodir_is_page( 'archive' ) ) {
			$queried_object = get_queried_object();

			if ( ! empty( $queried_object->term_id ) && ! empty( $queried_object->taxonomy ) && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				if ( $_metadesc = \WPSEO_Taxonomy_Meta::get_term_meta( $queried_object->term_id, $queried_object->taxonomy, 'desc' ) ) {
					$metadesc = $_metadesc;
				} elseif ( $_metadesc = \WPSEO_Options::get( 'metadesc-tax-' . $queried_object->taxonomy ) ) {
					$metadesc = $_metadesc;
				}
			}

			if ( strpos( $metadesc, '%%' ) !== false ) {
				$metadesc = wpseo_replace_vars( $metadesc, $queried_object );
			}

			if ( strpos( $metadesc, '%%' ) !== false ) {
				$metadesc = $this->replacer->replace( $metadesc );
			}
		}

		return $metadesc;
	}

	/**
	 * Filters Yoast breadcrumbs to add category to details page.
	 *
	 * @param array $crumbs The breadcrumb array.
	 * @return array Filtered breadcrumbs.
	 */
	public function breadcrumb_links( array $crumbs ): array {
		global $gd_post;

		if ( ! empty( $crumbs ) && ! empty( $gd_post->default_category ) && geodir_is_page( 'single' ) ) {
			$term = get_term( (int) $gd_post->default_category, $gd_post->post_type . 'category' );
			$term_added = false;

			$_crumbs = [];

			if ( ! empty( $term->term_id ) ) {
				foreach ( $crumbs as $key => $crumb ) {
					if ( ! empty( $crumb['term_id'] ) ) {
						if ( ! $term_added ) {
							$_crumbs[] = [
								'url' => get_term_link( $term->term_id, $term->taxonomy ),
								'text' => $term->name,
								'term_id' => $term->term_id
							];
							$term_added = true;
						}
					} else {
						$_crumbs[] = $crumb;
					}
				}

				$crumbs = $_crumbs;
			}
		}

		return $crumbs;
	}

	/**
	 * Filters Yoast SEO generated open graph URL.
	 *
	 * @param string $canonical The URL.
	 * @param mixed $presentation The presentation object.
	 * @return string Filtered URL.
	 */
	public function opengraph_url( string $canonical, $presentation ): string {
		if ( $canonicals = $this->canonical_manager->get_canonicals() ) {
			if ( ! empty( $canonicals['canonical'] ) ) {
				$canonical = $canonicals['canonical'];
			}
		}

		return $canonical;
	}

	/**
	 * Adds OpenGraph images for GeoDirectory pages.
	 *
	 * @param object $image_container The image container object.
	 * @return void
	 */
	public function opengraph_image( $image_container ): void {
		global $gd_post;

		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		if ( $image_container->has_images() ) {
			return;
		}

		// Post type archive page
		if ( geodir_is_page( 'post_type' ) ) {
			$post_type = geodir_get_current_posttype();

			if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
				if ( ! empty( $post_type_obj->default_image ) ) {
					$image_container->add_image_by_id( $post_type_obj->default_image );
				}
			}
		}
		// Archive (category/tag) page
		elseif ( geodir_is_page( 'archive' ) ) {
			$image_id = 0;
			$term = get_queried_object();

			if ( ! empty( $term->term_id ) && ( $image = get_term_meta( $term->term_id, 'ct_cat_default_img', true ) ) ) {
				if ( ! empty( $image['id'] ) ) {
					$image_id = (int) $image['id'];
				}
			}

			if ( empty( $image_id ) ) {
				// Post type default image
				$post_type = geodir_get_current_posttype();

				if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
					if ( ! empty( $post_type_obj->default_image ) ) {
						$image_id = (int) $post_type_obj->default_image;
					}
				}
			}

			if ( $image_id > 0 ) {
				$image_container->add_image_by_id( $image_id );
			}
		}
		// Single listing page
		elseif ( geodir_is_page( 'single' ) ) {
			$image_id = 0;
			$post_image = ! empty( $gd_post->ID ) ? geodir_get_images( (int) $gd_post->ID, 1, false, 0, [ 'post_images' ], [ 'post_images' ] ) : [];

			if ( ! empty( $post_image ) && ! empty( $post_image[0] ) ) {
				$post_image = $post_image[0];

				if ( ! empty( $post_image->metadata ) ) {
					$post_image->metadata = maybe_unserialize( $post_image->metadata );
				}

				$image = [
					'url' => geodir_get_image_src( $post_image, 'original' )
				];

				if ( ! empty( $post_image->metadata['width'] ) && ! empty( $post_image->metadata['height'] ) ) {
					$image['width'] = (int) $post_image->metadata['width'];
					$image['height'] = (int) $post_image->metadata['height'];
				}

				if ( ! empty( $image['url'] ) ) {
					$image_container->add_image( $image );
					return;
				}
			}

			// Default category image
			if ( ! empty( $gd_post->default_category ) && ( $image = get_term_meta( (int) $gd_post->default_category, 'ct_cat_default_img', true ) ) ) {
				if ( ! empty( $image['id'] ) ) {
					$image_id = (int) $image['id'];
				}
			}

			if ( empty( $image_id ) ) {
				// Post type default image
				$post_type = ! empty( $gd_post->post_type ) ? $gd_post->post_type : geodir_get_current_posttype();

				if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
					if ( ! empty( $post_type_obj->default_image ) ) {
						$image_id = (int) $post_type_obj->default_image;
					}
				}
			}

			if ( $image_id > 0 ) {
				$image_container->add_image_by_id( $image_id );
			}
		}
	}

	/**
	 * Filters Yoast SEO generated canonical URL.
	 *
	 * @param string $canonical The URL.
	 * @param mixed $presentation The presentation object.
	 * @return string Filtered URL.
	 */
	public function canonical( string $canonical, $presentation ): string {
		if ( $canonicals = $this->canonical_manager->get_canonicals() ) {
			if ( ! empty( $canonicals['canonical_paged'] ) ) {
				$canonical = $canonicals['canonical_paged'];
			}
		}

		return $canonical;
	}

	/**
	 * Filters the rel next/prev URL.
	 *
	 * @param string $rel The next/prev URL.
	 * @param string $type next or prev.
	 * @param mixed $presentation The presentation object.
	 * @return string Filtered next/prev URL.
	 */
	public function adjacent_rel_url( string $rel, string $type, $presentation ): string {
		if ( $rel && $type && ( $canonicals = $this->canonical_manager->get_canonicals() ) ) {
			if ( ! empty( $canonicals['canonical_' . $type] ) ) {
				$rel = $canonicals['canonical_' . $type];
			}
		}

		return $rel;
	}

	/**
	 * Filters the meta robots output array of Yoast SEO.
	 *
	 * @param array $robots The meta robots directives to be used.
	 * @param mixed $presentation The presentation object.
	 * @return array The meta robots array.
	 */
	public function robots_array( array $robots, $presentation ): array {
		if ( ! empty( $robots ) && geodir_is_page( 'single' ) ) {
			if ( ! empty( $presentation ) && ! empty( $presentation->model->object_id ) ) {
				$indexable = (bool) $this->is_indexable( (int) $presentation->model->object_id );

				if ( empty( $robots['follow'] ) ) {
					$robots['follow'] = \WPSEO_Meta::get_value( 'meta-robots-nofollow', (int) $presentation->model->object_id );
				}
			} elseif ( $post_type = geodir_get_current_posttype() ) {
				$indexable = \WPSEO_Options::get( 'noindex-' . $post_type, false ) === false;
			} else {
				return $robots;
			}

			if ( $indexable ) {
				$robots['index'] = 'index';
			} else {
				$robots['index'] = 'noindex';
			}
		}

		return $robots;
	}

	/**
	 * Yoast SEO: Determines whether a particular post_id is of an indexable post type.
	 *
	 * @param int $post_id The post ID to check.
	 * @return bool Whether or not it is indexable.
	 */
	private function is_indexable( int $post_id ): bool {
		if ( ! empty( $post_id ) && \WPSEO_Meta::get_value( 'meta-robots-noindex', $post_id ) !== '0' ) {
			return \WPSEO_Meta::get_value( 'meta-robots-noindex', $post_id ) === '2';
		}

		return \WPSEO_Options::get( 'noindex-' . get_post_type( $post_id ), false ) === false;
	}

	/**
	 * Prevents using post type archive index model on term archive page.
	 *
	 * @param object $presentation The presentation of an indexable.
	 * @param object $context The meta tags context.
	 * @return object Filtered presentation of an indexable.
	 */
	public function frontend_presentation( $presentation, $context ) {
		if ( geodir_is_geodir_page() && is_tax() && is_post_type_archive() && geodir_is_page( 'archive' ) ) {
			try {
				if ( ! empty( $presentation->model->object_id ) && ! empty( $presentation->model->object_sub_type ) ) {
					$term = get_term( $presentation->model->object_id, $presentation->model->object_sub_type );

					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						$presentation->source = $term;
						$term_meta = \WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, null );

						if ( ! empty( $term_meta ) ) {
							$is_robots_noindex = null;

							if ( array_key_exists( 'wpseo_noindex', $term_meta ) ) {
								$value = $term_meta['wpseo_noindex'];

								if ( $value === 'noindex' ) {
									$is_robots_noindex = true;
								} elseif ( $value === 'index' ) {
									$is_robots_noindex = false;
								} elseif ( $value == 'default' ) {
									$is_robots_noindex = ! \WPSEO_Options::get( 'noindex-tax-' . $term->taxonomy, false ) ? false : true;
								}
							}

							$presentation->model->is_robots_noindex = $is_robots_noindex;
							$presentation->model->is_public = ( $presentation->model->is_robots_noindex === null ) ? null : ! $presentation->model->is_robots_noindex;
						}
					}
				}
			} catch ( \Exception $e ) {}
		}

		return $presentation;
	}

	/**
	 * Filters the X title.
	 *
	 * @param string $title X title.
	 * @param mixed $presentation The presentation object.
	 * @return string Filtered title.
	 */
	public function twitter_title( string $title, $presentation ): string {
		if ( geodir_is_page( 'search' ) && ! empty( $presentation ) ) {
			$title = isset( $presentation->model->twitter_title ) && ! is_null( $presentation->model->twitter_title ) ? $presentation->model->twitter_title : '';

			if ( $title && strpos( $title, '%%' ) !== false ) {
				$title = wpseo_replace_vars( $title, get_post( (int) \GeoDir_Compatibility::gd_page_id() ) );
			}

			if ( $title && strpos( $title, '%%' ) !== false ) {
				$title = $this->replacer->replace( $title );
			}
		}

		return $title;
	}

	/**
	 * Filters post metadata for Yoast SEO.
	 *
	 * @param mixed $value The metadata value.
	 * @param int $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param bool $single Whether to return only the first value.
	 * @param string $meta_type Type of object metadata is for.
	 * @return mixed Post metadata value.
	 */
	public function filter_post_metadata( $value, int $object_id, string $meta_key, bool $single = false, string $meta_type = '' ) {
		global $geodir_post_meta_loop;

		if ( null === $value ) {
			return $value;
		}

		if ( defined( 'WPSEO_VERSION' ) && ! empty( $object_id ) && ! is_admin() && empty( $meta_key ) && is_array( $value ) && empty( $geodir_post_meta_loop ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) ) {
			$geodir_post_meta_loop = true;

			// Check & remove filters
			$has_filter_1 = has_filter( 'get_post_metadata', [ 'GeoDir_Compatibility', 'dynamically_add_post_meta' ] );
			if ( $has_filter_1 ) {
				remove_filter( 'get_post_metadata', [ 'GeoDir_Compatibility', 'dynamically_add_post_meta' ] );
			}

			$has_filter_2 = has_filter( 'get_post_metadata', [ $this, 'filter_post_metadata' ] );
			if ( $has_filter_2 ) {
				remove_filter( 'get_post_metadata', [ $this, 'filter_post_metadata' ] );
			}

			$_value = get_post_custom( $object_id );

			if ( ! empty( $_value ) && is_array( $_value ) ) {
				// Reserved post meta keys for single listing
				$reserve_keys = [ '_yoast_wpseo_content_score', '_yoast_wpseo_linkdex', '_yoast_wpseo_meta-robots-adv', '_yoast_wpseo_meta-robots-nofollow', '_yoast_wpseo_meta-robots-noindex', '_yoast_wpseo_is_cornerstone', '_yoast_wpseo_title', '_yoast_wpseo_metadesc' ];

				// Remove template page post meta values
				foreach ( $value as $key => $data ) {
					if ( in_array( $key, $reserve_keys ) ) {
						unset( $value[ $key ] );
					}
				}

				// Add single listing post meta values
				foreach ( $_value as $key => $data ) {
					if ( in_array( $key, $reserve_keys ) ) {
						$value[ $key ] = $data;
					}
				}
			}

			$geodir_post_meta_loop = false;

			// Check & add filters back
			if ( $has_filter_1 ) {
				add_filter( 'get_post_metadata', [ 'GeoDir_Compatibility', 'dynamically_add_post_meta' ], 10, 4 );
			}

			if ( $has_filter_2 ) {
				add_filter( 'get_post_metadata', [ $this, 'filter_post_metadata' ], 99, 5 );
			}
		}

		return $value;
	}

	/**
	 * Filters Yoast SEO simple page id.
	 *
	 * @param int $page_id The page id.
	 * @return int Filtered page id.
	 */
	public function frontend_page_type_simple_page_id( int $page_id ): int {
		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) || geodir_is_page( 'single' ) ) && ! is_tax() && ( $_page_id = (int) \GeoDir_Compatibility::gd_page_id() ) ) {
			$page_id = $_page_id;
		}

		return $page_id;
	}

	/**
	 * Setup Yoast SEO opengraph meta.
	 *
	 * @return void
	 */
	public function template_redirect(): void {
		// OpenGraph
		add_action( 'wpseo_opengraph', [ $this, 'head_setup_meta' ], 0 );
		add_action( 'wpseo_opengraph', [ $this, 'head_unset_meta' ], 99 );

		// X
		if ( \WPSEO_Options::get( 'twitter' ) === true ) {
			add_action( 'wpseo_head', [ $this, 'head_setup_meta' ], 39 );
			add_action( 'wpseo_head', [ $this, 'head_unset_meta' ], 41 );
		}
	}

	/**
	 * Sets Yoast SEO opengraph meta.
	 *
	 * @return void
	 */
	public function head_setup_meta(): void {
		add_filter( 'wpseo_frontend_page_type_simple_page_id', [ $this, 'frontend_page_type_simple_page_id' ], 10, 1 );

		if ( geodir_is_page( 'single' ) && ( $this->gd_has_filter_thumbnail_id = has_filter( 'get_post_metadata', [ 'GeoDir_Template_Loader', 'filter_thumbnail_id' ] ) ) ) {
			remove_filter( 'get_post_metadata', [ 'GeoDir_Template_Loader', 'filter_thumbnail_id' ], 10, 4 );
		}
	}

	/**
	 * Unsets Yoast SEO opengraph meta.
	 *
	 * @return void
	 */
	public function head_unset_meta(): void {
		remove_filter( 'wpseo_frontend_page_type_simple_page_id', [ $this, 'frontend_page_type_simple_page_id' ], 10, 1 );

		if ( geodir_is_page( 'single' ) && $this->gd_has_filter_thumbnail_id && ! has_filter( 'get_post_metadata', [ 'GeoDir_Template_Loader', 'filter_thumbnail_id' ] ) ) {
			add_filter( 'get_post_metadata', [ 'GeoDir_Template_Loader', 'filter_thumbnail_id' ], 10, 4 );

			$this->gd_has_filter_thumbnail_id = false;
		}
	}

	/**
	 * Registers GD variables for Yoast SEO extra replacements.
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

		$variables = apply_filters( 'geodir_wpseo_register_extra_replacements', $variables );

		$replacer = new \WPSEO_Replace_Vars();

		foreach ( $variables as $var => $help ) {
			if ( is_string( $var ) && $var !== '' ) {
				$var = trim( $var, '%' );

				if ( ! empty( $var ) ) {
					$var = '_gd_' . $var; // Add prefix to prevent conflict with Yoast default vars

					if ( ! method_exists( $replacer, 'retrieve_' . $var ) ) {
						wpseo_register_var_replacement( $var, [ $this, 'replacement' ], 'advanced', $help );
					}
				}
			}
		}
	}

	/**
	 * Replaces GD variables for Yoast SEO.
	 *
	 * @param string $var Variable name.
	 * @param array $args Variable args.
	 * @return string Variable value.
	 */
	public function replacement( string $var, array $args ): string {
		$var = strpos( $var, '_gd_' ) === 0 ? substr( $var, 4 ) : $var;

		return $this->replacer->replace( '%%' . $var . '%%' );
	}
}
