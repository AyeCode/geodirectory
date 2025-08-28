<?php
/**
 * Hookable Trait
 *
 * Provides methods for classes to register and later remove their own hooks.
 *
 * @package GeoDirectory\Support
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Support;

trait Hookable {
	/**
	 * A list of registered hooks for this instance.
	 *
	 * @var array
	 */
	private array $hooks = [];

	/**
	 * Wrapper for add_action that tracks the hook.
	 *
	 * @param string   $tag      The name of the action to which the $function_to_add is hooked.
	 * @param callable $callback The name of the function you wish to be called.
	 * @param int      $priority Optional. Used to specify the order in which the functions
	 * are executed. Default 10.
	 * @param int      $args     Optional. The number of arguments the function accepts. Default 1.
	 * @return void
	 */
	protected function on( string $tag, callable $callback, int $priority = 10, int $args = 1 ): void {
		add_action( $tag, $callback, $priority, $args );
		$this->hooks[] = [ 'type' => 'action', 'tag' => $tag, 'callback' => $callback, 'priority' => $priority ];
	}

	/**
	 * Wrapper for add_filter that tracks the hook.
	 *
	 * @param string   $tag      The name of the filter to hook the $function_to_add callback to.
	 * @param callable $callback The callback to be run when the filter is applied.
	 * @param int      $priority Optional. Used to specify the order in which the functions
	 * are executed. Default 10.
	 * @param int      $args     Optional. The number of arguments the function accepts. Default 1.
	 * @return void
	 */
	protected function filter( string $tag, callable $callback, int $priority = 10, int $args = 1 ): void {
		add_filter( $tag, $callback, $priority, $args );
		$this->hooks[] = [ 'type' => 'filter', 'tag' => $tag, 'callback' => $callback, 'priority' => $priority ];
	}

	/**
	 * Removes all hooks that were registered by this instance.
	 *
	 * @return void
	 */
	public function unhook_all(): void {
		foreach ( $this->hooks as $hook ) {
			if ( $hook['type'] === 'action' ) {
				remove_action( $hook['tag'], $hook['callback'], $hook['priority'] );
			} else {
				remove_filter( $hook['tag'], $hook['callback'], $hook['priority'] );
			}
		}
		$this->hooks = []; // Clear the hooks array.
	}
}
