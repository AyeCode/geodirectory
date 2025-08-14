<?php
namespace AyeCode\GeoDirectory\Ajax;

/**
 * PaneRegistry Class
 *
 * Manages the registration and rendering of HTML content panes for the
 * settings framework. This acts as a "phonebook" for all available panes.
 */
class PaneRegistry {
	/**
	 * Holds the mapping of pane names to their handler classes.
	 * @var array
	 */
	private static $panes = [];

	/**
	 * Registers a pane name to a specific handler class.
	 *
	 * @param string $name The unique name of the pane (e.g., 'status_report').
	 * @param string $class_name The fully qualified name of the class that handles it.
	 */
	public static function register(string $name, string $class_name) {
		self::$panes[$name] = $class_name;
	}

	/**
	 * Finds and executes the action to render the requested pane.
	 *
	 * @param string $name The name of the pane to render.
	 */
	public static function render(string $name) {
		if (isset(self::$panes[$name])) {
			$class_name = self::$panes[$name];

			if (class_exists($class_name)) {
				// Instantiate only the class we need and call its dispatch method.
				$action = new $class_name();
				if (method_exists($action, 'dispatch')) {
					$action->dispatch();
					return;
				}
			}
		}
	}
}
