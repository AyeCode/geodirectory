<?php
/**
 * GeoDirectory Core Helper Functions
 *
 * @package GeoDirectory
 * @since 3.0.0
 */

/**
 * The main function for accessing the GeoDirectory plugin instance.
 *
 * Acts as a gateway to all the plugin's services. On first run, it
 * receives the container to initialize the main \AyeCode\GeoDirectory\GeoDirectory
 * object. On all subsequent runs, it returns that same object.
 *
 * @param \AyeCode\GeoDirectory\Core\Container|null $container The container instance, only used on initialization.
 *
 * @return \AyeCode\GeoDirectory\GeoDirectory The main GeoDirectory instance.
 */
function geodirectory( \AyeCode\GeoDirectory\Core\Container $container = null ): ?\AyeCode\GeoDirectory\GeoDirectory {
	static $instance = null;

	// On the first run, it receives the container and creates the main object.
	if ( null === $instance && $container !== null ) {
		$instance = new \AyeCode\GeoDirectory\GeoDirectory( $container );
	}

	// On all subsequent runs, it just returns the object it already created.
	return $instance;
}
