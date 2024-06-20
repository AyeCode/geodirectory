<?php
/**
 * GeoDirectory REST Functions
 *
 * Functions for REST specific things.
 *
 * @author   GeoDirectory
 * @category Core
 * @package  GeoDirectory/Functions
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function for api enabled.
 *
 * @since 2.0.0
 *
 * @return bool $api_enabled.
 */
function geodir_api_enabled() {
	$api_enabled = ! geodir_has_request_uri( '/wp-json/geodir/v2/markers' ) && geodir_get_option( 'rest_api_enabled' ) ? true : false;
	return apply_filters( 'geodir_api_enabled', $api_enabled );
}

/**
 * Parses and formats a date for ISO8601/RFC3339.
 *
 * @since  2.0.0
 * @param  string|null|GeoDir_DateTime $date
 * @param  bool Send false to get local/offset time.
 * @return string|null ISO8601/RFC3339 formatted datetime.
 */
function geodir_rest_prepare_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new GeoDir_DateTime( "@$date", new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( geodir_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new GeoDir_DateTime( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( geodir_timezone_string() ) );
	}

	if ( ! is_a( $date, 'GeoDir_DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
}

/**
 * Returns image mime types users are allowed to upload via the API.
 * @since  2.0.0
 * @return array
 */
function geodir_rest_allowed_image_mime_types() {
	return apply_filters( 'geodir_rest_allowed_image_mime_types', array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'gif'          => 'image/gif',
		'png'          => 'image/png',
		'bmp'          => 'image/bmp',
		'tiff|tif'     => 'image/tiff',
		'ico'          => 'image/x-icon',
		'webp'         => 'image/webp',
		'avif'         => 'image/avif'
	) );
}

/**
 * Validate reports request arguments.
 *
 * @since 2.0.0
 * @param mixed $value
 * @param WP_REST_Request $request
 * @param string $param
 * @return WP_Error|boolean
 */
function geodir_rest_validate_reports_request_arg( $value, $request, $param ) {

	$attributes = $request->get_attributes();
	if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
		return true;
	}
	$args = $attributes['args'][ $param ];

	if ( 'string' === $args['type'] && ! is_string( $value ) ) {
		return new WP_Error( 'geodir__rest_invalid_param', sprintf( __( '%1$s is not of type %2$s', 'geodirectory' ), $param, 'string' ) );
	}

	if ( 'date' === $args['format'] ) {
		$regex = '#^\d{4}-\d{2}-\d{2}$#';

		if ( ! preg_match( $regex, $value, $matches ) ) {
			return new WP_Error( 'geodir__rest_invalid_date', __( 'The date you provided is invalid.', 'geodirectory' ) );
		}
	}

	return true;
}

/**
 * Encodes a value according to RFC 3986.
 * Supports multidimensional arrays.
 *
 * @since 2.0.0
 * @param string|array $value The value to encode.
 * @return string|array       Encoded values.
 */
function geodir_rest_urlencode_rfc3986( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'geodir_rest_urlencode_rfc3986', $value );
	} else {
		return str_replace( array( '+', '%7E' ), array( ' ', '~' ), rawurlencode( $value ) );
	}
}

/**
 * Check permissions of posts on REST API.
 *
 * @since 2.0.0
 * @param string $post_type Post type.
 * @param string $context   Request context.
 * @param int    $object_id Post ID.
 * @return bool
 */
function geodir_rest_check_post_permissions( $post_type, $context = 'read', $object_id = 0 ) {
	$contexts = array(
		'read'   => 'read_private_posts',
		'create' => 'publish_posts',
		'edit'   => 'edit_post',
		'delete' => 'delete_post',
		'batch'  => 'edit_others_posts',
	);

	if ( 'revision' === $post_type ) {
		$permission = false;
	} else {
		$cap = $contexts[ $context ];
		$post_type_object = get_post_type_object( $post_type );
		$permission = current_user_can( $post_type_object->cap->$cap, $object_id );
	}

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, $object_id, $post_type );
}

/**
 * Check permissions of product terms on REST API.
 *
 * @since 2.0.0
 * @param string $taxonomy  Taxonomy.
 * @param string $context   Request context.
 * @param int    $object_id Post ID.
 * @return bool
 */
function geodir_rest_check_post_term_permissions( $taxonomy, $context = 'read', $object_id = 0 ) {
	$contexts = array(
		'read'   => 'manage_terms',
		'create' => 'edit_terms',
		'edit'   => 'edit_terms',
		'delete' => 'delete_terms',
		'batch'  => 'edit_terms',
	);

	$cap = $contexts[ $context ];
	$taxonomy_object = get_taxonomy( $taxonomy );
	$permission = current_user_can( $taxonomy_object->cap->$cap, $object_id );

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, $object_id, $taxonomy );
}

/**
 * Check manager permissions on REST API.
 *
 * @since 2.0.0
 * @param string $object  Object.
 * @param string $context Request context.
 * @return bool
 */
function geodir_rest_check_manager_permissions( $object, $context = 'read' ) {
	$objects = array(
		'reports'          => 'manage_options',
		'settings'         => 'manage_options',
		'system_status'    => 'manage_options',
	);

	$permission = current_user_can( $objects[ $object ] );

	return apply_filters( 'geodir_rest_check_permissions', $permission, $context, 0, $object );
}

/**
 * Function for rest get countries.
 *
 * @since 2.0.0
 *
 * @param array $params Optional. Countries argument parameters. Default array().
 * @return array $items.
 * @todo kiran, please check this, i implemented new country class
 */
function geodir_rest_get_countries( $params = array() ) {
	$defaults = array(
		'fields'       => array(),
		'where'        => array(),
		'like'         => array(),
		'translated'   => true,
		'order'        => 'name',
		'orderby'      => 'ASC',
		'limit'        => '' // All
	);

	$args = wp_parse_args( $params, $defaults );
	$items = geodir_wp_countries( $args );

	if ( empty( $args['translated'] ) ) {
		return $items;
	}

	if ( ! empty( $items ) ) {
		foreach ( $items as $key => $item ) {
			$items[ $key ]->title = __( $item->name, 'geodirectory' ); // translate
		}
	}

	return $items;
}

/**
 * Function for get rest country by id.
 *
 * @since 2.0.0
 *
 * @param int $value Country id.
 * @return array $rows.
 */
function geodir_rest_country_by_id( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND CountryId = '" . (int)$value . "'", 'limit' => 1 ) );
    
    if ( !empty( $rows ) ) {
		return $rows[0];
    }
}

/**
 * Function for get rest country by name.
 *
 * @since 2.0.0
 *
 * @param string $value Country name.
 * @return array $rows.
 */
function geodir_rest_country_by_name( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND Country LIKE '" . wp_slash( $value ) . "'", 'limit' => 1 ) );
    
    if ( !empty( $rows ) ) {
        return $rows[0];
    }
}

/**
 * Function for get rest country by iso2.
 *
 * @since 2.0.0
 *
 * @param string $value Country iso2 value.
 * @return array $rows.
 */
function geodir_rest_country_by_iso2( $value ) {
    $rows = geodir_rest_get_countries( array( 'where' => "AND ISO2 LIKE '" . wp_slash( $value ) . "'", 'limit' => 1 ) );

    if ( !empty( $rows ) ) {
        return $rows[0];
    }
    
    return NULL;
}

/**
 * Function for convert datatype to filed type.
 *
 * @since 2.0.0
 *
 * @param string $data_type Data type.
 * @return string $type.
 */
function geodir_rest_data_type_to_field_type( $data_type ) {
    switch ( strtolower( $data_type ) ) {
        case 'float':
            $type = 'number';
            break;
        case 'int':
        case 'tinyint':
        case 'integer':
            $type = 'integer';
            break;
        case 'date':
        case 'time':
        case 'text':
        case 'varchar':
        default:
            $type = 'string';
            break;
    }
    
    return $type;
}

/**
 * Function for get enum values.
 *
 * @since 2.0.0
 *
 * @param array $options {
 *      Get enum option values.
 *
 *      @type string $value enum option value.
 *      @type string  $optgroup enum option group value.
 * }
 * @return array $values.
 */
function geodir_rest_get_enum_values( $options ) {
    $values = array();
    
    if ( !empty( $options ) ) {
        foreach ( $options as $option ) {
            if ( isset( $option['value'] ) && $option['value'] !== '' && empty( $option['optgroup'] ) ) {
                $values[] = $option['value'];
            }            
        }
    }
    
    return $values;
}

/**
 * Function for rest validate request arguments.
 *
 * @since 2.0.0
 *
 * @param string $value Request argument value.
 * @param object $request Request argument object.
 * @param string $param Request argument parameter.
 * @return bool|string Return true or Wp_error.
 */
function geodir_rest_validate_request_arg( $value, $request, $param ) {
    $attributes = $request->get_attributes();
    if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
        return true;
    }
    $args = $attributes['args'][ $param ];

    return geodir_rest_validate_value_from_schema( $value, $args, $param );
}

/**
 * Function for rest validate value from schema.
 *
 * @since 2.0.0
 *
 * @param string $value Request argument value.
 * @param array $args Request validate value schema array.
 * @param string $param Request argument parameter.
 * @return bool|string Return true or Wp_error.
 */
function geodir_rest_validate_value_from_schema( $value, $args, $param = '' ) {
    if ( 'array' === $args['type'] ) {
        if ( ! is_array( $value ) ) {
            $value = preg_split( '/[\s,]+/', $value );
        }
        if ( ! wp_is_numeric_array( $value ) ) {
            /* translators: 1: parameter, 2: type name */
            return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not of typeggg %2$s.' ), $param, 'array' ) );
        }
        foreach ( $value as $index => $v ) {
            $is_valid = geodir_rest_validate_value_from_schema( $v, $args['items'], $param . '[' . $index . ']' );
            if ( is_wp_error( $is_valid ) ) {
                return $is_valid;
            }
        }
    }

    if ( ! empty( $args['enum'] ) ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $index => $v ) {
                if ( empty( $args['required'] ) && count( $value ) == 1 && isset( $value[0] ) && $value[0] === '' ) {
                    continue;
                }

                if ( ! in_array( $v, $args['enum'] ) ) {
                    /* translators: 1: parameter, 2: value, 3: list of valid values */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s value "%2$s" is not one of %3$s.' ), $param, $v, implode( ', ', $args['enum'] ) ) );
                }
            }
        } else {
            if ( empty( $args['required'] ) && $value === '' ) {
                // Empty value
            } else if ( ! in_array( $value, $args['enum'] ) ) {
                /* translators: 1: parameter, 2: list of valid values */
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not one of %2$s.' ), $param, implode( ', ', $args['enum'] ) ) );
            }
        }
    }

    if ( in_array( $args['type'], array( 'integer', 'number' ) ) && ! is_numeric( $value ) ) {
        /* translators: 1: parameter, 2: type name */
        return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not of type %2$s.' ), $param, $args['type'] ) );
    }

    if ( 'boolean' === $args['type'] && ! rest_is_boolean( $value ) ) {
        /* translators: 1: parameter, 2: type name */
        return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not of type %2$s.' ), $value, 'boolean' ) );
    }

    if ( 'string' === $args['type'] && ! is_string( $value ) ) {
        /* translators: 1: parameter, 2: type name */
        return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not of type %2$s.' ), $param, 'string' ) );
    }

    if ( isset( $args['format'] ) ) {
        switch ( $args['format'] ) {
            case 'date-time' :
                if ( !empty( $args[ 'date_format' ] ) ) {
                    if ( $value && $value != geodir_date( $value, $args[ 'date_format' ], $args[ 'date_format' ] ) ) {
                        return new WP_Error( 'rest_invalid_date', sprintf( __( 'Invalid date. Valid format is %1$s.' ), $args[ 'date_format' ], 'string' ) );
                    }
                } else {
                    if ( ! rest_parse_date( $value ) ) {
                        return new WP_Error( 'rest_invalid_date', __( 'Invalid date.' ) );
                    }
                }
                break;
            case 'email' :
                // is_email() checks for 3 characters (a@b), but
                // wp_handle_comment_submission() requires 6 characters (a@b.co)
                //
                // https://core.trac.wordpress.org/ticket/38506
                if ( ( $value === '' || $value === null ) && empty( $args[ 'required' ] ) ) {
                    // Bail when empty and not required.
                } else {
                    if ( ! is_email( $value ) || strlen( $value ) < 6 ) {
                        return new WP_Error( 'rest_invalid_email', __( 'Invalid email address.' ) );
                    }
                }
                break;
            case 'ip' :
                if ( ! rest_is_ip_address( $value ) ) {
                    /* translators: %s: IP address */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%s is not a valid IP address.' ), $value ) );
                }
                break;
        }
    }

    if ( in_array( $args['type'], array( 'number', 'integer' ), true ) && ( isset( $args['minimum'] ) || isset( $args['maximum'] ) ) ) {
        if ( isset( $args['minimum'] ) && ! isset( $args['maximum'] ) ) {
            if ( ! empty( $args['exclusiveMinimum'] ) && $value <= $args['minimum'] ) {
                /* translators: 1: parameter, 2: minimum number */
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be greater than %2$d (exclusive)' ), $param, $args['minimum'] ) );
            } elseif ( empty( $args['exclusiveMinimum'] ) && $value < $args['minimum'] ) {
                /* translators: 1: parameter, 2: minimum number */
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be greater than %2$d (inclusive)' ), $param, $args['minimum'] ) );
            }
        } elseif ( isset( $args['maximum'] ) && ! isset( $args['minimum'] ) ) {
            if ( ! empty( $args['exclusiveMaximum'] ) && $value >= $args['maximum'] ) {
                /* translators: 1: parameter, 2: maximum number */
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be less than %2$d (exclusive)' ), $param, $args['maximum'] ) );
            } elseif ( empty( $args['exclusiveMaximum'] ) && $value > $args['maximum'] ) {
                /* translators: 1: parameter, 2: maximum number */
                return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be less than %2$d (inclusive)' ), $param, $args['maximum'] ) );
            }
        } elseif ( isset( $args['maximum'] ) && isset( $args['minimum'] ) ) {
            if ( ! empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
                if ( $value >= $args['maximum'] || $value <= $args['minimum'] ) {
                    /* translators: 1: parameter, 2: minimum number, 3: maximum number */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be between %2$d (exclusive) and %3$d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                }
            } elseif ( empty( $args['exclusiveMinimum'] ) && ! empty( $args['exclusiveMaximum'] ) ) {
                if ( $value >= $args['maximum'] || $value < $args['minimum'] ) {
                    /* translators: 1: parameter, 2: minimum number, 3: maximum number */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be between %2$d (inclusive) and %3$d (exclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                }
            } elseif ( ! empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                if ( $value > $args['maximum'] || $value <= $args['minimum'] ) {
                    /* translators: 1: parameter, 2: minimum number, 3: maximum number */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be between %2$d (exclusive) and %3$d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                }
            } elseif ( empty( $args['exclusiveMinimum'] ) && empty( $args['exclusiveMaximum'] ) ) {
                if ( $value > $args['maximum'] || $value < $args['minimum'] ) {
                    /* translators: 1: parameter, 2: minimum number, 3: maximum number */
                    return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s must be between %2$d (inclusive) and %3$d (inclusive)' ), $param, $args['minimum'], $args['maximum'] ) );
                }
            }
        }
    }

    return true;
}

/**
 * Function for get rest markers url.
 *
 * @since 2.0.0
 *
 * @param array $query_args Optional. Rest markers url query argument. Default array.
 * @return string $url.
 */
function geodir_rest_markers_url( $query_args = array() ) {
	$namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
	$rest_base = 'markers';

	$url = rest_url( sprintf( '%s/%s/', $namespace, $rest_base ) );

	if ( ! empty( $query_args ) && is_array( $query_args ) ) {
		$url = add_query_arg( $query_args, $url );
	}

	return apply_filters( 'geodir_rest_markers_url', $url, $query_args );
}

/**
 * Function for get rest markers url.
 *
 * @since 2.0.0
 *
 * @param array $query_args Optional. Rest markers url query argument. Default array.
 * @return string $url.
 */
function geodir_rest_url($rest_base = '', $query_args = array() ) {
	$namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;

	if($rest_base){
		$url = rest_url( sprintf( '%s/%s/', $namespace, $rest_base ) );
	}else{
		$url = rest_url( sprintf( '%s/', $namespace ) );
	}

	if ( ! empty( $query_args ) && is_array( $query_args ) ) {
		$url = add_query_arg( $query_args, $url );
	}

	return apply_filters( 'geodir_rest_url', $url, $query_args, $namespace, $rest_base );
}

/**
 * Function for rest sort options by posttype.
 *
 * @since 2.0.0
 *
 * @param string $post_type Posttype.
 * @return array $options.
 */
function geodir_rest_post_sort_options( $post_type ) {
    $sort_options = geodir_get_sort_options( $post_type );
    
    $default_orderby = 'post_date';
    $default_order = 'desc';

	$orderby_options = array();
    if ( !empty( $sort_options ) ) {
        $has_default = false;
        $fields = array();
        foreach ( $sort_options as $sort ) {
            $sort = stripslashes_deep( $sort );

			$field_name = $sort->htmlvar_name;
            $field_label = __( $sort->frontend_title, 'geodirectory' );

			if ( $sort->field_type == 'random' ) {
				$field_name = 'random';
				$sort->sort = '';
			}

			if ( $sort->sort == 'asc' ) {
                $orderby_options[ $field_name . '_asc' ] = $field_label;
            } else if ( $sort->sort == 'desc' ) {
                $orderby_options[ $field_name . '_desc' ] = $field_label;
            } else {
				$orderby_options[ $field_name ] = $field_label;
			}

			if ( (int)$sort->is_default == 1 ) {
                $has_default = true;
                $default_order = $sort->sort == 'desc' ? 'desc' : 'asc';
                $default_orderby = $field_name;
            }

            $fields[] = $field_name;
        }
        
        if ( ! $has_default && ! in_array( $default_orderby, $fields ) ) {
            $default_orderby = $default_orderby . '_' . $default_order;
        }
    }

	$options = array( 'orderby_options' => $orderby_options, 'default_orderby' => $default_orderby, 'default_order' => $default_order );

    return apply_filters( 'geodir_rest_post_sort_options', $options, $post_type );
}

/**
 * Sanitize a request argument based on details registered to the route.
 *
 * @since 2.2.16
 *
 * @param mixed           $value
 * @param WP_REST_Request $request
 * @param string          $param
 * @return mixed
 */
function geodir_rest_sanitize_request_arg( $value, $request, $param ) {
	$attributes = $request->get_attributes();
	if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
		return $value;
	}
	$args = $attributes['args'][ $param ];

	if ( ! empty( $value ) && ! empty( $args['format'] ) ) {
		if ( $args['format'] == 'textarea-field' ) {
			return geodir_sanitize_textarea_field( $value );
		} else if ( $args['format'] == 'html-field' ) {
			return geodir_sanitize_html_field( $value );
		}
	}

	return rest_sanitize_request_arg( $value, $request, $param );
}