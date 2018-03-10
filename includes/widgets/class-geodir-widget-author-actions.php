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

		$output = '';
		if(!empty($post->ID) && geodir_listing_belong_to_current_user($post->ID)){
			ob_start();

			do_action( 'geodir_widget_before_detail_user_actions' );

			geodir_edit_post_link();

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

