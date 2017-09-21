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
        if ( geodir_is_singular() ) {
            $default_file = 'single-listing.php';
        } elseif ( geodir_is_taxonomy() ) {
            $default_file = 'taxonomy-listing.php';
        } elseif ( geodir_is_post_type_archive() ) {
            $default_file = 'archive-listing.php';
        //} elseif ( geodir_is_page( 'add-listing' ) ) {
            //$default_file = 'add-listing.php';
        } elseif ( geodir_is_page( 'author' ) ) {
            $default_file = 'author.php';
        } elseif ( geodir_is_page( 'home' ) ) {
            $default_file = 'home.php';
        } elseif ( geodir_is_page( 'location' ) ) {
            $default_file = 'location.php';
        } elseif ( geodir_is_page( 'search' ) ) {
            $default_file = 'search.php';
        } elseif ( geodir_is_page( 'login' ) ) {
            $default_file = 'signup.php';
        } elseif ( geodir_is_page( 'listing-success' ) ) {
            $default_file = 'listing-success.php';
        } else {
            $default_file = '';
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
        $search_files = apply_filters( 'geodir_template_loader_files', array(), $default_file );
        $search_files[] = 'geodirectory.php';

        if ( geodir_is_taxonomy() ) {
            $term = get_queried_object();

            $search_files[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
            $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
            $search_files[] = 'taxonomy-' . $term->taxonomy . '.php';
            $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '.php';
        }

        $search_files[] = $default_file;
        $search_files[] = geodir_get_theme_template_dir_name() . '/' . $default_file;

        return array_unique( $search_files );
    }
}

GeoDir_Template_Loader::init();
