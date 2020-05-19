<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Taxonomies extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     * @since 2.0.0
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['category','taxonomies','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_taxonomies', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Taxonomies','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-taxonomies-container', // widget class
                'description' => esc_html__('Shows the current post`s categories and tags.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
        );


        parent::__construct( $options );
    }

    /**
     * The Super block output function.
     *
     * @param array $args
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output($args = array(), $widget_args = array(),$content = ''){
        global $preview, $post, $gd_post;

        if ( empty( $post->post_type ) ) {
            return;
        }

        $post_id = isset( $post->ID ) ? $post->ID : '';
        $post_type = $post->post_type;
        $post_type_name = geodir_post_type_singular_name( $post_type, true );
        $cat_taxonomy = $post_type . 'category';
        $tag_taxonomy = $post_type . '_tags';

        $taxonomies = array();

        if ( ! empty( $gd_post->post_tags ) ) {
            if ( taxonomy_exists( $tag_taxonomy ) ) {
                // To limit post tags
                $post_tags = trim( $gd_post->post_tags, "," );

                /**
                 * Filter the post tags.
                 *
                 * Allows you to filter the post tags output on the details page of a post.
                 *
                 * @since 1.0.0
                 * @param string $post_tags A comma seperated list of tags.
                 * @param int $post_id The current post id.
                 */
                $post_tags = apply_filters( 'geodir_action_details_post_tags', $post_tags, $post_id );

                $gd_post->post_tags = $post_tags;
                $post_tags = explode( ",", trim( $gd_post->post_tags, "," ) );

                $terms = array();
                $links = array();
                foreach ( $post_tags as $post_term ) {
                    // Fix slug creation order for tags & location
                    $post_term = trim( $post_term );

                    $priority_location = false;
                    if ( $insert_term = term_exists( $post_term, $tag_taxonomy ) ) {
                        $term = get_term_by( 'id', $insert_term['term_id'], $tag_taxonomy );
                    } else {
                        continue;
                    }

                    if ( ! empty( $term ) && ! is_wp_error( $term ) && is_object( $term ) ) {
                        // Fix tag link on detail page
                        if ( $priority_location ) {
                            $tag_link = "<a href=''>" . $post_term . "</a>";
                            /**
                             * Filter the tag name on the details page.
                             *
                             * @since 1.5.6
                             * @param string $tag_link The tag link html.
                             * @param object $term The tag term object.
                             */
                            $tag_link = apply_filters( 'geodir_details_taxonomies_tag_link', $tag_link, $term );
                            $links[] = $tag_link;
                        } else {
                            $tag_link = "<a href='" . esc_attr( get_term_link( $term->term_id, $term->taxonomy ) ) . "'>" . $term->name . "</a>";
                            /** This action is documented in geodirectory-template_actions.php */
                            $tag_link = apply_filters( 'geodir_details_taxonomies_tag_link', $tag_link, $term );
                            $links[] = $tag_link;
                        }

                        $terms[] = $term;
                    }
                }

                $taxonomies[ $tag_taxonomy ] = wp_sprintf( __( '%s Tags: %l', 'geodirectory' ), $post_type_name, $links, (object) $terms );
            }
        }

        if ( ! empty( $gd_post->post_category ) ) {
            if ( ! is_array( $gd_post->post_category ) ) {
                $post_terms = explode( ",", trim( $gd_post->post_category, "," ) );
            } else {
                $post_terms = $gd_post->post_category;

                if ( $preview ) {
                    $post_terms = geodir_add_parent_terms( $post_terms, $cat_taxonomy );
                }
            }

            $post_terms = array_unique( $post_terms );
            $terms = array();
            $links = array();
            $termsOrdered = array();
            if ( ! empty( $post_terms ) ) {
                foreach ( $post_terms as $post_term ) {
                    $post_term = trim( $post_term );

                    if ( $post_term != '' ) {
                        $term = get_term_by( 'id', $post_term, $cat_taxonomy );

                        if ( ! empty( $term ) && ! is_wp_error( $term ) && is_object( $term ) ) {
                            $term_link = "<a href='" . esc_attr( get_term_link( $term, $cat_taxonomy ) ) . "'>" . $term->name ."</a>";
                            /**
                             * Filter the category name on the details page.
                             *
                             * @since 1.5.6
                             * @param string $term_link The link html to the category.
                             * @param object $term The category term object.
                             */
                            $term_link = apply_filters( 'geodir_details_taxonomies_cat_link', $term_link, $term );
                            $links[] = $term_link;
                            $terms[] = $term;
                        }
                    }
                }
                // Order alphabetically
                asort( $links );
                foreach ( array_keys( $links ) as $key ) {
                    $termsOrdered[$key] = $terms[$key];
                }
                $terms = $termsOrdered;
            }

            $taxonomies[ $cat_taxonomy ] = wp_sprintf( __( '%s Category: %l', 'geodirectory' ), $post_type_name, $links, (object) $terms );
        }

        /**
         * Filter the taxonomies array before output.
         *
         * @since 1.5.9
         * @param array $taxonomies The array of cats and tags.
         * @param string $post_type The post type being output.
         * @param string $post_type_name The post type label.
         * @param string $post_type_name The post type label with ucwords function.
         */
        $taxonomies = apply_filters( 'geodir_details_taxonomies_output', $taxonomies, $post_type, $post_type_name, geodir_ucwords( $post_type_name ) );

        // Block demo content
        if ( geodir_is_block_demo() &&  empty( $taxonomies ) && $post_type == 'page' ) {
            $taxonomies[ $cat_taxonomy ] = __( 'Category:', 'geodirectory' ) . " <a href='#'>" . __( 'Demo', 'geodirectory' ) . "</a>, <a href='#'>" . __( 'Example', 'geodirectory' ) . "</a>";
            $taxonomies[ $tag_taxonomy ] = __( 'Tags:', 'geodirectory' ) . " <a href='#'>" . __( 'Demo', 'geodirectory' ) . "</a>, <a href='#'>" . __( 'Example', 'geodirectory' ) . "</a>";
        }

        $content = '<p class="geodir_post_taxomomies clearfix">';

        if ( isset( $taxonomies[ $cat_taxonomy ] ) ) {
            $content .= '<span class="geodir-category">' . $taxonomies[ $cat_taxonomy ] . '</span>';
        }

        if ( isset( $taxonomies[ $tag_taxonomy ] ) ) {
            $content .= '<span class="geodir-tags">' . $taxonomies[ $tag_taxonomy ] . '</span>';
        }

        $content .= '</p>';

        return $content;
    }
}