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
 * @param int $user_id Optional. The user id to get, defaults to current user.
 *
 * @return array User listing count for each post type.
 */
function geodir_user_favourite_listing_count( $user_id = 0 ) {
	return geodirectory()->users->get_favorite_counts( $user_id );
}

/**
 * Add a post to the user's favorites list.
 *
 * @since 3.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID to add.
 * @param int $user_id Optional. The user id. Defaults to current user.
 * @return bool True on success, false on failure.
 */
function geodir_add_user_favourite( $post_id, $user_id = 0 ) {
	return geodirectory()->users->add_favorite( $post_id, $user_id );
}

/**
 * Remove a post from the user's favorites list.
 *
 * @since 3.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID to remove.
 * @param int $user_id Optional. The user id. Defaults to current user.
 * @return bool True on success, false on failure.
 */
function geodir_remove_user_favourite( $post_id, $user_id = 0 ) {
	return geodirectory()->users->remove_favorite( $post_id, $user_id );
}

/**
 * Get the array of user favourites.
 *
 * @param int $user_id Optional. The user id to get, defaults to current user.
 *
 * @since 1.6.24
 * @return array Array of post IDs that are favorited.
 */
function geodir_get_user_favourites( $user_id = 0 ) {
	return geodirectory()->users->get_favorites( $user_id );
}

/**
 * Get user's post listing count.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int  $user_id     Optional. The user id to get, defaults to current user.
 * @param bool $unpublished Optional. Include unpublished posts. Default false.
 * @return array User listing count for each post type.
 */
function geodir_user_post_listing_count( $user_id = 0, $unpublished = false ) {
	return geodirectory()->users->get_listing_counts( $user_id, $unpublished );
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
 * Delete a user's post.
 *
 * @since 3.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID to delete.
 * @return bool|\WP_Error True on success, WP_Error on failure.
 */
function geodir_delete_user_post( $post_id ) {
	return geodirectory()->users->delete_post( $post_id );
}

/**
 * Returns whether the current user has the specified capability.
 *
 * @since 2.1.1.9
 *
 * @param string $capability Capability name.
 * @param array  $args       Optional. Further parameters. Default empty array.
 * @return bool Whether the current user has the given capability.
 */
function geodir_user_can( $capability, $args = array() ) {
	return geodirectory()->users->user_can( $capability, $args );
}
