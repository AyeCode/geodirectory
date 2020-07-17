<?php
/**
 * GeoDirectory GeoDirectory Popular Post View Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory listings widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Listings extends WP_Super_Duper {

	public $post_title_tag;

    /**
     * Register the popular posts widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'fas fa-th-list',
            'block-category'=> 'widgets',
	        'block-supports'=> array(
		        'customClassName'   => false
	        ),
            'block-keywords'=> "['listings','posts','geo']",
            'block-output'   => array( // the block visual output elements as an array
	            array(
		            'element' => 'div',
		            'title'   => __( 'Placeholder for listings', 'geodirectory' ),
		            'class'   => '[%className%]',
		            'style'   => '{background: "#eee",width: "100%", height: "450px", position:"relative"}',
		            array(
			            'element' => 'i',
			            'if_class'   => '[%animation%]=="fade" ? "far fa-image gd-fadein-animation" : "fas fa-bars gd-right-left-animation"',
			            'style'   => '{"text-align": "center", "vertical-align": "middle", "line-height": "450px", "height": "100%", width: "100%","font-size":"140px",color:"#aaa"}',
		            ),
	            ),
            ),
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_listings', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Listings','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-listings', // widget class
                'description' => esc_html__('Shows the GD listings filtered by your choices.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
            ),
        );

        


        parent::__construct( $options );

    }

    /**
     * Set the arguments later.
     *
     * @return array
     */
    public function set_arguments(){

        $arguments = array(
            'title'  => array(
                'title' => __('Title:', 'geodirectory'),
                'desc' => __('The widget title.', 'geodirectory'),
                'type' => 'text',
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false
            ),
            'post_type'  => array(
                'title' => __('Default Post Type:', 'geodirectory'),
                'desc' => __('The custom post types to show by default. Only used when there are multiple CPTs.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  geodir_get_posttypes('options-plural'),
                'default'  => 'gd_place',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Filters","geodirectory")
            ),
            'category'  => array(
                'title' => __('Categories:', 'geodirectory'),
                'desc' => __('The categories to show.', 'geodirectory'),
                'type' => 'select',
                'multiple' => true,
                'options'   =>  $this->get_categories(),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Filters","geodirectory")
            ),
            'related_to'  => array(
                'title' => __('Filter listings related to:', 'geodirectory'),
                'desc' => __('Select to filter the listings related to current listing categories/tags on detail page.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                    '' => __('No filter', 'geodirectory'),
                    'default_category' => __('Default Category only', 'geodirectory'),
                    'category' => __('Categories', 'geodirectory'),
					'tags' => __('Tags', 'geodirectory')
                ),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => true,
                'group'     => __("Filters","geodirectory")
            ),
            'tags'  => array(
                'title' => __('Filter by tags:', 'geodirectory'),
                'desc' => __('Insert separate tags with commas to filter listings by tags.', 'geodirectory'),
                'type' => 'text',
                'default' => '',
	            'placeholder' => __('garden,dinner,pizza', 'geodirectory'),
                'desc_tip' => true,
                'advanced' => true,
                'group'     => __("Filters","geodirectory")
            ),
            'post_author'  => array(
                'title' => __('Filter by author:', 'geodirectory'),
                'desc' => __('Filter by current_user, current_author or ID (default = unfiltered). current_user: Filters the listings by author id of the logged in user. current_author: Filters the listings by author id of current viewing post/listing. 11: Filters the listings by author id = 11. Leave blank to show posts from all authors.', 'geodirectory'),
                'type' => 'text',
                'default' => '',
                'desc_tip' => true,
                'advanced' => true,
                'group'     => __("Filters","geodirectory")
            ),
            'post_limit'  => array(
	            'title' => __('Posts to show:', 'geodirectory'),
	            'desc' => __('The number of posts to show by default.', 'geodirectory'),
	            'type' => 'number',
	            'default'  => '5',
	            'desc_tip' => true,
	            'advanced' => false,
	            'group'     => __("Filters","geodirectory")
            ),
            'post_ids'  => array(
	            'title' => __('Posts IDs:', 'geodirectory'),
	            'desc' => __('Enter a comma separated list of post ids (1,2,3) to limit the listing to these posts only, or a negative list (-1,-2,-3) to exclude those post IDs (negative and positive IDs can not be mixed) ', 'geodirectory'),
	            'type' => 'text',
	            'default'  => '',
	            'placeholder' => '1,2,3',
	            'desc_tip' => true,
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'add_location_filter'  => array(
	            'title' => __("Enable location filter?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '1',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'show_featured_only'  => array(
	            'title' => __("Show featured only?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'show_special_only'  => array(
	            'title' => __("Only show listings with special offers?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'with_pics_only'  => array(
	            'title' => __("Only show listings with pictures?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'with_videos_only'  => array(
	            'title' => __("Only show listings with videos?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'show_favorites_only'  => array(
	            'title' => __("Show favorited by user?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => true,
	            'group'     => __("Filters","geodirectory")
            ),
            'favorites_by_user'  => array(
	            'title' => __('Favorited by user:', 'geodirectory'),
	            'desc' => __('Display listings favorited by current_user, current_author or ID (default = unfiltered). current_user: Display listings favorited by author id of the logged in user. current_author: Display listings favorited by author id of current viewing post/listing. 11: Display listings favorited author id = 11. Leave blank to show listings favorited by logged user.', 'geodirectory'),
	            'type' => 'text',
	            'default' => '',
	            'desc_tip' => true,
	            'advanced' => true,
	            'element_require' => '[%show_favorites_only%]=="1"',
	            'group'     => __("Filters","geodirectory")
            ),
            'use_viewing_post_type'  => array(
                'title' => __("Use current viewing post type?", 'geodirectory'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '0',
                'advanced' => true,
                'group'     => __("Filters","geodirectory")
            ),
            'use_viewing_term' => array(
                'title' => __( 'Filter by current viewing category/tag?', 'geodirectory'),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value' => '1',
                'default' => '0',
                'advanced' => true,
                'group' => __( 'Filters', 'geodirectory' )
            ),
            'sort_by'  => array(
                'title' => __('Sort by:', 'geodirectory'),
                'desc' => __('How the listings should be sorted.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  $this->get_sort_options(),
                'default'  => '',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Sorting","geodirectory")
            ),
            'title_tag'  => array(
                'title' => __('Title tag:', 'geodirectory'),
                'desc' => __('The title tag used for the listings.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  array(
                    "h3"        =>  __('h3 (default)', 'geodirectory'),
                    "h2"        =>  __('h2 (if main content of page)', 'geodirectory'),
                ),
                'default'  => 'h3',
                'desc_tip' => true,
                'advanced' => false,
                'group'     => __("Design","geodirectory")
            ),
            'layout'  => array(
                'title' => __('Layout:', 'geodirectory'),
                'desc' => __('How the listings should laid out by default.', 'geodirectory'),
                'type' => 'select',
                'options'   =>  geodir_get_layout_options(),
                'default'  => 'h3',
                'desc_tip' => true,
                'advanced' => false,
	            'group'     => __("Design","geodirectory")
            ),
			'view_all_link'  => array(
                'title' => __( 'Show view all link?', 'geodirectory' ),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '1',
                'advanced' => false,
                'group'     => __("Design","geodirectory")
            ),
            'with_pagination'  => array(
                'title' => __( "Show pagination?", 'geodirectory' ),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '0',
                'advanced' => true,
                'group'     => __("Design","geodirectory")
            ),
            'top_pagination'  => array(
                'title' => __( "Show pagination on top of the listings?", 'geodirectory' ),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '0',
                'advanced' => true,
				'element_require' => '[%with_pagination%]=="1"',
                'group'     => __("Design","geodirectory")
            ),
            'bottom_pagination'  => array(
                'title' => __( "Show pagination at bottom of the listings?", 'geodirectory' ),
                'type' => 'checkbox',
                'desc_tip' => true,
                'value'  => '1',
                'default'  => '1',
                'advanced' => true,
				'element_require' => '[%with_pagination%]=="1"',
                'group'     => __("Design","geodirectory")
            ),
            'pagination_info'  => array(
                'title' => __( "Show advance pagination info?", 'geodirectory' ),
                'desc' => '',
                'type' => 'select',
                'options' =>  array(
                    "" => __( 'Never display', 'geodirectory' ),
                    "before" => __( 'Before pagination', 'geodirectory' ),
                    "after" => __( 'After pagination', 'geodirectory' ),
                ),
                'default'  => '',
                'desc_tip' => false,
                'advanced' => true,
				'element_require' => '[%with_pagination%]=="1"',
                'group'     => __("Design","geodirectory")
            ),
            'hide_if_empty'  => array(
	            'title' => __("Hide widget if no posts?", 'geodirectory'),
	            'type' => 'checkbox',
	            'desc_tip' => true,
	            'value'  => '1',
	            'default'  => '0',
	            'advanced' => false,
	            'group'     => __("Design","geodirectory")
            ),
        );

	    /*
		 * Elementor Pro features below here
		 */
	    if(defined( 'ELEMENTOR_PRO_VERSION' )){
		    $arguments['skin_id'] = array(
			    'title' => __( "Elementor Skin", 'geodirectory' ),
			    'desc' => '',
			    'type' => 'select',
			    'options' =>  GeoDir_Elementor::get_elementor_pro_skins(),
			    'default'  => '',
			    'desc_tip' => false,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );

		    $arguments['skin_column_gap'] = array(
			    'title' => __('Skin column gap', 'geodirectory'),
			    'desc' => __('The px value for the column gap.', 'geodirectory'),
			    'type' => 'number',
			    'default'  => '30',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
		    $arguments['skin_row_gap'] = array(
			    'title' => __('Skin row gap', 'geodirectory'),
			    'desc' => __('The px value for the row gap.', 'geodirectory'),
			    'type' => 'number',
			    'default'  => '35',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
	    }
	    
	    return $arguments;
    }


    /**
     * The Super block output function.
     *
     * @param array $instance Settings for the current widget instance.
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output( $instance = array(), $widget_args = array(), $content = '' ) {
        $instance = wp_parse_args(
			(array)$instance,
            array('title' => '',
                  'post_type' => '',
                  'category' => array(),
				  'related_to' => '',
				  'tags' => '',
				  'post_author' => '',
                  'category_title' => '',
                  'sort_by' => 'az',
                  'title_tag' => 'h3',
                  'list_order' => '',
                  'post_limit' => '5',
                  'post_ids' => '',
                  'layout' => 'gridview_onehalf',
                  'listing_width' => '',
                  'add_location_filter' => '1',
                  'character_count' => '20',
                  'show_featured_only' => '',
                  'show_special_only' => '',
                  'with_pics_only' => '',
                  'with_videos_only' => '',
                  'show_favorites_only' => '',
                  'favorites_by_user' => '',
                  'use_viewing_post_type' => '',
                  'use_viewing_term' => '',
                  'hide_if_empty' => '',
				  'view_all_link' => '1',
				  'with_pagination' => '0',
				  'top_pagination' => '0',
				  'bottom_pagination' => '1',
				  'pagination_info' => '',
	            // elementor settings
	              'skin_id' => '',
	              'skin_column_gap' => '',
	              'skin_row_gap' => '',
            )
        );

        ob_start();

        $this->output_html( $widget_args, $instance );

        return ob_get_clean();
    }

	/**
	 * Generates popular postview HTML.
	 *
	 * @since   1.0.0
	 * @since   1.5.1 View all link fixed for location filter disabled.
	 * @since   1.6.24 View all link should go to search page with near me selected.
	 * @package GeoDirectory
	 * @global object $post                    The current post object.
	 * @global string $gd_layout_class The girdview style of the listings for widget.
	 * @global bool $geodir_is_widget_listing  Is this a widget listing?. Default: false.
	 *
	 * @param array|string $args               Display arguments including before_title, after_title, before_widget, and
	 *                                         after_widget.
	 * @param array|string $instance           The settings for the particular instance of the widget.
	 */
	public function output_html( $args = '', $instance = '' ) {
		global $wp, $geodirectory, $gd_post, $post, $gd_advanced_pagination, $posts_per_page, $paged, $geodir_ajax_gd_listings;

		$is_single = ( geodir_is_page( 'single' ) || ! empty( $instance['set_post'] ) ) && ! empty( $gd_post ) ? true : false;

		// Prints the widget
		extract( $args, EXTR_SKIP );

		/** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
		$title = empty( $instance['title'] ) ? geodir_ucwords( $instance['category_title'] ) : apply_filters( 'widget_title', __( $instance['title'], 'geodirectory' ) );
		/**
		 * Filter the widget post type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['post_type'] Post type of listing.
		 */
		$post_type = empty( $instance['post_type'] ) ? 'gd_place' : apply_filters( 'widget_post_type', $instance['post_type'] );
		/**
		 * Filter the widget's term.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['category'] Filter by term. Can be any valid term.
		 */
		$category = empty( $instance['category'] ) ? '0' : apply_filters( 'widget_category', $instance['category'] );
		/**
		 * Filter the widget related_to param.
		 *
		 * @since 2.0.0
		 *
		 * @param string $instance ['related_to'] Filter by related to categories/tags.
		 */
		$related_to = empty( $instance['related_to'] ) ? '' : apply_filters( 'widget_related_to', ( $is_single ? $instance['related_to'] : '' ), $instance, $this->id_base );
		/**
		 * Filter the widget tags param.
		 *
		 * @since 2.0.0
		 *
		 * @param string $instance ['tags'] Filter by tags.
		 */
		$tags = empty( $instance['tags'] ) ? '' : apply_filters( 'widget_tags', $instance['tags'], $instance, $this->id_base );
		/**
		 * Filter the widget post_author param.
		 *
		 * @since 2.0.0
		 *
		 * @param string $instance ['post_author'] Filter by author.
		 */
		$post_author = empty( $instance['post_author'] ) ? '' : apply_filters( 'widget_post_author', $instance['post_author'], $instance, $this->id_base );
		/**
		 * Filter the widget listings limit.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['post_number'] Number of listings to display.
		 */
		$post_number = empty( $instance['post_limit'] ) ? '5' : apply_filters( 'widget_post_number', $instance['post_limit'] );
		/**
		 * Filter the widget listings post ids.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['post_ids'] Post ids to include or exclude.
		 */
		$post_ids = empty( $instance['post_ids'] ) ? '' : apply_filters( 'widget_post_ids', $instance['post_ids'] );
		/**
		 * Filter widget's "layout" type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['layout'] Widget layout type.
		 */
		$layout = !isset( $instance['layout'] )  ? 'gridview_onehalf' : apply_filters( 'widget_layout', $instance['layout'] );
		/**
		 * Filter widget's "add_location_filter" value.
		 *
		 * @since 1.0.0
		 *
		 * @param string|bool $instance ['add_location_filter'] Do you want to add location filter? Can be 1 or 0.
		 */
		$add_location_filter = empty( $instance['add_location_filter'] ) ? '0' : apply_filters( 'widget_add_location_filter', $instance['add_location_filter'] );
		/**
		 * Filter widget's listing width.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['listing_width'] Listing width.
		 */
		$listing_width = empty( $instance['listing_width'] ) ? '' : apply_filters( 'widget_listing_width', $instance['listing_width'] );
		/**
		 * Filter widget's "list_sort" type.
		 *
		 * @since 1.0.0
		 *
		 * @param string $instance ['list_sort'] Listing sort by type.
		 */
		$list_sort             = empty( $instance['sort_by'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['sort_by'] );
		/**
		 * Filter widget's "title_tag" type.
		 *
		 * @since 1.6.26
		 *
		 * @param string $instance ['title_tag'] Listing title tag.
		 */
		$title_tag            = empty( $instance['title_tag'] ) ? 'h3' : apply_filters( 'widget_title_tag', $instance['title_tag'] );
		/**
		 * Filter widget's "show_favorites_only" type.
		 *
		 * @since 1.6.26
		 *
		 * @param string $instance ['show_favorites_only'] Listing show favorites only.
		 */
		$show_favorites_only = empty( $instance['show_favorites_only'] ) ? '' : apply_filters( 'widget_show_favorites_only', absint( $instance['show_favorites_only'] ), $instance, $this->id_base );
		/**
		 * Filter the widget favorites_by_user param.
		 *
		 * @since 2.0.0
		 *
		 * @param string $instance ['favorites_by_user'] Filter favorites by user.
		 */
		$favorites_by_user = empty( $instance['favorites_by_user'] ) || empty( $show_favorites_only ) ? '' : apply_filters( 'widget_favorites_by_user', $instance['favorites_by_user'], $instance, $this->id_base );

		/**
		 * Filter the widget skin_id param.
		 *
		 * @since 2.0.0.86
		 *
		 * @param string $instance ['skin_id'] Filter skin_id.
		 */
		$skin_id = empty( $instance['skin_id'] ) ? '' : apply_filters( 'widget_skin_id', $instance['skin_id'], $instance, $this->id_base );

		$view_all_link = ! empty( $instance['view_all_link'] ) ? true : false;
		$use_viewing_post_type = ! empty( $instance['use_viewing_post_type'] ) ? true : false;
		$use_viewing_term = ! empty( $instance['use_viewing_term'] ) ? true : false;
		$shortcode_atts = ! empty( $instance['shortcode_atts'] ) ? $instance['shortcode_atts'] : array();
		$top_pagination = ! empty( $instance['with_pagination'] ) && ! empty( $instance['top_pagination'] ) ? true : false;
		$bottom_pagination = ! empty( $instance['with_pagination'] ) && ! empty( $instance['bottom_pagination'] ) ? true : false;
		$pagination_info = ! empty( $instance['with_pagination'] ) && ! empty( $instance['pagination_info'] ) ? $instance['pagination_info'] : '';
		$pageno = ! empty( $instance['pageno'] ) ? absint( $instance['pageno'] ) : 1;
		if ( $pageno < 1 ) {
			$pageno = 1;
		}

		// set post type to current viewing post type
		if ( $use_viewing_post_type ) {
			$current_post_type = geodir_get_current_posttype();
			if ( $current_post_type != '' && $current_post_type != $post_type ) {
				$post_type = $current_post_type;
				$category  = array(); // old post type category will not work for current changed post type
			}
		}
		if ( ( $related_to == 'default_category' || $related_to == 'category' || $related_to == 'tags' ) && ! empty( $gd_post->ID ) ) {
			if ( $post_type != $gd_post->post_type ) {
				$post_type = $gd_post->post_type;
				$category = array();
			}
		}

		// check its a GD post type, if not then bail
		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return '';
		}

		// Filter posts by current terms on category/tag/search archive pages.
		if ( $use_viewing_term ) {
			if ( is_tax() && ( $queried_object = get_queried_object() ) ) {
				if ( ! empty( $queried_object->taxonomy ) ) {
					if ( $queried_object->taxonomy == $post_type . 'category' ) {
						$category = $queried_object->term_id;
						$instance['category'] = $category;
					} elseif ( $queried_object->taxonomy == $post_type . '_tags' ) {
						$tags = $queried_object->name;
						$instance['tags'] = $tags;
					}
				}
			}

			if ( geodir_is_page( 'search' ) && ! empty( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $post_type && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
				if ( is_array( $_REQUEST['spost_category'] ) ) {
					$_post_category = array_map( 'absint', $_REQUEST['spost_category'] );
				} else {
					$_post_category = array( absint( $_REQUEST['spost_category'] ) );
				}
				$category = implode( ',', $_post_category );
				$instance['category'] = $category;
			}
		}

		// replace widget title dynamically
		$posttype_plural_label   = __( get_post_type_plural_label( $post_type ), 'geodirectory' );
		$posttype_singular_label = __( get_post_type_singular_label( $post_type ), 'geodirectory' );

		$title = str_replace( "%posttype_plural_label%", $posttype_plural_label, $title );
		$title = str_replace( "%posttype_singular_label%", $posttype_singular_label, $title );

		$categories = $category;
		$category_taxonomy = $post_type . 'category';
		$category = is_array( $category ) ? $category : explode( ",", $category ); // convert to array
		$category = apply_filters( 'geodir_filter_query_var_categories', $category, $post_type );

		if ( isset( $instance['character_count'] ) ) {
			/**
			 * Filter the widget's excerpt character count.
			 *
			 * @since 1.0.0
			 *
			 * @param int $instance ['character_count'] Excerpt character count.
			 */
			$character_count = apply_filters( 'widget_list_character_count', $instance['character_count'] );
		} else {
			$character_count = '';
		}

		if ( empty( $title ) || $title == 'All' ) {
			$title .= ' ' . __( get_post_type_plural_label( $post_type ), 'geodirectory' );
		}

		$location_allowed = GeoDir_Post_types::supports( $post_type, 'location' );

		if ( $location_allowed && $add_location_filter && ( $user_lat = get_query_var( 'user_lat' ) ) && ( $user_lon = get_query_var( 'user_lon' ) ) && geodir_is_page( 'location' ) ) {
			$viewall_url = add_query_arg( array(
				'geodir_search' => 1,
				'stype' => $post_type,
				's' => '',
				'snear' => __( 'Near:', 'geodirectory' ) . ' ' . __( 'Me', 'geodirectory' ),
				'sgeo_lat' => $user_lat,
				'sgeo_lon' => $user_lon
			), geodir_search_page_base_url() );

			if ( ! empty( $category ) && !in_array( '0', $category ) ) {
				$viewall_url = add_query_arg( array( 's' . $post_type . 'category' => $category ), $viewall_url );
			}
		} else {
			$viewall_url = get_post_type_archive_link( $post_type );

			if ( ! empty( $category ) && $category[0] != '0' ) {
				global $geodir_add_location_url;

				$geodir_add_location_url = '0';

				if ( $add_location_filter != '0' ) {
					$geodir_add_location_url = '1';
				}

				$viewall_url = get_term_link( (int) $category[0], $post_type . 'category' );

				$geodir_add_location_url = null;
			}
		}

		if ( is_wp_error( $viewall_url ) ) {
			$viewall_url = '';
		}

		$distance_to_post = $list_sort == 'distance_asc' && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) && $is_single ? true : false;

		if ( $list_sort == 'distance_asc' && ! $distance_to_post ) {
			$list_sort = geodir_get_posts_default_sort( $post_type );
		}

		 $query_args = array(
			'posts_per_page' => $post_number,
			'is_geodir_loop' => true,
			'gd_location'    => $add_location_filter ? true : false,
			'post_type'      => $post_type,
			'order_by'       => $list_sort,
			'distance_to_post' => $distance_to_post,
			'pageno'         => $pageno,
			'is_gd_author'   => ! empty( $instance['is_gd_author'] ) || geodir_is_page( 'author' )
		);

		// Post_number needs to be a positive integer
		if ( ! empty( $post_author ) ) {
			// 'current' left for backwards compatibility
			if ( $post_author == 'current' || $post_author == 'current_author' ) {
				if ( ! empty( $post ) && $post->post_type != 'page' && isset( $post->post_author ) ) {
					$query_args['post_author'] = $post->post_author;
				} else {
					$query_args['post_author'] = -1; // Don't show any listings.
				}
			} else if ( $post_author == 'current_user' ) {
				if ( is_user_logged_in() && ( $current_user_id = get_current_user_id() ) ) {
					$query_args['post_author'] = $current_user_id;
				} else {
					$query_args['post_author'] = -1; // If not logged in then don't show any listings.
				}
			} else if ( absint( $post_author ) > 0) {
				$query_args['post_author'] = absint( $post_author );
			} else {
				$query_args['post_author'] = -1; // Don't show any listings.
			}
		}

		// Posts favorited by user.
		if ( ! empty( $show_favorites_only ) ) {
			if ( empty( $favorites_by_user ) ) {
				$favorites_by_user = 'current_user';
			}

			// 'current' left for backwards compatibility
			if ( $favorites_by_user == 'current' || $favorites_by_user == 'current_author' ) {
				if ( ! empty( $post ) && $post->post_type != 'page' && isset( $post->post_author ) ) {
					$query_args['favorites_by_user'] = $post->post_author;
				} else {
					$query_args['favorites_by_user'] = -1; // Don't show any listings.
				}
			} else if ( $favorites_by_user == 'current_user' ) {
				if ( is_user_logged_in() && ( $current_user_id = get_current_user_id() ) ) {
					$query_args['favorites_by_user'] = $current_user_id;
				} else {
					$query_args['favorites_by_user'] = -1; // If not logged in then don't show any listings.
				}
			} else if ( absint( $favorites_by_user ) > 0) {
				$query_args['favorites_by_user'] = absint( $favorites_by_user );
			} else {
				$query_args['favorites_by_user'] = -1; // Don't show any listings.
			}
		}

		if ( $character_count ) {
			$query_args['excerpt_length'] = $character_count;
		}

		if ( ! empty( $instance['show_featured_only'] ) ) {
			$query_args['show_featured_only'] = 1;
		}

		if ( ! empty( $instance['show_special_only'] ) ) {
			$query_args['show_special_only'] = 1;
		}

		if ( ! empty( $instance['with_pics_only'] ) ) {
			$query_args['with_pics_only']      = 0;
			$query_args['featured_image_only'] = 1;
		}

		if ( ! empty( $instance['with_videos_only'] ) ) {
			$query_args['with_videos_only'] = 1;
		}
		$hide_if_empty = ! empty( $instance['hide_if_empty'] ) ? true : false;

		if ( ! empty( $categories ) && $categories[0] != '0' ) {
			$tax_query = array(
				'taxonomy' => $category_taxonomy,
				'field'    => 'id',
				'terms'    => $category
			);

			$query_args['tax_query'] = array( $tax_query );
		}

		if ( ( $related_to == 'default_category' || $related_to == 'category' || $related_to == 'tags' ) && ! empty( $gd_post->ID ) ) {
			$terms = array();
			$term_field = 'id';
			$term_taxonomy = $post_type . 'category'; 
			if ( $related_to == 'category' && ! empty( $gd_post->post_category ) ) {
				$terms = explode( ',', trim( $gd_post->post_category, ',' ) );
			} else if ( $related_to == 'tags' && ! empty( $gd_post->post_tags ) ) {
				$term_taxonomy = $post_type . '_tags'; 
				$term_field = 'name';
				$terms = explode( ',', trim( $gd_post->post_tags, ',' ) );
			}elseif($related_to == 'default_category' && !empty($gd_post->default_category)){
				$terms = absint($gd_post->default_category);
			}
			$query_args['post__not_in'] = $gd_post->ID;

			$query_args['tax_query'] = array( 
				array(
					'taxonomy' => $term_taxonomy,
					'field'    => $term_field,
					'terms'    => $terms
				)
			);
		} elseif ( $is_single && empty( $instance['franchise_of'] ) ) { 
			$query_args['post__not_in'] = $gd_post->ID;
		}

		// Clean tags
		if ( ! empty( $tags ) ) {
			if ( ! is_array( $tags ) ) {
				$comma = _x( ',', 'tag delimiter' );

				if ( ',' !== $comma ) {
					$tags = str_replace( $comma, ',', $tags );
				}
				$tags = explode(',', trim( $tags, " \n\t\r\0\x0B," ) );
				$tags = array_map( 'trim', $tags );
			}

			if ( ! empty( $tags ) ) {
				$tag_query = array(
					'taxonomy' => $post_type . '_tags',
					'field' => 'name',
					'terms' => $tags
				);

				if ( ! empty( $query_args['tax_query'] ) ) {
					$query_args['tax_query'][] = $tag_query;
				} else {
					$query_args['tax_query'] = array( $tag_query );
				}
			}
		}

		// $post_ids, include or exclude post ids
		if ( ! empty( $post_ids ) ) {
			$post__not_in = array();
			$post__in = array();
			$post_ids = explode( ",", $post_ids );

			foreach ( $post_ids as $pid ) {
				$tmp_id = trim( $pid );
				if ( abs( $tmp_id ) != $tmp_id ) {
					$post__not_in[] = absint( $tmp_id );
				} else {
					$post__in[] = absint( $tmp_id );
				}
			}

			if ( ! empty( $post__in ) ) {
				$query_args['post__in'] = implode( ",", $post__in );
			} elseif ( ! empty( $post__not_in ) ) {
				if ( ! empty( $query_args['post__not_in'] ) ) {
					$post__not_in[] = $query_args['post__not_in'];
				}
				$query_args['post__not_in'] = implode( ",", $post__not_in );
			}
		}

		global $geodir_widget_cpt, $gd_layout_class, $geodir_is_widget_listing;

		/*
		 * Filter widget listings query args.
		 */
		$query_args = apply_filters( 'geodir_widget_listings_query_args', $query_args, $instance );

		$query_args['country'] = isset($instance['country']) ? $instance['country'] : '';
		$query_args['region'] = isset($instance['region']) ? $instance['region'] : '';
		$query_args['city'] = isset($instance['city']) ? $instance['city'] : '';

		$post_count = geodir_get_widget_listings( $query_args, true );

		if ( $hide_if_empty && empty( $post_count ) ) {
			return;
		}

		$widget_listings = geodir_get_widget_listings( $query_args );

		// Filter post title tag.
		$this->post_title_tag = $title_tag;
		add_filter( 'geodir_widget_gd_post_title_tag', array( $this, 'filter_post_title_tag' ), 10, 4 );

		$gd_layout_class = geodir_convert_listing_view_class( $layout );

		$class = $top_pagination || $bottom_pagination ? ' geodir-wgt-pagination' : '';
		if ( $top_pagination ) {
			$class .= ' geodir-wgt-pagination-top';
		}
		if ( $bottom_pagination ) {
			$class .= ' geodir-wgt-pagination-bottom';
		}
		$backup_posts_per_page = $posts_per_page;
		$backup_paged = $paged;
		$backup_gd_advanced_pagination = $gd_advanced_pagination;

		$geodir_widget_cpt = $post_type;
		$posts_per_page = $post_number;
		$paged = $pageno;
		$gd_advanced_pagination = $pagination_info;
		$unique_id = 'geodir_' . uniqid();

		// Elementor
		$skin_active = false;
		$elementor_wrapper_class = '';
		if ( defined( 'ELEMENTOR_PRO_VERSION' )  && $skin_id ) {
			if ( get_post_status ( $skin_id ) == 'publish' ) {
				$skin_active = true;
			}

			if ( $skin_active ) {
				$columns = isset( $layout ) ? absint( $layout ) : 1;
				if ( $columns == '0' ) {
					$columns = 6; // we have no 6 row option to lets use list view
				}
				$elementor_wrapper_class = ' elementor-element elementor-element-9ff57fdx elementor-posts--thumbnail-top elementor-grid-' . $columns . ' elementor-grid-tablet-2 elementor-grid-mobile-1 elementor-widget elementor-widget-posts ';
			}
		}

		?>
		<div id="<?php echo $unique_id; ?>" class="geodir_locations geodir_location_listing<?php echo $class; echo $elementor_wrapper_class; ?>">
			<?php
			if ( ! isset( $character_count ) ) {
				/**
				 * Filter the widget's excerpt character count.
				 *
				 * @since 1.0.0
				 *
				 * @param int $instance ['character_count'] Excerpt character count.
				 */
				$character_count = $character_count == '' ? 50 : apply_filters( 'widget_character_count', $character_count );
			}

			if ( isset( $post ) ) {
				$reset_post = $post;
			}
			if ( isset( $gd_post ) ) {
				$reset_gd_post = $gd_post;
			}
			$geodir_is_widget_listing = true;

			if ( ! empty( $widget_listings ) && $top_pagination ) {
				self::get_pagination( 'top', $post_count, $post_number, $pageno );
			}

			if($skin_active){
				$column_gap = !empty($instance['skin_column_gap']) ? absint($instance['skin_column_gap']) : '';
				$row_gap = !empty($instance['skin_row_gap']) ? absint($instance['skin_row_gap']) : '';
				geodir_get_template( 'elementor/content-widget-listing.php', array( 'widget_listings' => $widget_listings,'skin_id' => $skin_id,'columns'=>$columns,'column_gap'=> $column_gap,'row_gap'=>$row_gap ) );
			}else{
				geodir_get_template( 'content-widget-listing.php', array( 'widget_listings' => $widget_listings ) );
			}


			if ( ! empty( $widget_listings ) && ( $bottom_pagination || $top_pagination ) ) {
				echo '<div class="geodir-ajax-listings-loader" style="display:none"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';

				if ( $bottom_pagination ) {
					self::get_pagination( 'bottom', $post_count, $post_number, $pageno );
				}
			}

			if ( ! empty( $widget_listings ) && $view_all_link && $viewall_url ) {
				/**
				 * Filter view all url.
				 *
				 * @since 2.0.0
				 *
				 * @param string $viewall_url View all url.
				 * @param array $query_args WP_Query args.
				 * @param array $instance Widget settings.
				 * @param array $args Widget arguments.
				 * @param object $this The GeoDir_Widget_Listings object.
				 */
				$viewall_url = apply_filters( 'geodir_widget_gd_listings_view_all_url', $viewall_url, $query_args, $instance, $args, $this );

				if ( $viewall_url ) {
					$view_all_link = '<a href="' . esc_url( $viewall_url ) .'" class="geodir-all-link">' . __( 'View all', 'geodirectory' ) . '</a>';

					/**
					 * Filter view all link content.
					 *
					 * @since 2.0.0
					 *
					 * @param string $view_all_link View all listings link content.
					 * @param string $viewall_url View all url.
					 * @param array $query_args WP_Query args.
					 * @param array $instance Widget settings.
					 * @param array $args Widget arguments.
					 * @param object $this The GeoDir_Widget_Listings object.
					 */
					$view_all_link = apply_filters( 'geodir_widget_gd_listings_view_all_link', $view_all_link, $viewall_url, $query_args, $instance, $args, $this );

					echo '<div class="geodir-widget-bottom">' . $view_all_link . '</div>';
				}
			}

			$geodir_is_widget_listing = false;

			if ( isset( $reset_post ) ) {
				if ( ! empty( $reset_post ) ) {
					setup_postdata( $reset_post );
				}
				$post = $reset_post;
			}
			if ( isset( $reset_gd_post ) ) {
				$gd_post = $reset_gd_post;
			}
			if ( ! empty( $widget_listings ) && ( $top_pagination || $bottom_pagination ) ) {
				$params = array_merge( $instance, $query_args );

				$set_query_vars = (array) $wp->query_vars;
				if ( isset( $query_args['tax_query'] ) ) {
					$set_query_vars['tax_query'] = $query_args['tax_query'];
				}

				if ( isset( $query_args['post__in'] ) ) {
					$set_query_vars['post__in'] = $query_args['post__in'];
				}

				if ( isset( $query_args['post__not_in'] ) ) {
					$set_query_vars['post__not_in'] = $query_args['post__not_in'];
				}

				$params['set_query_vars'] = $set_query_vars;

				if ( $is_single ) {
					$params['set_post'] = $gd_post->ID;
				}

				if ( ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
					$params['sgeo_lat'] = isset( $_REQUEST['sgeo_lat'] ) ? filter_var( $_REQUEST['sgeo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
					$params['sgeo_lon'] = isset( $_REQUEST['sgeo_lon'] ) ? filter_var( $_REQUEST['sgeo_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : '';
				}

				foreach ( $params as $key => $value ) {
					if ( is_scalar( $value ) && ( $value === true || $value === false ) ) {
						$value = (int) $value;
					}
					$params[ $key ] = $value;
				}

				$params = apply_filters( 'geodir_widget_listings_pagination_set_params', $params, $instance, $this->id_base );
				?>
				<script type="text/javascript">
					/* <![CDATA[ */
					jQuery(function() {try {
						var params = <?php echo json_encode( $params ); ?>;
						params['action'] = 'geodir_widget_listings';
						params['widget_args'] = <?php echo json_encode( $args ); ?>;
						params['security'] = geodir_params.basic_nonce;
						geodir_widget_listings_pagination('<?php echo $unique_id; ?>', params);
					} catch (err) {
						console.log(err.message);
					}});
					/* ]]> */
				</script>
			<?php 
		}
		?>
		</div>
		<?php 

		$geodir_widget_cpt = false;
		$posts_per_page = $backup_posts_per_page;
		$paged = $backup_paged;
		$gd_advanced_pagination = $backup_gd_advanced_pagination;

		remove_filter( 'geodir_widget_gd_post_title_tag', array( $this, 'filter_post_title_tag' ), 10, 2 );
	}

    /**
     * Get categories.
     *
     * @since 2.0.0
     *
     * @param string $post_type Optional. Post type. Default gd_place0
     * @return array $options.s
     */
    public function get_categories($post_type = 'gd_place'){
	    return geodir_category_options($post_type);
    }

    /**
     * Get sort options.
     *
     * @since 2.0.0
     *
     * @param string $post_type Optional. Post type. Default gd_place.
     * @return array $options.
     */
    public function get_sort_options($post_type = 'gd_place'){
        return geodir_sort_by_options($post_type);
    }

	/**
     * Post title tag filter.
     *
     * @since 2.0.0
     *
     * @param string $tag Optional. Title tag.
     * @param array $instance Widget settings.
     * @param array $rags Optional. Widget arguments.
	 * @param object $widget Widget object.
     * @return string $title
     */
	public function filter_post_title_tag( $tag, $instance = array(), $rags = array(), $widget = array() ) {
		if ( ! empty( $this->post_title_tag ) ) {
			$tag = $this->post_title_tag;
		}

		return $tag;
	}

	public function ajax_listings( $data = array() ) {
		global $wp, $geodirectory, $post, $gd_post, $geodir_ajax_gd_listings;

		$backup_wp = $wp;
		$geodir_ajax_gd_listings = true;

		$data = apply_filters( 'geodir_widget_listings_ajax_listings', $data );
		
		if ( ! empty( $data['set_post'] ) ) {
			$post = get_post( absint( $data['set_post'] ) );
			$gd_post = geodir_get_post_info( absint( $data['set_post'] ) );
		}

		if ( ! empty( $data['set_query_vars'] ) ) {
			$wp->query_vars = $data['set_query_vars'];

			add_filter( 'geodir_location_set_current_check_404', array( $this, 'set_current_check_404' ), 999, 1 );

			$geodirectory->location->set_current();
		}

		ob_start();

		do_action( 'geodir_widget_ajax_listings_before', $data );

		if ( isset( $data['widget_args'] ) ) {
			$widget_args = (array)$data['widget_args'];
			unset( $data['widget_args'] );
		} else {
			$widget_args = array();
		}

		echo $this->output( $data, $widget_args );

		do_action( 'geodir_widget_ajax_listings_after', $data );

		$output = ob_get_clean();

		$wp = $backup_wp;
		$geodir_ajax_gd_listings = false;

		wp_send_json_success( array( 'content' => trim( $output ) ) );
	}

	public static function get_pagination( $position, $post_count, $post_number, $pageno = 1 ) {
		global $wp_query;

		$backup_wp_query = $wp_query;
		if ( isset( $wp_query->paged ) ) {
			$backup_paged = $wp_query->paged;
		}
		if ( isset( $wp_query->max_num_pages ) ) {
			$backup_max_num_pages = $wp_query->max_num_pages;
		}
		if ( isset( $wp_query->found_posts ) ) {
			$backup_found_posts = $wp_query->found_posts;
		}
		if ( isset( $wp_query->is_paged ) ) {
			$backup_is_paged = $wp_query->is_paged;
		}

		$max_num_pages = ceil( $post_count / $post_number );
		set_query_var( 'paged', $pageno );
		$wp_query->max_num_pages = $max_num_pages;
		$wp_query->found_posts = $post_count;
		$wp_query->is_paged = true;

		add_filter( 'geodir_pagination_args', array( __CLASS__, 'filter_pagination_args' ), 999999, 1 );

		ob_start();

		echo do_shortcode( '[gd_loop_paging]' );

		$pagination = ob_get_clean();

		echo $pagination;

		remove_filter( 'geodir_pagination_args', array( __CLASS__, 'filter_pagination_args' ), 999999, 1 );

		$wp_query = $backup_wp_query;
		if ( isset( $backup_paged ) ) {
			set_query_var( 'paged', $backup_paged );
		}
		if ( isset( $backup_max_num_pages ) ) {
			$wp_query->max_num_pages = $backup_max_num_pages;
		}
		if ( isset( $backup_found_posts ) ) {
			$wp_query->found_posts = $backup_found_posts;
		}
		if ( isset( $backup_is_paged ) ) {
			$wp_query->is_paged = $backup_is_paged;
		}
	}

	public static function filter_pagination_args( $pagination_args ) {
		$pagination_args['base'] = '%_%';
		$pagination_args['format'] = '#%#%#';

		return $pagination_args;
	}

	public function set_current_check_404( $check_404 ) {
		return false;
	}
}
