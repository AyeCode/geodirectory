<?php
/**
 * Core functions.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */
 
 /*
 * A function to log GD errors no matter the type given.
 *
 * This function will log GD errors if the WP_DEBUG constant is true, it can be filtered.
 *
 * @since 1.5.7
 * @param mixed $log The thing that should be logged.
 * @package GeoDirectory
 */
function geodir_error_log( $log, $title = '', $file = '', $line = '', $exit = false ) {
    /*
     * A filter to override the WP_DEBUG setting for function geodir_error_log().
     *
     * @since 1.5.7
     */
    $should_log = apply_filters( 'geodir_log_errors', WP_DEBUG );

    if ( $should_log ) {
        $label = '';
        if ( $file && $file !== '' ) {
            $label .= basename( $file ) . ( $line ? '(' . $line . ')' : '' );
        }
        
        if ( $title && $title !== '' ) {
            $label = $label !== '' ? $label . ' ' : '';
            $label .= $title . ' ';
        }
        
        $label = $label !== '' ? trim( $label ) . ' : ' : '';
        
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( $label . print_r( $log, true ) );
        } else {
            error_log( $label . $log );
        }
        
        if ( $exit ) {
            exit;
        }
    }
}

function geodir_doing_it_wrong( $function, $message, $version ) {
    $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

    if ( is_ajax() ) {
        do_action( 'doing_it_wrong_run', $function, $message, $version );
        geodir_error_log( $function . ' was called incorrectly. ' . $message . '. This message was added in version ' . $version . '.' );
    } else {
        _doing_it_wrong( $function, $message, $version );
    }
}