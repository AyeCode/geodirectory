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
			'block-category'=> 'widgets',
			'block-keywords'=> "['author','actions','geo']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_author_actions', // this us used as the widget id and the shortcode id.
			'name'          => __( 'GD > Author Actions', 'geodirectory' ), // the name of the widget.
			'widget_ops'    => array(
				'classname'   	=> 'geodir-author-actions', // widget class
				'description' 	=> esc_html__( 'Display author actions.', 'geodirectory' ), // widget description
				'geodirectory' 	=> true,
				'gd_show_pages' => array( 'detail' ),
			),
			'arguments'     => array(
				'hide_edit'  => array(
					'title' => __('Hide edit:', 'geodirectory'),
					'desc' => __('Hide the edit action.', 'geodirectory'),
					'type' => 'checkbox',
					'value'=> '1',
					'default'=> '0',
					'desc_tip' => true,
					'advanced' => true
				),
				'hide_delete'  => array(
					'title' => __('Hide delete:', 'geodirectory'),
					'desc' => __('Hide the delete action.', 'geodirectory'),
					'type' => 'checkbox',
					'value'=> '1',
					'default'=> '0',
					'desc_tip' => true,
					'advanced' => true
				),
			)
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
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $post;

		//print_r($args);

		$defaults = array(
			'hide_edit'      => 0,
			'hide_delete'      => 0,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$output = '';
		if(!empty($post->ID) && geodir_listing_belong_to_current_user($post->ID)){
			ob_start();

			echo '<div class="geodir_post_meta  gd-author-actions" ">';

			do_action( 'geodir_widget_before_detail_user_actions' );


			if(!$args['hide_edit']){
				$post_id = $post->ID;
				if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '') {
					$post_id = (int)$_REQUEST['pid'];
				}
				$editlink = geodir_edit_post_link($post_id);
				echo ' <span class="edit_link"><i class="fa fa-pencil"></i> <a href="' . esc_url($editlink) . '">' . __('Edit', 'geodirectory') . '</a></span>';
			}

			if(!$args['hide_delete']) {
				echo ' <span class="edit_link"><i class="fa fa-pencil"></i> <a href="' . esc_url($editlink) . '">' . __('Delete', 'geodirectory') . '</a></span>';
			}



			do_action( 'geodir_widget_after_detail_user_actions' );

			$output .= ob_get_clean();
		}

		return $output;
	}

	public function author_actions(){
		return array(
			'edit' => __("Edit","geodirectory"),
			'delete' => __("Delete","geodirectory"),
		);
	}
}

