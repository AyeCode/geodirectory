<?php
/**
 * V3 General Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// google map api generation URL
$gm_api_url = 'https://console.cloud.google.com/apis/enableflow?apiid=maps-backend.googleapis.com,static-maps-backend.googleapis.com,street-view-image-backend.googleapis.com,maps-embed-backend.googleapis.com,places-backend.googleapis.com,geocoding-backend.googleapis.com,directions-backend.googleapis.com,distance-matrix-backend.googleapis.com,geolocation.googleapis.com,elevation-backend.googleapis.com,timezone-backend.googleapis.com&keyType=CLIENT_SIDE&reusekey=true&pli=1';

return array(
	'id'          => 'general',
	'name'        => __( 'General', 'geodirectory' ),
	'icon'        => 'fa-solid fa-gear',
	'description' => __( 'Configure core site behavior, pages, search, and other general settings for GeoDirectory.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: Site & Listings
		 */
		array(
			'id'          => 'site',
			'name'        => __( 'Site & Listings', 'geodirectory' ),
			'description' => __( 'Manage general site settings and default behavior for new listings.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'admin_blocked_roles',
					'type'    => 'multiselect',
					'label'   => __( 'Restrict wp-admin access', 'geodirectory' ),
					'description' => __( 'Select user roles that should be restricted from accessing the wp-admin area.', 'geodirectory' ),
					'options' => function_exists('geodir_user_roles') ? geodir_user_roles(array('administrator')) : array(),
					'default' => array('subscriber'),
					'class'   => 'aui-select2',
					'placeholder' => __('Select roles...', 'geodirectory'),
					'searchable' => array('admin', 'restrict', 'block', 'dashboard', 'access', 'backend'),
				),
				array(
					'id'      => 'shortcodes_allowed_roles',
					'type'    => 'multiselect',
					'label'   => __( 'Allow shortcodes in description', 'geodirectory' ),
					'description' => __( 'Select user roles that are allowed to use shortcodes or blocks in the listing description field.', 'geodirectory' ),
					'options' => function_exists('geodir_user_roles') ? geodir_user_roles() : array(),
					'default' => array('administrator'),
					'class'   => 'aui-select2',
					'placeholder' => __('Select roles...', 'geodirectory'),
					'searchable' => array('shortcode', 'blocks', 'description', 'content', 'permissions'),
				),
				array(
					'id'      => 'user_trash_posts',
					'type'    => 'toggle',
					'label'   => __( 'Move user-deleted posts to trash', 'geodirectory' ),
					'description' => __( 'When enabled, posts deleted by users from the frontend are moved to the trash instead of being permanently deleted.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('delete', 'trash', 'remove', 'user', 'listing'),
				),
				array(
					'id'      => 'default_status',
					'type'    => 'select',
					'label'   => __( 'New listing default status', 'geodirectory' ),
					'description' => __( 'Choose the default status for new listings submitted from the frontend.', 'geodirectory' ),
					'options' => array(
						'pending' => __( 'Pending Review', 'geodirectory' ),
						'publish' => __( 'Published', 'geodirectory' ),
					),
					'default' => 'pending',
					'searchable' => array('status', 'listing', 'default', 'publish', 'pending', 'submission'),
				),
				array(
					'id'      => 'post_logged_out',
					'type'    => 'toggle',
					'label'   => __( 'Allow guest submissions', 'geodirectory' ),
					'description' => __( 'Allow users who are not logged in to submit listings from the frontend.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('guest', 'anonymous', 'logged out', 'submit', 'post'),
				),
				array(
					'id'      => 'post_preview',
					'type'    => 'toggle',
					'label'   => __( 'Enable submission preview', 'geodirectory' ),
					'description' => __( 'Show a "Preview" button on the add listing form, allowing users to see how their listing will look before submitting.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('preview', 'submission', 'add listing', 'form'),
				),
				array(
					'id'      => 'upload_max_filesize',
					'type'    => 'number',
					'label'   => __( 'Max upload file size (MB)', 'geodirectory' ),
					'description' => __( 'Set the maximum file size for uploads. This overrides the default WordPress limit for GD uploads.', 'geodirectory' ),
					'default' => 8,
					'placeholder' => '8',
					'searchable' => array('upload', 'size', 'file', 'image', 'limit', 'megabytes'),
				),
				array(
					'id'      => 'noindex_archives',
					'type'    => 'toggle',
					'label'   => __( 'Noindex empty archives', 'geodirectory' ),
					'description' => __( 'Add a "noindex" meta tag to GeoDirectory category or tag archive pages that do not contain any listings.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('noindex', 'seo', 'archive', 'empty', 'category', 'tag'),
				),
			),
		),

		/**
		 * Subsection: Map Settings
		 */
		array(
			'id'          => 'map_settings',
			'name'        => __( 'Map Settings', 'geodirectory' ),
			'description' => __( 'Configure your map provider, API keys, and map behavior.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'maps_api',
					'type'    => 'select',
					'label'   => __( 'Maps API Provider', 'geodirectory' ),
					'description' => __( 'Choose which mapping service to use. "Automatic" is recommended.', 'geodirectory' ),
					'options' => array(
						'auto'   => __('Automatic (recommended)', 'geodirectory'),
						'google' => __('Google Maps API', 'geodirectory'),
						'osm'    => __('OpenStreetMap API', 'geodirectory'),
						'none'   => __('Disable Maps', 'geodirectory'),
					),
					'default' => 'auto',
					'class'   => 'aui-select2',
					'searchable' => array('map', 'api', 'google', 'openstreetmap', 'osm'),
				),
				array(
					'id'      => 'google_maps_api_key',
					'type'    => 'google_api_key',
					'label'   => __( 'Google Maps API Key', 'geodirectory' ),
					'description' => sprintf( __( 'Required if using Google Maps. Leave blank to use OpenStreetMap. %s How to generate a Google API key %s', 'geodirectory' ), '<a href="https://wpgeodirectory.com/documentation/article/installation/get-a-google-api-key/" target="_blank" class="text-dark">','</a>'),
					'placeholder' => __( 'Leave this blank to use Open Street Maps (OSM)', 'geodirectory' ),
					'input_group_right' => '<button class="btn btn-success text-white" type="button"  onclick="geodir_validate_google_api_key(jQuery(\'#google_maps_api_key\').val());">'.esc_attr__( 'Verify', 'geodirectory' ).'</button><button class="btn btn-primary" type="button"  onclick=\'window.open("'.wp_slash($gm_api_url).'", "newwindow", "width=600, height=400"); return false;\' >' . esc_attr__( 'Generate Key', 'geodirectory' ) . '</button>',
					'searchable' => array('google', 'maps', 'api', 'key', 'gmaps'),
					'show_if'   => '[%maps_api%] == "auto" || [%maps_api%] == "google"',
				),
				array(
					'id'      => 'google_geocode_api_key',
					'type'    => 'password',
					'label'   => __( 'Google Geocoding API Key', 'geodirectory' ),
					'description' => __( 'Optional. Use a separate key for geocoding if your main key is restricted by HTTP referrer.', 'geodirectory' ),
					'placeholder' => __( 'Uses Maps API Key if blank', 'geodirectory' ),
					'searchable' => array('google', 'geocoding', 'api', 'key', 'address'),
					'show_if'   => '[%maps_api%] == "auto" || [%maps_api%] == "google"',

				),
				array(
					'id'      => 'maps_lazy_load',
					'type'    => 'select',
					'label'   => __( 'Lazy Load Maps', 'geodirectory' ),
					'description' => __( 'Choose how maps should be loaded to improve page performance.', 'geodirectory' ),
					'options' => array(
						''      => __( 'Off (no lazy loading)', 'geodirectory' ),
						'auto'  => __( 'Auto (load on scroll)', 'geodirectory' ),
						'click' => __( 'Click to Load', 'geodirectory' ),
					),
					'default' => 'auto',
					'class'   => 'aui-select2',
					'show_if'   => '[%maps_api%] != "none"',
				),
				array(
					'id'       => 'map_language',
					'type'     => 'select',
					'label'    => __( 'Default map language', 'geodirectory' ),
					'description' => __( 'This will determine the language of location slugs. Avoid changing this after listings have been added.', 'geodirectory' ),
					'default'  => 'en',
					'class'    => 'aui-select2',
					'options'  => self::supported_map_languages(), // Placeholder options
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('map', 'language', 'slug'),
					'show_if'   => '[%maps_api%] != "none"',
				),
				array(
					'id'       => 'map_default_marker_icon',
					'type'     => 'image',
					'label'    => __( 'Default marker icon', 'geodirectory' ),
					'description' => __( 'This marker is used if a category does not have a marker icon set.', 'geodirectory' ),
					'default'  => '',
					'searchable' => array('map', 'marker', 'icon', 'pin', 'default'),
					'show_if'   => '[%maps_api%] != "none"',
				),
				array(
					'id'       => 'split_uk',
					'type'     => 'toggle',
					'label'    => __( 'Split United Kingdom', 'geodirectory' ),
					'description' => __( 'Split the UK into England, Northern Ireland, Scotland & Wales. <b class="text-danger">Existing records will need to be updated manually or via import/export.</b>', 'geodirectory' ),
					'default'  => false,
					'searchable' => array('uk', 'united kingdom', 'england', 'scotland', 'wales', 'ireland'),
				),
				array(
					'id'       => 'map_cache',
					'type'     => 'toggle',
					'label'    => __( 'Enable map cache', 'geodirectory' ),
					'description' => __( 'This will cache the map JSON for 24 hours or until a listing is saved to improve performance.', 'geodirectory' ),
					'default'  => false,
					'searchable' => array('map', 'cache', 'performance', 'speed', 'json'),
					'show_if'   => '[%maps_api%] != "none"',
				),
			),
		),

		/**
		 * Subsection: Default Location
		 */
		array(
			'id'          => 'default_location',
			'name'        => __( 'Default Location', 'geodirectory' ),
			'description' => __( 'Set the default location used for map centering and searches.', 'geodirectory' ),
			'fields'      => array(

				array(
					'id'      => 'default_location_notice',
					'type'    => 'alert',
					'alert_type' => '',
					//'label'   => __( 'Default City', 'geodirectory' ),
					'description' => __( 'Drag the map or the marker to set the city/town you wish to use as the default location, then click save changes.', 'geodirectory' ),
					'default' => 'Philadelphia',
					//'searchable' => array('default', 'location', 'city', 'address'),
				),
				array(
					'id'          => 'listing_location_map',
					'type'        => 'custom_renderer',
					'label'       => 'Listing Location',
					'description' => 'Drag the marker to set the precise location.',
					'lat_field'   => 'default_location_latitude',  // The setting that will store the latitude
					'lng_field'   => 'default_location_latitude', // The setting that will store the longitude
					'renderer_function' => 'geodir_listing_location_map',
				),
//				array(
//					'id'          => 'listing_location_map',
//					'type'        => 'gd_map',
//					'label'       => 'Listing Location',
//					'description' => 'Drag the marker to set the precise location.',
//					'lat_field'   => 'default_location_latitude',  // The setting that will store the latitude
//					'lng_field'   => 'default_location_latitude', // The setting that will store the longitude
//				),
				array(
					'id'      => 'default_location_city',
					'type'    => 'text',
					'label'   => __( 'Default City', 'geodirectory' ),
					'description' => __( 'The default city name.', 'geodirectory' ),
					'default' => 'Philadelphia',
					'searchable' => array('default', 'location', 'city', 'address'),
				),
				array(
					'id'      => 'default_location_region',
					'type'    => 'text',
					'label'   => __( 'Default Region', 'geodirectory' ),
					'description' => __( 'The default region or state name.', 'geodirectory' ),
					'default' => 'Pennsylvania',
					'searchable' => array('default', 'location', 'region', 'state', 'address'),
				),
				array(
					'id'      => 'default_location_country',
					'type'    => 'select',
					'label'   => __( 'Default Country', 'geodirectory' ),
					'description' => __( 'The default country.', 'geodirectory' ),
					'options' => function_exists('geodir_get_countries') ? geodir_get_countries() : array(),
					'default' => 'United States',
					'class'   => 'aui-select2',
					'searchable' => array('default', 'location', 'country', 'address'),
				),
				array(
					'id'      => 'default_location_latitude',
					'type'    => 'text',
					'label'   => __( 'Default Latitude', 'geodirectory' ),
					'description' => __( 'The latitude for the default map center.', 'geodirectory' ),
					'default' => '39.952389',
					'searchable' => array('default', 'location', 'latitude', 'map', 'coordinates'),
				),
				array(
					'id'      => 'default_location_longitude',
					'type'    => 'text',
					'label'   => __( 'Default Longitude', 'geodirectory' ),
					'description' => __( 'The longitude for the default map center.', 'geodirectory' ),
					'default' => '-75.163598',
					'searchable' => array('default', 'location', 'longitude', 'map', 'coordinates'),
				),
				array(
					'id'       => 'default_location_timezone_string',
					'type'     => 'select', // May require a special renderer for timezones
					'label'    => __( 'Timezone', 'geodirectory' ),
					'description' => __( 'Select the default city/timezone.', 'geodirectory' ),
					'class'    => 'aui-select2',
					'default'  => function_exists('geodir_timezone_string') ? geodir_timezone_string() : 'UTC',
					'options'  => array('UTC' => 'UTC'), // Placeholder
					'searchable' => array('timezone', 'time', 'location'),
				),
				array(
					'id'      => 'multi_city',
					'type'    => 'toggle',
					'label'   => __( 'Enable Multi-City', 'geodirectory' ),
					'description' => __( 'Allow listings to be added anywhere, outside of the defined default location.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('multi city', 'location', 'anywhere', 'worldwide'),
				),
			)
		),

		/**
		 * Subsection: Pages & Templates
		 */
		array(
			'id'          => 'pages',
			'name'        => __( 'Pages & Templates', 'geodirectory' ),
			'description' => __( 'Assign the essential pages and templates used by GeoDirectory for its core functionality.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'page_location',
					'type'    => 'select',
					'label'   => __( 'Location Page', 'geodirectory' ),
					'description' => __( 'This page displays location-specific archives. [gd_locations] shortcode is required.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('page', 'location', 'assign'),
				),
				array(
					'id'      => 'page_add',
					'type'    => 'select',
					'label'   => __( 'Add Listing Page', 'geodirectory' ),
					'description' => __( 'This page contains the form for submitting new listings. [gd_add_listing] shortcode is required.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('page', 'add listing', 'submit', 'form', 'assign'),
				),
				array(
					'id'      => 'page_search',
					'type'    => 'select',
					'label'   => __( 'Search Page', 'geodirectory' ),
					'description' => __( 'This page is used to display search results. [gd_search] shortcode is required.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('page', 'search', 'results', 'assign'),
				),
				array(
					'id'      => 'page_terms_conditions',
					'type'    => 'select',
					'label'   => __( 'Terms and Conditions Page', 'geodirectory' ),
					'description' => __( 'Select your site\'s Terms and Conditions page.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('page', 'terms', 'conditions', 'privacy', 'legal', 'assign'),
				),
				array(
					'id'      => 'page_details',
					'type'    => 'select',
					'label'   => __( 'Details Page Template', 'geodirectory' ),
					'description' => __( 'The template used for single listing detail pages.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('template', 'details', 'listing page', 'single', 'assign'),
				),
				array(
					'id'      => 'page_archive',
					'type'    => 'select',
					'label'   => __( 'Archive Page Template', 'geodirectory' ),
					'description' => __( 'The template used for category, tag, and location archive pages.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('template', 'archive', 'category', 'tag', 'location', 'assign'),
				),
				array(
					'id'      => 'page_archive_item',
					'type'    => 'select',
					'label'   => __( 'Archive Item Template', 'geodirectory' ),
					'description' => __( 'The template for a single item within an archive loop.', 'geodirectory' ),
					'options' => $this->get_pages_options(),
					'class'   => 'aui-select2',
					'extra_attributes' => array(
						'data-select'   => '{"searchEnabled":true}',
					),
					'searchable' => array('template', 'archive item', 'loop', 'listing card', 'assign'),
				),
			)
		),

		/**
		 * Subsection: Search
		 */
		array(
			'id'          => 'search',
			'name'        => __( 'Search', 'geodirectory' ),
			'description' => __( 'Customize the search bar and search results behavior.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'search_default_text',
					'type'    => 'text',
					'label'   => __( 'Search field placeholder', 'geodirectory' ),
					'description' => __( 'The placeholder text for the main search input field.', 'geodirectory' ),
					'placeholder' => 'e.g. pizza, hotel, plumber...',
					'searchable' => array('search', 'placeholder', 'text', 'keyword'),
				),
				array(
					'id'      => 'search_default_near_text',
					'type'    => 'text',
					'label'   => __( 'Near field placeholder', 'geodirectory' ),
					'description' => __( 'The placeholder text for the location ("near") input field.', 'geodirectory' ),
					'placeholder' => 'e.g. London or NW1 5QT',
					'searchable' => array('search', 'placeholder', 'near', 'location', 'address'),
				),
				array(
					'id'      => 'search_default_button_text',
					'type'    => 'font-awesome',
					'label'   => __( 'Search button label', 'geodirectory' ),
					'description' => __( 'The text or Font Awesome icon class for the search button.', 'geodirectory' ),
					'default' => 'fa-solid fa-magnifying-glass',
					'placeholder' => 'fa-solid fa-magnifying-glass',
					'searchable' => array('search', 'button', 'label', 'text', 'icon'),
				),
				array(
					'id'      => 'search_radius',
					'type'    => 'range',
					'label'   => __( 'Default search radius', 'geodirectory' ),
					'description' => __( 'The default radius to use for location-based searches.', 'geodirectory' ),
					'default' => 7,
					'min' => 1,
					'max' => 3000,
					'step' => 1,
					'searchable' => array('search', 'radius', 'distance', 'near'),
				),
				array(
					'id'      => 'search_distance_long',
					'type'    => 'select',
					'label'   => __( 'Distance unit', 'geodirectory' ),
					'description' => __( 'The unit of measurement to use for distances.', 'geodirectory' ),
					'options' => array(
						'miles' => __('Miles', 'geodirectory'),
						'km'    => __('Kilometers', 'geodirectory'),
					),
					'default' => 'miles',
					'class'   => 'aui-select2',
					'searchable' => array('search', 'distance', 'unit', 'miles', 'km', 'kilometers'),
				),
				array(
					'id'      => 'search_distance_short',
					'type'    => 'select',
					'label'   => __( 'Short distance unit', 'geodirectory' ),
					'description' => __( 'If distance is very small, show distance in meters or feet.', 'geodirectory' ),
					'options' => array(
						'feet'   => __('Feet', 'geodirectory'),
						'meters' => __('Meters', 'geodirectory'),
					),
					'default' => 'feet',
					'class'   => 'aui-select2',
					'searchable' => array('search', 'distance', 'unit', 'feet', 'meters'),
				),
				array(
					'id'      => 'search_near_addition',
					'type'    => 'text',
					'label'   => __( 'Append to "near" searches', 'geodirectory' ),
					'description' => __( 'Useful for directories limited to one location (e.g., "New York"). This text will be added to location searches.', 'geodirectory' ),
					'placeholder' => 'New York',
					'searchable' => array('search', 'near', 'location', 'suffix'),
				),
				array(
					'id'      => 'search_word_limit',
					'type'    => 'select',
					'label'   => __( 'Exclude short words from search', 'geodirectory' ),
					'description' => __( 'Limit individual words being searched for to improve relevance.', 'geodirectory' ),
					'options' => array(
						'0' => __('Disabled', 'geodirectory'),
						'1' => __('1 Character words excluded', 'geodirectory'),
						'2' => __('2 Character words and less excluded', 'geodirectory'),
						'3' => __('3 Character words and less excluded', 'geodirectory'),
					),
					'default' => '0',
					'class'   => 'aui-select2',
					'searchable' => array('search', 'word', 'limit', 'exclude', 'short'),
				),
			)
		),
	)
);
