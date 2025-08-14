<?php
namespace AyeCode\GeoDirectory\Ajax;

class ActionRegistry {
	/**
	 * Holds the mapping of tool names to their handler classes.
	 * @var array
	 */
	private static $actions = [];

	/**
	 * Registers a tool name to a specific handler class.
	 * This is called during initialization. It does NOT create an object.
	 *
	 * @param string $name The unique name of the tool (e.g., 'clear_version_numbers').
	 * @param string $class_name The fully qualified name of the class that handles it.
	 */
	public static function register(string $name, string $class_name) {
		self::$actions[$name] = $class_name;
	}

	/**
	 * Finds and executes the requested action.
	 * This is called during the AJAX request.
	 *
	 * @param string $name The name of the tool to execute.
	 */
	public static function dispatch(string $name) {
		if (isset(self::$actions[$name])) {
			$class_name = self::$actions[$name];

			if (class_exists($class_name)) {
				// Instantiate ONLY the class we need, right when we need it.
				$action = new $class_name();

				// Assuming your action classes have a public 'dispatch' method.
				if (method_exists($action, 'dispatch')) {
					$action->dispatch();
					return;
				}
			}
		}

		// Optional: Handle cases where the tool is not found.
		wp_send_json_error(['message' => 'Requested tool not found.'], 404);
	}
}
