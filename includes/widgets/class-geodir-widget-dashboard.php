<?php
/**
* GeoDirectory Login Widget
*
* @since 1.0.0
*
* @package GeoDirectory
*/

/**
 * Dashboard Widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Dashboard extends WP_Super_Duper {
    
    /**
     * Register the Dashboard widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {

	    $options = array(
		    'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
		    'block-icon'    => 'admin-site',
		    'block-category'=> 'widgets',
		    'block-keywords'=> "['dashboard','login','geo']",

		    'class_name'    => __CLASS__,
		    'base_id'       => 'gd_dashboard', // this us used as the widget id and the shortcode id.
		    'name'          => __('GD > Dashboard','geodirectory'), // the name of the widget.
		    'widget_ops'    => array(
			    'classname'   => 'geodir-dashboard-container', // widget class
			    'description' => esc_html__('Shows the user dashboard to logged in users.','geodirectory'), // widget description
			    'customize_selective_refresh' => true,
			    'geodirectory' => true,
		    ),
		    'arguments'     => array(
			    'title'  => array(
				    'title' => __('Title:', 'geodirectory'),
				    'desc' => __('The widget title.', 'geodirectory'),
				    'type' => 'text',
                    'placeholder' => __( 'My Dashboard', 'geodirectory' ),
				    'default'  => '',
				    'desc_tip' => true,
				    'advanced' => false
			    )
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
	public function output($args = array(), $widget_args = array(),$content = ''){

		ob_start();
		// options
		$defaults = array(
			'title'      => __( 'My Dashboard', 'geodirectory' ),
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $args, $defaults );
		
		if ( is_user_logged_in() ) {


			echo "<div class='geodir-dashboard'>";

			$this->dashboard_output( $options );

			echo "</div>";
		}

		return ob_get_clean();
	}

	/**
	 * Generates login box HTML.
	 *
	 * @since   1.0.0
	 * @package GeoDirectory
	 * @global object $current_user  Current user object.
	 *
	 * @param array|string $args     Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array|string $instance The settings for the particular instance of the widget.
	 */
	public function dashboard_output( $instance = '' ) {


		if ( is_user_logged_in() ) {
			global $current_user;

			$author_link = get_author_posts_url( $current_user->data->ID );
			$author_link = geodir_getlink( $author_link, array( 'geodir_dashbord' => 'true' ), false );

			echo '<ul class="geodir-loginbox-list">';
			ob_start();
			do_action( 'geodir_dashboard_links_top' );

			do_action( 'geodir_dashboard_before_listings_links' );


			// Add listing links
			GeoDir_User::show_add_listings();

			// My Favourites in Dashboard
			GeoDir_User::show_favourites( $current_user->data->ID, 'select' );

			// My Listings
			GeoDir_User::show_listings( $current_user->data->ID, 'select' );

			/*
			 * if we are not adding any login functionalitty then we should prob remove this also
			?>
			<li><i class="fas fa-sign-out-alt" aria-hidden="true"></i><a class="signin" href="<?php echo wp_logout_url( home_url() ); ?>"><?php _e( 'Logout', 'geodirectory' ); ?></a></li>
			<?php
			*/

			$dashboard_link = ob_get_clean();


			/**
			 * Filter dashboard links HTML.
			 *
			 * @since 1.0.0
			 *
			 * @param string $dashboard_link Dashboard links HTML.
			 */
			echo apply_filters( 'geodir_dashboard_links', $dashboard_link );

			echo '</ul>';

			/**
			 * Called after the loginwidget form for logged in users.
			 *
			 * @since 1.6.6
			 */
			do_action( 'geodir_after_dashboard_form_logged_in' );


		} else {
			/**
			 * Called after the loginwidget form for logged out users.
			 *
			 * @since 1.6.6
			 */
			do_action( 'geodir_after_dashboard_form_logged_out' );
		}

	}




}