<?php
class Favourites extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testAddFavourite()
    {
        global $current_user;

        $user_id = $current_user->ID;

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

        ob_start();
        geodir_add_to_favorite($post_id);
        $output = ob_get_clean();
        $this->assertContains( 'Remove from Favorites', $output );


        $user_fav_posts = get_user_meta($user_id, 'gd_user_favourite_post', true);

        $this->assertContains( $post_id, $user_fav_posts );


    }

    public function testRemoveFavourite()
    {
        global $current_user;

        $user_id = $current_user->ID;

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

        ob_start();
        geodir_add_to_favorite($post_id);
        $output = ob_get_clean();
        $this->assertContains( 'Remove from Favorites', $output );


        $user_fav_posts = get_user_meta($user_id, 'gd_user_favourite_post', true);

        $this->assertContains( $post_id, $user_fav_posts );

        ob_start();
        geodir_remove_from_favorite($post_id);
        $output = ob_get_clean();
        $this->assertContains( 'Add to Favorites', $output );

        $user_fav_posts = get_user_meta($user_id, 'gd_user_favourite_post', true);

        $this->assertNotContains( $post_id, $user_fav_posts );
    }


    public function tearDown()
    {
        parent::tearDown();
    }
}
?>