<?php
/**
 * This file contains the service class for handling dummy data.
 *
 * @author   AyeCode
 * @category Services
 * @package  GeoDirectory\DummyData
 */

namespace AyeCode\GeoDirectory\DummyData;

/**
 * DummyDataService Class
 *
 * The core "headless" service for installing and uninstalling dummy data.
 */
class DummyDataService
{
	private $data_cache = [];

	private function get_available_data_types(string $post_type): array {
		$data = [
			'standard_places' => ['name' => __('Default', 'geodirectory'), 'count' => 30],
			'property_sale'   => ['name' => __('Property for sale', 'geodirectory'), 'count' => 10],
			'property_rent'   => ['name' => __('Property for rent', 'geodirectory'), 'count' => 10],
			'classifieds'     => ['name' => __('Classifieds', 'geodirectory'), 'count' => 20],
			'job_board'       => ['name' => __('Job Board', 'geodirectory'), 'count' => 20],
		];
		return apply_filters('geodir_dummy_data_types', $data, $post_type);
	}

	private function load_data_file(string $post_type, string $data_type): array
	{
		$cache_key = $post_type . '_' . $data_type;
		if (isset($this->data_cache[$cache_key])) {
			return $this->data_cache[$cache_key];
		}

		$available_types = $this->get_available_data_types($post_type);
		if (!array_key_exists($data_type, $available_types)) {
			return [];
		}

		$dummy_posts = $dummy_categories = $dummy_custom_fields = $dummy_sort_fields = [];
		$dummy_image_url = '';

		// --- Start of fix: Only add the filter for specific data types ---
		$types_with_filters = ['property_sale', 'property_rent', 'classifieds', 'freelancer', 'job_board'];
		if (in_array($data_type, $types_with_filters)) {
			if (has_filter('geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $data_type)) {
				remove_filter('geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $data_type, 10);
			}
			add_filter('geodir_extra_custom_fields', 'geodir_extra_custom_fields_' . $data_type, 10, 3);
		}
		// --- End of fix ---

		do_action('geodir_dummy_data_include_file', $post_type, $data_type);

		$file_path = GEODIRECTORY_PLUGIN_DIR . 'src/Admin/dummy-data/' . $data_type . '.php';

		if (file_exists($file_path)) {
			include $file_path;
		}

		$dummy_posts = apply_filters('geodir_dummy_data_posts', $dummy_posts, $data_type);
		$dummy_image_url = apply_filters('dummy_image_url', $dummy_image_url, $post_type, $data_type);

		$this->data_cache[$cache_key] = compact(
			'dummy_posts', 'dummy_categories', 'dummy_custom_fields', 'dummy_sort_fields'
		);

		return $this->data_cache[$cache_key];
	}

	public function install_setup(string $post_type, string $data_type)
	{
		global $plugin_prefix;
		geodir_add_column_if_not_exist($plugin_prefix . $post_type . "_detail", 'post_dummy', "TINYINT(1) NULL DEFAULT '0'");

		$data = $this->load_data_file($post_type, $data_type);

		if (!empty($data['dummy_custom_fields'])) foreach ($data['dummy_custom_fields'] as $field) geodir_custom_field_save($field);
		if (!empty($data['dummy_sort_fields'])) foreach ($data['dummy_sort_fields'] as $field) \GeoDir_Settings_Cpt_Sorting::save_custom_field($field);
		if (!empty($data['dummy_categories'])) $this->create_taxonomies($post_type, $data['dummy_categories']);

		geodir_update_option($post_type . '_dummy_data_type', $data_type);

		$available_types = $this->get_available_data_types($post_type);
		$total = $available_types[$data_type]['count'] ?? 0;

		return ['total_posts' => $total];
	}

	public function install_post(string $post_type, string $data_type, int $post_index, array $location_data)
	{
		$data = $this->load_data_file($post_type, $data_type);

		if (empty($data['dummy_posts']) || !isset($data['dummy_posts'][$post_index])) {
			return new \WP_Error('no_post_data', 'Dummy post data not found for this step.');
		}

		$post_info = $data['dummy_posts'][$post_index];
		if (\geodirectory()->post_types->supports($post_type, 'location')) {
			$post_info = $this->generate_dummy_address($post_info, $location_data);
		}
		$post_info['post_status'] = 'publish';
		$post_id = wp_insert_post($post_info, true);

		$available_types = $this->get_available_data_types($post_type);
		$total_posts = $available_types[$data_type]['count'] ?? 0;

		if (($post_index + 1) >= $total_posts) {
			delete_transient('cached_dummy_images');
			geodir_get_term_icon_rebuild();
			flush_rewrite_rules();
		}
		return $post_id;
	}

	public function uninstall(string $post_type): int
	{
		global $wpdb;
		$table = geodir_db_cpt_table($post_type);
		$deleted_count = 0;
		$post_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$table} WHERE post_dummy = %d", 1));
		if (!empty($post_ids)) {
			foreach ($post_ids as $post_id) {
				if (wp_delete_post((int) $post_id, true)) $deleted_count++;
			}
		}
		geodir_update_option($post_type . '_dummy_data_type', '');
		return $deleted_count;
	}

	private function create_taxonomies(string $post_type, array $categories) {
		if (empty($categories)) return;
		foreach ($categories as $slug => $category) {
			if (term_exists($category['name'], $post_type . 'category')) continue;
			$args = [];
			if (!empty($category['parent-name'])) {
				$parent = get_term_by('name', $category['parent-name'], $post_type . 'category');
				if (!empty($parent->term_id)) $args['parent'] = absint($parent->term_id);
			}
			$term = wp_insert_term($category['name'], $post_type . 'category', $args);
			if (is_wp_error($term)) {
				geodir_error_log($term->get_error_message(), 'dummy_data');
				continue;
			}
			$term_id = $term['term_id'];
			if (!empty($category['schema_type'])) update_term_meta($term_id, 'ct_cat_schema', $category['schema_type']);
			if (!empty($category['font_icon'])) update_term_meta($term_id, 'ct_cat_font_icon', $category['font_icon']);
			if (!empty($category['color'])) update_term_meta($term_id, 'ct_cat_color', $category['color']);

			if (!empty($category['icon']) || !empty($category['default_img'])) {
				if (!function_exists('wp_generate_attachment_metadata')) require_once ABSPATH . 'wp-admin/includes/image.php';
				add_filter('upload_mimes', fn($m) => array_merge($m, ['svg' => 'image/svg+xml']), 99);
				add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
					if ('.svg' === strtolower(substr($filename, -4))) {
						$data['ext'] = 'svg';
						$data['type'] = 'image/svg+xml';
					}
					return $data;
				}, 10, 4);

				$image_types = geodir_image_mime_types();
				if (!empty($category['icon'])) {
					$uploaded = (array) \GeoDir_Media::get_external_media($category['icon'], '', $image_types);
					if (empty($uploaded['error']) && !empty($uploaded['file'])) $this->attach_media_to_term($term_id, $uploaded, 'ct_cat_icon');
				}
				if (!empty($category['default_img'])) {
					$uploaded = (array) \GeoDir_Media::get_external_media($category['default_img'], '', $image_types);
					if (empty($uploaded['error']) && !empty($uploaded['file'])) $this->attach_media_to_term($term_id, $uploaded, 'ct_cat_default_img');
				}
			}
		}
	}

	private function attach_media_to_term(int $term_id, array $uploaded, string $meta_key) {
		$uploads = wp_upload_dir();
		$wp_filetype = wp_check_filetype(basename($uploaded['file']), null);
		$attachment = [
			'guid'           => $uploads['baseurl'] . '/' . basename($uploaded['file']),
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => preg_replace('/\.[^.]+$/', '', basename($uploaded['file'])),
			'post_content'   => '', 'post_status'    => 'inherit'
		];
		$attach_id = wp_insert_attachment($attachment, $uploaded['file']);
		$attach_data = wp_generate_attachment_metadata($attach_id, $uploaded['file']);
		wp_update_attachment_metadata($attach_id, $attach_data);
		if (isset($attach_data['file'])) {
			update_term_meta($term_id, $meta_key, ['id' => $attach_id, 'src' => $attach_data['file']]);
		}
	}

	private function generate_dummy_address(array $post_info, array $location_data): array {
		global $geodirectory;
		$default_location = $geodirectory->location->get_default_location();
		$lat1 = $location_data['lat1'] ?? $default_location->latitude;
		$lng1 = $location_data['lng1'] ?? $default_location->longitude;
		$lat2 = $location_data['lat2'] ?? $default_location->latitude;
		$lng2 = $location_data['lng2'] ?? $default_location->longitude;
		$dummy_lat = geodir_random_float(min($lat1, $lat2), max($lat1, $lat2));
		$dummy_lon = geodir_random_float(min($lng1, $lng2), max($lng1, $lng2));
		$post_address_parts = geodir_get_address_by_lat_lan($dummy_lat, $dummy_lon);
		$post_info['latitude'] = $dummy_lat; $post_info['longitude'] = $dummy_lon;
		$post_info['city'] = $default_location->city; $post_info['region'] = $default_location->region;
		$post_info['country'] = $default_location->country;
		if (!empty($post_address_parts)) {
			$address = $postal_code = ''; $street_parts = [];
			foreach ($post_address_parts as $part) {
				if (in_array('postal_code', $part->types)) $postal_code = $part->long_name;
				if (in_array('street_number', $part->types)) $street_parts['number'] = $part->long_name;
				if (in_array('route', $part->types)) $street_parts['route'] = $part->long_name;
			}
			if (!empty($street_parts)) $address = implode(' ', $street_parts);
			$post_info['street'] = !empty($address) ? $address : "123 " . $default_location->city;
			$post_info['zip'] = $postal_code;
		} else {
			$post_info['street'] = "123 " . $default_location->city; $post_info['zip'] = "12345";
		}
		return $post_info;
	}
}
