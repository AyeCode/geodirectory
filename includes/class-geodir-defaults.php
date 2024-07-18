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
	 public static function page_add_content($no_filter = false, $blocks = false){

		 if($blocks){
			 $content = "<!-- wp:geodirectory/geodir-widget-notifications {\"content\":\"\",\"sd_shortcode\":\"[gd_notifications]\",\"className\":\"wp-block-geodirectory-geodir-widget-notifications\"} /-->

<!-- wp:geodirectory/geodir-widget-add-listing {\"content\":\"\",\"sd_shortcode\":\"[gd_add_listing post_type=''  show_login='true'  login_msg=''  container=''  mapzoom='0'  label_type=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-add-listing\"></div>
<!-- /wp:geodirectory/geodir-widget-add-listing -->";
		 }else{
			 $content = "[gd_notifications]\n[gd_add_listing show_login=1]";
		 }

		 if($no_filter){
			 return $content;
		 }else{
			 return apply_filters("geodir_default_page_add_content", $content, $blocks);
		 }
	 }

	/**
	 * The content for the search page.
	 *
	 * @return string
	 */
	public static function page_search_content($no_filter = false, $blocks = false){

		if($blocks){
			$content = "<!-- wp:geodirectory/geodir-widget-notifications {\"content\":\"\",\"sd_shortcode\":\"[gd_notifications]\",\"className\":\"wp-block-geodirectory-geodir-widget-notifications\"} /-->

<!-- wp:geodirectory/geodir-widget-search {\"content\":\"\",\"sd_shortcode\":\"[gd_search hide_search_input='false'  hide_near_input='false'  input_size=''  bar_flex_wrap=''  bar_flex_wrap_md=''  bar_flex_wrap_lg=''  input_border=''  input_border_opacity=''  input_rounded_size=''  btn_bg=''  btn_rounded_size=''  btn_rounded_size_md=''  btn_rounded_size_lg=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  rounded_size_md=''  rounded_size_lg=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-search\"></div>
<!-- /wp:geodirectory/geodir-widget-search -->

<!-- wp:geodirectory/geodir-widget-loop-actions {\"content\":\"\",\"sd_shortcode\":\"[gd_loop_actions hide_layouts=''  btn_size=''  btn_style=''  btn_bg=''  btn_border=''  text_color=''  text_align=''  text_align_md=''  text_align_lg=''  bg=''  mt=''  mr=''  mb='3'  ml=''  mt_md=''  mr_md=''  mb_md=''  ml_md=''  mt_lg=''  mr_lg=''  mb_lg=''  ml_lg=''  pt=''  pr=''  pb=''  pl=''  pt_md=''  pr_md=''  pb_md=''  pl_md=''  pt_lg=''  pr_lg=''  pb_lg=''  pl_lg=''  border=''  border_type=''  border_width=''  border_opacity=''  rounded=''  rounded_size=''  shadow=''  display=''  display_md=''  display_lg=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop-actions\"></div>
<!-- /wp:geodirectory/geodir-widget-loop-actions -->

<!-- wp:geodirectory/geodir-widget-loop {\"content\":\"\",\"sd_shortcode\":\"[gd_loop layout='2'  row_gap=''  column_gap=''  card_border=''  card_shadow=''  bg=''  mt=''  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  template_type=''  tmpl_page='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop\"></div>
<!-- /wp:geodirectory/geodir-widget-loop -->

<!-- wp:geodirectory/geodir-widget-loop-paging {\"content\":\"\",\"sd_shortcode\":\"[gd_loop_paging show_advanced=''  mid_size=''  mid_size_sm=''  paging_style=''  size=''  size_sm=''  ap_text_color=''  ap_font_size=''  ap_pt=''  ap_pr=''  ap_pb=''  ap_pl=''  bg=''  mt=''  mr=''  mb='3'  ml=''  mt_md=''  mr_md=''  mb_md=''  ml_md=''  mt_lg=''  mr_lg=''  mb_lg=''  ml_lg=''  pt=''  pr=''  pb=''  pl=''  pt_md=''  pr_md=''  pb_md=''  pl_md=''  pt_lg=''  pr_lg=''  pb_lg=''  pl_lg=''  border=''  border_type=''  border_width=''  border_opacity=''  rounded=''  rounded_size=''  shadow=''  display=''  display_md=''  display_lg=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop-paging\"></div>
<!-- /wp:geodirectory/geodir-widget-loop-paging -->";
		}else{
			$content = "[gd_notifications]\n[gd_search]\n[gd_loop_actions]\n[gd_loop layout=2]\n[gd_loop_paging]";
		}

		if($no_filter){
			return $content;
		}else{
			return apply_filters("geodir_default_page_search_content",$content, $blocks);
		}
	}

	/**
	 * The content for the location page.
	 *
	 * @return string
	 */
	public static function page_location_content($no_filter = false, $blocks = false){
		if($blocks){
			$content = "<!-- wp:geodirectory/geodir-widget-notifications {\"content\":\"\",\"sd_shortcode\":\"[gd_notifications]\",\"className\":\"wp-block-geodirectory-geodir-widget-notifications\"} /-->

<!-- wp:geodirectory/geodir-widget-categories {\"hide_empty\":true,\"design_type\":\"icon-top\",\"icon_size\":\"box-medium\",\"content\":\"\",\"sd_shortcode\":\"[gd_categories title=''  widget_title_tag=''  widget_title_size_class=''  widget_title_align_class=''  widget_title_color_class=''  widget_title_border_class=''  widget_title_border_color_class=''  widget_title_mt_class=''  widget_title_mr_class=''  widget_title_mb_class=''  widget_title_ml_class=''  widget_title_pt_class=''  widget_title_pr_class=''  widget_title_pb_class=''  widget_title_pl_class=''  post_type='0'  cpt_ajax='false'  filter_ids=''  hide_empty='true'  max_level='1'  max_count='all'  max_count_child='all'  no_cpt_filter='false'  no_cat_filter='false'  sort_by='count'  design_type='icon-top'  row_items=''  row_positioning=''  card_padding_inside=''  card_color=''  card_shadow=''  hide_icon='false'  use_image='false'  image_size='medium'  icon_color=''  icon_size='box-medium'  cat_text_color=''  cat_text_color_custom=''  cat_font_size=''  cat_font_size_custom=''  cat_font_weight=''  cat_font_case=''  hide_count='false'  badge_position=''  badge_color='light'  badge_text_append='light'  badge_text_color=''  badge_text_color_custom=''  badge_font_size=''  badge_font_size_custom=''  badge_font_weight=''  badge_font_case=''  cpt_title='false'  title_tag='h4'  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-categories\"></div>
<!-- /wp:geodirectory/geodir-widget-categories -->

<!-- wp:geodirectory/geodir-widget-map {\"show_advanced\":true,\"map_type\":\"directory\",\"search_filter\":true,\"post_type_filter\":true,\"cat_filter\":true,\"child_collapse\":true,\"height\":\"300px\",\"mb\":\"4\",\"content\":\"\",\"sd_shortcode\":\"[gd_map title=''  map_type='directory'  post_settings='true'  post_type=''  terms=''  tick_terms=''  tags=''  all_posts='false'  post_id=''  search_filter='true'  post_type_filter='true'  cat_filter='true'  child_collapse='true'  map_directions='false'  scrollwheel='false'  hide_zoom_control='false'  hide_street_control='false'  sticky='false'  static='false'  width='100%'  height='300px'  maptype='ROADMAP'  zoom='0'  bg=''  mt=''  mr=''  mb='4'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-map\"></div>
<!-- /wp:geodirectory/geodir-widget-map -->

<!-- wp:geodirectory/geodir-widget-search {\"content\":\"\",\"sd_shortcode\":\"[gd_search hide_search_input='false'  hide_near_input='false'  input_size=''  bar_flex_wrap=''  bar_flex_wrap_md=''  bar_flex_wrap_lg=''  input_border=''  input_border_opacity=''  input_rounded_size=''  btn_bg=''  btn_rounded_size=''  btn_rounded_size_md=''  btn_rounded_size_lg=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  rounded_size_md=''  rounded_size_lg=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-search\"></div>
<!-- /wp:geodirectory/geodir-widget-search -->

<!-- wp:geodirectory/geodir-widget-listings {\"post_limit\":6,\"content\":\"\",\"sd_shortcode\":\"[gd_listings title=''  widget_title_tag=''  widget_title_size_class=''  widget_title_align_class=''  widget_title_color_class=''  widget_title_border_class=''  widget_title_border_color_class=''  widget_title_mt_class=''  widget_title_mr_class=''  widget_title_mb_class=''  widget_title_ml_class=''  widget_title_pt_class=''  widget_title_pr_class=''  widget_title_pb_class=''  widget_title_pl_class=''  hide_if_empty='false'  post_type='gd_place'  category=''  related_to=''  tags=''  post_author=''  post_limit='6'  post_ids=''  add_location_filter='true'  nearby_gps='false'  show_featured_only='false'  show_special_only='false'  with_pics_only='false'  with_videos_only='false'  show_favorites_only='false'  favorites_by_user=''  use_viewing_post_type='false'  use_viewing_term='false'  sort_by=''  title_tag='h3'  layout='2'  view_all_link='true'  with_pagination='false'  top_pagination='false'  bottom_pagination='true'  pagination_info=''  template_type=''  tmpl_page=''  row_gap=''  column_gap=''  card_border=''  card_shadow=''  with_carousel=''  with_indicators=''  indicators_mb=''  with_controls=''  slide_interval='5'  slide_ride=''  center_slide=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-listings\"></div>
<!-- /wp:geodirectory/geodir-widget-listings -->";
		}else{
			$content = "[gd_notifications]\n[gd_categories]\n[gd_map map_type='directory' width=100% height=300 search_filter=1 cat_filter=1 post_type_filter=1]\n[gd_search]\n[gd_listings post_limit=10 add_location_filter='1']";
		}

		if($no_filter){
			return $content;
		}else{
			return apply_filters("geodir_default_page_location_content",$content, $blocks);
		}
	}

	/**
	 * The content for the archive page.
	 *
	 * @return string
	 */
	public static function page_archive_content($no_filter = false, $blocks = false){
		if($blocks){
			$content = "<!-- wp:geodirectory/geodir-widget-notifications {\"content\":\"\",\"sd_shortcode\":\"[gd_notifications]\",\"className\":\"wp-block-geodirectory-geodir-widget-notifications\"} /-->

<!-- wp:geodirectory/geodir-widget-search {\"content\":\"\",\"sd_shortcode\":\"[gd_search hide_search_input='false'  hide_near_input='false'  input_size=''  bar_flex_wrap=''  bar_flex_wrap_md=''  bar_flex_wrap_lg=''  input_border=''  input_border_opacity=''  input_rounded_size=''  btn_bg=''  btn_rounded_size=''  btn_rounded_size_md=''  btn_rounded_size_lg=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  rounded_size_md=''  rounded_size_lg=''  shadow=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-search\"></div>
<!-- /wp:geodirectory/geodir-widget-search -->

<!-- wp:geodirectory/geodir-widget-loop-actions {\"content\":\"\",\"sd_shortcode\":\"[gd_loop_actions hide_layouts=''  btn_size=''  btn_style=''  btn_bg=''  btn_border=''  text_color=''  text_align=''  text_align_md=''  text_align_lg=''  bg=''  mt=''  mr=''  mb='3'  ml=''  mt_md=''  mr_md=''  mb_md=''  ml_md=''  mt_lg=''  mr_lg=''  mb_lg=''  ml_lg=''  pt=''  pr=''  pb=''  pl=''  pt_md=''  pr_md=''  pb_md=''  pl_md=''  pt_lg=''  pr_lg=''  pb_lg=''  pl_lg=''  border=''  border_type=''  border_width=''  border_opacity=''  rounded=''  rounded_size=''  shadow=''  display=''  display_md=''  display_lg=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop-actions\"></div>
<!-- /wp:geodirectory/geodir-widget-loop-actions -->

<!-- wp:geodirectory/geodir-widget-loop {\"content\":\"\",\"sd_shortcode\":\"[gd_loop layout='2'  row_gap=''  column_gap=''  card_border=''  card_shadow=''  bg=''  mt=''  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  template_type=''  tmpl_page='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop\"></div>
<!-- /wp:geodirectory/geodir-widget-loop -->

<!-- wp:geodirectory/geodir-widget-loop-paging {\"content\":\"\",\"sd_shortcode\":\"[gd_loop_paging show_advanced=''  mid_size=''  mid_size_sm=''  paging_style=''  size=''  size_sm=''  ap_text_color=''  ap_font_size=''  ap_pt=''  ap_pr=''  ap_pb=''  ap_pl=''  bg=''  mt=''  mr=''  mb='3'  ml=''  mt_md=''  mr_md=''  mb_md=''  ml_md=''  mt_lg=''  mr_lg=''  mb_lg=''  ml_lg=''  pt=''  pr=''  pb=''  pl=''  pt_md=''  pr_md=''  pb_md=''  pl_md=''  pt_lg=''  pr_lg=''  pb_lg=''  pl_lg=''  border=''  border_type=''  border_width=''  border_opacity=''  rounded=''  rounded_size=''  shadow=''  display=''  display_md=''  display_lg=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-loop-paging\"></div>
<!-- /wp:geodirectory/geodir-widget-loop-paging -->";
		}else{
			$content = "[gd_notifications]\n[gd_category_description]\n[gd_search]\n[gd_loop_actions]\n[gd_loop layout=2]\n[gd_loop_paging]";
		}

		if($no_filter){
			return $content;
		}else{
			return apply_filters("geodir_default_page_archive_content",$content,$blocks);
		}
	}
	/**
	 * The content for the archive item page.
	 *
	 * @return string
	 */
	public static function page_archive_item_content($no_filter = false, $blocks = false){

		if($blocks){
			$content = "<!-- wp:geodirectory/geodir-widget-simple-archive-item {\"content\":\"\",\"sd_shortcode\":\"[gd_simple_archive_item preview_type='grid'  card_wrap='grid'  card_border=''  card_shadow=''  image_type='image'  image_link='post'  top_left_badge_preset='featured'  top_left_badge_key=''  top_left_badge_condition='is_equal'  top_left_badge_search=''  top_left_badge_icon_class=''  top_left_badge_badge=''  top_left_badge_link=''  top_left_badge_type=''  top_left_badge_color=''  top_left_badge_bg_color='#0073aa'  top_left_badge_txt_color='#ffffff'  top_right_badge_preset='new'  top_right_badge_key=''  top_right_badge_condition='is_equal'  top_right_badge_search=''  top_right_badge_icon_class=''  top_right_badge_badge=''  top_right_badge_link=''  top_right_badge_type=''  top_right_badge_color=''  top_right_badge_bg_color='#0073aa'  top_right_badge_txt_color='#ffffff'  bottom_left_badge_preset='category'  bottom_left_badge_key=''  bottom_left_badge_condition='is_equal'  bottom_left_badge_search=''  bottom_left_badge_icon_class=''  bottom_left_badge_badge=''  bottom_left_badge_link=''  bottom_left_badge_type=''  bottom_left_badge_color=''  bottom_left_badge_bg_color='#0073aa'  bottom_left_badge_txt_color='#ffffff'  bottom_right_badge_preset='favorite'  bottom_right_badge_key=''  bottom_right_badge_condition='is_equal'  bottom_right_badge_search=''  bottom_right_badge_icon_class=''  bottom_right_badge_badge=''  bottom_right_badge_link=''  bottom_right_badge_type=''  bottom_right_badge_color=''  bottom_right_badge_bg_color='#0073aa'  bottom_right_badge_txt_color='#ffffff'  body_bg_color=''  body_pt=''  body_pr=''  body_pb=''  body_pl=''  circle_image_type='author'  circle_image_align='center'  title_font_size=''  title_text_align=''  title_text_color=''  title_pt=''  title_pb=''  limit='20'  read_more='0'  desc_text_color=''  desc_text_align='justify'  desc_pt=''  desc_pb='1'  list_style='none'  item_py='1'  list_text_align=''  list_pt=''  list_pb=''  footer_items='2'  footer_item_1='rating'  footer_item_1_show=''  footer_item_2='business_hours'  footer_item_2_show=''  footer_item_3='business_hours'  footer_item_3_show=''  footer_item_4='business_hours'  footer_item_4_show=''  footer_item_5='business_hours'  footer_item_5_show=''  footer_bg_color=''  footer_border=''  footer_font_size=''  footer_pt=''  footer_pr=''  footer_pb=''  footer_pl='' ]\"} /-->";
		}else{
			$content = "[gd_archive_item_section type='open' position='left']
[gd_post_badge key='featured' condition='is_not_empty' badge='FEATURED' bg_color='#fd4700' txt_color='#ffffff' css_class='gd-ab-top-left-angle gd-badge-shadow']
[gd_post_images type='image' ajax_load='true' link_to='post' types='logo,post_images']
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']
[gd_post_badge key='post_date' condition='is_less_than' search='+30' icon_class='fas fa-certificate' badge='New' bg_color='#ff0000' txt_color='#ffffff' alignment='left']
[gd_post_badge key='featured' condition='is_not_empty' icon_class='fas fa-certificate' badge='Featured' bg_color='#ffb100' txt_color='#ffffff' alignment='left']
[gd_post_badge key='claimed' condition='is_not_empty' search='+30' icon_class='fas fa-user-check fa-fw' badge='Verified' bg_color='#23c526' txt_color='#ffffff' alignment='left' list_hide_secondary='3']
[gd_post_badge key='facebook' condition='is_not_empty' icon_class='fab fa-facebook-f fa-fw' link='%%input%%' new_window='1' bg_color='#2b4be8' txt_color='#ffffff' alignment='left']
[gd_post_badge key='twitter' condition='is_not_empty' icon_class='fab fa-twitter fa-fw' link='%%input%%' new_window='1' bg_color='#2bb8e8' txt_color='#ffffff' alignment='left']
[gd_post_badge key='website' condition='is_not_empty' icon_class='fas fa-link fa-fw' link='%%input%%' new_window='1' bg_color='#85a9b5' txt_color='#ffffff' alignment='left']
[gd_author_actions author_page_only='1']
[gd_post_distance]
[gd_post_rating alignment='left' list_hide_secondary='2']
[gd_post_fav show='' alignment='right' list_hide_secondary='2']
[gd_post_meta key='business_hours' location='listing' list_hide_secondary='2']
[gd_output_location location='listing']
[gd_post_content key='post_content' limit='60' max_height='120']
[gd_archive_item_section type='close' position='right']";
		}


		if($no_filter){
			return $content;
		}else{
			return apply_filters("geodir_default_page_archive_item_content",$content, $blocks);
		}
	}

	/**
	 * The content for the details page.
	 *
	 * @return string
	 */
	public static function page_details_content($no_filter = false,$blocks = false){
		if($blocks){
			$content = "<!-- wp:geodirectory/geodir-widget-notifications {\"content\":\"\",\"sd_shortcode\":\"[gd_notifications]\",\"className\":\"wp-block-geodirectory-geodir-widget-notifications\"} /-->

<!-- wp:geodirectory/geodir-widget-post-images {\"show_advanced\":true,\"type\":\"slider\",\"show_caption\":true,\"content\":\"\",\"sd_shortcode\":\"[gd_post_images title=''  id=''  types=''  fallback_types=''  ajax_load='true'  limit=''  limit_show=''  css_class=''  type='slider'  slideshow='true'  controlnav='1'  animation='slide'  show_title='true'  show_caption='true'  image_size=''  aspect=''  cover=''  link_to=''  link_screenshot_to=''  bg=''  mt=''  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-post-images\"></div>
<!-- /wp:geodirectory/geodir-widget-post-images -->

<!-- wp:geodirectory/geodir-widget-single-taxonomies {\"content\":\"\",\"sd_shortcode\":\"[gd_single_taxonomies taxonomy=''  prefix=''  link_style=''  link_color=''  link_color_custom=''  link_icon='false'  mt=''  mr=''  mb='2'  ml=''  pt=''  pr=''  pb=''  pl='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-single-taxonomies\"></div>
<!-- /wp:geodirectory/geodir-widget-single-taxonomies -->

<!-- wp:geodirectory/geodir-widget-single-tabs {\"content\":\"\",\"sd_shortcode\":\"[gd_single_tabs show_as_list='false'  output=''  tab_style=''  disable_greedy='false'  remove_separator_line='false'  hide_icon='false'  heading_tag=''  heading_font_size=''  heading_text_color=''  heading_text_color_custom=''  heading_font_weight=''  lists_mb=''  mt=''  mr=''  mb=''  ml=''  mt_md=''  mr_md=''  mb_md=''  ml_md=''  mt_lg='3'  mr_lg=''  mb_lg=''  ml_lg=''  pt=''  pr=''  pb=''  pl=''  pt_md=''  pr_md=''  pb_md=''  pl_md=''  pt_lg=''  pr_lg=''  pb_lg=''  pl_lg=''  border=''  border_type=''  border_width=''  border_opacity=''  rounded=''  rounded_size=''  shadow=''  display=''  display_md=''  display_lg=''  css_class='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-single-tabs\"></div>
<!-- /wp:geodirectory/geodir-widget-single-tabs -->

<!-- wp:geodirectory/geodir-widget-single-next-prev {\"mt\":\"4\",\"content\":\"\",\"sd_shortcode\":\"[gd_single_next_prev bg=''  mt='4'  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-single-next-prev\"></div>
<!-- /wp:geodirectory/geodir-widget-single-next-prev -->";
		}else{
			$content = "[gd_notifications]\n[gd_post_images type='slider' ajax_load='true' slideshow='true' show_title='true' animation='slide' controlnav='1' ]\n[gd_single_taxonomies]\n[gd_single_tabs]\n[gd_single_next_prev]";
		}


		if($no_filter){
			return $content;
		}else{
			return apply_filters("geodir_default_page_details_content",$content,$blocks);
		}
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

A listing [#listing_link#] has been edited by its author [#post_author_name#].

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
	 * The report post admin email subject default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_report_post_subject(){
		return apply_filters( 'geodir_email_admin_report_post_subject', __( "[[#site_name#]] Someone has reported a post!", "geodirectory" ) );
	}

	/**
	 * The report post admin email body default.
	 *
	 * @return mixed|void
	 */
	public static function email_admin_report_post_body(){
		return apply_filters('geodir_email_admin_report_post_body',
			__("Dear Admin,

Someone has reported a post [#listing_link#].

Details:
Post: [#listing_title#] (Post ID: [#post_id#])
Post Url: [#listing_url#]
Reporter Name: [#report_post_user_name#] (User ID: [#report_post_user_id#])
Reporter Email: [#report_post_user_email#]
Reporter IP: [#report_post_user_ip#]
Date: [#report_post_date#]
Reason: [#report_post_reason#]
Message: [#report_post_message#]

---
Please visit the report post section for more details: [#report_post_section_link#]

Thank You.", "geodirectory" )
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
		return __("%%pt_plural%% %%in_location%% %%page%% %%sep%% %%sitename%%","geodirectory");
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
		return __("%%category%% %%in_location%% %%page%% %%sep%% %%sitename%%","geodirectory");
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
		return __("%%tag%% %%in_location%% %%page%% %%sep%% %%sitename%%","geodirectory");
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
		return __("%%location_single%%","geodirectory");
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
		return __("%%pt_plural%% search results for %%search_term%% %%search_near%% %%sep%% %%sitename%%","geodirectory");
	}

	/**
	 * The default search meta description.
	 *
	 * @return string
	 */
	public static function seo_search_meta_description(){
		return __("%%pt_plural%% search results for %%search_term%% %%search_near%%","geodirectory");
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


