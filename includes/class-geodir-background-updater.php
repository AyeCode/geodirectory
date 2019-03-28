<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    GeoDir_Background_Updater
 * @version  2.0.0
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   GeoDirectory
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Background_Process', false ) ) {
	include_once dirname( __FILE__ ) . '/abstracts/class-geodir-background-process.php';
}

/**
 * GeoDir_Background_Updater Class.
 */
class GeoDir_Background_Updater extends GeoDir_Background_Process {

	/**
	 * Initiate new background process.
	 */
	public function __construct() {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = 'geodir_updater';

		parent::__construct();
	}

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
     *
     * @since 2.0.0
	 */
	public function dispatch() {
		$dispatched = parent::dispatch();

		if ( is_wp_error( $dispatched ) ) {
			geodir_error_log( sprintf( 'Unable to dispatch GeoDirectory updater: %s', $dispatched->get_error_message() ) );
		}
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
     *
     * @since 2.0.0
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
     *
     * @since 2.0.0
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
     *
     * @since 2.0.0
     *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
     *
     * @since 2.0.0
	 *
	 * @param string $callback Update callback function.
	 * @return mixed
	 */
	protected function task( $callback ) {
		geodir_maybe_define( 'GD_UPDATING', true );
		$result = false;
		if ( is_callable( $callback ) ) {
			geodir_error_log( sprintf( 'Running %s callback', $callback ) );
			$result = call_user_func( $callback );

			if ( $result ) {
				geodir_error_log( sprintf( 'Callback needs to run again: %s', $callback ) );
			} else {
				geodir_error_log( sprintf( 'Finished %s callback', $callback ) );
			}


		} else {
			geodir_error_log( sprintf( 'Could not find %s callback', $callback ) );
		}

		return $result ? $callback : false;
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
     *
     * @since 2.0.0
	 */
	protected function complete() {
		geodir_error_log( 'Data update complete' );
		GeoDir_Admin_Install::update_db_version();
		parent::complete();
	}

	/**
	 * See if the batch limit has been exceeded.
	 *
	 * @return bool
	 */
	public function is_memory_exceeded() {
		return $this->memory_exceeded();
	}
}
