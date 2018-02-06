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
    public static function template_loader( $template ) { //return $template;
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
        $default_file = '';
        if ( geodir_is_singular() ) { // @todo we should make it use the "GD Details template" page template.
            //$default_file = 'single-listing.php';
            $default_file = 'page.php';
            //self::setup_singular_page();
            add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );
        } elseif ( geodir_is_taxonomy() ) {// @todo we should make it use the "GD Archive template" page template.
            //$default_file = 'page.php'; // i think index.php works better here, more likely to have paging
            $default_file = 'index.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_post_type_archive() ) {// @todo we should make it use the "GD Archive template" page template.
            //$default_file = 'page.php'; // i think index.php works better here, more likely to have paging
            $default_file = 'index.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_page( 'author' ) ) {
            //$default_file = 'author.php';
        } elseif ( geodir_is_page( 'home' ) ) {
           // $default_file = 'home.php';
        } elseif ( geodir_is_page( 'location' ) ) {
            //$default_file = 'location.php';
        } elseif ( geodir_is_page( 'search' ) ) {
           // $default_file = 'search.php';
            $default_file = 'index.php';
            //$default_file = 'search.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_page( 'login' ) ) {
           // $default_file = '';//signup.php';
        } elseif ( geodir_is_page( 'listing-success' ) ) {
           // $default_file = 'listing-success.php';
        } else {
            $default_file = '';
        }

        return $default_file;
    }


    /**
     * Setup the GD Archive page content.
     *
     * @since 2.0.0
     * @return string The filtered content.
     */
    public static function setup_archive_page_content(){

        // remove our filter so we don't get stuck in a loop
        remove_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );

        // reset the query count so the correct number of listings are output.
        rewind_posts();

        // reset the proper loop content
        global $wp_query,$gd_temp_wp_query;
        $wp_query->posts = $gd_temp_wp_query;

        // get the archive template page content
        if(geodir_is_page('search')){
            $archive_page_id = geodir_search_page_id();
        }else{
            $archive_page_id = geodir_archive_page_id();
        }
        $content = get_post_field('post_content', $archive_page_id  );

        // if the content is blank then just add the main loop
        if($content==''){
            $content = "[gd_loop]";
        }

        // run the shortcodes on the content
        $content = do_shortcode($content);

        // add our filter back, not sure we even need to add it back if we are only running it once.
        add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );

        // fake the has_posts() to false so it will not loop any more.
        $wp_query->current_post = $wp_query->post_count;

        return $content;
    }

    /**
     * Setup the GD archive loop content.
     *
     * @since 2.0.0
     */
    public static function setup_archive_loop_as_page(){

        // get the main query
        global $wp_query;

        // declare our global var so we can store the main query temporarily.
        global $gd_temp_wp_query;

        // Set our temp var with the main query posts.
        $gd_temp_wp_query = $wp_query->posts;

        // Set the main query to our archive page template
        if(geodir_is_page('search')){
            $archive_page_id = geodir_search_page_id();
        }else{
            $archive_page_id = geodir_archive_page_id();
        }
        $archive_page = get_post($archive_page_id);
        $wp_query->posts = array($archive_page);

        //$wp_query->current_post = $wp_query->post_count-1;


        // add the filter to call our own loop for the archive page content.
        add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );

        // if the template is only using the excerpt then bypass it.
        add_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );

    }

    /**
     * Setup the GD archive loop content.
     *
     * @since 2.0.0
     */
    public static function setup_singular_page(){

        // remove our filter so we don't get stuck in a loop
        remove_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );


        // get the main query
        global $wp_query;

        //print_r($wp_query);


        // get the archive template page content
        $page_id = geodir_details_page_id();
        $content = get_post_field('post_content', $page_id  );

        // if the content is blank then just add the main loop
        if($content==''){
            $content = "[gd_single_closed_text]
                        [gd_single_slider]
                        [gd_single_taxonomies]
                        [gd_single_tabs]";
        }

        // run the shortcodes on the content
        $content = do_shortcode($content);

        // add our filter back
        add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );


        return $content;

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
