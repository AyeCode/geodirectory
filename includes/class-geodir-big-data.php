<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Big_Data {


	public function __construct() {

        // create a weekly cron to clear the cache
        add_action('wp', array($this, 'schedule_weekly_cache_clear'));
        add_action('geodir_weekly_cache_clear_hook', array($this, 'clear_total_query_cache'));


        // Set priority 11 so we call it after GD
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ), 11 );

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
        delete_option('gd_found_posts_cache');
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
		if ($wp_query->is_main_query() && !is_admin() && $wp_query->query_vars['gd_is_geodir_page'] && geodir_is_page('archive') ) {

			// Generate a unique key for the query
			$tmp_qv = $wp_query->query_vars;

			// search as this can cause caches to fill up too quick
			if (empty($tmp_qv['search_orderby_title'])) {

				unset($tmp_qv['paged']); // remove pages as it will always be the same
				unset($tmp_qv['search_orderby_title']); // can have dynamic changes on every call

				$query_key = md5(serialize($tmp_qv));
				$found_posts_cache = get_option('gd_found_posts_cache', []);

				if (!isset($found_posts_cache[$query_key])) {
					// Store the found_posts and max_num_pages values after query execution
					$found_posts_cache[$query_key] = [
						'found_posts' => $wp_query->found_posts,
						'max_num_pages' => $wp_query->max_num_pages,
					];
					update_option('gd_found_posts_cache', $found_posts_cache);
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
			$tmp_qv = $wp_query->query_vars;
			unset($tmp_qv['paged']);
			$query_key = md5(serialize($tmp_qv));

			$found_posts_cache = get_option('gd_found_posts_cache', []);

			if (isset($found_posts_cache[$query_key])) {
				// If we have cached found posts, remove SQL_CALC_FOUND_ROWS
				return str_replace('SQL_CALC_FOUND_ROWS', '', $query);
			}
		}
		return $query;
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
