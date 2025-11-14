<?php
/**
 * Debug Service
 *
 * Handles error logging and debugging functionality.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

/**
 * Debug class for error logging and debugging.
 */
final class Debug {

	/**
	 * Log GeoDirectory errors.
	 *
	 * This function will log GD errors if the WP_DEBUG constant is true, it can be filtered.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $log The thing that should be logged.
	 * @param string $title Optional log title.
	 * @param string $file Optional file name.
	 * @param string $line Optional line number.
	 * @param bool $exit Whether to exit after logging.
	 */
	public function error_log( $log, string $title = '', string $file = '', string $line = '', bool $exit = false ): void {
		/**
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

	/**
	 * Mark a function as being called incorrectly.
	 *
	 * @since 3.0.0
	 *
	 * @param string $function The function that was called.
	 * @param string $message A message explaining what has been done incorrectly.
	 * @param string $version The version of WordPress where the message was added.
	 */
	public function doing_it_wrong( string $function, string $message, string $version ): void {
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( defined( 'DOING_AJAX' ) ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			$this->error_log( $function . ' was called incorrectly. ' . $message . '. This message was added in version ' . $version . '.' );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
	}

	/**
	 * Display a GeoDirectory help tip.
	 *
	 * @since 3.0.0
	 *
	 * @param string $tip Help tip text.
	 * @param bool $allow_html Allow sanitized HTML if true or escape.
	 * @return string Help tip HTML.
	 */
	public function help_tip( string $tip, bool $allow_html = false ): string {
		global $aui_bs5;

		if ( $allow_html ) {
			$tip = geodir_sanitize_tooltip( $tip );
		} else {
			$tip = esc_attr( $tip );
		}
		$ml = $aui_bs5 ? 'ms-2' : 'ml-2';

		return '<i class="fas fa-question-circle gd-help-tip ' . $ml . ' text-muted" title="' . $tip . '" data-bs-toggle="tooltip" data-bd-html="true"></i>';
	}
}
