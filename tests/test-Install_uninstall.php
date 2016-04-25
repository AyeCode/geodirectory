<?php
class Install_Uninstall extends WP_UnitTestCase
{
	public function setUp()
	{
		parent::setUp();
		wp_set_current_user(1);
	}

	public function testGDInstall() {
		global $wpdb;
		$this->drop_tables();

		geodir_create_tables();
		geodir_register_defaults();
		geodir_create_default_fields();
		geodir_set_default_options();
		geodir_create_pages();
		geodir_set_default_widgets();
		gd_install_theme_compat();
		geodir_default_taxonomies();

	}

	public function testGDUninstall() {

	}

	function drop_tables() {
		global $wpdb, $plugin_prefix;
		//build our query to delete our custom table
		$sql = "DROP TABLE IF EXISTS " . GEODIR_COUNTRIES_TABLE . ", " . GEODIR_ICON_TABLE . ", " . GEODIR_CUSTOM_FIELDS_TABLE . ", " . $plugin_prefix . "gd_place_detail, " . GEODIR_ATTACHMENT_TABLE . ", " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . ", " . GEODIR_REVIEW_TABLE . ";";

		//execute the query deleting the table
		$wpdb->query($sql);

	}

	public function tearDown()
	{
		parent::tearDown();
	}
}
?>