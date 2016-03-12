<?php
class SearchWithKeyword extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSearchWithKeyword()
    {
        $_REQUEST['geodir_search'] = 1;
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place',
            'posts_per_page' => 1,
            's' => 'Longwood Gardens'
        );

        $all_posts = new WP_Query( $query_args );

        $total_posts = $all_posts->found_posts;

        $this->assertTrue(is_int((int) $total_posts));

        $title = null;
        while ( $all_posts->have_posts() ) : $all_posts->the_post();
            $title = get_the_title();
        endwhile;

        $this->assertEquals($title, 'Longwood Gardens');
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>