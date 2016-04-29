<?php
class AdminTests extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testAdminPanel()
    {

        $_POST = array(
            'geodir_wrapper_open_id' => '',
            'geodir_wrapper_open_class' => 'main-container clearfix',
            'geodir_wrapper_open_replace' => '',
            'geodir_wrapper_close_replace' => '',
            'geodir_wrapper_content_open_id' => '',
            'geodir_wrapper_content_open_class' => '',
            'geodir_wrapper_content_open_replace' => '<div class="main-left" ><div class="main-content  "><div class="site-content page-wrap">',
            'geodir_wrapper_content_close_replace' => '</div></div></div>',
            'geodir_article_open_id' => '',
            'geodir_article_open_class' => '',
            'geodir_article_open_replace' => '',
            'geodir_article_close_replace' => '',
            'geodir_sidebar_right_open_id' => '',
            'geodir_sidebar_right_open_class' => '',
            'geodir_sidebar_right_open_replace' => '<aside  class="sidebar" role="complementary" itemscope itemtype="[itemtype]" >',
            'geodir_sidebar_right_close_replace' => '',
            'geodir_sidebar_left_open_id' => '',
            'geodir_sidebar_left_open_class' => '',
            'geodir_sidebar_left_open_replace' => '<aside  class="secondary-sidebar" role="complementary" itemscope itemtype="[itemtype]" >',
            'geodir_sidebar_left_close_replace' => '',
            'geodir_main_content_open_id' => '',
            'geodir_main_content_open_class' => '',
            'geodir_main_content_open_replace' => '<div class="site-content page-wrap">',
            'geodir_main_content_close_replace' => '</div>',
            'geodir_top_content_add' => '',
            'geodir_before_main_content_add' => '',
            'geodir_full_page_class_filter' => 'section full-width-section',
            'geodir_before_widget_filter' => '',
            'geodir_after_widget_filter' => '',
            'geodir_before_title_filter' => '<div class="widget-title"><h2>',
            'geodir_after_title_filter' => '</h2></div>',
            'geodir_menu_li_class_filter' => '',
            'geodir_sub_menu_ul_class_filter' => '',
            'geodir_sub_menu_li_class_filter' => '',
            'geodir_menu_a_class_filter' => '',
            'geodir_sub_menu_a_class_filter' => '',
            'geodir_location_switcher_menu_li_class_filter' => '',
            'geodir_location_switcher_menu_a_class_filter' => '',
            'geodir_location_switcher_menu_sub_ul_class_filter' => '',
            'geodir_location_switcher_menu_sub_li_class_filter' => '',
            'geodir_theme_compat_css' => stripslashes('.full-width-section .geodir-search{margin:0;width:100%}.geodir_full_page .geodir-search{margin:0 auto;float:none}.geodir-search input[type=button],.geodir-search input[type=submit]{width:13%}.geodir-search input[type=text]{border:1px solid #ddd;border-radius:0;padding:0 8px}.geodir-category-list-in,.geodir-loc-bar-in{background:#f2f2f2;border-color:#dbdbdb}.geodir-category-list-in{margin-top:0}.geodir-cat-list .widget-title h2{margin:-13px -13px 13px}.widget .geodir-cat-list ul li.geodir-pcat-show a:before{display:none!important}.widget .geodir-cat-list ul li.geodir-pcat-show i{margin-right:5px}.container .geodir-search select{margin:0 3% 0 0;padding:8px 10px;width:13%}#geodir_carousel,#geodir_slider{border-radius:0;-webkit-border-radius:0;-moz-border-radius:0;margin-bottom:20px!important;border:1px solid #e1e1e1;box-shadow:none}#geodir_carousel{padding:10px}.geodir-tabs-content ol.commentlist{margin:40px 0;padding:0}li#post_mapTab{min-height:400px}#reviewsTab ol.commentlist li{border-bottom:none}#reviewsTab ol.commentlist li article.comment{border-bottom:1px solid #e1e1e1;padding-bottom:10px}.comment-content .rating{display:none}.comment-respond .gd_rating{margin-bottom:20px}div.geodir-rating{width:85px!important}.comment-respond .comment-notes{margin-bottom:10px}.average-review span,.comment-form label,.dtreviewed,.geodir-details-sidebar-user-links a,.geodir-viewall,.geodir_more_info span,.reviewer,dl.geodir-tab-head dd a{font-family:"Archivo Narrow",sans-serif}section.comment-content{margin:0 0 0 12%}#reviewsTab .comments-area .comment-content{width:auto}section.comment-content .description,section.comment-content p{margin:15px 0}dl.geodir-tab-head dd a{background:#f3f3f3;margin-top:-1px;font-size:14px;padding:0 15px}dl.geodir-tab-head dd.geodir-tab-active a{padding-bottom:1px}.geodir-widget .geodir_list_heading,.geodir-widget h3.widget-title{padding:0 15px;background:#e9e9e9;border:1px solid #dbdbdb;height:38px;line-height:38px;color:#2d2d2d}.geodir-widget .geodir_list_heading h3{background:0 0;border:none}.geodir-widget .geodir_list_heading{margin:-13px -14px 13px}.geodir-map-listing-page{border-width:1px 0 0;border-style:solid;border-color:#dbdbdb}.geodir-sidebar-wrap .geodir-company_info{margin:15px}.geodir-details-sidebar-social-sharing iframe{float:left}.geodir-details-sidebar-rating{overflow:hidden}.geodir-details-sidebar-rating .gd_rating_show,.geodir-details-sidebar-rating .geodir-rating{float:left;margin-right:15px}.geodir-details-sidebar-rating span.item{float:left;margin-top:5px}.geodir-details-sidebar-rating .average-review{top:-4px;position:relative}.geodir-details-sidebar-rating span.item img{margin-top:5px}.geodir_full_page{background:#fff;border:1px solid #e1e1e1;-webkit-box-shadow:0 1px 0 #e5e5e5;box-shadow:0 1px 0 #e5e5e5;padding:15px;margin-bottom:20px;clear:both}.geodir_map_container .main_list img{margin:0 5px}.geodir_category_list_view li.geodir-gridview .geodir-post-img .geodir_thumbnail{margin-bottom:10px}.geodir-addinfo .geodir-pinpoint,.geodir-addinfo a i{margin-right:5px}.geodir_category_list_view li.geodir-gridview h3{font-size:18px;margin-bottom:10px}#related_listingTab ul.geodir_category_list_view{padding:0!important}#reviewsTab #comments .gd_rating{margin-top:5px}.widget .geodir_category_list_view li .geodir-entry-content,.widget .geodir_category_list_view li a:before{display:none!important}.geodir_category_list_view li .geodir-entry-title{margin-bottom:10px}.widget ul.geodir_category_list_view{padding:15px}.sidebar .widget .geodir_category_list_view li{width:calc(100% - 25px)}.widget .geodir-loginbox-list li{overflow:visible!important}.widget ul.chosen-results{margin:0!important}.main_list_selecter{margin-right:5px}.geodir-viewall{float:right;width:auto!important}.widget-title h2{padding:0 15px;background:#e9e9e9;border:1px solid #dbdbdb;height:38px;line-height:38px}.widget:first-child .geodir_list_heading .widget-title{margin-top:0}.geodir_list_heading .widget-title{float:left;width:80%;margin-top:0}.geodir_list_heading .widget-title h2{padding:0 px;background:0 0;border:none;height:auto;line-height:auto}.chosen-default:before{content:none;display:none;position:absolute;margin-left:-1000000px;float:left}#geodir-wrapper .entry-crumbs{margin-bottom:20px}.geodir-search .mom-select{float:left;width:150px;margin:5px;border:1px solid #ddd;height:40px}.iprelative .gm-style .gm-style-iw{width:100%!important}'),
            'geodir_theme_compat_js' => 'jQuery(document).ready(function(e){e(".geodir_full_page").length&&""===e.trim(e(".geodir_full_page").html())&&e(".geodir_full_page").css({display:"none"})});',
            'geodir_theme_compat_default_options' => '',
            'geodir_theme_compat_code' => 'Multi_News',
            'gd_theme_compat' => 'Twenty_Fifteen_custom'
        );
        geodir_update_options_compatibility_settings();

        ob_start();
        geodir_admin_panel();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'gd-wrapper-main', $output );

    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>