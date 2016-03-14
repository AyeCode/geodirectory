<?php
class CheckShortcodes extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);

    }

    public function testHomeMapShortcode()
    {
        $output = do_shortcode('[gd_homepage_map width=100% height=300 scrollwheel=false]');
        $this->assertContains( 'geodir-map-home-page', $output );
    }

    public function testCptCategoriesShortcode()
    {
        $output = do_shortcode('[gd_cpt_categories show_count=1]');
        $this->assertContains( 'gd-cptcats-widget', $output );
    }

    public function testListingMapShortcode()
    {
        $output = do_shortcode('[gd_listing_map width=100% height=300 scrollwheel=false sticky=true]');
        $this->assertContains( 'geodir-map-listing-page', $output );
    }

    public function testListingSliderShortcode()
    {
        $output = do_shortcode('[gd_listing_slider post_number=5 category=3 slideshow=true show_featured_only=true]');
        $this->assertContains( 'geodir_widget_carousel', $output );
    }

    public function testLoginBoxShortcode()
    {
        $output = do_shortcode('[gd_login_box]');
        $this->assertContains( 'geodir-loginbox-list', $output );
    }

    public function texstPopPostCatShortcode()
    {
        global $geodir_post_type;
        $geodir_post_type = 'gd_place';
        $output = do_shortcode('[gd_popular_post_category category_limit=30]');
        $this->assertContains( 'geodir-popular-cat-list', $output );
    }

    public function testPopPostViewShortcode()
    {
        $output = do_shortcode('[gd_popular_post_view category=3 layout=5 add_location_filter=true character_count=0 show_featured_only=true]');
        $this->assertContains( 'geodir_category_list_view', $output );
    }

    public function testRecentReviewsShortcode()
    {
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place',
            'posts_per_page' => 1,
        );

        $all_posts = new WP_Query( $query_args );
        $post_id = null;
        while ( $all_posts->have_posts() ) : $all_posts->the_post();
            $post_id = get_the_ID();
        endwhile;

        $this->assertTrue(is_int($post_id));

        $time = current_time('mysql');

        $data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => 'admin',
            'comment_author_email' => 'admin@admin.com',
            'comment_author_url' => 'http://wpgeodirectory.com',
            'comment_content' => 'content here testtcc',
            'comment_type' => '',
            'comment_parent' => 0,
            'user_id' => 1,
            'comment_author_IP' => '127.0.0.1',
            'comment_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)',
            'comment_date' => $time,
            'comment_approved' => 1,
        );

        $comment_id = wp_insert_comment($data);

        $_REQUEST['geodir_overallrating'] = 5.0;
        geodir_save_rating($comment_id);

        $output = do_shortcode('[gd_recent_reviews count=5]');
        $this->assertContains( 'geodir_sc_recent_reviews', $output );
    }

    public function texstRelatedListingsShortcode()
    {
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place',
            'posts_per_page' => 1,
        );

        $all_posts = new WP_Query( $query_args );
        $post_id = null;
        while ( $all_posts->have_posts() ) : $all_posts->the_post();
            $post_id = get_the_ID();
            global $post;
            $post =  geodir_get_post_info($post_id);
            setup_postdata($post);
            $output = do_shortcode('[gd_related_listings relate_to=category layout=3 add_location_filter=0 list_sort=featured character_count=0]');
            $this->assertContains( 'geodir_location_listing', $output );
        endwhile;

        $this->assertTrue(is_int($post_id));

    }

    public function texstAdvSearchShortcode()
    {
        $output = do_shortcode('[gd_advanced_search]');
        $this->assertContains( 'geodir-map-home-page', $output );
    }

    public function texstListingsShortcode()
    {
        $output = do_shortcode('[gd_listings post_type="gd_place" category="1,3" post_number="10" list_sort="high_review"]');
        $this->assertContains( 'geodir-sc-gd-listings', $output );
    }

    public function texstBestOfWidgetShortcode()
    {
        $output = do_shortcode('[gd_bestof_widget title="widget title" post_type=gd_hotel post_limit=5 categ_limit=6 character_count=50 use_viewing_post_type=true add_location_filter=true tab_layout=bestof-tabs-as-dropdown]');
        $this->assertContains( 'geodir_bestof_widget', $output );
    }

    public function testAddListingShortcode()
    {
        $_REQUEST['listing_type'] = 'gd_place';
        $output = do_shortcode('[gd_add_listing listing_type=gd_place login_msg="Please register and login to submit listings" show_login=true]');
        $this->assertContains( 'geodir-add-listing-submit', $output );
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>