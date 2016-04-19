<?php
class GeoDirectoryTests extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testGDSettings() {
        geodir_set_default_options();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>