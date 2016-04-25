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

    public function tearDown()
    {
        parent::tearDown();
    }

}
?>