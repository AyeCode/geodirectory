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
		    'block-category'=> 'geodirectory',
		    'block-keywords'=> "['dashboard','login','geo']",

		    'class_name'    => __CLASS__,
		    'base_id'       => 'gd_dashboard', // this us used as the widget id and the shortcode id.
		    'name'          => __('GD > Dashboard','geodirectory'), // the name of the widget.
		    'widget_ops'    => array(
			    'classname'   => 'geodir-dashboard-container '.geodir_bsui_class(), // widget class
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

	    $design_style = geodir_design_style();

	    if($design_style) {

		    // background
		    $arguments['bg']  = geodir_get_sd_background_input('mt');

		    // margins
		    $arguments['mt']  = geodir_get_sd_margin_input('mt');
		    $arguments['mr']  = geodir_get_sd_margin_input('mr');
		    $arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
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
	public function output($args = array(), $widget_args = array(),$content = ''){

		ob_start();
		// options
		$defaults = array(
			'title'      => __( 'My Dashboard', 'geodirectory' ),
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
		$options = wp_parse_args( $args, $defaults );

		// wrap class
		$wrap_class = geodir_build_aui_class($options);
		
		if ( is_user_logged_in() ) {


			echo "<div class='geodir-dashboard $wrap_class'>";

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

			$design_style = geodir_design_style();

			$author_link = get_author_posts_url( $current_user->data->ID );
			$author_link = geodir_getlink( $author_link, array( 'geodir_dashbord' => 'true' ), false );

			$ul_class = $design_style ? 'list-unstyled p-0 m-0' : '';
			echo '<ul class="geodir-loginbox-list '.$ul_class.'">';
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