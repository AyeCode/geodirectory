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

		if ( is_preview() ) {
			return;
		}

		$defaults = array(
			'hide_edit'      => 0,
			'hide_delete'      => 0,
			'author_page_only'      => 0,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$show = true;
		if ( $args['author_page_only'] && ! self::is_author_page() ) {
			$show = false;
		}

		$output = '';
		if ( $show && ! empty( $post->ID ) && geodir_listing_belong_to_current_user( $post->ID ) ) {
			ob_start();

			echo '<div class="geodir_post_meta  gd-author-actions" ">';

			do_action( 'geodir_widget_before_detail_user_actions' );

			self::post_status_author_page(); // the post status on the author page

			if ( ! $args['hide_edit'] ) {
				$post_id = $post->ID;
				if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
					$post_id = (int) $_REQUEST['pid'];
				}
				$editlink = geodir_edit_post_link( $post_id );
				echo '<span class="gd_user_action edit_link"><i class="fas fa-pencil-alt" aria-hidden="true"></i> <a href="' . esc_url( $editlink ) . '">' . __( 'Edit', 'geodirectory' ) . '</a></span>';
			}

			if ( ! $args['hide_delete'] ) {
				echo '<span class="gd_user_action delete_link"><i class="fas fa-trash" aria-hidden="true"></i> <a href="javascript:void(0);" onclick="gd_delete_post(' . $post_id . ');">' . __( 'Delete', 'geodirectory' ) . '</a></span>';
			}

			do_action( 'geodir_widget_after_detail_user_actions' );

			echo "</div>";

			$output .= ob_get_clean();
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
	public static function post_status_author_page() {
		global $wpdb, $post;

		$html = '';
		if ( get_current_user_id() ) {
			$is_author_page = self::is_author_page();

			if ( $is_author_page && ! empty( $post ) && isset( $post->post_author ) && $post->post_author == get_current_user_id() ) {
				// we need to query real status direct as we dynamically change the status for author on author page so even non author status can view them.
				$real_status = $wpdb->get_var( "SELECT post_status FROM $wpdb->posts WHERE ID = $post->ID" );
				$status = "<strong>(";
				$status_icon = '<i class="fas fa-play" aria-hidden="true"></i>';
				if ( $real_status == 'publish' ) {
					$status .= __( 'Published', 'geodirectory' );
				} elseif ( $real_status == 'pending' ) {
					$status .= __( 'Awaiting review', 'geodirectory' );
					$status_icon = '<i class="fas fa-pause" aria-hidden="true"></i>';
				} else {
					$status .= __( 'Not published', 'geodirectory' );
					$status_icon = '<i class="fas fa-pause" aria-hidden="true"></i>';
				}
				$status .= ")</strong>";

				$status = apply_filters( 'geodir_post_status_author_page',$status, $real_status, $post->ID );
				$status_icon = apply_filters( 'geodir_post_status_icon_author_page', $status_icon, $real_status, $post->ID );

				$html = '<span class="gd_user_action geodir-post-status">' . $status_icon . ' <span class="geodir-status-label">' . __('Status: ', 'geodirectory') . '</span>' . $status . '</span>';
			}
		}

		if ( $html != '' ) {
			/**
			 * Filter the post status text on the author page.
			 *
			 * @since 1.0.0
			 * @param string $html The HTML of the status.
			 */
			echo apply_filters( 'geodir_filter_status_text_on_author_page', $html );
		}
	}

	public static function is_author_page() {
		$is_author_page = apply_filters( 'geodir_post_status_is_author_page', geodir_is_page( 'author' ) );

		if ( ! $is_author_page && wp_doing_ajax() && ! empty( $_REQUEST['is_gd_author'] ) ) {
			$is_author_page = true;
		}

		return $is_author_page;
	}
}

