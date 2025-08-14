<?php
namespace AyeCode\GeoDirectory\Ajax;

/**
 * AjaxHandler Class
 *
 * This class acts as the central router for all WordPress AJAX actions
 * originating from the settings framework. It delegates the handling
 * to the appropriate registry.
 */
class AjaxHandler {

	public function __construct() {
		// Hook into your settings framework for both types of actions.
		add_action( 'asf_execute_tool_geodir_tools', [$this, 'execute_tool']);
		add_action( 'asf_render_content_pane_geodir_tools', [$this, 'render_html']);
	}

	/**
	 * Delegates a tool execution request to the Action Registry.
	 */
	public function execute_tool(string $name) {
		ActionRegistry::dispatch($name);
	}

	/**
	 * Delegates an HTML pane rendering request to the Pane Registry.
	 */
	public function render_html(string $name) {
		PaneRegistry::render($name);
	}
}
