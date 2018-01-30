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

/**
 * Validate a latitude.
 *
 * @param string $latitude The latitude to validate.
 *
 * @return bool
 */
function geodir_is_valid_lat($latitude){
    if (preg_match("/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}$/", $latitude)) {
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
function geodir_is_valid_lon($longitude){
    if(preg_match("/^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,6}$/",
        $longitude)) {
        return true;
    } else {
        return false;
    }
}