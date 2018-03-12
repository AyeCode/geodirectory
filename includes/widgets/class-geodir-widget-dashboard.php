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
			    'description' => esc_html__('Shows the user dashboard.','geodirectory'), // widget description
			    'customize_selective_refresh' => true,
			    'geodirectory' => true,
			    'gd_show_pages' => array(),
		    ),
		    'arguments'     => array(
			    'dashboard_title'  => array(
				    'title' => __('Title:', 'geodirectory'),
				    'desc' => __('The widget title.', 'geodirectory'),
				    'type' => 'text',
                    'placeholder' => __( 'My Dashboard', 'geodirectory' ),
				    'default'  => '',
				    'desc_tip' => true,
				    'advanced' => false
			    ),
			    'show_login'  => array(
				    'title' => __("Show login box for users who are not logged in?", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 1,
				    'advanced' => true
			    ),
			    'login_title'  => array(
				    'title' => __('Login title:', 'geodirectory'),
				    'desc' => __('The widget title for users who are not logged in.', 'geodirectory'),
				    'type' => 'text',
                    'placeholder' => __( 'Login', 'geodirectory' ),
				    'default'  => '',
				    'desc_tip' => true,
				    'advanced' => false
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
	public function output($args = array(), $widget_args = array(),$content = ''){

		ob_start();
		// options
		$defaults = array(
			'dashboard_title'      => __( 'My Dashboard', 'geodirectory' ),
			'show_login' => '1',
			'login_title'      => __( 'Login', 'geodirectory' ),
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$options = wp_parse_args( $args, $defaults );

		//print_r($options);

		if ( is_user_logged_in() || $options['show_login']) {


			echo "<div class='geodir-dashbaord'>";

			if ( is_user_logged_in() ) {
				if ( ! empty( $options['dashboard_title'] ) ) {
					echo ! empty( $widget_args['before_title'] ) ? $widget_args['before_title'] : "<h2>";
					echo __( $options['dashboard_title'], "geodirectory" );
					echo ! empty( $widget_args['after_title'] ) ? $widget_args['after_title'] : "</h2>";
				}
			} else {
				if ( ! empty( $options['login_title'] ) ) {
					echo ! empty( $widget_args['before_title'] ) ? $widget_args['before_title'] : "<h2>";
					echo __( $options['login_title'], "geodirectory" );
					echo ! empty( $widget_args['after_title'] ) ? $widget_args['after_title'] : "</h2>";
				}
			}


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
			?>
            <li><i class="fa fa-sign-out"></i><a class="signin" href="<?php echo wp_logout_url( home_url() ); ?>"><?php _e( 'Logout', 'geodirectory' ); ?></a></li>
			<?php
			do_action( 'geodir_dashboard_before_listings_links' );


			// Add listing links
			GeoDir_User::show_add_listings();

			// My Favourites in Dashboard
			GeoDir_User::show_favourites( $current_user->data->ID, 'select' );

			// My Listings
			GeoDir_User::show_listings( $current_user->data->ID, 'select' );

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
			do_action( 'geodir_after_loginwidget_form_logged_in' );


		} else {
			?>
			<?php
			/**
			 * Filter signup form action link.
			 *
			 * @since 1.0.0
			 */
			?>
            <form name="loginform" class="loginform1"
                  action="<?php echo geodir_login_url(); ?>"
                  method="post">
                <div class="geodir_form_row"><input placeholder="<?php _e( 'Username or Email Address', 'geodirectory' ); ?>" name="log"
                                                    type="text" class="textfield user_login1"/> <span
                            class="user_loginInfo"></span></div>
                <div class="geodir_form_row"><input placeholder="<?php _e( 'Password', 'geodirectory' ); ?>"
                                                    name="pwd" type="password"
                                                    class="textfield user_pass1 input-text"/><span
                            class="user_passInfo"></span></div>

                <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars( geodir_curPageURL() ); ?>"/>
                <input type="hidden" name="testcookie" value="1"/>

				<?php do_action( 'login_form' ); ?>

                <div class="geodir_form_row clearfix"><input type="submit" name="submit"
                                                             value="<?php echo SIGN_IN_BUTTON; ?>" class="b_signin"/>

                    <p class="geodir-new-forgot-link">
						<?php
						/**
						 * Filter signup page register form link.
						 *
						 * @since 1.0.0
						 */
						$is_enable_signup = get_option( 'users_can_register' );

						if ( $is_enable_signup ) {
							?>
                            <a href="<?php echo wp_registration_url(); ?>"
                               class="goedir-newuser-link"><?php echo NEW_USER_TEXT; ?></a>

							<?php
						}
						/**
						 * Filter signup page forgot password form link.
						 *
						 * @since 1.0.0
						 */
						?>
                        <a href="<?php echo wp_lostpassword_url( get_permalink() ); ?>"
                           class="goedir-forgot-link"><?php echo FORGOT_PW_TEXT; ?></a></p></div>
            </form>
			<?php
			/**
			 * Called after the loginwidget form for logged out users.
			 *
			 * @since 1.6.6
			 */
			do_action( 'geodir_after_loginwidget_form_logged_out' );
		}

	}




}