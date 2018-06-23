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
		return "[gd_category_description]\n[gd_search]\n[gd_loop_actions]\n[gd_loop]\n[gd_loop_paging]";
	}
	/**
	 * The content for the archive item page.
	 *
	 * @return string
	 */
	public static function page_archive_item_content(){
		return "[gd_archive_item_section type='open' position='left']
[gd_post_images type='image' ajax_load='true' link_to='post' show_logo='true' ]
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']
[gd_author_actions author_page_only='1']
[gd_post_distance]
[gd_post_rating alignment='left' ]
[gd_post_fav show='' alignment='right' ]
[gd_post_meta key='business_hours' location='listing']
[gd_output_location location='listing']
[gd_post_meta key='post_content' show='value-strip']
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



	/**
	 * The pending post user email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_user_pending_post_subject(){
		return apply_filters('geodir_email_user_pending_post_subject',__("[[#site_name#]] Your listing has been submitted for approval","geodirectory"));
	}

	/**
	 * The pending post user email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_user_pending_post_body(){
		return apply_filters('geodir_email_user_pending_post_body',
			__("Dear [#client_name#],

You submitted the below listing information. This email is just for your information.

[#listing_link#]

Thank you for your contribution.","geodirectory"
			)
		);
	}

	/**
	 * The publish post user email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_user_publish_post_subject(){
		return apply_filters('geodir_email_user_publish_post_subject',__("[[#site_name#]] Listing Published Successfully","geodirectory"));
	}

	/**
	 * The publish post user email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_user_publish_post_body(){
		return apply_filters('geodir_email_user_publish_post_body',
			__("Dear [#client_name#],

Your listing [#listing_link#] has been published. This email is just for your information.

[#listing_link#]

Thank you for your contribution.","geodirectory"
			)
		);
	}

	/**
	 * The listing owner comment submit email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_owner_comment_submit_subject(){
		return apply_filters('geodir_email_owner_comment_submit_subject',__("[[#site_name#]] A new comment has been submitted on your listing [#listing_title#]","geodirectory"));
	}

	/**
	 * The listing owner comment submit email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_owner_comment_submit_body(){
		return apply_filters('geodir_email_owner_comment_submit_body',
			__("Dear [#client_name#],

A new comment has been submitted on your listing [#listing_link#].

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Thank You.","geodirectory"
			)
		);
	}

	/**
	 * The listing owner comment approved email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_owner_comment_approved_subject(){
		return apply_filters('geodir_email_owner_comment_approved_subject',__("[[#site_name#]] A comment on your listing [#listing_title#] has been approved","geodirectory"));
	}

	/**
	 * The listing owner comment approved email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_owner_comment_approved_body(){
		return apply_filters('geodir_email_owner_comment_submit_body',
			__("Dear [#client_name#],

A new comment has been submitted on your listing [#listing_link#].

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Thank You.","geodirectory"
			)
		);
	}

	/**
	 * The commenter comment approved email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_author_comment_approved_subject(){
		return apply_filters('geodir_email_author_comment_approved_subject',__("[[#site_name#]] Your comment on listing [#listing_title#] has been approved","geodirectory"));
	}

	/**
	 * The commenter comment approved email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_author_comment_approved_body(){
		return apply_filters('geodir_email_author_comment_approved_body',
			__("Dear [#comment_author#],

Your comment on listing [#listing_link#] has been approved.

Comment: [#comment_content#]

Thank You.","geodirectory"
			)
		);
	}

	/**
	 * The pending post admin email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_pending_post_subject(){
		return apply_filters('geodir_email_admin_pending_post_subject',__("[[#site_name#]] A new listing has been submitted for review","geodirectory"));
	}

	/**
	 * The pending post admin email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_pending_post_body(){
		return apply_filters('geodir_email_admin_pending_post_body',
			__("Dear Admin,

A new listing has been submitted [#listing_link#]. This email is just for your information.

Thank you,
[#site_name_url#]","geodirectory")
		);
	}

	/**
	 * The post edited admin email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_post_edit_subject(){
		return apply_filters('geodir_email_admin_post_edit_subject',__("[[#site_name#]] Listing edited by Author","geodirectory"));
	}

	/**
	 * The post edited admin email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_post_edit_body(){
		return apply_filters('geodir_email_admin_post_edit_body',
			__("Dear Admin,

A listing [#listing_link#] has been edited by it's author [#post_author_name#].

Listing Details:
Listing ID: [#post_id#]
Listing URL: [#listing_link#]
Date: [#current_date#]

This email is just for your information.","geodirectory"
			)
		);
	}

	/**
	 * The moderate comment admin email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_moderate_comment_subject(){
		return apply_filters('geodir_email_admin_moderate_comment_subject',__("[[#site_name#]] A new comment is waiting for your approval","geodirectory"));
	}

	/**
	 * The moderate comment admin email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_moderate_comment_body(){
		return apply_filters('geodir_email_admin_moderate_comment_body',
			__("Dear Admin,

A new comment has been submitted on the listing [#listing_link#] and it is waiting for your approval.

Author: [#comment_author#] ( IP: [#comment_author_IP#] )
Email: [#comment_author_email#]
Listing: [#listing_url#]
Date: [#comment_date#]
Comment: [#comment_content#]

Approve it: [#comment_approve_link#]
Trash it: [#comment_trash_link#]
Spam it: [#comment_spam_link#]

Please visit the moderation panel for more details: [#comment_moderation_link#]

Thank You.","geodirectory"
			)
		);
	}

	/**
	 * The default email name text.
	 *
	 * @return mixed|void
	 */
	public static function email_name(){
		return get_bloginfo('name');
	}

	/**
	 * The default email address.
	 *
	 * @return mixed|void
	 */
	public static function email_address(){
		return get_bloginfo('admin_email');
	}

	/**
	 * The default CPT page title.
	 *
	 * @return string
	 */
	public static function seo_cpt_title(){
		return __("All %%pt_plural%% %%in_location_single%%","geodirectory");
	}

	/**
	 * The default CPT meta title.
	 *
	 * @return string
	 */
	public static function seo_cpt_meta_title(){
		return __("%%pt_plural%% %%in_location%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default CPT meta description.
	 *
	 * @return string
	 */
	public static function seo_cpt_meta_description(){
		return __("%%pt_plural%% %%in_location%%","geodirectory");
	}

	/**
	 * The default cat archive page title.
	 *
	 * @return string
	 */
	public static function seo_cat_archive_title(){
		return __("All %%category%% %%in_location_single%%","geodirectory");
	}

	/**
	 * The default cat archive meta title.
	 *
	 * @return string
	 */
	public static function seo_cat_archive_meta_title(){
		return __("%%category%% %%in_location%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default cat archive meta description.
	 *
	 * @return string
	 */
	public static function seo_cat_archive_meta_description(){
		return __("Posts related to Category: %%category%% %%in_location%%","geodirectory");
	}

	/**
	 * The default tag archive page title.
	 *
	 * @return string
	 */
	public static function seo_tag_archive_title(){
		return __("Tag: %%tag%% %%in_location_single%%","geodirectory");
	}

	/**
	 * The default tag archive meta title.
	 *
	 * @return string
	 */
	public static function seo_tag_archive_meta_title(){
		return __("%%tag%% %%in_location%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default tag archive meta description.
	 *
	 * @return string
	 */
	public static function seo_tag_archive_meta_description(){
		return __("Posts related to Tag: %%tag%% %%in_location%%","geodirectory");
	}

	/**
	 * The default single page title.
	 *
	 * @return string
	 */
	public static function seo_single_title(){
		return __("%%title%%","geodirectory");
	}

	/**
	 * The default single meta title.
	 *
	 * @return string
	 */
	public static function seo_single_meta_title(){
		return __("%%title%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default single meta description.
	 *
	 * @return string
	 */
	public static function seo_single_meta_description(){
		return __("%%excerpt%%","geodirectory");
	}

	/**
	 * The default location page title.
	 *
	 * @return string
	 */
	public static function seo_location_title(){
		return __("%%title%%","geodirectory");
	}

	/**
	 * The default location meta title.
	 *
	 * @return string
	 */
	public static function seo_location_meta_title(){
		return __("%%title%% %%location%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default location meta description.
	 *
	 * @return string
	 */
	public static function seo_location_meta_description(){
		return __("%%location%%","geodirectory");
	}

	/**
	 * The default search page title.
	 *
	 * @return string
	 */
	public static function seo_search_title(){
		return __("Search results for: %%search_term%% %%search_near%%","geodirectory");
	}

	/**
	 * The default search meta title.
	 *
	 * @return string
	 */
	public static function seo_search_meta_title(){
		return __("%%pt_plural%% search results for %%search_term%%, Near %%search_near%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default search meta description.
	 *
	 * @return string
	 */
	public static function seo_search_meta_description(){
		return __("%%pt_plural%% search results for %%search_term%%, Near %%search_near%%","geodirectory");
	}

	/**
	 * The default add_listing page title.
	 *
	 * @return string
	 */
	public static function seo_add_listing_title(){
		return __("Add %%pt_single%%","geodirectory");
	}

	/**
	 * The default add_listing page title when editing.
	 *
	 * @return string
	 */
	public static function seo_add_listing_title_edit(){
		return __("Edit %%pt_single%%","geodirectory");
	}

	/**
	 * The default add_listing meta title.
	 *
	 * @return string
	 */
	public static function seo_add_listing_meta_title(){
		return __("Add %%pt_single%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default add_listing meta description.
	 *
	 * @return string
	 */
	public static function seo_add_listing_meta_description(){
		return __("Add your %%pt_single%% to %%sitename%%","geodirectory");
	}

}


