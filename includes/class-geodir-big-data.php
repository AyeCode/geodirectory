<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Big_Data {

	public $cache_key = 'geodir_big_data_paging';

	public function __construct() {

        // create a weekly cron to clear the cache
        add_action('wp', array($this, 'schedule_weekly_cache_clear'));
        add_action('geodir_weekly_cache_clear_hook', array($this, 'clear_total_query_cache'));


        // Set priority 11 so we call it after GD
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 11 );

		// Location manager calls function to create all location on activation, this should not fire on activation on very large sites.
		add_filter( 'geodir_location_skip_merge_post_locations', array( $this, 'skip_merge_post_locations_on_activation' ) );


		// disable LM taxonomy location counts
		if ( defined( 'GEODIRLOCATION_VERSION' ) ) {
			add_action( 'init', array( $this, 'lm_init' ), 0 );
		}

	}

	/**
	 * Prevent Location Manger taking over the taxonomies class.
	 *
	 * @return void
	 */
	public function lm_init() {
		global $geodir_location_manager;
		remove_filter('geodir_class_taxonomies',array( $geodir_location_manager, 'extend_taxonomies'));
	}

	/**
	 * Skip post location creations on plugin activation.
	 *
	 * @param $skip
	 *
	 * @return mixed|true
	 */
	public function skip_merge_post_locations_on_activation( $skip ) {

		// skip on location manager activation
		if (!empty($_REQUEST['activate'])) {
			$skip = true;
		}

		return $skip;
	}

    /**
     * Set a weekly cron.
     *
     * @return void
     */
    public function schedule_weekly_cache_clear() {
        if (!wp_next_scheduled('geodir_weekly_cache_clear_hook')) {
            wp_schedule_event(time(), 'weekly', 'geodir_weekly_cache_clear_hook');
        }
    }

    /**
     * Delete the cache on cron run.
     *
     * @return void
     */
    public function clear_total_query_cache() {
        delete_option($this->cache_key);
    }


	/**
	 * Add some hooks called after the main GD filters.
	 *
	 * @return void
	 */
	function pre_get_posts()
	{

		// filter GD where clause
		add_filter('geodir_posts_where', array( $this, 'gd_posts_where'), 10, 2 );

		add_filter('posts_request', array( $this, 'maybe_use_cached_page_numbers' ), 10, 2);

		add_action( 'wp',  array( $this, 'maybe_get_cached_page_numbers' ));

	}

	/**
	 * If we have a cached paging numbers count then set it for the global query so paging will still work.
	 *
	 * @return void
	 */
	public function maybe_get_cached_page_numbers() {

		global $wp_query;
		if ($wp_query->is_main_query() && !is_admin() && !empty( $wp_query->query_vars['gd_is_geodir_page'] ) && geodir_is_page('archive') ) {

			// Generate a unique key for the query
			$tmp_qv = $this->prepare_qv( $wp_query->query_vars ) ;

			// search as this can cause caches to fill up too quick
			if (empty($tmp_qv['search_orderby_title']) ) {

				$query_key = md5(serialize($tmp_qv));
				$found_posts_cache = get_option($this->cache_key, []);

				if (!isset($found_posts_cache[$query_key])) {
					// Store the found_posts and max_num_pages values after query execution

					// bail if conditions not met
					if ( !isset($wp_query->found_posts) || $wp_query->found_posts < 10 ) {
						return;
					}

					$found_posts_cache[$query_key] = [
						'found_posts' => $wp_query->found_posts,
						'max_num_pages' => $wp_query->max_num_pages,
					];

					// Check if the array count exceeds 500
					if (count($found_posts_cache) > 500) {
						// Keep only the last 500 elements
						$found_posts_cache = array_slice($found_posts_cache, -500, 500, true);
					}

					update_option($this->cache_key, $found_posts_cache);
				} else {
					// if we have a cache then set it
					global $wp_query;
					$wp_query->found_posts = $found_posts_cache[$query_key]['found_posts'];
					$wp_query->max_num_pages = $found_posts_cache[$query_key]['max_num_pages'];
				}
			}
		}
	}

	/**
	 * If we have cached paging data then use it and remove SQL_CALC_FOUND_ROWS to speed up queries.
	 *
	 * @param $query
	 * @param $wp_query
	 * @return array|mixed|string|string[]
	 */
	public function maybe_use_cached_page_numbers( $query, $wp_query ) {

		// Only apply to the main query
		if ($wp_query->is_main_query()) {
			// Generate a unique key for the query (you can improve the uniqueness)
			$tmp_qv = $this->prepare_qv( $wp_query->query_vars );

			$query_key = md5(serialize($tmp_qv));

			$found_posts_cache = get_option($this->cache_key, []);

			if (isset($found_posts_cache[$query_key])) {
				// If we have cached found posts, remove SQL_CALC_FOUND_ROWS
				return str_replace('SQL_CALC_FOUND_ROWS', '', $query);
			}
		}
		return $query;
	}

	/**
	 * Prepare query args to be hashed.
	 *
	 * @param $query_vars
	 *
	 * @return mixed
	 */
	public function prepare_qv( $query_vars ) {

		unset($query_vars['paged']);
		unset($query_vars['order']);

		return $query_vars;
	}

	/**
	 * Remove the Wp default OR post_status items from query.
	 *
	 * @param $where
	 * @param $query
	 * @return array|mixed|string|string[]|null
	 */
	function gd_posts_where( $where, $query = array() ) {

		// Remove all custom post statuses except 'publish' //@todo need full testing on things like author and fav pages
		if (!is_author()) {
			$where = preg_replace("/OR wp_posts\.post_status = '[^']+'/", '', $where);
		}

		return $where;
	}




}
new GeoDir_Big_Data();
