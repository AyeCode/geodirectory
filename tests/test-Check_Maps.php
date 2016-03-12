<?php
class CheckMaps extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCheckHomeMap()
    {
        $output = do_shortcode('[gd_homepage_map width=100% height=300 scrollwheel=false]');
        $this->assertContains( 'geodir-map-home-page', $output );
    }

    public function testListingsPageMap()
    {
        $output = do_shortcode('[gd_listing_map width=100% height=300 scrollwheel=false sticky=true]');
        $this->assertContains( 'geodir-map-listing-page', $output );
    }

    public function testDetailPageMap()
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

            $map_args = array();
            $map_args['map_canvas_name'] = 'detail_page_map_canvas';
            $map_args['width'] = '600';
            $map_args['height'] = '300';
            if ($post->post_mapzoom) {
                $map_args['zoom'] = '' . $post->post_mapzoom . '';
            }
            $map_args['autozoom'] = false;
            $map_args['child_collapse'] = '0';
            $map_args['enable_cat_filters'] = false;
            $map_args['enable_text_search'] = false;
            $map_args['enable_post_type_filters'] = false;
            $map_args['enable_location_filters'] = false;
            $map_args['enable_jason_on_load'] = true;
            $map_args['enable_map_direction'] = true;
            $map_args['map_class_name'] = 'geodir-map-detail-page';
            $map_args['maptype'] = (!empty($post->post_mapview)) ? $post->post_mapview : 'ROADMAP';
            ob_start();
            geodir_draw_map($map_args);
            $output = ob_get_clean();
            $this->assertContains( 'geodir-map-detail-page', $output );
        endwhile;


    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>