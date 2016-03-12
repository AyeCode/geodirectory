<?php
class SendEnquiry extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSendEnquiry()
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

        $data = array(
            'sendact' => 'send_inqury',
            'pid' => (string) $post_id,
            'inq_name' => 'Test User',
            'inq_email' => 'test@test.com',
            'inq_phone' => 'Test',
            'inq_msg' => 'Hi there, I would like to enquire about this place. I would like to ask more info about...',
            'Send' => 'Send'
        );

        add_filter('wp_redirect', '__return_false');
        geodir_send_inquiry($data);
        remove_filter('wp_redirect', '__return_false');
    }

    public function testSendToFriend()
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

        $data = array(
            'sendact' => 'email_frnd',
            'pid' => (string) $post_id,
            'yourname' => 'Test User',
            'youremail' => 'test@test.com',
            'frnd_subject' => 'Test',
            'frnd_comments' => 'Hi there, This is a comment',
            'to_email' => 'test2@test.com',
            'to_name' => 'Test User 2',
            'Send' => 'Send'
        );

        add_filter('wp_redirect', '__return_false');
        geodir_send_friend($data);
        remove_filter('wp_redirect', '__return_false');
    }

    public function texstSendToFriendFailure()
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

        $data = array(
            'sendact' => 'email_frnd',
            'pid' => (string) $post_id,
            'yourname' => 'Test User',
            'youremail' => 'test@test.com',
            'frnd_subject' => 'Test',
            'frnd_comments' => 'Hi there, This is a comment',
            'to_email' => 'Test User 2', //incorrect email
            'to_name' => 'test@test.com',
            'Send' => 'Send'
        );

        add_filter('wp_redirect', '__return_false');
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_send_friend($data);
        $output = ob_get_clean();
        $this->assertContains( 'Email from GeoDirectory failed to send', $output );
        remove_filter('wp_mail', 'print_mail');
        remove_filter('wp_redirect', '__return_false');
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>