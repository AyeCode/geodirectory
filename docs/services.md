# GeoDirectory Global API Reference

This document details the public methods available on the services accessed via the `geodirectory()` global function.

## Main Instance (`geodirectory()`)

These methods are available directly on the `geodirectory()` function call.
These need to be registered in geodirectory.php and in /src/GeoDirectory.php

```php
/**
 * Provides direct access to the service container.
 * @return \AyeCode\GeoDirectory\Core\Container
 */
geodirectory()->container();
```

---

## `geodirectory()->locations`
**Class:** `AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface`

This service handles retrieving `LocationData` objects, location hierarchy (countries, regions, cities), and location-related queries.

```php
/**
 * Gets the LocationData object for the current page view.
 * @return \AyeCode\GeoDirectory\Core\Data\LocationData
 */
geodirectory()->locations->get_current();

/**
 * Gets the default LocationData object set in the admin settings.
 * @return \AyeCode\GeoDirectory\Core\Data\LocationData
 */
geodirectory()->locations->get_default();

/**
 * Gets the LocationData object for a specific post.
 * @param int $post_id The ID of the post.
 * @return \AyeCode\GeoDirectory\Core\Data\LocationData
 */
geodirectory()->locations->get_for_post( $post_id );

/**
 * Get current location terms from query vars.
 * @param string|null $location_array_from Deprecated parameter.
 * @param string $gd_post_type The post type.
 * @return array The location term array.
 */
geodirectory()->locations->get_current_location_terms( null, 'gd_place' );

/**
 * Get all countries from the database.
 * @param array $args Query arguments.
 * @param bool $split Split UK into constituent countries. Default true.
 * @return array Countries array.
 */
geodirectory()->locations->get_countries( [], true );

/**
 * Get countries as key-value pairs (name => translated name).
 * @return array Countries array.
 */
geodirectory()->locations->get_countries_list();

/**
 * Get country dropdown HTML or array.
 * @param string $post_country Selected country.
 * @param string $prefix Not yet implemented.
 * @param bool $return_array Return array instead of HTML.
 * @return string|array Dropdown HTML or array.
 */
geodirectory()->locations->get_country_dropdown( 'United States', '', false );

/**
 * Get regions for a country (stub - extended by Location Manager).
 * @param string $country Country slug or name.
 * @return array Regions array.
 */
geodirectory()->locations->get_regions( 'united-states' );

/**
 * Get cities for a country/region (stub - extended by Location Manager).
 * @param string $country Country slug or name.
 * @param string $region Region slug or name.
 * @return array Cities array.
 */
geodirectory()->locations->get_cities( 'united-states', 'california' );

/**
 * Get neighbourhoods for a city (stub - extended by Location Manager).
 * @param string $city City slug or name.
 * @return array Neighbourhoods array.
 */
geodirectory()->locations->get_neighbourhoods( 'san-francisco' );

/**
 * Create location slug from string.
 * @param string $location_string Location string.
 * @return string Location slug.
 */
geodirectory()->locations->create_location_slug( 'New York City' );

/**
 * Check if multi-city mode is active.
 * @return bool True if multi-city is active.
 */
geodirectory()->locations->core_multi_city();

/**
 * Check if UK should be split into constituent countries.
 * @return bool True to split UK.
 */
geodirectory()->locations->split_uk();
```

---

## `geodirectory()->query_vars`
**Class:** `AyeCode\GeoDirectory\Core\Services\QueryVars`

This service handles parsing and sanitizing query variables from WordPress query vars and HTTP requests. It provides centralized access to search, location, and GeoDirectory-specific query parameters.

```php
/**
 * Get latitude and longitude from query vars or request.
 * Checks for lat/lon in WP query var 'latlon' (comma-separated)
 * or $_REQUEST params 'sgeo_lat' and 'sgeo_lon'.
 * @return array Array with 'lat' and 'lon' keys, empty strings if not found.
 */
geodirectory()->query_vars->get_latlon();

/**
 * Retrieve a query variable from WP query vars or $_REQUEST.
 * Checks WP query vars first, then falls back to $_REQUEST.
 * @param string $var The variable key to retrieve.
 * @param mixed $default Optional. Value to return if not found. Default empty string.
 * @return mixed The query variable value, sanitized if from $_REQUEST.
 */
geodirectory()->query_vars->get( 'snear', '' );

/**
 * Get search distance from request or settings.
 * @return float Search radius distance.
 */
geodirectory()->query_vars->get_search_distance();

/**
 * Get search near location value.
 * @return string Near location search term.
 */
geodirectory()->query_vars->get_search_near();

/**
 * Get search keyword.
 * @return string Search keyword.
 */
geodirectory()->query_vars->get_search_term();

/**
 * Check if search is an exact match search (wrapped in quotes).
 * @param string $search_term The search term to check.
 * @return bool True if exact search.
 */
geodirectory()->query_vars->is_exact_search( 'pizza' );

/**
 * Get post categories from search request.
 * @return array Array of category IDs.
 */
geodirectory()->query_vars->get_search_categories();

/**
 * Get post type from search request.
 * @return string Post type slug.
 */
geodirectory()->query_vars->get_search_post_type();

/**
 * Get sort by parameter.
 * @return string Sort by parameter.
 */
geodirectory()->query_vars->get_sort_by();
```

---

## `geodirectory()->geolocation`
**Class:** `AyeCode\GeoDirectory\Core\Geolocation`

This service handles GPS coordinates, geocoding, reverse geocoding, IP geolocation, and timezone detection.

```php
/**
 * Get location info from IP address.
 * @param string $ip IP address. Empty for current user's IP.
 * @return array Location data from IP.
 */
geodirectory()->geolocation->geo_by_ip( '' );

/**
 * Get location data from IP via ip-api.com.
 * @param string $ip IP address.
 * @return array|bool Location data or false on failure.
 */
geodirectory()->geolocation->ip_api_data( '8.8.8.8' );

/**
 * Get GPS coordinates from an address.
 * @param array|string $address Address array or string.
 * @param bool $wp_error Whether to return WP_Error on failure.
 * @return array|bool|WP_Error GPS data (lat, lng) or false/WP_Error.
 */
geodirectory()->geolocation->get_gps_from_address( '123 Main St, New York, NY', false );

/**
 * Get GPS info using Google Geocode API.
 * @param array|string $address Array of address elements or full address.
 * @param bool $wp_error Whether to return WP_Error on failure.
 * @return array|bool|WP_Error GPS data or false/WP_Error.
 */
geodirectory()->geolocation->google_get_gps_from_address( '123 Main St', false );

/**
 * Get GPS info using OpenStreetMap Nominatim API.
 * @param array|string $address Array of address elements or full address.
 * @param bool $wp_error Whether to return WP_Error on failure.
 * @return array|bool|WP_Error GPS data or false/WP_Error.
 */
geodirectory()->geolocation->osm_get_gps_from_address( '123 Main St', false );

/**
 * Get address from latitude and longitude.
 * @param string $lat Latitude.
 * @param string $lng Longitude.
 * @return string|bool Address string or false on failure.
 */
geodirectory()->geolocation->get_address_by_lat_lng( '40.7128', '-74.0060' );

/**
 * Get OpenStreetMap address from latitude and longitude.
 * @param string $lat Latitude.
 * @param string $lng Longitude.
 * @return array|bool Address data or false on failure.
 */
geodirectory()->geolocation->get_osm_address_by_lat_lng( '40.7128', '-74.0060' );

/**
 * Get timezone data from latitude and longitude.
 * @param string $latitude Latitude.
 * @param string $longitude Longitude.
 * @param int $timestamp Timestamp (0 for current time).
 * @return array|WP_Error Timezone data or WP_Error.
 */
geodirectory()->geolocation->get_timezone_by_lat_lng( '40.7128', '-74.0060', 0 );
```

---

## `geodirectory()->location_formatter`
**Class:** `AyeCode\GeoDirectory\Core\LocationFormatter`

This service handles location display formatting, URL generation, variable replacement, and address rendering.

```php
/**
 * Replace location variables in content.
 * @param string $content Content with variables like %%city%%.
 * @param array $location_array Location data array.
 * @param string|null $sep Separator.
 * @param string $gd_page Page being filtered.
 * @return string Filtered content.
 */
geodirectory()->location_formatter->replace_location_variables( 'Places in %%city%%', [], null, '' );

/**
 * Get location variable replacements.
 * @param array $location_array Location data array.
 * @param string|null $sep Separator.
 * @param string $gd_page Page being filtered.
 * @return array Array of variable => value replacements.
 */
geodirectory()->location_formatter->location_replace_vars( [], null, '' );

/**
 * Get location link based on type.
 * @param string $which_location Location link type ('current', 'base', etc).
 * @return string|bool Location URL or false.
 */
geodirectory()->location_formatter->get_location_link( 'current' );

/**
 * Check and modify location slug to ensure uniqueness.
 * @param string $slug Term slug.
 * @return string Modified slug.
 */
geodirectory()->location_formatter->location_slug_check( 'new-york' );

/**
 * Render the post address field value.
 * @param string $value Field value.
 * @param string $key Field key.
 * @param int|object $post The post.
 * @param mixed $default Default value.
 * @return string Filtered field value.
 */
geodirectory()->location_formatter->post_address( '123 Main St', 'post_address', $post, null );
```

---

## `geodirectory()->formatter`
**Class:** `AyeCode\GeoDirectory\Core\Formatter`

This service handles data sanitization, cleaning, date/time formatting, and price formatting.

```php
/**
 * Clean variables using sanitize_text_field. Arrays cleaned recursively.
 * @param string|array $var Variable to clean.
 * @return string|array Cleaned variable.
 */
geodirectory()->formatter->clean( $user_input );

/**
 * Sanitize text field in a %%variable%% safe way.
 * @param string $value String to sanitize.
 * @return string Sanitized string.
 */
geodirectory()->formatter->sanitize_text_field( $value );

/**
 * Clean slug for posts, taxonomies, and terms.
 * @param string $string String to clean.
 * @return string Cleaned slug.
 */
geodirectory()->formatter->clean_slug( 'My Post Title!' );

/**
 * Sanitize a tooltip string.
 * @param string $var Tooltip text.
 * @return string Sanitized tooltip.
 */
geodirectory()->formatter->sanitize_tooltip( 'Help text' );

/**
 * Get formatted date from date/time string.
 * @param string $date Date in 'Y-m-d H:i:s' format.
 * @return string Formatted date.
 */
geodirectory()->formatter->get_formatted_date( '2025-01-15 14:30:00' );

/**
 * Get formatted time from date/time string.
 * @param string $time Time in 'Y-m-d H:i:s' format.
 * @return string Formatted time.
 */
geodirectory()->formatter->get_formatted_time( '2025-01-15 14:30:00' );

/**
 * Get date format.
 * @return string Date format.
 */
geodirectory()->formatter->date_format();

/**
 * Get time format.
 * @return string Time format.
 */
geodirectory()->formatter->time_format();

/**
 * Get date and time format combined.
 * @param string|bool $sep Separator. Default null.
 * @return string Date time format.
 */
geodirectory()->formatter->date_time_format( ' ' );

/**
 * Transform php.ini notation for numbers (like '2M') to integer.
 * @param string $size Size string.
 * @return int Size in bytes.
 */
geodirectory()->formatter->let_to_num( '2M' );

/**
 * Get price thousand separator.
 * @return string Thousand separator.
 */
geodirectory()->formatter->get_price_thousand_separator();

/**
 * Get price decimal separator.
 * @return string Decimal separator.
 */
geodirectory()->formatter->get_price_decimal_separator();

/**
 * Get number of decimals after decimal point.
 * @return int Number of decimals.
 */
geodirectory()->formatter->get_price_decimals();

/**
 * Get rounding precision for internal calculations.
 * @return int Precision value.
 */
geodirectory()->formatter->get_rounding_precision();

/**
 * Format decimal numbers for DB storage.
 * @param float|string $number Number to format.
 * @param mixed $dp Decimal points (false to avoid rounding).
 * @param bool $trim_zeros Trim trailing zeros.
 * @return string Formatted number.
 */
geodirectory()->formatter->format_decimal( 123.456, 2, true );

/**
 * Get timezone string for the site.
 * @return string PHP timezone string.
 */
geodirectory()->formatter->timezone_string();

/**
 * Get timezone UTC offset.
 * @param string $timezone_string Timezone string.
 * @param bool $dst Include DST adjustment.
 * @return string UTC offset.
 */
geodirectory()->formatter->timezone_utc_offset( '', true );

/**
 * Get timezone offset in seconds.
 * @return float Offset in seconds.
 */
geodirectory()->formatter->timezone_offset();

/**
 * Sanitize HTML field with allowed HTML.
 * @param string $str Content to filter.
 * @param array|null $allowed_html Allowed HTML elements.
 * @return string Sanitized content.
 */
geodirectory()->formatter->sanitize_html_field( $content, null );

/**
 * Sanitize textarea field preserving newlines.
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
geodirectory()->formatter->sanitize_textarea_field( $text );

/**
 * Strip shortcodes/blocks from content.
 * @param string $content Content to sanitize.
 * @return string Sanitized content.
 */
geodirectory()->formatter->strip_shortcodes( $content );

/**
 * Sanitize a keyword.
 * @param string $keyword Keyword to sanitize.
 * @param string $extra Extra parameter.
 * @return string Sanitized keyword.
 */
geodirectory()->formatter->sanitize_keyword( 'search term', '' );

/**
 * Get character replacements for keyword sanitization.
 * @return array Replacements array.
 */
geodirectory()->formatter->keyword_replacements();

/**
 * Strip block content to shortcodes only.
 * @param string $content Content with blocks.
 * @return string Content as shortcodes.
 */
geodirectory()->formatter->blocks_to_shortcodes( $content );

/**
 * Replace entities formatted back to normal.
 * @param string $text Text to un-texturize.
 * @return string Plain text.
 */
geodirectory()->formatter->untexturize( $text );

/**
 * Replace formatted entities with plain text characters.
 * @param string $text Text to process.
 * @return string Plain text.
 */
geodirectory()->formatter->unwptexturize( $text );

/**
 * Sanitize float value.
 * @param float $number Number value.
 * @return float Sanitized number.
 */
geodirectory()->formatter->sanitize_float( 123.456 );

/**
 * Sanitize CSS class.
 * @param string $string String to sanitize.
 * @return string Sanitized class name.
 */
geodirectory()->formatter->sanitize_html_class( 'my-class' );

/**
 * Minify JavaScript.
 * @param string $script Input JavaScript.
 * @return string Minified JavaScript.
 */
geodirectory()->formatter->minify_js( $script );
```

---

## `geodirectory()->images`
**Class:** `AyeCode\GeoDirectory\Core\Images`

This service handles image operations, screenshots, thumbnails, lazy loading, and icon detection.

```php
/**
 * Make image tag lazy-load compatible.
 * @param string $img_tag Image HTML tag.
 * @param bool $lazy_load Enable lazy loading.
 * @return string Modified image tag.
 */
geodirectory()->images->image_tag_ajaxify( '<img src="...">', true );

/**
 * Get image source URL.
 * @param mixed $image Image ID, URL, or array.
 * @param string $size Image size.
 * @return string Image URL.
 */
geodirectory()->images->get_image_src( 123, 'medium' );

/**
 * Get complete image HTML tag.
 * @param mixed $image Image ID, URL, or array.
 * @param string $size Image size.
 * @param string $align Image alignment.
 * @param string $classes Additional CSS classes.
 * @return string Image HTML tag.
 */
geodirectory()->images->get_image_tag( 123, 'medium', 'left', 'custom-class' );

/**
 * Get screenshot of a URL.
 * @param string $url URL to screenshot.
 * @param array $params Screenshot parameters.
 * @return string Screenshot URL.
 */
geodirectory()->images->get_screenshot( 'https://example.com', [] );

/**
 * Get video screenshot/thumbnail.
 * @param string $field_raw Video field raw value.
 * @return string Video thumbnail URL.
 */
geodirectory()->images->get_video_screenshot( 'https://youtube.com/watch?v=...' );

/**
 * Check if string is a Font Awesome icon.
 * @param string $icon Icon string.
 * @return bool True if FA icon.
 */
geodirectory()->images->is_fa_icon( 'fas fa-home' );

/**
 * Get image dimensions.
 * @param string $image_url Image URL.
 * @param array $default Default dimensions.
 * @return array Dimensions array with 'w' and 'h'.
 */
geodirectory()->images->get_image_dimension( 'https://example.com/image.jpg', [] );

/**
 * Get field screenshot (for website fields).
 * @param array $field Field data array.
 * @param array $sizes Image sizes.
 * @param array $the_post Post data.
 * @return string Screenshot HTML.
 */
geodirectory()->images->get_field_screenshot( $field, [], [] );

/**
 * Get images for a post with fallbacks.
 * @param int $post_id Post ID (0 for current post).
 * @param string $limit Limit number of images.
 * @param bool $logo Whether to get logo image.
 * @param string $revision_id Revision ID.
 * @param array $types Image types to get.
 * @param array $fallback_types Fallback image types.
 * @param string $status Image status filter.
 * @return array Array of image objects.
 */
geodirectory()->images->get_images( $post_id, '', false, '', [], [], '' );

/**
 * Get post images from attachments table.
 * @param int $post_id Post ID.
 * @param string $limit Limit number of images.
 * @param string $revision_id Revision ID.
 * @param string $status Image status filter.
 * @return array Array of image objects.
 */
geodirectory()->images->get_post_images( $post_id, '', '', '' );

/**
 * Check if string is an icon URL (SVG or icon font).
 * @param string $icon Icon string/URL.
 * @return bool True if icon URL.
 */
geodirectory()->images->is_icon_url( 'https://example.com/icon.svg' );

/**
 * Check if post has specific image types.
 * @param array $types Image types to check.
 * @param int $post_id Post ID (0 for current post).
 * @param int $revision_id Revision ID.
 * @return array Matched image types.
 */
geodirectory()->images->post_has_image_types( [], $post_id, 0 );

/**
 * Get image size information (wrapper for getimagesize).
 * @param string $image_path Image file path.
 * @return array|false Image size array or false on failure.
 */
geodirectory()->images->getimagesize( '/path/to/image.jpg' );

/**
 * Set external image srcset for responsive images.
 * @param array $sources Srcset sources.
 * @param array $size_array Size array.
 * @param string $image_src Image source URL.
 * @param array $image_meta Image metadata.
 * @param int $attachment_id Attachment ID.
 * @return array Modified sources.
 */
geodirectory()->images->set_external_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id );
```

---

## `geodirectory()->business_hours`
**Class:** `AyeCode\GeoDirectory\Core\BusinessHours`

This service handles business hours operations, timezones, week days, and time formatting.

```php
/**
 * Get weekdays array.
 * @param bool $untranslated If day names should be untranslated.
 * @return array Weekdays array (e.g., ['Mo' => 'Monday', ...]).
 */
geodirectory()->business_hours->get_weekdays( false );

/**
 * Get short weekdays (3-letter abbreviation).
 * @param bool $untranslated If day names should be untranslated.
 * @return array Short weekdays (e.g., ['Mo' => 'Mon', ...]).
 */
geodirectory()->business_hours->get_short_weekdays( false );

/**
 * Get day short names mapping (1-7 to Mo-Su).
 * @return array Day names (e.g., ['1' => 'Mo', '2' => 'Tu', ...]).
 */
geodirectory()->business_hours->day_short_names();

/**
 * Get UTC offset without DST.
 * @param string $offset Offset string.
 * @param bool $formatted Format as +00:00.
 * @return string|float UTC offset.
 */
geodirectory()->business_hours->utc_offset( '', true );

/**
 * Get GMT offset (includes DST).
 * @param string $offset Offset string.
 * @param bool $formatted Format as +00:00.
 * @return string|float GMT offset.
 */
geodirectory()->business_hours->gmt_offset( '', true );

/**
 * Convert seconds to HH:MM format.
 * @param int $seconds Seconds value.
 * @param bool $abs Use absolute value.
 * @return string Time in HH:MM format.
 */
geodirectory()->business_hours->seconds_to_hhmm( 3600, false );

/**
 * Get UTC offset with DST for timezone.
 * @param string $time_zone Timezone string.
 * @param bool $formatted Format as +00:00.
 * @return string|int UTC offset with DST.
 */
geodirectory()->business_hours->utc_offset_dst( 'America/New_York', true );

/**
 * Get WordPress GMT offset.
 * @param bool $formatted Format as +00:00.
 * @return string|float GMT offset.
 */
geodirectory()->business_hours->wp_gmt_offset( true );

/**
 * Get default UTC offset for a timezone (without DST).
 * @param string $timezone Timezone string.
 * @return float UTC offset in hours.
 */
geodirectory()->business_hours->timezone_default_utc_offset( 'America/New_York' );

/**
 * Get default business hours values.
 * @return array Default hours (Mo-Fr 09:00-17:00).
 */
geodirectory()->business_hours->default_values();

/**
 * Convert business hours array to schema format.
 * @param array $schema_input Business hours array.
 * @return string Schema string (e.g., "Mo 09:00-17:00 Tu 09:00-17:00").
 */
geodirectory()->business_hours->array_to_schema( $hours_array );

/**
 * Convert schema format to business hours array.
 * @param string $schema Schema string.
 * @param string $country Country code.
 * @return array Business hours array.
 */
geodirectory()->business_hours->schema_to_array( 'Mo 09:00-17:00', '' );

/**
 * Parse property string from schema.
 * @param string $str Property string (e.g., "Mo-Fr 09:00-17:00").
 * @return array Parsed property array.
 */
geodirectory()->business_hours->parse_property( 'Mo-Fr 09:00-17:00' );

/**
 * Parse days string (Mo, Mo-Fr, Mo,We,Fr).
 * @param string $days_str Days string.
 * @return array Array of day codes.
 */
geodirectory()->business_hours->parse_days( 'Mo-Fr' );

/**
 * Parse days range (Mo-Fr).
 * @param string $days_str Days range string.
 * @return array Array of day codes.
 */
geodirectory()->business_hours->parse_days_range( 'Mo-Fr' );

/**
 * Parse hours string (09:00-17:00).
 * @param string $hours_str Hours string.
 * @return array Hours array.
 */
geodirectory()->business_hours->parse_hours( '09:00-17:00' );

/**
 * Parse hours range (09:00-17:00).
 * @param string $hours_str Hours range string.
 * @return array Hours range ['opens' => '09:00', 'closes' => '17:00'].
 */
geodirectory()->business_hours->parse_hours_range( '09:00-17:00' );

/**
 * Convert HH:MM to minutes since start of week.
 * @param string $hm Time in HH:MM format.
 * @param int $day_no Day number (0-6, 0 = Monday).
 * @return int Minutes since start of week.
 */
geodirectory()->business_hours->hhmm_to_bh_minutes( '09:00', 0 );

/**
 * Sanitize business hours value.
 * @param string $value Business hours value.
 * @param string $country Country code.
 * @return string Sanitized business hours.
 */
geodirectory()->business_hours->sanitize_business_hours( 'Mo 09:00-17:00', '' );

/**
 * Get input time format.
 * @param bool $jqueryui Convert to jQuery UI format.
 * @return string Time format.
 */
geodirectory()->business_hours->input_time_format( false );

/**
 * Convert offset string to minutes.
 * @param string $offset Offset (e.g., "+05:30").
 * @return int Minutes.
 */
geodirectory()->business_hours->offset_to_minutes( '+05:30' );

/**
 * Get timezone data for a timezone string.
 * @param string $tzstring Timezone string.
 * @param int|null $time Timestamp (null for current time).
 * @return array Timezone data with offset, abbreviation, etc.
 */
geodirectory()->business_hours->timezone_data( 'America/New_York', null );

/**
 * Convert UTC offset to timezone string.
 * @param float $offset Offset in hours.
 * @param string $country Country code to filter.
 * @return string Timezone string.
 */
geodirectory()->business_hours->offset_to_timezone_string( -5.0, 'US' );

/**
 * Get timezone to countries mapping.
 * @return array Timezone countries mapping.
 */
geodirectory()->business_hours->timezone_countries();

/**
 * Get parsed business hours array from schema string.
 * @param string $value Business hours schema string.
 * @param string $country Country code.
 * @return array Parsed business hours array.
 */
geodirectory()->business_hours->get_business_hours( 'Mo 09:00-17:00', '' );
```

---

## `geodirectory()->templates`
**Class:** `AyeCode\GeoDirectory\Core\Templates`

This service handles template loading, paths, and template-related utilities.

```php
/**
 * Get templates directory path.
 * @return string Templates dir path.
 */
geodirectory()->templates->get_templates_dir();

/**
 * Get templates directory URL.
 * @return string Templates dir URL.
 */
geodirectory()->templates->get_templates_url();

/**
 * Get theme template directory name.
 * @return string Theme template dir name (e.g., 'geodirectory').
 */
geodirectory()->templates->get_theme_template_dir_name();

/**
 * Convert listing view columns to CSS class.
 * @param string $columns Column configuration.
 * @return string CSS class.
 */
geodirectory()->templates->convert_listing_view_class( '3' );

/**
 * Get grid view class by view number.
 * @param int $view View number (0-5).
 * @return string Grid view class.
 */
geodirectory()->templates->grid_view_class( 3 );

/**
 * Get advanced toggle class.
 * @param string $default Default class.
 * @return string Toggle class.
 */
geodirectory()->templates->advanced_toggle_class( 'gd-advanced-setting' );

/**
 * Check if current theme is a block theme.
 * @return bool True if block theme.
 */
geodirectory()->templates->is_block_theme();

/**
 * Get template type options.
 * @return array Template types ('page', 'part').
 */
geodirectory()->templates->template_type_options();

/**
 * Get template page options.
 * @param array $args Query arguments.
 * @return array Page ID => Title array.
 */
geodirectory()->templates->template_page_options( [] );

/**
 * Get template part options.
 * @param array $args Query arguments.
 * @return array Template part slug => Title array.
 */
geodirectory()->templates->template_part_options( [] );

/**
 * Get template part by slug.
 * @param string $slug Template part slug.
 * @return object|null Template part object or null.
 */
geodirectory()->templates->get_template_part_by_slug( 'header' );

/**
 * Filter textarea output (wpautop, shortcodes).
 * @param string $text Text to filter.
 * @param string $context Context.
 * @param array $args Additional arguments.
 * @return string Filtered text.
 */
geodirectory()->templates->filter_textarea_output( $text, '', [] );

/**
 * Get A-Z search options.
 * @param string $post_type Post type.
 * @return array A-Z options (['0-9', 'A', 'B', ...]).
 */
geodirectory()->templates->az_search_options( 'gd_place' );

/**
 * Get A-Z search value from request.
 * @return string A-Z search value.
 */
geodirectory()->templates->az_search_value();

/**
 * Make embeds responsive.
 * @param string $html Embed HTML.
 * @param string $url Embed URL.
 * @param array $attr Embed attributes.
 * @param int $post_ID Post ID.
 * @return string Filtered embed HTML.
 */
geodirectory()->templates->responsive_embeds( $html, $url, [], 0 );
```

---

## `geodirectory()->helpers`
**Class:** `AyeCode\GeoDirectory\Core\Helpers`

This service provides common utility functions for strings, colors, URLs, and misc operations.

```php
/**
 * Convert string to uppercase (multibyte safe).
 * @param string $string String to convert.
 * @param string $charset Character encoding.
 * @return string Uppercase string.
 */
geodirectory()->helpers->strtoupper( 'hello', 'UTF-8' );

/**
 * Convert string to lowercase (multibyte safe).
 * @param string $string String to convert.
 * @param string $charset Character encoding.
 * @return string Lowercase string.
 */
geodirectory()->helpers->strtolower( 'HELLO', 'UTF-8' );

/**
 * Convert string to title case (multibyte safe).
 * @param string $string String to convert.
 * @param string $charset Character encoding.
 * @return string Title case string.
 */
geodirectory()->helpers->ucwords( 'hello world', 'UTF-8' );

/**
 * Get UTF-8 string length.
 * @param string $str String.
 * @param string $encoding Character encoding.
 * @return int String length.
 */
geodirectory()->helpers->utf8_strlen( 'hello', 'UTF-8' );

/**
 * Get UTF-8 substring.
 * @param string $str String.
 * @param int $start Start position.
 * @param int|null $length Length.
 * @param string $encoding Character encoding.
 * @return string Substring.
 */
geodirectory()->helpers->utf8_substr( 'hello world', 0, 5, 'UTF-8' );

/**
 * Find position of first occurrence in UTF-8 string.
 * @param string $str Haystack.
 * @param string $find Needle.
 * @param int $offset Start offset.
 * @param string $encoding Character encoding.
 * @return int|false Position or false.
 */
geodirectory()->helpers->utf8_strpos( 'hello', 'l', 0, 'UTF-8' );

/**
 * Find position of last occurrence in UTF-8 string.
 * @param string $str Haystack.
 * @param string $find Needle.
 * @param int $offset Start offset.
 * @param string $encoding Character encoding.
 * @return int|false Position or false.
 */
geodirectory()->helpers->utf8_strrpos( 'hello', 'l', 0, 'UTF-8' );

/**
 * UTF-8 first character uppercase.
 * @param string $str String.
 * @param bool $lower_str_end Lowercase rest of string.
 * @param string $encoding Character encoding.
 * @return string Modified string.
 */
geodirectory()->helpers->utf8_ucfirst( 'hello', false, 'UTF-8' );

/**
 * Get IP address.
 * @return string IP address.
 */
geodirectory()->helpers->get_ip();

/**
 * Convert hex color to RGB.
 * @param string $color Hex color.
 * @return array RGB values ['r' => int, 'g' => int, 'b' => int].
 */
geodirectory()->helpers->rgb_from_hex( '#FF5733' );

/**
 * Make hex color darker.
 * @param string $color Hex color.
 * @param int $factor Darkening factor (0-100).
 * @return string Darker hex color.
 */
geodirectory()->helpers->hex_darker( '#FF5733', 30 );

/**
 * Make hex color lighter.
 * @param string $color Hex color.
 * @param int $factor Lightening factor (0-100).
 * @return string Lighter hex color.
 */
geodirectory()->helpers->hex_lighter( '#FF5733', 30 );

/**
 * Determine if color is light or dark.
 * @param string $color Hex color.
 * @param string $dark Color to return if dark.
 * @param string $light Color to return if light.
 * @return string Dark or light color.
 */
geodirectory()->helpers->light_or_dark( '#FF5733', '#000000', '#FFFFFF' );

/**
 * Format hex color.
 * @param string $hex Hex color.
 * @return string Formatted hex color.
 */
geodirectory()->helpers->format_hex( 'F57' );

/**
 * Check if URL is a full URL.
 * @param string $url URL to check.
 * @return bool True if full URL.
 */
geodirectory()->helpers->is_full_url( 'https://example.com' );

/**
 * Check if request URI contains a string.
 * @param string $match String to match.
 * @return bool True if matches.
 */
geodirectory()->helpers->has_request_uri( '/listings/' );

/**
 * Generate random float.
 * @param float $min Minimum value.
 * @param float $max Maximum value.
 * @return float Random float.
 */
geodirectory()->helpers->random_float( 0.0, 1.0 );

/**
 * Get PHP arg separator for output.
 * @return string Arg separator.
 */
geodirectory()->helpers->get_php_arg_separator_output();

/**
 * Check if file is an image.
 * @param string $url File URL.
 * @return bool True if image.
 */
geodirectory()->helpers->is_image_file( 'image.jpg' );

/**
 * Escape CSV data.
 * @param string $data CSV data.
 * @return string Escaped data.
 */
geodirectory()->helpers->escape_csv_data( '=formula' );

/**
 * Format CSV data.
 * @param string $data CSV data.
 * @return string Formatted data.
 */
geodirectory()->helpers->format_csv_data( '<b>data</b>' );

/**
 * Remove last word from text.
 * @param string $text Text.
 * @return string Text without last word.
 */
geodirectory()->helpers->remove_last_word( 'Hello world test' );

/**
 * Set cookie.
 * @param string $name Cookie name.
 * @param string $value Cookie value.
 * @param int $expire Expiration timestamp.
 * @param bool $secure HTTPS only.
 * @param bool $httponly HTTP only.
 * @return bool True on success.
 */
geodirectory()->helpers->setcookie( 'name', 'value', time() + 3600, false, false );

/**
 * Get cookie value.
 * @param string $name Cookie name.
 * @return string Cookie value or empty string.
 */
geodirectory()->helpers->getcookie( 'name' );
```

---

## `geodirectory()->debug`
**Class:** `AyeCode\GeoDirectory\Core\Debug`

This service provides error logging, debugging utilities, and help tooltips.

```php
/**
 * Write to error log.
 * @param mixed $log Data to log.
 * @param string $title Log title.
 * @param string $file File name.
 * @param string $line Line number.
 * @param bool $exit Exit after logging.
 * @return void
 */
geodirectory()->debug->error_log( $data, 'Debug Info', __FILE__, __LINE__, false );

/**
 * Log when function is used incorrectly.
 * @param string $function Function name.
 * @param string $message Error message.
 * @param string $version Version when deprecated/changed.
 * @return void
 */
geodirectory()->debug->doing_it_wrong( 'my_function', 'You should not do this', '2.0.0' );

/**
 * Display help tip with tooltip.
 * @param string $tip Tooltip text.
 * @param bool $allow_html Allow HTML in tooltip.
 * @return string Help tip HTML.
 */
geodirectory()->debug->help_tip( 'This is helpful info', false );
```

---

## `geodirectory()->reviews`
**Class:** `AyeCode\GeoDirectory\Core\Reviews`

This service manages the business logic for reviews, linking WordPress comments to the review system.

```php
/**
 * Creates a new review record when a new comment is posted.
 * @param int $comment_id The newly created WordPress comment ID.
 * @return void
 */
geodirectory()->reviews->handle_new_comment( $comment_id );

/**
 * Updates a review record when its corresponding comment is edited.
 * @param int $comment_id The WordPress comment ID.
 * @return void
 */
geodirectory()->reviews->handle_edited_comment( $comment_id );

/**
 * Deletes a review record when its corresponding comment is deleted.
 * @param int $comment_id The WordPress comment ID.
 * @return void
 */
geodirectory()->reviews->handle_deleted_comment( $comment_id );

/**
 * Triggers a rating recalculation when a comment's status changes (e.g., 'approve', 'hold').
 * @param int $comment_id The WordPress comment ID.
 * @param string $status The new status (e.g., 'approve', 'hold').
 * @return void
 */
geodirectory()->reviews->handle_status_change( $comment_id, $new_status );

/**
 * Recalculates and updates the overall average rating and count for a post.
 * @param int $post_id The post ID.
 * @return void
 */
geodirectory()->reviews->update_overall_post_rating( $post_id );

/**
 * Checks if the current user is allowed to submit a review for a post.
 * @param int $post_id The post ID.
 * @return bool
 */
geodirectory()->reviews->can_user_submit_review( $post_id );
```

---

## `geodirectory()->seo`
**Class:** `AyeCode\GeoDirectory\Core\Seo`

This service detects and manages integrations with active SEO plugins like Yoast or Rank Math.

```php
/**
 * Gets the currently active SEO integration (e.g., Yoast, RankMath, or a default fallback).
 * @return \AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface
 */
geodirectory()->seo->get_active_integration();
```

---

## `geodirectory()->review_epository`
**Class:** `AyeCode\GeoDirectory\Database\Repository\ReviewRepository`

This service handles direct database operations for the `geodir_post_review` table.

```php
/**
 * Finds a single review row from the database by its WordPress comment ID.
 * @param int $comment_id The WordPress comment ID.
 * @return object|null The review data row, or null if not found.
 */
geodirectory()->review_epository->find( $comment_id );

/**
 * Gets just the rating value (float) for a specific comment, with caching.
 * @param int $comment_id The WordPress comment ID.
 * @return float|null The rating value, or null if not found.
 */
geodirectory()->review_epository->get_rating( $comment_id );

/**
 * Deletes a review from the custom table.
 * @param int $comment_id The WordPress comment ID.
 * @return void
 */
geodirectory()->review_epository->delete( $comment_id );

/**
 * Creates a new review record in the custom table.
 * @param array $data_array The data to insert. Keys should be column names.
 * @return int|false The number of rows inserted, or false on error.
 */
geodirectory()->review_epository->create( $data_array );

/**
 * Updates an existing review record in the custom table.
 * @param int $comment_id The WordPress comment ID.
 * @param array $data_array The data to update. Keys should be column names.
 * @return int|false The number of rows updated, or false on error.
 */
geodirectory()->review_epository->update( $comment_id, $data_array );

/**
 * Calculates the average rating for a given post from approved comments.
 * @param int $post_id The post ID.
 * @return float The average rating.
 */
geodirectory()->review_epository->get_average_rating_for_post( $post_id );

/**
 * Gets the total number of approved reviews for a post, with caching.
 * @param int $post_id The post ID.
 * @return int The total number of reviews.
 */
geodirectory()->review_epository->get_count_for_post( $post_id );

/**
 * Counts reviews for a specific post by a specific user (by ID or email).
 * @param int $post_id The post ID.
 * @param int $user_id The user ID.
 * @param string $author_email The author's email address.
 * @return int The number of reviews found.
 */
geodirectory()->review_epository->count_user_reviews_for_post( $post_id, $user_id, $author_email );
```

---

## `geodirectory()->tables`
**Class:** `AyeCode\GeoDirectory\Core\Tables`

This service provides a safe way to get the full, prefixed names of custom database tables.

```php
/**
 * Gets the full name of a "static" custom table (e.g., 'reviews', 'custom_fields').
 * @param string $key The short name of the table (e.g., 'reviews').
 * @return string|null The full table name, or null if not found.
 */
geodirectory()->tables->get( 'reviews' );

/**
 * Generates and returns the full table name for a CPT's details table.
 * @param string $post_type The post type slug (e.g., 'gd_place').
 * @return string The full, prefixed details table name.
 */
geodirectory()->tables->get_cpt_details_table( 'gd_place' );
```

---

## `geodirectory()->settings`
**Class:** `AyeCode\GeoDirectory\Core\Utils\Settings`

This service manages all plugin settings stored in the `geodir_settings` option.

```php
/**
 * Gets a specific setting value, with an optional fallback.
 * @param string $key Name of the setting to retrieve.
 * @param mixed $default Optional. Default value to return.
 * @return mixed The value of the setting.
 */
geodirectory()->settings->get( 'setting_key', 'default_value' );

/**
 * Updates a specific setting in the database.
 * @param string $key The key of the setting to update.
 * @param mixed $value The new value.
 * @return bool True if the option was updated, false otherwise.
 */
geodirectory()->settings->update( 'setting_key', 'new_value' );

/**
 * Deletes a specific setting from the database.
 * @param string $key The key of the setting to delete.
 * @return bool True if the option was updated, false otherwise.
 */
geodirectory()->settings->delete( 'setting_key' );

/**
 * Gets the entire array of all GeoDirectory settings.
 * @return array All GeoDirectory settings.
 */
geodirectory()->settings->get_all();
```

---

## `geodirectory()->utils`
**Class:** `AyeCode\GeoDirectory\Core\Utils\Utils`

This service provides general helper utilities.

```php
/**
 * Creates a random hash string.
 * @return string The random hash.
 */
geodirectory()->utils->rand_hash();

/**
 * Creates a sha256 hmac hash, used for APIs.
 * @param string $data_string The string to hash.
 * @return string
 */
geodirectory()->utils->api_hash( $data_string );
```

---

## `geodirectory()->media`
**Class:** `AyeCode\GeoDirectory\Core\Services\Media`

This service handles business logic for media attachments, particularly for the custom attachments table.

```php
/**
 * Get post type fields that are for file uploads and return allowed file types.
 * @param string $post_type The post type slug.
 * @return array Array of [ 'htmlvar_name' => [ 'allowed', 'types' ] ].
 */
geodirectory()->media->get_file_fields( 'gd_place' );

/**
 * Sideloads a file from a URL, adds it to the WP media library, and saves it to the custom attachments table.
 * @param int $post_id The post ID to attach to.
 * @param string $type The type of attachment (e.g., 'post_images').
 * @param string $url The URL of the file to sideload.
 * @return array|\WP_Error The new attachment data array, or a WP_Error on failure.
 */
geodirectory()->media->insert_from_url( $post_id, 'post_images', $image_url );

/**
 * Get the edit string for files per field (formatted for the JS uploader).
 * @param int $post_id Post ID.
 * @param string $field Field htmlvar_name.
 * @param string $revision_id Optional revision ID.
 * @param string $other_id Optional temp ID.
 * @param bool $is_export Whether this is for export.
 * @return string Formatted file string for JS uploader.
 */
geodirectory()->media->get_field_edit_string( $post_id, 'post_images', '', '', false );

/**
 * Get attachments for a specific post and field.
 * @param int $post_id Post ID.
 * @param string $mime_type Attachment type (e.g. 'post_images', 'post_video').
 * @param int $limit Limit number of results (0 for all).
 * @param string $revision_id Optional revision ID for previews/autosaves.
 * @param string $other_id Optional temp ID.
 * @param string $status Status filter (1=approved).
 * @return array Array of attachment objects.
 */
geodirectory()->media->get_attachments_by_type( $post_id, 'post_images', 0, '', '', '' );

/**
 * Deletes an attachment from the custom table and its physical file.
 * @param int $attachment_id The ID of the attachment in our custom table.
 * @return bool True on success, false on failure.
 */
geodirectory()->media->delete( $attachment_id );

/**
 * Counts all image attachments in the custom attachments table.
 * @return int Total count of image attachments.
 */
geodirectory()->media->count_image_attachments();

/**
 * Save post files during post save operation.
 * Handles file uploads, featured images, validation, and attachment management.
 * @param int $post_id The post ID.
 * @param array $gd_post The post data array from $_POST.
 * @param string $post_type The post type slug.
 * @param bool $is_dummy Whether this is a dummy/sample post.
 * @return string|false The featured image path or false.
 */
geodirectory()->media->save_post_files( $post_id, $gd_post, 'gd_place', false );
```

---

## `geodirectory()->statuses`
**Class:** `AyeCode\GeoDirectory\Core\Statuses`

This service manages custom and core post statuses for listings.

```php
/**
 * Gets the arguments needed to register custom post statuses (e.g., 'gd-closed').
 * @return array The array of status arguments for `register_post_status()`.
 */
geodirectory()->statuses->get_registration_args();

/**
 * Gets a simple array of custom status keys and their labels.
 * @param string $post_type The post type context.
 * @return array A `[ 'status_key' => 'Status Label' ]` array.
 */
geodirectory()->statuses->get_custom( $post_type );

/**
 * Gets a merged array of all WordPress and GeoDirectory statuses.
 * @param string $post_type The post type context.
 * @return array An array of all available post statuses.
 */
geodirectory()->statuses->get_all( $post_type );

/**
 * Gets the list of statuses that are considered "published" (e.g., 'publish').
 * @return array An array of status keys.
 */
geodirectory()->statuses->get_publishable();

/**
 * Gets the list of statuses that are considered "pending" (e.g., 'pending').
 * @return array An array of status keys.
 */
geodirectory()->statuses->get_pending();

/**
 * Get status list for a specific context.
 * @param string $context Context ('edit', 'view', etc.).
 * @param array $args Additional arguments.
 * @return array Array of statuses for the context.
 */
geodirectory()->statuses->get_stati_for_context( 'edit', [] );

/**
 * Get the display name for a post status.
 * @param string $status Post status key.
 * @return string Status display name.
 */
geodirectory()->statuses->get_status_name( 'gd-closed' );

/**
 * Check if a post is closed.
 * @param \WP_Post|int $post Post object or ID.
 * @return bool True if post is closed.
 */
geodirectory()->statuses->is_post_closed( $post );
```

---

## `geodirectory()->post_save_service`
**Class:** `AyeCode\GeoDirectory\Core\Services\PostSaveService`

This service orchestrates the entire post save process, handling custom fields, categories, tags, location, media, and database operations when a GeoDirectory post is saved.

```php
/**
 * Filter post data before WordPress inserts/updates it.
 * @param array $data Post data to be inserted/updated.
 * @param array $postarr Unmodified post data array.
 * @return array Modified post data.
 */
geodirectory()->post_save_service->filter_insert_post_data( $data, $postarr );

/**
 * Main handler for the save_post action.
 * @param int $post_id Post ID.
 * @param \WP_Post $post Post object.
 * @param bool $update Whether this is an update or new post.
 * @return void
 */
geodirectory()->post_save_service->handle_save_post( $post_id, $post, $update );

/**
 * Set post data to temporary storage.
 * @param array $data Post data array.
 * @return void
 */
geodirectory()->post_save_service->set_post_data( $data );

/**
 * Get post data from temporary storage.
 * @return array|null Post data or null if not set.
 */
geodirectory()->post_save_service->get_post_data();

/**
 * Clear post data from temporary storage.
 * @return void
 */
geodirectory()->post_save_service->clear_post_data();
```

---

## `geodirectory()->fields`
**Class:** `AyeCode\GeoDirectory\Fields\FieldsService`

This service provides high-level API for working with custom fields, delegating to the repository and field registry.

```php
/**
 * Get a single field by a specific column value.
 * @param string $column Column name to query by (e.g., 'id', 'htmlvar_name').
 * @param mixed $value Value to search for.
 * @param string $post_type Post type slug.
 * @param bool $stripslashes Whether to stripslashes the result. Default true.
 * @return array|false Field data array or false if not found.
 */
geodirectory()->fields->get_field_info( 'htmlvar_name', 'business_hours', 'gd_place', true );

/**
 * Get custom fields based on criteria.
 * * Replaces: geodir_post_custom_fields()
 * @param int|string $package_id Optional. The package ID.
 * @param string $default Optional. 'all', 'default', or 'custom'. Default 'all'.
 * @param string $post_type Optional. The post type slug. Default 'gd_place'.
 * @param string $fields_location Optional. Location context for show_in filtering. Default 'none'.
 * @return array Array of custom fields with normalized structure.
 */
geodirectory()->fields->get_custom_fields( '', 'all', 'gd_place', 'none' );

/**
 * Render all custom fields for a specific location/context.
 * @param int $post_id The ID of the post (if editing) or 0.
 * @param string $post_type The CPT slug (gd_place, etc).
 * @param string $location Where to render (listing_form, admin, search).
 * @param string $package_id Current package ID (for visibility checks).
 * @return void Echoes HTML.
 */
geodirectory()->fields->render_fields( $post_id, 'gd_place', 'listing', '' );
```

---

## `geodirectory()->taxonomies`
**Class:** `AyeCode\GeoDirectory\Core\Services\Taxonomies`

This service manages GeoDirectory taxonomies, including retrieval, validation, term operations, and database queries.

```php
/**
 * Get all custom taxonomies.
 * @param string $post_type The post type to filter by.
 * @param bool $include_tags Whether to include tag taxonomies. Default false.
 * @return array Array of taxonomy slugs.
 */
geodirectory()->taxonomies->get_taxonomies( 'gd_place', false );

/**
 * Get post type listing slug.
 * @param string $object_type The post type or taxonomy.
 * @return string|false Slug on success, false on failure.
 */
geodirectory()->taxonomies->get_listing_slug( 'gd_placecategory' );

/**
 * Get a taxonomy post type.
 * @param string $taxonomy The WordPress taxonomy string.
 * @return string|false Post type on success, false on failure.
 */
geodirectory()->taxonomies->get_taxonomy_posttype( 'gd_placecategory' );

/**
 * Check whether a term exists or not.
 * Returns term data on success, false on failure.
 * @param int|string $term The term ID or slug.
 * @param string $taxonomy The taxonomy name.
 * @param int $parent Parent term ID.
 * @return array|int|false Term data on success, false on failure.
 */
geodirectory()->taxonomies->term_exists( 'restaurant', 'gd_placecategory', 0 );

/**
 * Get term icon using term ID.
 * If term ID not passed, returns all icons.
 * @param int|false $term_id The term ID.
 * @param bool $rebuild Force rebuild the icons when set to true.
 * @return mixed|string|void Term icon(s).
 */
geodirectory()->taxonomies->get_term_icon( 123, false );

/**
 * Recount product terms, ignoring hidden products.
 * @param array $terms Terms array.
 * @param object $taxonomy Taxonomy object.
 * @param string $post_type Post type.
 * @param bool $callback Use standard callback.
 * @param bool $terms_are_term_taxonomy_ids Whether terms are term taxonomy IDs.
 * @return void
 */
geodirectory()->taxonomies->term_recount( $terms, $taxonomy, 'gd_place', true, true );

/**
 * Get all child terms.
 * @param int $child_of Parent term to get child terms.
 * @param string $taxonomy Taxonomy.
 * @param array $terms Array of terms. Default Empty.
 * @return array Array of child terms.
 */
geodirectory()->taxonomies->get_term_children( 123, 'gd_placecategory', [] );

/**
 * Get the term post type.
 * @param int $term_id The term id.
 * @return string Post type.
 */
geodirectory()->taxonomies->get_term_post_type( 123 );

/**
 * Check given taxonomy belongs to GD with caching.
 * @param string $taxonomy The taxonomy.
 * @return bool True if given taxonomy belongs to GD, otherwise False.
 */
geodirectory()->taxonomies->is_gd_taxonomy( 'gd_placecategory' );

/**
 * Build term link with location parameters.
 * Returns the term link with parameters.
 * @param string $termlink The term link.
 * @param object $term The term object.
 * @param string $taxonomy The taxonomy name.
 * @return string The modified term link.
 */
geodirectory()->taxonomies->build_term_link( $termlink, $term, 'gd_placecategory' );

/**
 * Get category select dropdown HTML.
 * @param string $post_type The post type.
 * @param string $selected The selected value.
 * @param bool $is_tag Is this a tag taxonomy?
 * @param bool $echo Prints the HTML when set to true.
 * @return string|void Dropdown HTML or void if echoing.
 */
geodirectory()->taxonomies->get_category_select( 'gd_place', '', false, true );

/**
 * Get category icon URL.
 * @param int $term_id Term ID.
 * @param bool $full_path Get full path.
 * @param bool $default Return default if not found.
 * @return string Category icon URL.
 */
geodirectory()->taxonomies->get_cat_icon( 123, false, false );

/**
 * Get category icon alt text.
 * @param int $term_id Category ID.
 * @param string|bool $default Default alt text. Default false.
 * @return string Icon alt text.
 */
geodirectory()->taxonomies->get_cat_icon_alt( 123, false );

/**
 * Get category default image.
 * @param int $term_id Term ID.
 * @param bool $full_path Get full path.
 * @return string Category image URL.
 */
geodirectory()->taxonomies->get_cat_image( 123, false );

/**
 * Get category top description HTML.
 * @param int $term_id Term ID.
 * @return string Top description HTML.
 */
geodirectory()->taxonomies->get_cat_top_description( 123 );

/**
 * Get category description HTML.
 * @param int $term_id Term ID.
 * @param string $type Description type ('top', 'bottom', 'main').
 * @return string Category description HTML.
 */
geodirectory()->taxonomies->get_category_description( 123, 'top' );

/**
 * Get schemas options array.
 * @return array Schemas array.
 */
geodirectory()->taxonomies->get_schemas();

/**
 * Taxonomy Walker.
 * Generates the HTML for category lists (Options, Checkboxes, or Radios).
 * @param string $taxonomy The taxonomy slug.
 * @param int $parent Parent term ID.
 * @param int $padding Visual depth/padding level.
 * @param array $args Configuration arguments:
 *   - display_type: 'select', 'multiselect', 'radio', 'checkbox'
 *   - selected: array of selected term IDs
 *   - exclude: array of term IDs to exclude
 *   - hide_empty: bool
 * @return string HTML output.
 */
geodirectory()->taxonomies->render_walker( 'gd_placecategory', 0, 0, [
	'display_type' => 'checkbox',
	'selected' => [1, 2, 3],
	'exclude' => [],
	'hide_empty' => false
] );
```

---

## `geodirectory()->maps`
**Class:** `AyeCode\GeoDirectory\Core\Utils\Maps`

This service provides helper methods for retrieving map settings and data.

```php
/**
 * Get the map JS API provider name.
 * @return string The map API provider name. ('google', 'osm', 'none')
 */
geodirectory()->maps->active_map();

/**
 * Get the marker icon size.
 * @param string $icon Marker icon URL.
 * @param array $default_size Default size array (e.g., ['w' => 36, 'h' => 45]).
 * @return array{w: int, h: int} The icon size.
 */
geodirectory()->maps->get_marker_size( $icon_url );

/**
 * Get the default marker icon URL.
 * @param bool $full_path Optional. Return full path instead of URL. Default false.
 * @return string The marker icon URL or path.
 */
geodirectory()->maps->default_marker_icon();

/**
 * Returns the default language of the map.
 * @return string Returns the default language code (e.g., 'en').
 */
geodirectory()->maps->map_language();

/**
 * Get OpenStreetMap routing language.
 * @return string Routing language.
 */
geodirectory()->maps->osm_routing_language();

/**
 * Returns the Google maps API key.
 * @param bool $query If true, format as a query string parameter ("&key=...").
 * @return string Returns the API key.
 */
geodirectory()->maps->google_api_key( $query );

/**
 * Returns the Google Geocoding API key (falls back to maps key).
 * @param bool $query If true, format as a query string parameter ("&key=...").
 * @return string Returns the Geocoding API key.
 */
geodirectory()->maps->google_geocode_api_key( $query );

/**
 * Get the map lazy load type.
 * @return string The map load type ('auto', 'click', or '').
 */
geodirectory()->maps->lazy_load_map();

/**
 * Get the footer script for map initialization.
 * @return string JavaScript for map footer.
 */
geodirectory()->maps->footer_script();

/**
 * Get the Google Maps callback function name.
 * @return string Callback function name.
 */
geodirectory()->maps->google_map_callback();
```

---

## `geodirectory()->users`
**Class:** `AyeCode\GeoDirectory\Core\Services\Users`

This service handles user-related operations including favorites, listings, and permissions.

```php
/**
 * Add a post to the user's favorites list.
 * @param int $post_id The post ID to add.
 * @param int $user_id Optional. The user ID. Defaults to current user.
 * @return bool True on success, false on failure.
 */
geodirectory()->users->add_favorite( 123, 0 );

/**
 * Remove a post from the user's favorites list.
 * @param int $post_id The post ID to remove.
 * @param int $user_id Optional. The user ID. Defaults to current user.
 * @return bool True on success, false on failure.
 */
geodirectory()->users->remove_favorite( 123, 0 );

/**
 * Get the user's favorite posts.
 * @param int $user_id Optional. The user ID. Defaults to current user.
 * @return array Array of post IDs that are favorited.
 */
geodirectory()->users->get_favorites( 0 );

/**
 * Get the favorite counts per post type for a user.
 * @param int $user_id Optional. The user ID. Defaults to current user.
 * @return array Array of post types with their favorite counts.
 */
geodirectory()->users->get_favorite_counts( 0 );

/**
 * Get the user's post listing counts.
 * @param int  $user_id     Optional. The user ID. Defaults to current user.
 * @param bool $unpublished Optional. Include unpublished posts. Default false.
 * @return array Array of post types with their listing counts.
 */
geodirectory()->users->get_listing_counts( 0, false );

/**
 * Delete a user's post.
 * @param int $post_id The post ID to delete.
 * @return bool|\WP_Error True on success, WP_Error on failure.
 */
geodirectory()->users->delete_post( 123 );

/**
 * Check if the current user has a specific capability.
 * @param string $capability Capability name.
 * @param array  $args       Optional. Further parameters. Default empty array.
 * @return bool Whether the current user has the given capability.
 */
geodirectory()->users->user_can( 'see_private_address', [ 'post' => 123 ] );
```

---

## `geodirectory()->email`
**Class:** `AyeCode\GeoDirectory\Core\Services\Email`

This service handles all email-related functionality including sending emails, template rendering, variable replacement, and email configuration. It manages transactional emails for listing submissions, publications, reviews, and more.

```php
/**
 * Get the email logo attachment HTML.
 * @param string $size Image size. Default 'full'.
 * @return string Logo HTML markup or empty string.
 */
geodirectory()->email->get_logo( 'full' );

/**
 * Get the email header text or logo.
 * @return string Header text or logo HTML.
 */
geodirectory()->email->get_header_text();

/**
 * Get the email footer text.
 * @return string Footer text.
 */
geodirectory()->email->get_footer_text();

/**
 * Render the email header.
 * @param string $email_heading Email heading text.
 * @param string $email_name    Email type identifier.
 * @param array  $email_vars    Email template variables.
 * @param bool   $plain_text    Whether this is plain text email.
 * @param bool   $sent_to_admin Whether this is sent to admin.
 * @return void
 */
geodirectory()->email->render_header( 'Welcome!', 'user_publish_post', [], false, false );

/**
 * Render the email footer.
 * @param string $email_name    Email type identifier.
 * @param array  $email_vars    Email template variables.
 * @param bool   $plain_text    Whether this is plain text email.
 * @param bool   $sent_to_admin Whether this is sent to admin.
 * @return void
 */
geodirectory()->email->render_footer( 'user_publish_post', [], false, false );

/**
 * Wrap email message with header and footer.
 * @param string $message       Email message body.
 * @param string $email_name    Email type identifier.
 * @param array  $email_vars    Email template variables.
 * @param string $email_heading Email heading text.
 * @param bool   $plain_text    Whether this is plain text email.
 * @param bool   $sent_to_admin Whether this is sent to admin.
 * @return string Wrapped email message.
 */
geodirectory()->email->wrap_message( 'Your listing is live!', 'user_publish_post', [], 'Congratulations', false, false );

/**
 * Check if an email type is enabled.
 * @param string $email_name Email type identifier.
 * @param mixed  $default    Default value if setting not found.
 * @return bool Whether email is enabled.
 */
geodirectory()->email->is_enabled( 'user_publish_post', true );

/**
 * Get email subject for a specific email type.
 * @param string $email_name Email type identifier.
 * @param array  $email_vars Email template variables.
 * @return string Email subject with variables replaced.
 */
geodirectory()->email->get_subject( 'user_publish_post', [ 'post' => $post ] );

/**
 * Get email content body for a specific email type.
 * @param string $email_name Email type identifier.
 * @param array  $email_vars Email template variables.
 * @return string Email body with variables replaced.
 */
geodirectory()->email->get_content( 'user_publish_post', [ 'post' => $post ] );

/**
 * Replace variables in email content.
 * Supports: [#blogname#], [#site_url#], [#listing_title#], [#listing_url#], [#client_name#], etc.
 * @param string $content    Content with variables.
 * @param string $email_name Email type identifier.
 * @param array  $email_vars Email template variables.
 * @return string Content with variables replaced.
 */
geodirectory()->email->replace_variables( 'Hi [#client_name#], your listing [#listing_title#] is live!', 'user_publish_post', [ 'post' => $post ] );

/**
 * Get the from name for outgoing emails.
 * @return string From name.
 */
geodirectory()->email->get_from_name();

/**
 * Get the from address for outgoing emails.
 * @return string From email address.
 */
geodirectory()->email->get_from();

/**
 * Get the site admin email address.
 * @return string Admin email address.
 */
geodirectory()->email->get_admin_email();

/**
 * Get email headers.
 * @param string $email_name  Email type identifier.
 * @param array  $email_vars  Email template variables.
 * @param string $from_email  Optional. From email override.
 * @param string $from_name   Optional. From name override.
 * @return string Email headers.
 */
geodirectory()->email->get_headers( 'user_publish_post', [ 'post' => $post ], '', '' );

/**
 * Get email content type.
 * @param string $content_type Optional. Default content type.
 * @param string $email_type   Optional. Email type override.
 * @return string Content type (text/html, text/plain, multipart/alternative).
 */
geodirectory()->email->get_content_type( 'text/html', '' );

/**
 * Get the email type from settings.
 * @return string Email type (html, plain, multipart).
 */
geodirectory()->email->get_email_type();

/**
 * Get email attachments for a specific email type.
 * @param string $email_name Email type identifier.
 * @param array  $email_vars Email template variables.
 * @return array Attachments array.
 */
geodirectory()->email->get_attachments( 'user_publish_post', [ 'post' => $post ] );

/**
 * Send an email.
 * @param string|array $to          Recipient email address(es).
 * @param string       $subject     Email subject.
 * @param string       $message     Email message body.
 * @param string       $headers     Email headers.
 * @param array        $attachments Email attachments.
 * @param string       $email_name  Email type identifier.
 * @param array        $email_vars  Email template variables.
 * @return bool Whether email was sent successfully.
 */
geodirectory()->email->send( 'user@example.com', 'Subject', 'Message', '', [], 'user_publish_post', [] );

/**
 * Style email body with inline CSS.
 * @param string $content    Email content.
 * @param string $email_name Email type identifier.
 * @param array  $email_vars Email template variables.
 * @return string Styled email content.
 */
geodirectory()->email->style_body( '<p>Content</p>', 'user_publish_post', [] );

/**
 * Check if admin BCC is active for an email type.
 * @param string $email_name Email type identifier.
 * @return bool Whether admin BCC is active.
 */
geodirectory()->email->is_admin_bcc_active( 'user_publish_post' );

/**
 * Send user publish post email.
 * @param object $post Post object.
 * @param array  $data Additional data.
 * @return bool Whether email was sent.
 */
geodirectory()->email->send_user_publish_post_email( $post, [] );
```

**Available Email Types:**
- `user_publish_post` - Sent when a listing is published
- `user_pending_post` - Sent when a listing is pending review
- `admin_pending_post` - Admin notification for pending listings
- `admin_post_edit` - Admin notification when listing is edited
- `owner_comment_submit` - Listing owner notification for new comment
- `owner_comment_approved` - Listing owner notification for approved comment
- `author_comment_approved` - Comment author notification for approval

**Email Template Variables:**
- `[#blogname#]` - Site name
- `[#site_url#]` - Site URL
- `[#site_link#]` - Site link HTML
- `[#listing_title#]` - Listing title
- `[#listing_url#]` - Listing URL
- `[#listing_link#]` - Listing link HTML
- `[#client_name#]` - User display name
- `[#post_id#]` - Post ID
- `[#comment_author#]` - Comment author name
- `[#comment_content#]` - Comment content
- `[#review_rating_star#]` - Review rating (1-5)
- And many more...

