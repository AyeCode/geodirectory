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
     *
     * @since 2.0.0
     */
    public static function init() {
        // filter the templates
        add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

        // remove the theme featured output
        add_action( "wp", array(__CLASS__,'disable_theme_featured_output') );

        // disable GD page templates on frontend
        add_action( "wp", array(__CLASS__,'disable_page_templates_frontend') );

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
     * @since 2.0.0
     *
     * @param mixed $template
     * @return string
     */
    public static function template_loader( $template ) { //return $template;
        if ( is_embed() || (is_404() && !isset($_REQUEST['geodir_search']))) {
            return $template;
        }

        if ( $default_file = self::get_template_loader_default_file() ) {//echo '###'. $default_file;
            /**
             * Filter hook to choose which files to find before GeoDirectory does it's own logic.
             *
             * @since 2.0.0
             * @var array
             */
            $search_files = self::get_template_loader_files( $default_file );
            $template     = locate_template( $search_files );
           // echo '###'. $template;
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

        if(geodir_is_geodir_page()){
            $default_file = ' '; // fake a return to trigger the defaults

            //echo '###xxx';
        }else{
            return '';
        }

        if ( geodir_is_singular() ) {
            $single_template = geodir_get_option('details_page_template');

            if($single_template && locate_template( $single_template )){
                $default_file = $single_template;
            }else{
				$post_type = geodir_get_current_posttype();
                $page_id = geodir_details_page_id($post_type);
                if($page_id &&  $template = get_page_template_slug( $page_id )){
                    // make sure the template exists before loading it, it might be from a old theme
                    if(locate_template( $template )){
                        $wp_query->is_page = 1;
                        $default_file = $template;
                    }

                }else{
                    // check if we have a theme compat setting.
                    if($theme_template = GeoDir_Compatibility::theme_single_template()){
                        $default_file = $theme_template;
                    }
                }
            }


            // setup the page content
            add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );
        } elseif(geodir_is_page( 'location' )){
            $page_id = geodir_location_page_id();
            if($page_id &&  $template = get_page_template_slug( $page_id )){
                // make sure the template exists before loading it, it might be from a old theme
                if(locate_template( $template )){
                    //$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
                    $default_file = $template;
                }
            }
        } elseif(geodir_is_page( 'add-listing' )){

            // the add listing page should never be cached
            geodir_nocache_headers();

            $page_id = geodir_add_listing_page_id();
            if($page_id &&  $template = get_page_template_slug( $page_id )){
                // make sure the template exists before loading it, it might be from a old theme
                if(locate_template( $template )){
                    //$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
                    $default_file = $template;
                }
            }
        }else{

            $archive_template = geodir_get_option('archive_page_template');

            if($archive_template && locate_template( $archive_template )){
                $default_file = $archive_template;
            }else{
                $post_type = geodir_get_current_posttype();
                //$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)

                if(geodir_is_page( 'search' ) ){
                    $page_id = geodir_search_page_id();
                   // $wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
                }

                if(!isset($page_id)){
                    $page_id = geodir_archive_page_id($post_type);
                }

                if($page_id &&  $template = get_page_template_slug( $page_id )){
                    // make sure the template exists before loading it, it might be from a old theme
                    if(locate_template( $template )){
                        //$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
                        $default_file = $template;
                    }
                }
            }

            if(
                geodir_is_taxonomy()
                || geodir_is_post_type_archive()
                ||( geodir_is_page( 'author' ) && !empty($wp_query->query['gd_favs']) )
                || geodir_is_page( 'search' )
            ){
                self::setup_archive_loop_as_page();
            }


            // fake some stuff for search page
            if ( geodir_is_page( 'search' ) ) {
                // If no posts found on search it goes to 404 so we fake it.
                //$wp_query->found_posts = 1;
                $wp_query->is_404 = '';
                $wp_query->is_page = 1;
                $wp_query->is_archive = 1;
                $wp_query->is_search = 1;
            }
        }

       // echo '###'.$default_file.'###';

        return $default_file;
    }

    /**
     * Get an array of filenames to search for a given template.
     *
     * @since  2.0.0
     * @param  string $default_file The default file name.
     * @return array $search_files.
     */
    private static function get_template_loader_files( $default_file ) {
        $search_files = apply_filters( 'geodir_template_loader_files', array(), $default_file );

        if ( geodir_is_taxonomy() ) {
            $term = get_queried_object();

            $search_files[] = 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
            $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
            $search_files[] = 'taxonomy-' . $term->taxonomy . '.php';
            $search_files[] = geodir_get_theme_template_dir_name() . '/taxonomy-' . $term->taxonomy . '.php';
        }

        $search_files[] = $default_file;
        $search_files[] = geodir_get_theme_template_dir_name() . '/' . $default_file;

        // check for archive template
        if(geodir_is_page('archive') || geodir_is_page('search')){
            $search_files[] = 'geodirectory-archive.php';
        }

        // check for single template
        if(geodir_is_page('single')){
            $search_files[] = 'geodirectory-single.php';
        }

        $search_files[] = 'geodirectory.php';
        $search_files[] = 'page.php';

        return array_unique( $search_files );
    }


    /**
     * Setup the GD Archive page content.
     *
     * @since 2.0.0
     * @return string The filtered content.
     */
    public static function setup_archive_page_content(){
		global $wp_query, $post,$gd_done_archive_loop;

        // if its outside the loop then bail so we don't set the current_post number and cause have_posts() to return false.
        if(!in_the_loop()){

            if(current_filter()=='the_excerpt' && $gd_done_archive_loop){
                // we might be inside a "the_excerpt" filter which might be outside the loop so we don't return.
            }else{
                return;
            }
        }

		/* Fix SEOPress conflict */
		if ( ! ( ! empty( $post ) && ! empty( $post->post_type ) && $post->post_type == 'page' ) ) {
			return;
		}

		// Backpup post.
		$gd_backup_post = $post;

        // remove our filter so we don't get stuck in a loop
        remove_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );
        remove_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );

        // reset the query count so the correct number of listings are output.
        if ( ! empty( $wp_query->posts ) ) {
			rewind_posts();
		}

        // reset the proper loop content
        global $wp_query,$gd_temp_wp_query;
        $wp_query->posts = $gd_temp_wp_query;

        // stop any GD archive pages outputing the comments section
        global $gd_is_comment_template_set;
        $gd_is_comment_template_set = true;

        // get the archive template page content
        if(geodir_is_page('search')){
            $archive_page_id = geodir_search_page_id();
        }else{
            $post_type = geodir_get_current_posttype();
			$archive_page_id = geodir_archive_page_id($post_type);
        }
        $content = get_post_field('post_content', $archive_page_id  );

        // if the content is blank then just add the main loop
        if($content==''){
            $content = GeoDir_Defaults::page_archive_content();
        }

        //$content = wpautop($content);// add double line breaks
        // run the shortcodes on the content
        $content = do_shortcode($content);

        // run block content if its available
        if(function_exists('do_blocks')){
            $content = do_blocks( $content );
        }

        // add our filter back, not sure we even need to add it back if we are only running it once.
        add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );
        add_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );

        // fake the has_posts() to false so it will not loop any more.
        $wp_query->current_post  = $wp_query->post_count;

		// Set original post.
		if ( ! empty( $gd_backup_post ) ) {
			$post = $gd_backup_post;
		}


        // set that the gd archive loop has run.
        $gd_done_archive_loop = true;

        return $content;
    }

    /**
     * Setup the GD archive loop content.
     *
     * @since 2.0.0
     */
    public static function setup_archive_loop_as_page(){


        /*
         * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
         */
        if(apply_filters('geodir_bypass_setup_archive_loop_as_page',false)){
            return;
        }

        // get the main query
        global $wp_query;

        // declare our global var so we can store the main query temporarily.
        global $gd_temp_wp_query;

        // Set our temp var with the main query posts.
        $gd_temp_wp_query = $wp_query->posts;


        //print_r($gd_temp_wp_query ); echo '@@@';
        // Set the main query to our archive page template
        if(geodir_is_page('search')){
            $archive_page_id = geodir_search_page_id();
        }else{
			$post_type = geodir_get_current_posttype();
            $archive_page_id = geodir_archive_page_id($post_type);
        }
        $archive_page = get_post($archive_page_id);
        $wp_query->posts = array($archive_page);
        //$wp_query->posts = array();
        $wp_query->post = $archive_page;


        // if no posts are found then the page will not display so we fake it
        if(empty($gd_temp_wp_query)){
            $wp_query->post_count = 1;
        }

        // we set a global so we can check if the gd archive loop has run
        global $gd_done_archive_loop;
        $gd_done_archive_loop = false;

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
    public static function setup_singular_page($content){

        /*
         * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
         */
        if(apply_filters('geodir_bypass_setup_singular_page',false)){
            return $content;
        }
        
        // remove our filter so we don't get stuck in a loop
        remove_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );

        if(in_the_loop()) {

            // get the archive template page content
            $post_type = geodir_get_current_posttype();
			$page_id = geodir_details_page_id($post_type);
            $content = get_post_field( 'post_content', $page_id );

            // if the content is blank then just add the main loop
            if ( $content == '' ) {
                $content = GeoDir_Defaults::page_details_content();
            }

            // run the shortcodes on the content
            $content = do_shortcode( $content );

            // run block content if its available
            if(function_exists('do_blocks')){
                $content = do_blocks( $content );
            }
        }

        // add our filter back
        add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );


        return $content;

    }




    /**
     * Setup the GD Archive item page content.
     *
     * @since 2.0.0
	 * @param string $post_type Post type.
     * @return string $content The filtered content.
     */
    public static function archive_item_template_content($post_type = ''){


//        global $wp_query,$gd_temp_wp_query; //@todo i don't think this is needed here, remove after a few version if not.
//        if(!empty($gd_temp_wp_query)){
//            $wp_query->posts = $gd_temp_wp_query;
//        }

        // get the archive template page content
        $archive_page_id = geodir_archive_item_page_id($post_type);
        $content = get_post_field('post_content', $archive_page_id  );

        // if the content is blank then we grab the page defaults
        if($content==''){
            $content = GeoDir_Defaults::page_archive_item_content();
        }

        // run the shortcodes on the content
        $content = do_shortcode($content);

        // run block content if its available
        if(function_exists('do_blocks')){
            $content = do_blocks( $content );
        }


        return $content;
    }


    /**
     * Attempt to remove the theme featured image output if set to do so.
     *
     * @since 2.0.0
     */
    public static function disable_theme_featured_output(){
        if(geodir_is_singular() && geodir_get_option('details_disable_featured',true) ){
            add_filter( "get_post_metadata", array(__CLASS__,'filter_thumbnail_id'), 10, 4 );
        }
    }

    /**
     * Filter the post_meta _thumbnail_id
     *
     * @since 2.0.0
     * 
     * @param bool $metadata metadata.
     * @param int $object_id object id.
     * @param string $meta_key meta key.
     * @param string $single single.
     *
     * @return bool $metadata.
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

            // run block content if its available
            if(function_exists('do_blocks')){
                $content = do_blocks( $content );
            }
		}

		return $content;
    }

    /**
     * Disable our page templates from frontend viewing.
     *
     * @global object $post WordPress Post object.
     *
     * @since 2.0.0
     */
    public static function disable_page_templates_frontend(){
        global $post;
        if(isset($post->ID) && !current_user_can('administrator') && (
                $post->ID == geodir_get_option('page_details')
                || $post->ID == geodir_get_option('page_archive')
                || $post->ID == geodir_get_option('page_archive_item')
				|| geodir_is_cpt_template_page( $post->ID )
            )){
            wp_die( __( 'Sorry, this is a page template and can not be accessed from the frontend, it is used for building the layouts of GeoDirectory sections.','geodirectory' ) );
        }
    }
}

GeoDir_Template_Loader::init();
