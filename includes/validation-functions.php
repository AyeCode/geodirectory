<?php
/**
 * Geodirectory Validation functions
 *
 *
 * @package     GeoDirectory
 * @category    Core
 * @author      WPGeoDirectory
 * @since       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Validate a latitude.
 *
 * @param string $latitude The latitude to validate.
 *
 * @return bool
 */
function geodir_is_valid_lat( $latitude ) {
    if ( $latitude != '' && preg_match( '/^(\+|-)?(?:90(?:(?:\.0{1,20})?)|(?:[0-9]|[1-8][0-9])(?:(?:\.[0-9]{1,20})?))$/', $latitude ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Validate a longitude.
 *
 * @param string $longitude The longitude to validate.
 *
 * @return bool
 */
function geodir_is_valid_lon( $longitude ){
    if ( $longitude != '' && preg_match( '/^(\+|-)?(?:180(?:(?:\.0{1,20})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,20})?))$/', $longitude ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Validate and parse the measurement value.
 *
 * @since 1.0.0
 *
 * @param string $value Input value to validate measurement.
 * @return string The measurement value in valid format.
 */
function geodir_validate_measurements( $value ) {
    if ( ( strlen( $value ) - 1 ) == strpos( trim( $value ), '%' ) ) {
        // $value is entered as a percent, so it can't be less than 0 or more than 100
        $value = preg_replace( '/\D/', '', $value );
        if ( 100 < $value ) {
            $value = 100;
        }
        // Re-add the percent symbol
        $value = $value . '%';
    } elseif ( ( strlen( $value ) - 2 ) == strpos( trim( $value ), 'px' ) ) {
        // Get the absint & re-add the 'px'
        $value = preg_replace( '/\D/', '', $value ) . 'px';
    } else {
        $value = preg_replace( '/\D/', '', $value );
    }

    return $value;
}