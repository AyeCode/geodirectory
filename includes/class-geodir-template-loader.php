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

//        global $wp_filter;
//        print_r($wp_filter['the_content']);exit;


        // filter the templates
        add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

        // remove the theme featured output
        add_action( "wp", array(__CLASS__,'disable_theme_featured_output') );

        // disable GD page templates on frontend
        add_action( "wp", array(__CLASS__,'disable_page_templates_frontend') );


        add_action( 'post_updated', array(__CLASS__,'set_clear_list_view_storage'), 10, 3 );

        // set search as post_type archive
//        add_action( "pre_handle_404", array(__CLASS__,'set_search_as_archive'),0);


    }

    // @todo we might need to adjust some query vars for beaver themer search page if the add our hooks.
//    public static function set_search_as_archive(){//echo 'xxx';exit;
//        global $wp_query;
//        if(!empty($wp_query)){
//
////            print_r( $wp_query->query_vars );
//
////            $wp_query->is_search = 0;
//            $wp_query->post_type = 'gd_place';
//            $wp_query->set('post_type', 'gd_place');
//            $wp_query->is_post_type_archive = 1;//echo '###x';exit;
//        }
//    }

    /**
     * If saving a page that contains the [gd_loop] shortcode then we set a flag to blank the localStorage for the admin so they see the change instantly.
     *
     * @param $post_ID
     * @param $post_after
     * @param $post_before
	 * @todo we will need ot updated this for block themes
     */
    public static function set_clear_list_view_storage($post_ID, $post_after, $post_before){
        if($post_after->post_type=='page' && has_shortcode( $post_after->post_content, 'gd_loop' ) ){
            geodir_update_option('clear_list_view_storage',true);
        }
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
	 * @since 2.0.60 Customizr Pro theme does not locates the GD templates so use default template.
	 *
	 * @param mixed $template
	 * @return string
	 */
	public static function template_loader( $template ) {
		if ( is_attachment() || is_embed() || ( is_404() && ! isset( $_REQUEST['geodir_search'] ) ) ) {
			return $template;
		}

		if ( $default_file = self::get_template_loader_default_file( $template ) ) {
			/**
			 * Filter hook to choose which files to find before GeoDirectory does it's own logic.
			 *
			 * @since 2.0.0
			 * @var array
			 */
			$search_files = self::get_template_loader_files( $default_file );

			$gd_template = locate_template( $search_files );

			if ( ! $gd_template && $default_file && $default_file !== ' ' ) {
				$gd_template = geodir_get_templates_dir() . '/' . $default_file;
			}

			if ( $gd_template ) {
				$template = $gd_template;
			}
		}

		return $template;
	}

	/**
	 * Get the default filename for a template.
	 *
	 * @param  string $default_template Default template file.
	 * @return string
	 */
	private static function get_template_loader_default_file( $default_template = '' ) {
		global $wp_query;

		$default_file = '';

		if ( geodir_is_geodir_page() ) {
			// Fake a return to trigger the defaults.
			$default_file = ' ';
		} else {
			return '';
		}

		if ( geodir_is_singular() ) {
			$single_template = geodir_get_option( 'details_page_template' );

			if ( $single_template && locate_template( $single_template ) ) {
				$default_file = $single_template;
			} else {
				$post_type = geodir_get_current_posttype();
				$page_id = geodir_details_page_id( $post_type );

				if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
					// Make sure the template exists before loading it, it might be from a old theme
					if ( locate_template( $template ) ) {
						$wp_query->is_page = 1;
						$default_file = $template;
					}
				} else {
					// Check if we have a theme compat setting.
					if ( $theme_template = GeoDir_Compatibility::theme_single_template() ) {
						$default_file = $theme_template;
					}
				}
			}

			// Setup the page content.
			add_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );
		} else if ( geodir_is_page( 'location' ) ) {
			$page_id = geodir_location_page_id();

			if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ){
				// Make sure the template exists before loading it, it might be from a old theme
				if ( locate_template( $template ) ) {
					//$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
					$default_file = $template;
				}
			}
		} else if ( geodir_is_page( 'add-listing' ) ) {
			// The add listing page should never be cached
			geodir_nocache_headers();

			$post_type = geodir_get_current_posttype();
			$page_id = geodir_add_listing_page_id( $post_type );

			if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
				// Make sure the template exists before loading it, it might be from a old theme
				if ( locate_template( $template ) ) {
					//$wp_query->is_page = 1; // @todo, is this needed? (depends on theme?)
					$default_file = $template;
				}
			}
		} else {
			$archive_template = geodir_get_option( 'archive_page_template' );

			if ( $archive_template && locate_template( $archive_template ) ) {
				$default_file = $archive_template;
			} else {
				$post_type = geodir_get_current_posttype();
				//$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)

				if ( geodir_is_page( 'search' ) ) {
					$page_id = geodir_search_page_id();
					// $wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
				}

				if ( ! isset( $page_id ) ) {
					$page_id = geodir_archive_page_id( $post_type );
				}

				if ( $page_id && ( $template = get_page_template_slug( $page_id ) ) ) {
					// Make sure the template exists before loading it, it might be from a old theme
					if ( locate_template( $template ) ) {
						//$wp_query->is_page = 1; //@todo, is this needed? (depends on theme?)
						$default_file = $template;
					}
				}
			}

			if ( geodir_is_taxonomy() || geodir_is_post_type_archive() || ( geodir_is_page( 'author' ) && ! empty( $wp_query->query['gd_favs'] ) ) || geodir_is_page( 'search' ) ) {
				self::setup_archive_loop_as_page( $default_file, $default_template );
			}

			// Fake some stuff for search page
			if ( geodir_is_page( 'search' ) ) {
				// If no posts found on search it goes to 404 so we fake it.
				//$wp_query->found_posts = 1;
				$wp_query->is_404 = '';
				$wp_query->is_page = 1;
				$wp_query->is_archive = 1;
				$wp_query->is_search = 1;
			}
		}

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

	    if ( !empty($default_file) && $default_file !== ' ' ) {
		    $search_files[] = $default_file;
		    $search_files[] = geodir_get_theme_template_dir_name() . '/' . $default_file;
	    }

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

        // Some themes like Twenty Twenty does not contain optional file page.php.
        if ( ( empty( $default_file ) || $default_file === ' ' ) && isset( $_REQUEST['geodir_search'] ) && is_search() && ! get_query_template( 'page' ) && get_index_template() ) {
            $search_files[] = 'index.php';
        }

        return array_unique( $search_files );
    }

    public static function is_archive_page_id( $id ) {
        global $geodirectory;
        $page_archive_id = isset( $geodirectory->settings['page_archive'] ) ? $geodirectory->settings['page_archive'] : 0;

		$result = false;
        if ( $id == $page_archive_id ) { // default page check
            $result = true;
        } elseif( geodir_is_cpt_template_page( $id ) ) { // could be a CPT specific page
            $result = true;
        }

        return apply_filters( 'geodir_is_archive_page_id', $result, $id );
    }

    /**
     * Check if we are dealing with archive page content.
     *
     * @return bool
     */
    public static function is_archive_page_content(){
        global $post,$wp_query,$geodirectory;
        $result = false;
        $queried_object = get_queried_object();

        if( ( (!empty($post) && $post->post_type=='page') || isset($_REQUEST['geodir_search']) ) && ! empty( $wp_query ) && !empty($queried_object)){
            if(!empty($queried_object->term_id)){// term
                $result = self::is_archive_page_id($post->ID);
            }elseif(!empty($queried_object->has_archive)){// CPT
                $result = self::is_archive_page_id($post->ID);
            }elseif(!empty($queried_object->ID) && $queried_object->ID==geodir_search_page_id()){ // search
                $result = true;
            }
        }

        return $result;
    }

	/**
	 * Setup the GD Archive page content.
	 *
	 * @since 2.0.0
	 *
	 * @global bool $gd_skip_the_content Prevent looping for the_content from listing post_content.
	 *
	 * @return string The filtered content.
	 */
	public static function setup_archive_page_content( $content ) {
		global $wp_query, $post, $gd_done_archive_loop, $gd_skip_the_content, $gd_in_gd_loop;

		// If we are not filtering the archive page content then bail.
		if ( $gd_skip_the_content || ! self::is_archive_page_content() || $gd_in_gd_loop ) {
			return $content;
		}

		// If its outside the loop then bail so we don't set the current_post number and cause have_posts() to return false.
		if ( ! in_the_loop() ) {
			if ( current_filter() == 'the_excerpt' && $gd_done_archive_loop ) {
				// We might be inside a "the_excerpt" filter which might be outside the loop so we don't return.
			} else {
				/**
				 * Bypass setup archive page content.
				 *
				 * @since 2.2.23
				 *
				 * @param bool|string $bypass_content True to basspass content.
				 * @param string      $content Loop content.
				 */
				$bypass_content = apply_filters( 'geodir_bypass_setup_archive_page_content', true, $content );

				if ( $bypass_content === true ) {
					return;
				} else if ( $bypass_content !== false ) {
					return $content;
				}
			}
		}

		global $gd_archive_content_start;

		// Backup post.
		$gd_backup_post = $post;
		$gd_archive_content_start = true;

		// Remove our filter so we don't get stuck in a loop
		remove_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );
		remove_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );

		// Reset the query count so the correct number of listings are output.
		if ( ! empty( $wp_query->posts ) ) {
			rewind_posts();
		}

		// Reset the proper loop content
		global $wp_query,$gd_temp_wp_query;
		$wp_query->posts = $gd_temp_wp_query;

		// Stop any GD archive pages outputting the comments section
		global $gd_is_comment_template_set;
		$gd_is_comment_template_set = true;

		// Get the archive template page content
		if ( geodir_is_page( 'search' ) ) {
			$archive_page_id = geodir_search_page_id();
		} else {
			$post_type = geodir_get_current_posttype();
			$archive_page_id = geodir_archive_page_id( $post_type );
		}

		$content = get_post_field( 'post_content', $archive_page_id  );

		/**
		 * Overwrite the archive template content.
		 *
		 * @since 2.2.17
		 *
		 * @param string $overwrite_content Overwrite content. Default empty.
		 * @param string $content           Archive template content.
		 * @param string $archive_page_id   Archive template ID.
		 */
		$overwrite_content = apply_filters( 'geodir_overwrite_archive_template_content', '', $content, $archive_page_id );
		if ( $overwrite_content ) {
			$content = $overwrite_content;
		} else {
			// If the content is blank then just add the main loop
			if ( $content == '' ) {
				$content = GeoDir_Defaults::page_archive_content();
			}

			// Run the shortcodes on the content
			$content = do_shortcode( $content );

			// Run block content if its available
			if ( function_exists( 'do_blocks' ) ) {
				$content = do_blocks( $content );
			}
		}

		// Add our filter back, not sure we even need to add it back if we are only running it once.
		add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );
		add_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );

		// Fake the has_posts() to false so it will not loop any more.
		$wp_query->current_post  = $wp_query->post_count;

		// Set original post.
		if ( ! empty( $gd_backup_post ) ) {
			$post = $gd_backup_post;
		}

		// Set that the gd archive loop has run.
		$gd_done_archive_loop = true;

		return $content;
	}

	/**
	 * Setup the GD archive loop content.
	 *
	 * @since 2.0.0
	 *
	 * @param string $default_file Default archive loop template file.
	 * @param string $default_template Default template.
	 */
	public static function setup_archive_loop_as_page( $default_file = '', $default_template = '' ) {
		global $wp_query;

		/*
		 * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
		 */
		if ( apply_filters('geodir_bypass_setup_archive_loop_as_page', false, $default_file, $default_template ) ) {
			return;
		}

		// Declare our global var so we can store the main query temporarily.
		global $gd_temp_wp_query, $gd_temp_wp_query_set, $gd_done_archive_loop;

		// Set our temp var with the main query posts.
		$gd_temp_wp_query = $wp_query->posts;
		$gd_temp_wp_query_set = true;

		// Set the main query to our archive page template.
		if ( geodir_is_page( 'search' ) ) {
			$post_type = '';
			$archive_page_id = geodir_search_page_id();
		} else {
			$post_type = geodir_get_current_posttype();
			$archive_page_id = geodir_archive_page_id( $post_type );
		}

		$archive_page = ! empty( $archive_page_id ) ? get_post( $archive_page_id ) : array();

		if ( ! empty( $archive_page ) ) {
			$wp_query->posts = array( $archive_page );
		} else {
			$wp_query->posts = array();

			geodir_error_log( 'Archive page template not found', $post_type . ':' . $archive_page_id, __FILE__, __LINE__ );
		}

		$wp_query->post = $archive_page;

		// If no posts are found then the page will not display so we fake it
		if ( empty( $gd_temp_wp_query ) ) {
			$wp_query->post_count = 1;
		}

		// We set a global so we can check if the gd archive loop has run.
		$gd_done_archive_loop = false;

		// Add the filter to call our own loop for the archive page content.
		add_filter( 'the_content', array( __CLASS__, 'setup_archive_page_content' ) );

		// If the template is only using the excerpt then bypass it.
		add_filter( 'the_excerpt', array( __CLASS__, 'setup_archive_page_content' ) );
	}

	/**
	 * Setup the GD archive loop content.
	 *
	 * @since 2.0.0
	 */
	public static function setup_singular_page( $content ) {
		// @todo this is Kiran's solution, lets keep an eye out and report any situations where this does not work out.
		global $post, $wp_query;

		if ( ! ( ! empty( $wp_query ) && ! empty( $post ) && ( $post->ID == get_queried_object_id() ) ) ) {
			return $content;
		}

		if ( post_password_required() ) {
			return $content;
		}

		/*
		 * Some page builders need to be able to take control here so we add a filter to bypass it on the fly
		 */
		if ( apply_filters( 'geodir_bypass_setup_singular_page', false ) ) {
			return $content;
		}

		// Remove our filter so we don't get stuck in a loop.
		remove_filter( 'the_content', array( __CLASS__, 'setup_singular_page' ) );

		if ( in_the_loop() ) {
			// Get the archive template page content
			$post_type = geodir_get_current_posttype();
			$page_id = geodir_details_page_id( $post_type );
			$content = get_post_field( 'post_content', $page_id );

			/**
			 * Overwrite the single template content.
			 *
			 * @since 2.2.18
			 *
			 * @param string $overwrite_content Overwrite content. Default empty.
			 * @param string $content           Single template content.
			 * @param string $page_id           Single template ID.
			 */
			$overwrite_content = apply_filters( 'geodir_overwrite_single_template_content', '', $content, $page_id );

			if ( $overwrite_content ) {
				$content = $overwrite_content;
			} else {
				// If the content is blank then just add the main loop.
				if ( $content == '' ) {
					$content = GeoDir_Defaults::page_details_content();
				}

				// Run the shortcodes on the content.
				$content = do_shortcode( $content );

				// Run block content if its available.
				if ( function_exists( 'do_blocks' ) ) {
					$content = do_blocks( $content );
				}
			}
		}

		// Add our filter back.
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
	public static function archive_item_template_content( $post_type = '', $page_id = 0 ) {
		global $geodir_item_tmpl;

		$content = '';
		$type    = 'page_id';
		// Get the archive template page content
		if ( (int) $page_id > 0 ) {
			$archive_page_id = (int) $page_id;
		} else if ( ! empty( $geodir_item_tmpl['type'] ) && $geodir_item_tmpl['type'] == 'page' && ! empty( $geodir_item_tmpl['id'] ) ) {
			$archive_page_id = (int) $geodir_item_tmpl['id'];
		} else if ( ! empty( $geodir_item_tmpl['type'] ) && $geodir_item_tmpl['type'] == 'template_part' && ! empty( $geodir_item_tmpl['content'] ) && geodir_is_block_theme() ) {
			$content = $geodir_item_tmpl['content'];
			$archive_page_id = esc_attr($geodir_item_tmpl['id']);
			$type = 'template_part';
		} else {
			$archive_page_id = (int) geodir_archive_item_page_id( $post_type );
		}

		if ( ! $content ) {
			$content = get_post_field( 'post_content', $archive_page_id  );
		}

		// Maybe bypass content
		$bypass_content = apply_filters( 'geodir_bypass_archive_item_template_content', '', $content, $archive_page_id, $type );
		if ( $bypass_content ) {
			return $bypass_content;
		}

		// If the content is blank then we grab the page defaults
		if ( $content == '' ) {
			$content = GeoDir_Defaults::page_archive_item_content();
		}

		// Run block content if its available
		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}

		// Run the shortcodes on the content
		$content = do_shortcode( $content ); // @todo this causes issues with nested blocks in the archive template block template part, specifically it outputs the last "[/bs_container]"  is it required here?

		return $content;
	}

    /**
     * Attempt to remove the theme featured image output if set to do so.
     *
     * @since 2.0.0
     */
    public static function disable_theme_featured_output(){
        if(geodir_is_singular() && geodir_get_option('details_disable_featured',false) ){
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
        global $wp_query;

        // try to fire only for the current post.
        if($meta_key=='_thumbnail_id' &&  ! empty( $wp_query ) && $object_id == get_queried_object_id() ){
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
     * @since 2.0.0.90 Added a post type as template part name.
     *
     * @global object $gd_post GeoDirectory post object.
     *
     * @return string The filtered content.
     */
    public static function map_popup_template_content() {
        global $gd_post;

        $design_style = geodir_design_style();

        $template = $design_style ? $design_style."/map/map-popup.php" : "map-popup.php";

        $content = geodir_get_template_html( $template  );

        if ( ! empty( $content ) ) {
            // Run the shortcodes on the content
            $content = do_shortcode( $content );

            // Run block content if its available
            if ( function_exists( 'do_blocks' ) ) {
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
    public static function disable_page_templates_frontend() {
        global $post;

        if ( isset( $post->ID ) && ! current_user_can( 'administrator' ) ) {
            if ( geodir_is_cpt_template_page( $post->ID, 'add' ) ) {
                return; // Bail for add listing page.
            }

            if ( $post->ID == geodir_get_option( 'page_details' )
                || $post->ID == geodir_get_option( 'page_archive' )
                || $post->ID == geodir_get_option( 'page_archive_item' )
                || geodir_is_cpt_template_page( $post->ID )
            ) {
                wp_redirect( home_url(), 301 );
                exit;
            }
        }
    }
}

GeoDir_Template_Loader::init();
