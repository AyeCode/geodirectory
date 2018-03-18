<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory GeoDir_Defaults.
 *
 * A place to store default values used in many places.
 *
 * @class    GeoDir_Defaults
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_Defaults {


	/**
	 * The content for the add listing page.
	 *
	 * @return string
	 */
	 public static function page_add_content(){
		 return "[gd_add_listing]";
	 }

	/**
	 * The content for the search page.
	 *
	 * @return string
	 */
	public static function page_search_content(){
		return "[gd_search]\n[gd_loop_actions]\n[gd_loop]\n[gd_loop_paging]";
	}

	/**
	 * The content for the location page.
	 *
	 * @return string
	 */
	public static function page_location_content(){
		return "[gd_categories]\n[gd_map map_type='directory' width=100% height=300 search_filter=1 cat_filter=1]\n[gd_search]\n[gd_listings post_limit=10]";
	}

	/**
	 * The content for the archive page.
	 *
	 * @return string
	 */
	public static function page_archive_content(){
		return "[gd_search]\n[gd_loop_actions]\n[gd_loop]\n[gd_loop_paging]";
	}
	/**
	 * The content for the archive item page.
	 *
	 * @return string
	 */
	public static function page_archive_item_content(){
		return "[gd_archive_item_section type='open' position='left']
[gd_post_images type='slider' ajax_load='true' slideshow='false' show_title='false' animation='slide' controlnav='1' ]
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']
[gd_post_rating alignment='left' ]
[gd_post_fav show='' alignment='right' ]
[gd_post_meta key='business_hours' location='listing']
[gd_post_meta key='post_content' location='listing']
[gd_post_meta key='facebook' ]
[gd_archive_item_section type='close' position='right']";
	}

	/**
	 * The content for the details page.
	 *
	 * @return string
	 */
	public static function page_details_content(){
		return "[gd_single_closed_text]\n[gd_post_images type='slider' ajax_load='true' slideshow='true' show_title='true' animation='slide' controlnav='1' ]\n[gd_single_taxonomies]\n[gd_single_tabs]\n[gd_single_next_prev]";
	}

}


