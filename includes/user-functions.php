<?php
/**
 * GeoDirectory User Functions
 *
 * Functions for users.
 *
 * @author 		AyeCode
 * @category 	Core
 * @package 	GeoDirectory/Functions
 * @version 	2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get user's favorite listing count.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param bool $user_id Optional. The user id to get, defaults to current user.
 *
 * @return array User listing count for each post type.
 */
function geodir_user_favourite_listing_count( $user_id = false ) {
	return GeoDir_User::get_post_type_fav_counts($user_id);
}

/**
 * Get the array of user favourites.
 *
 * @param string $user_id
 *
 * @since 1.6.24
 * @return mixed
 */
function geodir_get_user_favourites( $user_id = '' ) {
	return GeoDir_User::get_user_favs( $user_id );
}

/**
 * Get user's post listing count.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $current_user Current user object.
 * @return array User listing count for each post type.
 */
function geodir_user_post_listing_count( $user_id = null, $unpublished = false ) {
	global $wpdb, $current_user;

	if ( ! $user_id ) {
		$user_id = $current_user->ID;
	}

	if ( ! $user_id ) {
		return array();
	}

	$unpublished_sql = '';

	if ( $unpublished ) {
		$unpublished_sql = " OR post_status = 'pending' OR post_status = 'gd-closed' OR post_status = 'gd-expired' ";
	}

	$all_postypes = geodir_get_posttypes();

	$user_listing = array();
	foreach ( $all_postypes as $post_type ) {
		$statuses = geodir_get_post_stati( 'posts-count-live', array( 'post_type' => $post_type ) );

		if ( $unpublished ) {
			$statuses = array_merge( $statuses, geodir_get_post_stati( 'posts-count-offline', array( 'post_type' => $post_type ) ) );
		}

		$statuses = array_unique( $statuses );

		$total_posts = $wpdb->get_var( "SELECT count( ID ) FROM " . $wpdb->posts . " WHERE post_author = " . $user_id . " AND post_type = '" . $post_type . "' AND {$wpdb->posts}.post_status IN( '" . implode( "', '", $statuses ) . "' )");

		if ( $total_posts > 0 ) {
			$user_listing[ $post_type ] = $total_posts;
		}
	}

	return $user_listing;
}

/**
 * Generate a unique username.
 *
 * @param $username
 *
 * @return mixed|string
 */
function geodir_generate_unique_username( $username ) {
	static $i;
	if ( null === $i ) {
		$i = 1;
	} else {
		$i++;
	}
	if ( ! username_exists( $username ) ) {
		return $username;
	}
	$new_username = sprintf( '%s-%s', $username, $i );
	if ( ! username_exists( $new_username ) ) {
		return $new_username;
	} else {
		return call_user_func( __FUNCTION__, $username );
	}
}

/**
 * Get users dropdown list.
 *
 * @param $args
 *
 * @return mixed|string
 */
function geodir_dropdown_users( $args = '' ) {
    $defaults = array(
        'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
        'orderby' => 'display_name', 'order' => 'ASC',
        'include' => '', 'exclude' => '', 'multi' => 0,
        'show' => 'display_name', 'echo' => 1,
        'selected' => 0, 'name' => 'user', 'class' => '', 'id' => '',
        'blog_id' => $GLOBALS['blog_id'], 'who' => '', 'include_selected' => false,
        'option_none_value' => -1
    );

    $defaults['selected'] = is_author() ? get_query_var( 'author' ) : 0;

    $r = wp_parse_args( $args, $defaults );

    $query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'who' ) );

    $fields = array( 'ID', 'user_login', 'user_email' );

    $show = ! empty( $r['show'] ) ? $r['show'] : 'display_name';
    if ( 'display_name_with_login' === $show ) {
        $fields[] = 'display_name';
    } else if ( 'display_name_with_email' === $show ) {
        $fields[] = 'display_name';
    } else if ( 'name_id_email' === $show ) {
        $fields[] = 'display_name';
    } else {
        $fields[] = $show;
    }

    $query_args['fields'] = $fields;

    $show_option_all = $r['show_option_all'];
    $show_option_none = $r['show_option_none'];
    $option_none_value = $r['option_none_value'];

    $query_args = apply_filters( 'geodir_dropdown_users_args', $query_args, $r );

    $users = get_users( $query_args );

    $output = '';
    if ( ! empty( $users ) && ( empty( $r['hide_if_only_one_author'] ) || count( $users ) > 1 ) ) {
        $name = esc_attr( $r['name'] );
        if ( $r['multi'] && ! $r['id'] ) {
            $id = '';
        } else {
            $id = $r['id'] ? " id='" . esc_attr( $r['id'] ) . "'" : " id='$name'";
        }
        $output = "<select name='{$name}'{$id} class='" . $r['class'] . "'>\n";

        if ( $show_option_all ) {
            $output .= "\t<option value='0'>$show_option_all</option>\n";
        }

        if ( $show_option_none ) {
            $_selected = selected( $option_none_value, $r['selected'], false );
            $output .= "\t<option value='" . esc_attr( $option_none_value ) . "'$_selected>$show_option_none</option>\n";
        }

        if ( $r['include_selected'] && ( $r['selected'] > 0 ) ) {
            $found_selected = false;
            $r['selected'] = (int) $r['selected'];
            foreach ( (array) $users as $user ) {
                $user->ID = (int) $user->ID;
                if ( $user->ID === $r['selected'] ) {
                    $found_selected = true;
                }
            }

            if ( ! $found_selected ) {
                $users[] = get_userdata( $r['selected'] );
            }
        }

        foreach ( (array) $users as $user ) {
            if ( 'display_name_with_login' === $show ) {
                /* translators: 1: display name, 2: user_login */
                $display = sprintf( _x( '%1$s (%2$s)', 'user dropdown' ), $user->display_name, $user->user_login );
            } elseif ( 'display_name_with_email' === $show ) {
                /* translators: 1: display name, 2: user_email */
                if ( $user->display_name == $user->user_email ) {
                    $display = $user->display_name;
                } else {
                    $display = sprintf( _x( '%1$s (%2$s)', 'user dropdown' ), $user->display_name, $user->user_email );
                }
            } elseif ( 'name_id_email' === $show ) {
                /* translators: 1: user display name 2: user ID 3: user email */
                if ( $user->display_name == $user->user_email ) {
                    $display = $user->display_name;
                } else {
                    $display = wp_sprintf( esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'geodirectory' ), $user->display_name, absint( $user->ID ), $user->user_email );
                }
            } elseif ( ! empty( $user->$show ) ) {
                $display = $user->$show;
            } else {
                $display = '(' . $user->user_login . ')';
            }

            $_selected = selected( $user->ID, $r['selected'], false );
            $output .= "\t<option value='$user->ID'$_selected>" . esc_html( $display ) . "</option>\n";
        }

        $output .= "</select>";
    }

    $html = apply_filters( 'geodir_dropdown_users', $output );

    if ( $r['echo'] ) {
        echo $html;
    }
    return $html;
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
function geodir_user_can( $capability, $args = array() ) {
	return GeoDir_User::user_can( $capability, $args );
}
