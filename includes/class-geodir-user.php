<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * User Class
 *
 * @version 2.0.0
 */
class GeoDir_User {


	/**
	 * The current blog id or blank if main site.
	 *
	 * @var mixed
	 */
	protected static $blog_id = '';

	public function __construct() {


	}


	/**
	 * Add a post to the user fav list.
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id (optional) The user id to add to.
	 *
	 * @return bool
	 */
	public static function add_fav( $post_id, $user_id = '' ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return false;
		}

		$user_favs = self::get_user_favs( $user_id );

		if ( empty( $user_favs ) || ( ! empty( $user_favs ) && ! in_array( $post_id, $user_favs ) ) ) {
			$user_favs[] = $post_id;
		}

		$site_id = '';
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id && $blog_id != '1' ) {
				$site_id = '_' . $blog_id;
			}
		}
		$return = update_user_meta( $user_id, 'gd_user_favourite_post' . $site_id, $user_favs );

		if ( ! $return ) {
			return false;
		}

		/**
		 * Called after adding the post from favourites.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 *
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_add_fav_true', $post_id, $user_id );

		return true;

	}

	/**
	 * Remove a post to the user fav list.
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id (optional) The user id to add to.
	 *
	 * @return bool
	 */
	public static function remove_fav( $post_id, $user_id = '' ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return false;
		}

		$user_favs = self::get_user_favs( $user_id );

		if ( ! empty( $user_favs ) ) {

			if ( ( $key = array_search( $post_id, $user_favs ) ) !== false ) {
				unset( $user_favs[ $key ] );
			}

		}

		$site_id = '';
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id && $blog_id != '1' ) {
				$site_id = '_' . $blog_id;
			}
		}
		$return = update_user_meta( $user_id, 'gd_user_favourite_post' . $site_id, $user_favs );

		if ( ! $return ) {
			return false;
		}

		/**
		 * Called after removing the post from favourites.
		 *
		 * @since 2.0.0
		 * @package GeoDirectory
		 *
		 * @param int $post_id The post ID.
		 * @param int $user_id The user ID.
		 */
		do_action( 'geodir_remove_fav_true', $post_id, $user_id );

		return true;

	}

	/**
	 * Get the user favs.
     *
     * @since 2.0.0
	 *
	 * @param string $user_id Optional. User id. Default null.
	 *
	 * @return array|bool|mixed
	 */
	public static function get_user_favs( $user_id = '' ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If we have no user then bail.
		if ( ! $user_id ) {
			return false;
		}

		$site_id = '';
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			if ( $blog_id && $blog_id != '1' ) {
				$site_id = '_' . $blog_id;
			}
		}

		$user_favs = get_user_meta( $user_id, 'gd_user_favourite_post' . $site_id, true );

		if ( ! $user_favs ) {
			return array();
		} else {
			return $user_favs;
		}
	}


	/**
	 * Get the fav counts per post type for a user.
     *
     * @since 2.0.0
	 *
	 * @param int $user_id Optional. User id. Default 0.
     *
     * @global object $wpdb WordPress Database object.
	 *
	 * @return array $user_listing.
	 */
	public static function get_post_type_fav_counts( $user_id = 0 ) {
		global $wpdb;

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return array();
		}

		$post_types = geodir_fav_allowed_post_types();

		$user_favorites = self::get_user_favs( $user_id );

		$user_listing = array();
		if ( is_array( $post_types ) && ! empty( $post_types ) && is_array( $user_favorites ) && ! empty( $user_favorites ) ) {
			$user_favorites = "'" . implode( "','", $user_favorites ) . "'";

			foreach ( $post_types as $ptype ) {
				$total_posts = $wpdb->get_var( "SELECT count( ID ) FROM " . $wpdb->prefix . "posts WHERE  post_type='" . $ptype . "' AND post_status = 'publish' AND ID IN (" . $user_favorites . ")" );

				if ( $total_posts > 0 ) {
					$user_listing[ $ptype ] = $total_posts;
				}
			}
		}

		return $user_listing;
	}

	/**
	 * Get the users favourites in a select or a string.
     *
     * @since 2.0.0
	 *
	 * @param string $user_id Optional. User id. Default null.
	 * @param string $output_type Optional. Output type. Default select.
	 *
	 * @return string
	 */
	public static function show_favourites( $user_id = '', $output_type = 'select' ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return '';
		}

		$design_style = geodir_design_style();
		$options = array();
		// My Favourites in Dashboard
		$show_favorite_link_user_dashboard = geodir_fav_allowed_post_types();
		$user_favourite                    = self::get_post_type_fav_counts( $user_id );

		if ( ! empty( $show_favorite_link_user_dashboard ) && ! empty( $user_favourite ) ) {
			$favourite_links = $output_type == 'select' ? '' : array();
			$post_types      = geodir_get_posttypes( 'object' );

			$author_link = get_author_posts_url( $user_id );
			$author_link = geodir_getlink( $author_link, array(), false );

			foreach ( $post_types as $key => $postobj ) {
				if ( in_array( $key, $show_favorite_link_user_dashboard ) && array_key_exists( $key, $user_favourite ) ) {
					$name           = $postobj->labels->name;
					$post_type_link = trailingslashit($author_link).GeoDir_Permalinks::favs_slug($key)."/".$postobj->rewrite->slug."/";

					$selected = '';

					if ( isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'favourite' && isset( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $key && isset( $_REQUEST['geodir_dashbord'] ) ) {
						$selected = 'selected="selected"';
					}
					/**
					 * Filter favorite listing link.
					 *
					 * @since 1.0.0
					 *
					 * @param string $post_type_link Favorite listing link.
					 * @param string $key Favorite listing array key.
					 * @param int $current_user ->ID Current user ID.
					 */
					$post_type_link = apply_filters( 'geodir_dashboard_link_favorite_listing', $post_type_link, $key, $user_id );

					if ( $output_type == 'select' ) {
						$favourite_links .= '<option ' . $selected . ' value="' . $post_type_link . '">' . __( geodir_utf8_ucfirst( $name ), 'geodirectory' ) . '</option>';
					} elseif ( $output_type == 'link' ) {
						$favourite_links[] = '<a href="' . $post_type_link . '">' . __( geodir_utf8_ucfirst( $name ), 'geodirectory' ) . '</a>';
					}elseif($output_type == 'array'){
						$favourite_links[$key] = array('url' => $post_type_link,'text'=>__( geodir_utf8_ucfirst( $name ), 'geodirectory' ));
					}

					$options[$post_type_link] = __( geodir_utf8_ucfirst( $name ), 'geodirectory' );


				}
			}

			if ( $favourite_links != '' ) {
				if ( $output_type == 'select' ) {
					if($design_style){
						echo "<li class='list-unstyled'>";
						echo  aui()->select( array(
							'id'               => "geodir_my_favourites",
							'name'             => "geodir_my_favourites",
							'class'             => 'mw-100',
							'placeholder'      => esc_attr__( 'My Favorites', 'geodirectory' ),
							'value'            => '',
							'options'          => $options,
							'extra_attributes' => array(
								'option-autoredirect' => "1"
							)
						) );
						echo "</li>";
					}else {
						?>
						<li>
							<select id="geodir_my_favourites" class="geodir-select"
							        option-autoredirect="1" name="geodir_my_favourites" option-ajaxchosen="false"
							        data-placeholder="<?php esc_attr_e( 'My Favorites', 'geodirectory' ); ?>"
							        aria-label="<?php esc_attr_e( 'My Favorites', 'geodirectory' ); ?>">
								<option value="" disabled="disabled" selected="selected"
								        style='display:none;'><?php echo esc_attr( __( 'My Favorites', 'geodirectory' ) ); ?></option>
								<?php echo $favourite_links; ?>
							</select>
						</li>
						<?php
					}
				} elseif ( $output_type == 'link' ) {
					if ( ! empty( $favourite_links ) ) {
						echo implode( " | ", $favourite_links );
					}

				}elseif($output_type == 'array'){
					return $favourite_links;
				}
			}
		}
	}


	/**
	 * Show the users listings in a select or string.
     *
     * @since 2.0.0
	 *
	 * @param string $user_id Optional. User id. Default null.
	 * @param string $output_type Optional. Output type. Default select.
	 *
	 * @return string
	 */
	public static function show_listings( $user_id = '', $output_type = 'select' ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return '';
		}

		$design_style = geodir_design_style();
		$options = array();

		$user_listing = geodir_user_post_listing_count( $user_id, true );

		$listing_links = array();

		$post_types = geodir_get_posttypes( 'object' );

		$author_link = get_author_posts_url( $user_id );
		$author_link = geodir_getlink( $author_link, array(), false );

		foreach ( $post_types as $key => $postobj ) {
			if ( array_key_exists( $key, $user_listing ) ) {
				$name         = $postobj->labels->name;
				$cpt_slug     = geodir_cpt_permalink_rewrite_slug( $key, $postobj );
				$listing_link = trailingslashit($author_link) . $cpt_slug . "/";


				$selected = '';
				if ( ! isset( $_REQUEST['list'] ) && isset( $_REQUEST['geodir_dashbord'] ) && isset( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $key ) {
					$selected = 'selected="selected"';
				}

				/**
				 * Filter my listing link.
				 *
				 * @since 1.0.0
				 *
				 * @param string $listing_link My listing link.
				 * @param string $key My listing array key.
				 * @param int $current_user ->ID Current user ID.
				 */
				$listing_link = apply_filters( 'geodir_dashboard_link_my_listing', $listing_link, $key, $user_id );
				if ( $output_type == 'select' ) {
					$listing_links[] = '<option ' . $selected . ' value="' . $listing_link . '">' . __( geodir_utf8_ucfirst( $name ), 'geodirectory' ) . '</option>';
				} elseif ( $output_type == 'link' ) {
					$listing_links[] = '<a href="' . $listing_link . '">' . __( geodir_utf8_ucfirst( $name ), 'geodirectory' ) . '</a>';
				}elseif($output_type == 'array'){
					$listing_links[] = array('url' => $listing_link,'text'=>__( geodir_utf8_ucfirst( $name ), 'geodirectory' ));
				}

				$options[$listing_link] = __( geodir_utf8_ucfirst( $name ), 'geodirectory' );

			}
		}


		if ( !empty($listing_links) ) {
			if ( $output_type == 'select' ) {
				if($design_style){
					echo "<li class='list-unstyled'>";
					echo  aui()->select( array(
						'id'               => "geodir_my_listings",
						'name'             => "geodir_my_listings",
						'class'             => 'mw-100',
						'placeholder'      => esc_attr__( 'My Listings', 'geodirectory' ),
						'value'            => '',
						'options'          => $options,
						'extra_attributes' => array(
							'option-autoredirect' => "1"
						)
					) );
					echo "</li>";
				}else {
					?>
					<li>
						<select id="geodir_my_listings" class="geodir-select"
						        option-autoredirect="1" name="geodir_my_listings" option-ajaxchosen="false"
						        data-placeholder="<?php echo esc_attr( __( 'My Listings', 'geodirectory' ) ); ?>"
						        aria-label="<?php esc_attr_e( 'My Listings', 'geodirectory' ); ?>">
							<option value="" disabled="disabled" selected="selected"
							        style='display:none;'><?php echo esc_attr( __( 'My Listings', 'geodirectory' ) ); ?></option>
							<?php echo implode( "", $listing_links ); ?>
						</select>
					</li>
					<?php
				}
			} elseif ( $output_type == 'link' ) {
				if ( ! empty( $listing_links ) ) {
					echo implode( " | ", $listing_links );
				}

			}elseif($output_type == 'array'){
				return $listing_links;
			}
		}


	}

    /**
     * Get the add listing links.
     *
     * @since 2.0.0
     *
     */
	public static function show_add_listings($output = 'select') {

		$post_types = geodir_get_posttypes( 'object' );
		$design_style = geodir_design_style();
		$options = array();
		$addlisting_links = $output=='array' ? array() : '';
		foreach ( $post_types as $key => $postobj ) {

			if ( ! isset( $postobj->disable_frontend_add ) || $postobj->disable_frontend_add == '0' ) {
				if ( $add_link = geodir_add_listing_page_url( $key ) ) {

					$name = $postobj->labels->name;

					$selected = '';
					if ( geodir_get_current_posttype() == $key && geodir_is_page( 'add-listing' ) ) {
						$selected = 'selected="selected"';
					}

					/**
					 * Filter add listing link.
					 *
					 * @since 1.0.0
					 *
					 * @param string $add_link Add listing link.
					 * @param string $key Add listing array key.
					 * @param int $current_user ->ID Current user ID.
					 */
					$add_link = apply_filters( 'geodir_dashboard_link_add_listing', $add_link, $key, get_current_user_id() );
					$name     = apply_filters( 'geodir_dashboard_label_add_listing', $name, $key, get_current_user_id() );

					$options[$add_link] = __( geodir_utf8_ucfirst( $name ), 'geodirectory' );
					if($output == 'array'){
						$addlisting_links[$key] = array('url' => $add_link,'text'=>__( geodir_utf8_ucfirst( $name ), 'geodirectory' ));
					}else{
						$addlisting_links .= '<option ' . $selected . ' value="' . $add_link . '">' . __( geodir_utf8_ucfirst( $name ), 'geodirectory' ) . '</option>';
					}

				}
			}

		}

		if($output == 'array'){
			return $addlisting_links;
		}
		elseif ( $addlisting_links != '' ) {
			if($design_style ){
				echo "<li class='list-unstyled'>";
				echo  aui()->select( array(
					'id'               => "geodir_add_listing",
					'name'               => "geodir_add_listing",
					'class'             => 'mw-100',
					'placeholder'      => esc_attr__( 'Add Listing', 'geodirectory' ),
					'value'            => '',
					'options'          => $options,
					'extra_attributes' => array(
						'option-autoredirect' => "1"
					)
				) );
				echo "</li>";
			}else {
				?>
				<li><select id="geodir_add_listing" class="geodir-select"
				            option-autoredirect="1" name="geodir_add_listing" option-ajaxchosen="false"
				            data-placeholder="<?php echo esc_attr( __( 'Add Listing', 'geodirectory' ) ); ?>"
				            aria-label="<?php esc_attr_e( 'Add Listing', 'geodirectory' ); ?>">
						<option value="" disabled="disabled" selected="selected"
						        style='display:none;'><?php echo esc_attr( __( 'Add Listing', 'geodirectory' ) ); ?></option>
						<?php echo $addlisting_links; ?>
					</select></li> <?php
			}

		}


	}

    /**
     * Delete Post.
     *
     * @since 2.0.0
     *
     * @param int $post_id Post id.
     *
     * @return bool|WP_Error
     */
	public static function delete_post($post_id){
	    if(!geodir_listing_belong_to_current_user($post_id)){
		    return new WP_Error( 'gd-delete-failed', __( "You do not have permission to delete this post.", "geodirectory" ) );
	    }
		$force_delete = geodir_get_option('user_trash_posts')==1 ? false : true;

	    if($force_delete){
		    $result = wp_delete_post( $post_id, $force_delete );
	    }else{
		    $result = wp_trash_post( $post_id );
	    }
	    if($result == false){
		    return new WP_Error( 'gd-delete-failed', __( "Delete post failed.", "geodirectory" ) );
	    }else{
	        return true;
        }
    }

	public static function login_link( $redirect = '' ) {
		$login_title = esc_attr__( 'Login', 'geodirectory' );

		if ( $redirect == '' ) {
			$redirect = geodir_curPageURL();
		}
		$login_link = wp_login_url( $redirect );

		$design_style = geodir_design_style();

		$btn_class = $design_style ? 'btn btn-primary' : '';

		$output = "<div class='gd-login-links'>";
		$output .= '<a class="login-link uwp-login-link '.$btn_class.'" href="' . esc_url( $login_link ) . '" title="' . $login_title . '">' . $login_title . '</a>';

		if ( get_option( 'users_can_register' ) ) {
			$btn_class = $design_style ? 'btn btn-outline-primary' : '';
			$register_title = esc_attr__( 'Register', 'geodirectory' );
			$register_link = wp_registration_url();
			$output .= $design_style ? ' ' : ' | ';
			$output .= '<a class="register-link uwp-register-link '.$btn_class.'" href="' . esc_url( $register_link ) . '" title="' . $register_title . '">' . $register_title . '</a>';
		}

		$output .= "</div>";

		return apply_filters( 'geodir_login_link', $output, $login_title, $login_link, $redirect );
	}

	public static function post_author_action( $action, $the_post ) {
		$data = array();

		return apply_filters( 'geodir_post_author_action_' . $action, $data, $action, $the_post );
	}

	/**
	 * Returns whether the current user has the specified capability.
	 *
	 * @since 2.1.1.9
	 *
	 * @param string $capability Capability name.
	 * @param array  $args Further parameters.
	 * @return bool Whether the current user has the given capability.
	 */
	public static function user_can( $capability, $args = array() ) {
		$_args = $args;

		switch( $capability ) {
			case 'see_private_address':
				$has_cap = true;

				$defaults = array(
					'post' => NULL,
					'author' => true,
				);

				$_args = wp_parse_args( $args, $defaults );

				if ( GeoDir_Post_Data::has_private_address( $_args['post'] ) ) {
					if ( ! empty( $_args['author'] ) ) {
						if ( is_scalar( $_args['post'] ) ) {
							$post_ID = absint( $_args['post'] );
						} else if ( is_object( $_args['post'] ) ) {
							$post_ID = $_args['post']->ID;
						} else {
							$post_ID = 0;
						}

						if ( ! geodir_listing_belong_to_current_user( $post_ID ) ) {
							$has_cap = false;
						}
					} else {
						$has_cap = false;
					}
				}
				break;
			default:
				$has_cap = false;

				break;
		}

		/**
		 * Filters whether the current user has the specified capability.
		 *
		 * @since 2.1.1.9
		 *
		 * @param bool   $has_cap Whether the current user has the given capability.
		 * @param string $capability Capability name.
		 * @param array  $_args Further parameters.
		 * @param array  $args Original parameters.
		 * @return bool Whether the current user has the given capability.
		 */
		return apply_filters( 'geodir_current_user_can', $has_cap, $capability, $_args, $args );
	}
}
