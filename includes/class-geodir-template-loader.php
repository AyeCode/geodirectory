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
        // filter the templates
        add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

        // remove the theme featured output
        add_action( "wp", array(__CLASS__,'disable_theme_featured_output') );

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
        global $wp_query;
        $default_file = '';
        if ( geodir_is_singular() ) { // @todo we should make it use the "GD Details template" page template.
            //$default_file = 'single-listing.php';
            $default_file = 'page.php';
            $page_id = geodir_details_page_id();
            if($page_id &&  $template = get_page_template_slug( $page_id )){
                if(is_page_template( $template )){
                    $default_file = $template;
                }
            }
            //echo '###'.$default_file;
            //self::setup_singular_page();
            //$default_file = 'index.php';
            add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );
        } elseif ( geodir_is_taxonomy() ) {// @todo we should make it use the "GD Archive template" page template.
            $default_file = 'page.php'; // i think index.php works better here, more likely to have paging
            //$default_file = 'index.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_post_type_archive() ) {// @todo we should make it use the "GD Archive template" page template.
            $default_file = 'page.php'; // i think index.php works better here, more likely to have paging
            //$default_file = 'index.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_page( 'author' ) && !empty($wp_query->query['gd_favs']) ) {
            //$default_file = 'author.php';
            $default_file = 'index.php';
            self::setup_archive_loop_as_page();
        } elseif ( geodir_is_page( 'home' ) ) {
           // $default_file = 'home.php';
        } elseif ( geodir_is_page( 'location' ) ) {
            //$default_file = 'location.php';
        } elseif ( geodir_is_page( 'search' ) ) {
            $default_file = 'page.php';
            //$default_file = 'index.php';
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
            $content = GeoDir_Defaults::page_archive_content();
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
            $content = GeoDir_Defaults::page_details_content();
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


    /**
     * Setup the GD Archive page content.
     *
     * @since 2.0.0
     * @return string The filtered content.
     */
    public static function archive_item_template_content(){

        // remove our filter so we don't get stuck in a loop
       // remove_filter( 'the_content', array( __CLASS__, 'setup_archive_item_page_content' ) );

        // reset the query count so the correct number of listings are output.
       // rewind_posts();

        // reset the proper loop content
        global $wp_query,$gd_temp_wp_query;
        $wp_query->posts = $gd_temp_wp_query;

        // get the archive template page content
       // if(geodir_is_page('search')){
            //$archive_page_id = geodir_search_page_id();
       // }else{
            $archive_page_id = geodir_archive_item_page_id();
        //}
        $content = get_post_field('post_content', $archive_page_id  );

        // if the content is blank then just add the main loop
        if($content==''){
            $content = GeoDir_Defaults::page_archive_item_content();
        }

        // run the shortcodes on the content
        $content = do_shortcode($content);

        // add our filter back, not sure we even need to add it back if we are only running it once.
        //add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );

        // fake the has_posts() to false so it will not loop any more.
        //$wp_query->current_post = $wp_query->post_count;

        return $content;
    }


    /**
     * Attempt to remove the theme featured image output if set to do so.
     */
    public static function disable_theme_featured_output(){
        if(geodir_is_singular() && geodir_get_option('details_disable_featured',true) ){
            add_filter( "get_post_metadata", array(__CLASS__,'filter_thumbnail_id'), 10, 4 );
        }
    }

    /**
     * Filter the post_meta _thumbnail_id
     * 
     * @param $metadata
     * @param $object_id
     * @param $meta_key
     * @param $single
     *
     * @return bool
     */
    public static function filter_thumbnail_id($metadata, $object_id, $meta_key, $single){

        if($meta_key=='_thumbnail_id'){
            $metadata = false;
        }

        // should only need to fire once:
        remove_action( "wp", array(__CLASS__,'disable_theme_featured_output') );

        return $metadata;
    }
	
	/**
     * Setup the map popup content.
     *
     * @since 2.0.0
     * @return string The filtered content.
     */
    public static function map_popup_template_content(){
		ob_start();

		geodir_get_template_part( 'map', 'popup' );

		$content = ob_get_clean();

		if ( ! empty( $content ) ) {
			// run the shortcodes on the content
			$content = do_shortcode( $content );
		}

		return $content;
    }

}

GeoDir_Template_Loader::init();
