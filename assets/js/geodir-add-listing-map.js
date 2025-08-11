/**
 * GeoDirectory Add Listing Map - Shim
 *
 * This script initializes the map via the global GeoDirectoryMapManager.
 *
 * @version 5.0.0
 */
(function($, data) {
	'use strict';

	if (typeof data === 'undefined' || typeof window.GeoDirectoryMapManager === 'undefined') {
		console.error('GeoDirectory Map Error: Missing geodirMapData or GeoDirectoryMapManager.');
		return;
	}

	$(function() {
		const mapContainerId = data.prefix + 'map';

		if ($('#' + mapContainerId).length > 0) {
			// Tell the global manager to initialize a map in this container with this data
			window.GeoDirectoryMapManager.initMap(mapContainerId, data);

			// Bind the button to the public manager method, passing the specific container ID
			$("#" + data.prefix + "set_address_button").on("click", function() {
				window.GeoDirectoryMapManager.codeAddress(mapContainerId, true);
			});
		}
	});

})(jQuery, window.geodirMapData);
