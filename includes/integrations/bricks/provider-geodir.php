<?php
namespace Bricks\Integrations\Dynamic_Data\Providers;

/**
 * GeoDirectory Bricks Dynamic Data Provider
 *
 * @since    2.3.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Provider_Geodir extends Base {

	public function register_tags() {
		$tags = $this->get_tags_config();

		foreach ( $tags as $key => $tag ) {
			$this->tags[ $key ] = [
				'name'     => '{' . $key . '}',
				'label'    => $tag['label'],
				'group'    => $tag['group'],
				'provider' => $this->name,
			];

			if ( ! empty( $tag['render'] ) ) {
				$this->tags[ $key ]['render'] = $tag['render'];
			}
		}
	}

	public function get_tags_config() {
		$tags = array();

		$post_meta_group = esc_html__( 'GD Post Meta', 'geodirectory' );
		$post_meta_keys = $this->get_post_meta_keys();

		foreach( $post_meta_keys as $key => $label ) {
			$tags[ 'gd_post_meta_' . $key ] = array(
				'label' => esc_html( $label . ' (' . $key . ')' ),
				'group' => $post_meta_group
			);
		}

		$cat_meta_group = esc_html__( 'GD Category Meta', 'geodirectory' );
		$cat_meta_keys = $this->get_category_meta_keys();

		foreach( $cat_meta_keys as $key => $label ) {
			$tags[ 'gd_cat_meta_' . $key ] = array(
				'label' => esc_html( $label ),
				'group' => $cat_meta_group
			);
		}

		return apply_filters( 'geodir_bricks_dynamic_data_tags', $tags, $this );
	}

	public function get_tag_value( $tag, $post, $args, $context ) {
		global $gd_post;

		if ( strpos( $tag, 'gd_post_meta_' ) === 0 ) {
			$_tag = explode( 'gd_post_meta_', $tag, 2 );
			$key = $_tag[1];
			$show = 'value-raw';
			$post_id = 0;

			if ( ! empty( $args[0] ) ) {
				if ( strlen( $args[0] ) == strlen( (int) $args[0] ) && (int) $args[0] > 0 ) {
					$args[1] = $args[0];
				} else {
					$show = $args[0];
				}
			}

			if ( ! empty( $args[1] ) && strlen( $args[1] ) == strlen( (int) $args[1] ) && (int) $args[1] > 0 && geodir_is_gd_post_type( get_post_type( (int) $args[1] ) ) ) {
				$post_id = (int) $args[1];
			}

			$value = do_shortcode( '[gd_post_meta key="' .esc_attr( $key )  . '" show="' .esc_attr( $show )  . '" no_wrap="1"' . ( $post_id ? ' id="' . $post_id . '"' : '') . ']' );

			return apply_filters( 'geodir_bricks_get_post_meta_tag_value', $value, $key, $tag, $args, $context, $post, $this, $post_id );
		} else if ( strpos( $tag, 'gd_cat_meta_' ) === 0 ) {
			$_tag = explode( 'gd_cat_meta_', $tag, 2 );
			$key = $_tag[1];
			$show = ! empty( $args[0] ) ? $args[0] : 'value-raw';
			$term_id = ! empty( $args[1] ) && (int) $args[1] > 0 ? (int) $args[1] : 0;
			$value = '';

			if ( ! $term_id && geodir_is_page( 'archive' ) ) {
				$current_category = get_queried_object();
				$term_id = isset( $current_category->term_id ) ?  absint( $current_category->term_id ) : 0;
			} else if ( ! $term_id && ! empty( $gd_post ) ) {
				$term_id = ! empty( $gd_post->default_category ) ? absint( $gd_post->default_category ) : 0;
			}

			if ( $term_id ) {
				if ( $key == 'top_description' ) {
					$cat_desc = do_shortcode( "[gd_category_description no_wrap=1]" );
					$value = $cat_desc ? trim( $cat_desc ) : '';
				} else if ( $key == 'bottom_description' ) {
					$cat_desc = do_shortcode( "[gd_category_description type='bottom' no_wrap=1]" );
					$value = $cat_desc ? trim( $cat_desc ) : '';
				} else if ( $key == 'icon' ) {
					$value = get_term_meta( $term_id, 'ct_cat_font_icon', true );

					if ( $show == 'value' ) {
						$value = "<i class='" . esc_attr( $value ) . "' aria-hidden='true'></i>";
					}
				} else if ( $key == 'map_icon' ) {
					$value = esc_url_raw( geodir_get_term_icon( $term_id ) );

					if ( $show == 'value' ) {
						$value = "<img src='" . esc_attr( $value ) . "' />";
					}
				} else if ( $key == 'color' ) {
					$value = get_term_meta( $term_id, 'ct_cat_color', true );
				} else if ( $key == 'schema' ) {
					$value = get_term_meta( $term_id, 'ct_cat_schema', true );
				} else if ( $key == 'image' ) {
					$value = esc_url_raw( geodir_get_cat_image( $term_id, true ) );

					if ( $show == 'value' ) {
						$value = "<img src='" . esc_attr( $value ) . "' />";
					}
				}
			}

			if ( $value && ( $show =='value-raw' || $show == 'value-strip' ) ) {
				$value = wp_strip_all_tags( $value );
			}

			return apply_filters( 'geodir_bricks_get_cat_meta_tag_value', $value, $key, $tag, $args, $context, $post, $this, $term_id );
		}

		return $tag;
	}

	public function get_post_meta_keys() {
		$fields = geodir_get_field_key_options( array( 'context' => 'bricks-dynamic-data', 'display_label' => 'title' ) );

		return apply_filters( 'geodir_bricks_post_meta_tags', $fields, $this );
	}

	public function get_category_meta_keys() {
		$keys = array();
		$keys['top_description'] = __( 'Category Top Description', 'geodirectory' );
		$keys['bottom_description'] = __( 'Category Bottom Description', 'geodirectory' );
		$keys['icon'] = __( 'Category Icon', 'geodirectory' );
		$keys['map_icon'] = __( 'Category Map Icon', 'geodirectory' );
		$keys['color'] = __( 'Category Color', 'geodirectory' );
		$keys['image'] = __( 'Category Image', 'geodirectory' );
		$keys['schema'] = __( 'Category Schema', 'geodirectory' );

		return apply_filters( 'geodir_bricks_dynamic_data_tags', $keys, $this );
	}
}
