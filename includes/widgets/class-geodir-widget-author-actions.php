<?php
/**
* GeoDirectory Detail User Actions Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Detail_User_Actions class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Author_Actions extends WP_Super_Duper {
    
    public $arguments;

	/**
     * Sets up a new Detail User Actions widget instance.
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-site',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['author','actions','geo']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_author_actions', // this us used as the widget id and the shortcode id.
			'name'          => __( 'GD > Author Actions', 'geodirectory' ), // the name of the widget.
			'widget_ops'    => array(
				'classname'   	=> 'geodir-author-actions '.geodir_bsui_class(), // widget class
				'description' 	=> esc_html__( 'Display author actions.', 'geodirectory' ), // widget description
				'geodirectory' 	=> true,
				'gd_wgt_showhide' => 'show_on',
				'gd_wgt_restrict' => array( 'gd-detail' ),
			),
			'arguments'     => array(
				'hide_edit'  => array(
					'title' => __('Hide edit', 'geodirectory'),
					'desc' => __('Hide the edit action.', 'geodirectory'),
					'type' => 'checkbox',
					'value'=> '1',
					'default'=> '0',
					'desc_tip' => true,
					'advanced' => true
				),
				'hide_delete'  => array(
					'title' => __('Hide delete', 'geodirectory'),
					'desc' => __('Hide the delete action.', 'geodirectory'),
					'type' => 'checkbox',
					'value'=> '1',
					'default'=> '0',
					'desc_tip' => true,
					'advanced' => true
				),
				'author_page_only'  => array(
					'title' => __('Show on author page only', 'geodirectory'),
					'desc' => __('Show the action only on the author page.', 'geodirectory'),
					'type' => 'checkbox',
					'value'=> '1',
					'default'=> '0',
					'desc_tip' => true,
					'advanced' => true
				),
			)
		);

	    $design_style = geodir_design_style();

	    if($design_style) {

		    // background
		    $arguments['bg']  = geodir_get_sd_background_input('mt');

		    // margins
		    $arguments['mt']  = geodir_get_sd_margin_input('mt');
		    $arguments['mr']  = geodir_get_sd_margin_input('mr');
		    $arguments['mb']  = geodir_get_sd_margin_input('mb');
		    $arguments['ml']  = geodir_get_sd_margin_input('ml');

		    // padding
		    $arguments['pt']  = geodir_get_sd_padding_input('pt');
		    $arguments['pr']  = geodir_get_sd_padding_input('pr');
		    $arguments['pb']  = geodir_get_sd_padding_input('pb');
		    $arguments['pl']  = geodir_get_sd_padding_input('pl');

		    // border
		    $arguments['border']  = geodir_get_sd_border_input('border');
		    $arguments['rounded']  = geodir_get_sd_border_input('rounded');
		    $arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

		    // shadow
		    $arguments['shadow']  = geodir_get_sd_shadow_input('shadow');

		    $options['arguments'] = $options['arguments'] + $arguments;

	    }

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
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $post;

//		if ( is_preview() ) {
//			//return;
//		}

		$defaults = array(
			'hide_edit'      => 0,
			'hide_delete'      => 0,
			'author_page_only'      => 0,
			'bg'    => '',
			'mt'    => '',
			'mb'    => '3',
			'mr'    => '',
			'ml'    => '',
			'pt'    => '',
			'pb'    => '',
			'pr'    => '',
			'pl'    => '',
			'border'    => '',
			'rounded'    => '',
			'rounded_size'    => '',
			'shadow'    => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$is_preview = $this->is_preview();

		$show = true;
		if ( $args['author_page_only'] && ! self::is_author_page() && ! $is_preview ) {
			$show = false;
		}

		$output = '';
		if( $show && !empty($post->ID) && geodir_listing_belong_to_current_user($post->ID)){


			$post_id = $post->ID;
			if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
				$post_id = absint($_REQUEST['pid']);
			}


			$author_actions = array();

			// status
			$post_status = self::post_status_author_page();// the post status on the author page
			if(!empty($post_status)){
				$author_actions['status'] = $post_status;
			}

			// Edit
			if(!$args['hide_edit']){

				$editlink = geodir_edit_post_link($post_id);
				if($editlink){
					$author_actions['edit'] = array(
						'icon'  => 'fas fa-pencil-alt',
						'title' => __('Edit', 'geodirectory'),
						'url'   => esc_url($editlink)
					);
				}
			}

			// Delete
			if(!$args['hide_delete']) {
				$author_actions['delete'] = array(
					'icon'  => 'fas fa-trash',
					'title' => __('Delete', 'geodirectory'),
					'url'   => 'javascript:void(0);',
					'onclick'   => 'gd_delete_post('.$post_id.');'
				);
			}

			// wrap class
			$wrap_class = geodir_build_aui_class($args);


			/*
			 * Filter the author actions.
			 *
			 * @since 2.1.0
			 * @param array $author_actions An array of author actions.
			 * @param int $post_id The post id.
			 */
			$author_actions = apply_filters('geodir_author_actions',$author_actions,$post_id);


			$design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : geodir_design_style();
			$template = $design_style ? $design_style."/author-actions.php" : "author-actions.php";
			
			$output = geodir_get_template_html( $template, array(
				'author_actions'    => $author_actions,
				'wrap_class'   => $wrap_class
			) );

		}

		return $output;
	}


	/**
	 * Adds post status on author page when the author is current user.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global object $post The current post object.
	 */
	public static function post_status_author_page()
	{
		global $wpdb, $post;

		$status_parts = array();
		if (get_current_user_id()) {

			$is_author_page = self::is_author_page();
			if ($is_author_page && !empty($post) && isset($post->post_author) && $post->post_author == get_current_user_id()) {

				// we need to query real status direct as we dynamically change the status for author on author page so even non author status can view them.
				$real_status = $wpdb->get_var("SELECT post_status from $wpdb->posts WHERE ID=$post->ID");
				if ($real_status == 'publish') {
					$status_icon = 'fas fa-play';
					$status = __('Published', 'geodirectory');
				} elseif($real_status == 'pending') {
					$status = __('Awaiting review', 'geodirectory');
					$status_icon = 'fas fa-pause';
				}else {
					$status = __('Not published', 'geodirectory');
					$status_icon = 'fas fa-pause';

				}

				$status_parts['title'] = apply_filters('geodir_post_status_author_page',$status,$real_status,$post->ID);
				$status_parts['icon'] = apply_filters('geodir_post_status_icon_author_page',$status_icon,$real_status,$post->ID);
			}
		}

		/**
		 * Filter the post status text on the author page.
		 *
		 * @since 2.1.0
		 * @param array $status_parts The array of status elements.
		 */
		return  apply_filters('geodir_filter_status_array_on_author_page', $status_parts );

	}

	public static function is_author_page() {
		$is_author_page = apply_filters( 'geodir_post_status_is_author_page', geodir_is_page( 'author' ) );

		if ( ! $is_author_page && wp_doing_ajax() && ! empty( $_REQUEST['is_gd_author'] ) ) {
			$is_author_page = true;
		}

		return $is_author_page;
	}
}

