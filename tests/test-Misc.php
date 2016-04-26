<?php
class MiscTests extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testOptionDesignSettings() {

        geodir_post_type_setting_fun();
        global $geodir_settings;

        $template = geodir_plugin_path() . '/geodirectory-admin/option-pages/design_settings_array.php';
        include_once($template);

        $output = $geodir_settings['design_settings'];

        $this->assertContains('Home Top Section Settings', $output[1]['name']);
    }

    public function testOptionGeneralSettings() {

        geodir_post_type_setting_fun();
        global $geodir_settings;

        $template = geodir_plugin_path() . '/geodirectory-admin/option-pages/general_settings_array.php';
        include_once($template);

        $output = $geodir_settings['general_settings'];

        $this->assertContains('General', $output[0]['name']);
    }

    public function testOptionNotiSettings() {

        geodir_post_type_setting_fun();
        global $geodir_settings;

        $template = geodir_plugin_path() . '/geodirectory-admin/option-pages/notifications_settings_array.php';
        include_once($template);

        $output = $geodir_settings['notifications_settings'];

        $this->assertContains('Options', $output[0]['name']);
    }

    public function testOptionPermalinkSettings() {

        geodir_post_type_setting_fun();
        global $geodir_settings;

        $template = geodir_plugin_path() . '/geodirectory-admin/option-pages/permalink_settings_array.php';
        include_once($template);

        $output = $geodir_settings['permalink_settings'];

        $this->assertContains('Permalink', $output[0]['name']);
    }

    public function testOptionMetaSettings() {

        geodir_post_type_setting_fun();
        global $geodir_settings;

        $template = geodir_plugin_path() . '/geodirectory-admin/option-pages/title_meta_settings_array.php';
        include_once($template);

        $output = $geodir_settings['title_meta_settings'];

        $this->assertContains('Title / Meta', $output[0]['name']);
    }

    public function testDiagnose() {
        ob_start();
        geodir_diagnose_default_pages();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'GD Home page exists with proper setting', $output );
        $this->assertContains( 'Add Listing page exists with proper setting', $output );
        $this->assertContains( 'Listing Preview page exists with proper setting', $output );
        $this->assertContains( 'Listing Success page exists with proper setting', $output );
        $this->assertContains( 'Info page exists with proper setting', $output );
        $this->assertContains( 'Login page exists with proper setting', $output );
        $this->assertContains( 'Location page exists with proper setting', $output );

        ob_start();
        geodir_diagnose_load_db_language();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains( 'ul', $output );

    }

    public function tearDown()
    {
        parent::tearDown();
    }

}
?>