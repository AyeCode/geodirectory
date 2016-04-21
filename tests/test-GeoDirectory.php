<?php
class GeoDirectoryTests extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testAdminSettingForms() {
        $_REQUEST['listing_type'] = 'gd_place';
        ob_start();
        geodir_admin_option_form('general_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('General Options', $output);

        ob_start();
        geodir_admin_option_form('design_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Home Top Section Settings', $output);

        ob_start();
        geodir_admin_option_form('permalink_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Listing Detail Permalink Settings', $output);

        ob_start();
        geodir_admin_option_form('title_meta_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Homepage Meta Settings', $output);

        ob_start();
        geodir_admin_option_form('notifications_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Notification Options', $output);

        ob_start();
        geodir_admin_option_form('default_location_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Set Default Location', $output);

        ob_start();
        geodir_admin_option_form('tools_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('GD Diagnostic Tools', $output);

        ob_start();
        geodir_admin_option_form('compatibility_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Theme Compatability Settings', $output);

        ob_start();
        geodir_admin_option_form('import_export');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('GD Import & Export CSV', $output);

        $_REQUEST['subtab'] = 'custom_fields';
        ob_start();
        geodir_admin_option_form('gd_place_fields_settings');
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('general-form-builder-frame', $output);

    }

    public function testPopPostWidget() {
        $args = array(
            'before_widget' => '<ul>',
            'after_widget' => '<ul>',
            'before_title' => '<ul>',
            'after_title' => '<ul>'
        );
        ob_start();
        geodir_popular_post_category_output($args);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Popular Categories', $output);
    }

    public function texstBestOfWidget() {
//        $args = array(
//            'before_widget' => '<ul>',
//            'after_widget' => '<ul>',
//            'before_title' => '<ul>',
//            'after_title' => '<ul>'
//        );
        include_once geodir_plugin_path() . "/geodirectory-widgets/geodirectory_cpt_categories_widget.php";
        $params = array(
            'title' => '',
            'post_type' => array(), // NULL for all
            'hide_empty' => '',
            'show_count' => '',
            'hide_icon' => '',
            'cpt_left' => '',
            'sort_by' => 'count',
            'max_count' => 'all',
            'max_level' => '1'
        );
        ob_start();
        geodir_cpt_categories_output($params);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('gd-cptcat-title', $output);
    }

    public function testGDWisgetListView() {
        global $gridview_columns_widget, $geodir_is_widget_listing;

        $_REQUEST['sgeo_lat'] = '40.7127837';
        $_REQUEST['sgeo_lon'] = '-74.00594130000002';
        $query_args = array(
            'posts_per_page' => 1,
            'is_geodir_loop' => true,
            'gd_location' => false,
            'post_type' => 'gd_place',
        );
        $widget_listings = geodir_get_widget_listings($query_args);
        $template = apply_filters("geodir_template_part-widget-listing-listview", geodir_locate_template('widget-listing-listview'));
        global $post, $map_jason, $map_canvas_arr;

        $current_post = $post;
        $current_map_jason = $map_jason;
        $current_map_canvas_arr = $map_canvas_arr;
        $geodir_is_widget_listing = true;

        ob_start();
        include($template);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('geodir-entry-content', $output);

        $geodir_is_widget_listing = false;

        $GLOBALS['post'] = $current_post;
        if (!empty($current_post))
            setup_postdata($current_post);
        $map_jason = $current_map_jason;
        $map_canvas_arr = $current_map_canvas_arr;

    }

    public function testRegTemplate() {
//        var_dump(geodir_login_page_id());
        wp_set_current_user(0);
        $template = geodir_plugin_path() . '/geodirectory-templates/geodir-signup.php';

        ob_start();
        include($template);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Sign In', $output);
    }

    public function testTemplates() {
        $homepage = get_page_by_title( 'GD Home page' );
        if ( $homepage )
        {
            update_option( 'page_on_front', $homepage->ID );
            update_option( 'show_on_front', 'page' );
        }

        ob_start();
        $this->go_to( home_url('/') );
        $this->load_template();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('body class="home', $output);
    }

    private function load_template() {
        do_action( 'template_redirect' );
        $template = false;
        if	 ( is_404()			&& $template = get_404_template()			) :
        elseif ( is_search()		 && $template = get_search_template()		 ) :
        elseif ( is_front_page()	 && $template = get_front_page_template()	 ) :
        elseif ( is_home()		   && $template = get_home_template()		   ) :
        elseif ( is_post_type_archive() && $template = get_post_type_archive_template() ) :
        elseif ( is_tax()			&& $template = get_taxonomy_template()	   ) :
        elseif ( is_attachment()	 && $template = get_attachment_template()	 ) :
            remove_filter('the_content', 'prepend_attachment');
        elseif ( is_single()		 && $template = get_single_template()		 ) :
        elseif ( is_page()		   && $template = get_page_template()		   ) :
        elseif ( is_category()	   && $template = get_category_template()	   ) :
        elseif ( is_tag()			&& $template = get_tag_template()			) :
        elseif ( is_author()		 && $template = get_author_template()		 ) :
        elseif ( is_date()		   && $template = get_date_template()		   ) :
        elseif ( is_archive()		&& $template = get_archive_template()		) :
        elseif ( is_comments_popup() && $template = get_comments_popup_template() ) :
        elseif ( is_paged()		  && $template = get_paged_template()		  ) :
        else :
            $template = get_index_template();
        endif;
        /**
         * Filter the path of the current template before including it.
         *
         * @since 3.0.0
         *
         * @param string $template The path of the template to include.
         */
        if ( $template = apply_filters( 'template_include', $template ) ) {
            $template_contents = file_get_contents( $template );
            $included_header = $included_footer = false;
            if ( false !== stripos( $template_contents, 'get_header();' ) ) {
                do_action( 'get_header', null );
                locate_template( 'header.php', true, false );
                $included_header = true;
            }
            include( $template );
            if ( false !== stripos( $template_contents, 'get_footer();' ) ) {
                do_action( 'get_footer', null );
                locate_template( 'footer.php', true, false );
                $included_footer = true;
            }
            if ( $included_header && $included_footer ) {
                global $wp_scripts;
                $wp_scripts->done = array();
            }
        }
        return;
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>