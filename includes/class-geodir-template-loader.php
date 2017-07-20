<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Template Loader
 *
 * @version 2.0.0
 */
class GeoDir_Template_Loader {

    /**
     * Hook in methods.
     */
    public static function init() {
        add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );
    }

    /**
     * Load a template.
     *
     * Handles template usage so that we can use our own templates instead of the themes.
     *
     * Templates are in the 'templates' folder. geodirectory looks for theme.
     * overrides in /theme/geodirectory/ by default.
     *
     * For beginners, it also looks for a geodirectory.php template first. If the user adds.
     * this to the theme (containing a geodirectory() inside) this will be used for all.
     * geodirectory templates.
     *
     * @param mixed $template
     * @return string
     */
    public static function template_loader( $template ) {
        if ( is_embed() ) {
            return $template;
        }

        if ( $default_file = self::get_template_loader_default_file() ) {
            /**
             * Filter hook to choose which files to find before GeoDirectory does it's own logic.
             *
             * @since 2.0.0
             * @var array
             */
            $search_files = self::get_template_loader_files( $default_file );
            $template     = locate_template( $search_files );

            if ( !$template ) {
                $template = geodir_get_templates_dir() . '/' . $default_file;
            }
        }

        return $template;
    }

    /**
     * Get the default filename for a template.
     *
     * @since  3.0.0
     * @return string
     */
    private static function get_template_loader_default_file() {
        global $geodir_custom_page_list;
        /**
         * Filter the custom page list.
         *
         * @since 1.0.0
         */
        $geodir_custom_page_list = apply_filters( 'geodir_set_custom_pages', 
            array(
                'geodir_signup_page' => apply_filters( 'geodir_set_custom_signup_page', false ),
                'geodir_add_listing_page' => apply_filters( 'geodir_set_custom_add_listing_page', false ),
                'geodir_preview_page' => apply_filters( 'geodir_set_custom_preview_page', false ),
                'geodir_listing_success_page' => apply_filters( 'geodir_set_custom_listing_success_page', false ),
                'geodir_listing_detail_page' => apply_filters( 'geodir_set_custom_listing_detail_page', false ),
                'geodir_listing_page' => apply_filters( 'geodir_set_custom_listing_page', false ),
                'geodir_search_page' => apply_filters( 'geodir_set_custom_search_page', false ),
                'geodir_author_page' => apply_filters( 'geodir_set_custom_author_page', false ),
                'geodir_home_map_page' => apply_filters( 'geodir_set_custom_home_map_page', false )
            )
        );
        
        $default_file = '';

        if ( geodir_is_page( 'login' ) || $geodir_custom_page_list['geodir_signup_page'] ) {
            $default_file = 'signup.php';
        } elseif ( geodir_is_page( 'add-listing' ) || $geodir_custom_page_list['geodir_add_listing_page'] ) {
            $default_file = 'add-listing.php';
        } elseif ( geodir_is_page( 'preview' ) || $geodir_custom_page_list['geodir_preview_page'] ) {
            $default_file = 'preview.php';
        } elseif ( geodir_is_page( 'listing-success' ) || $geodir_custom_page_list['geodir_listing_success_page'] ) {
            $default_file = 'listing-success.php';
        } elseif ( geodir_is_page( 'detail' ) || $geodir_custom_page_list['geodir_listing_detail_page'] ) {
            $default_file = 'listing-detail.php';
        } elseif ( geodir_is_page( 'listing') || $geodir_custom_page_list['geodir_listing_page'] ) {
            $default_file = 'listing-detail.php';
        } elseif ( geodir_is_page( 'search' ) || $geodir_custom_page_list['geodir_search_page'] ) {
            $default_file = 'search.php';
        } elseif ( geodir_is_page( 'author' ) || $geodir_custom_page_list['geodir_author_page'] ) {
            $default_file = 'author.php';
        } elseif ( geodir_is_page( 'home' ) || geodir_is_page( 'location' ) ) {
            global $post, $wp_query;

            if ( geodir_is_page( 'home' ) || ( 'page' == get_option( 'show_on_front' ) && !empty( $post->ID ) && $post->ID == get_option( 'page_on_front' ) ) || ( is_home() && !$wp_query->is_posts_page ) ) {
                $default_file = 'home.php';
            } elseif ( geodir_is_page( 'location' ) ) {
                $default_file = 'location.php';
            }
        }
        
        return $default_file;
    }

    /**
     * Get an array of filenames to search for a given template.
     *
     * @since  2.0.0
     * @param  string $file The default file name.
     * @return string[]
     */
    private static function get_template_loader_files( $default_file ) {
        global $geodir_custom_page_list;
        
        $search_files = apply_filters( 'geodir_template_loader_files', array(), $default_file );
        $search_files[] = 'geodirectory.php';

        if ( geodir_is_page( 'listing' ) ) {
            if ( is_tax() ) {
                $term = get_queried_object();
                
                if ( !empty( $term ) ) {
                    $search_files[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
                    $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
                    $search_files[] = 'taxonomy-' . $term->taxonomy . '.php';
                    $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '.php';
                }
                
                $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy.php';
            }
        }

        $search_files[] = $default_file;
        $search_files[] = geodir_get_theme_template_dir_name() . '/' . $default_file;

        return array_unique( $search_files );
    }
}

GeoDir_Template_Loader::init();
