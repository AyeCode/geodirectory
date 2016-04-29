<?php
class GDAjaxTests extends WP_Ajax_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testBestOfWidgetAjax() {
        $template = geodir_plugin_path() . '/geodirectory-widgets/geodirectory_bestof_widget.php';
        include_once($template);

        $time = current_time('mysql');

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
            'comment_post_ID' => $post_id,
            'comment_author' => 'admin',
            'comment_author_email' => 'admin@admin.com',
            'comment_author_url' => 'http://wpgeodirectory.com',
            'comment_content' => 'content here',
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

        $this->assertTrue(is_int($comment_id));

        //ajax function test
        $ajax_nonce = wp_create_nonce("geodir-bestof-nonce");

        $terms = get_terms( array(
            'taxonomy' => 'gd_placecategory',
            'hide_empty' => false,
        ) );
        $term_id = $terms[0]->term_id;

        $_POST['post_type'] = 'gd_place';
        $_POST['post_limit'] = '5';
        $_POST['char_count'] = '20';
        $_POST['taxonomy'] = 'gd_placecategory';
        $_POST['add_location_filter'] = '1';
        $_POST['term_id'] = (string) $term_id;
        $_POST['excerpt_type'] = 'show-reviews';
        $_POST['geodir_bestof_nonce'] = $ajax_nonce;

        $this->_handleAjax('geodir_bestof');
        $this->assertContains("bestof-cat-title", $this->_last_response);


    }

    public function texstImportExport() {
        $nonce = wp_create_nonce( 'geodir_import_export_nonce' );

        $_POST['_nonce'] = $nonce;
        $_POST['_pt'] = 'gd_place';
        $_POST['task'] = 'export_posts';
        $_POST['_n'] = 5000;
        $_POST['_p'] = 1;

        $this->_handleAjax('geodir_import_export');
        try {
            $this->assertContains("bestof-cat-title", $this->_last_response);
        } catch (WPAjaxDieStopException $e) {
            var_dump("WPAjaxDieStopException: " . $e );
        } catch (WPAjaxDieContinueException $e) {
            var_dump("WPAjaxDieContinueException: " . $e );
        }

    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>