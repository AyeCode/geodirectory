<?php
class Initialize extends GD_Test
{
    public function setUp()
    {
        parent::setUp();

        //skip test if already completed.
        if ($this->skipTest($this->getCurrentFileNumber(pathinfo(__FILE__, PATHINFO_FILENAME)), $this->getCompletedFileNumber())) {
            $this->markTestSkipped('Skipping '.pathinfo(__FILE__, PATHINFO_FILENAME).' since its already completed......');
        } else {
            $this->prepareSession()->currentWindow()->maximize();
        }

    }

    public function testInitialize()
    {
        // Check plugins available
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
//        echo $this->source();
        $this->waitForPageLoadAndCheckForErrors();

        $stop_script = false;

        $plugins = array(
            'geodirectory',
            'geodirectory-advance-search-filters',
            'geodirectory-affiliatewp-integration',
            'geodirectory-ajax-duplicate-alert',
            'geodirectory-buddypress-integration',
            'geodirectory-claim-manager',
            'geodirectory-custom-post-types',
            'geodirectory-events',
            'gd-booster',
            'geodirectory-location-manager',
            'geodirectory-marker-cluster',
            'geodirectory-payment-manager',
            'geodirectory-re-captcha',
            'geodirectory-review-rating-manager',
            'geodirectory-social-importer',
            'stripe-payment-geodirectory-add-on',
            'buddypress',
//            'wordpress-database-reset'
        );

        foreach ($plugins as $plugin) {
            if (!$this->isElementExists($plugin)) {
                $plugin_name = ucwords(str_replace('-', ' ', $plugin));
                $this->logError($plugin_name.' not installed');
                $stop_script = true;
            }
        }

        if($stop_script) {
            $this->logError("Stopping the script. Please fix the errors to continue");
            return;
        }


        //Activate WordPress database reset
        //$this->maybeActivatePlugin("wordpress-database-reset");

        //reset the db
//        $this->logInfo('Resetting WordPress database......');
//        $this->url(self::GDTEST_BASE_URL.'wp-admin/tools.php?page=database-reset');
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byId('select-all')->click();
//        $this->waitForPageLoadAndCheckForErrors(5000);
//        $this->byId('db-reset-reactivate-theme-data')->click();
//        $code = $this->byId('security-code')->text();
//        $this->byId('db-reset-code-confirm')->value($code);
//        $this->waitForPageLoadAndCheckForErrors(5000);
//        $this->byId('db-reset-submit')->click();
//        $this->acceptAlert();
//        $this->waitForPageLoadAndCheckForErrors();

        // make sure all plugins not active. We will activate it programatically.
        $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->hideAdminBar();

//        if(($key = array_search('wordpress-database-reset', $plugins)) !== false) {
//            unset($plugins[$key]);
//        }

        foreach ($plugins as $plugin) {
            if (!is_int(strpos($this->byId($plugin)->attribute('class'), 'inactive'))) {
                $plugin_name = ucwords(str_replace('-', ' ', $plugin));
                $this->logError($plugin_name." is active. Please deactivate it. It will be activated programatically.");
                $stop_script = true;
            }
        }


        if($stop_script) {
            $this->logInfo("Stopping the script. Please fix the errors to continue");
            return;
        }

        //make sure GDF theme installed
        $this->url(self::GDTEST_BASE_URL.'wp-admin/themes.php');
        $this->waitForPageLoadAndCheckForErrors();
        if (!$this->isElementExists("GeoDirectory_framework-name")) {
            $this->logError("GeoDirectory Framework theme not installed");
            $stop_script = true;
        }

        if (!$this->isElementExists("gdf_test_child-name")) {
            $this->logError("GDF child theme not installed");
            $stop_script = true;
        }

        if($stop_script) {
            $this->logError("Stopping the script. Please fix the errors to continue");
            return;
        }

        //Activate GDF theme if not active
        $this->logInfo('Checking GDF theme......');
        $is_active = $this->byXPath("//div[contains(@class, 'theme') and contains(@class, 'active')]")->attribute('aria-describedby');
        if (strpos($is_active, 'gdf_test_child-name')) {
            //GDF already active
        } else {
            //Activate GDF
            $this->logInfo('Activating GDF theme......');
            $this->byXPath("//div[contains(@aria-describedby, 'gdf_test_child-action') and contains(@aria-describedby, 'gdf_test_child-name')]//div[@class='theme-actions']//a[contains(@class, 'activate')]")->click();
            $this->waitForPageLoadAndCheckForErrors();
            $this->assertTrue( $this->isTextPresent("New theme activated"), "'New theme activated' text not found");
        }

        //Activate Geodirectory core
        $this->maybeActivatePlugin("geodirectory", 20000);

        //set default location
        $this->logInfo('Setting default location......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=default_location_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('city')->value('New York');
        $this->byId('set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Your settings have been saved"), "'Your settings have been saved' text not found");
        $this->waitForPageLoadAndCheckForErrors();

        //install place dummy data
        $this->logInfo('Installing place dummy data......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory');
        $this->waitForPageLoadAndCheckForErrors();
        //$this->byLinkText('Dummy Data')->click();
        $this->byXPath("//dd[@id='dummy_data_settings']/a")->click();
        $html = $this->byId('sub_dummy_data_settings')->attribute('innerHTML');
        if (strpos($html, 'Yes Delete Please!')) {
            //delete old place data
            $this->byXPath("//div[@id='sub_dummy_data_settings']//a[@id='geodir_dummy_delete']")->click();
            $this->acceptAlert();
            $this->waitForPageLoadAndCheckForErrors();
        }
        $this->select($this->byXPath("//div[@id='sub_dummy_data_settings']//select[@class='selected_sample_data']"))->selectOptionByLabel('10');
        $this->byXPath("//div[@id='sub_dummy_data_settings']//a[@id='geodir_dummy_insert']")->click();
        $this->waitForPageLoadAndCheckForErrors(60000);

        //make sure dummy data installed
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//dd[@id='dummy_data_settings']/a")->click();
        $html = $this->byId('sub_dummy_data_settings')->attribute('innerHTML');
        $this->assertTrue( is_int(strpos($html, 'Yes Delete Please!')), "Places Demo data not installed correctly");


        //Activate Geodirectory Events
        $this->maybeActivatePlugin("geodirectory-events", 20000);

        //install Events dummy data
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//dd[@id='gdevent_dummy_data_settings']/a")->click();
        $html = $this->byId('sub_gdevent_dummy_data_settings')->attribute('innerHTML');
        if (strpos($html, 'Yes Delete Please!')) {
            //delete old data
            $this->byXPath("//div[@id='sub_gdevent_dummy_data_settings']//a[@id='geodir_dummy_delete']")->click();
            $this->acceptAlert();
            $this->waitForPageLoadAndCheckForErrors();
        }
        $this->select($this->byXPath("//div[@id='sub_gdevent_dummy_data_settings']//select[@class='selected_sample_data']"))->selectOptionByLabel('10');
        $this->byXPath("//div[@id='sub_gdevent_dummy_data_settings']//a[@id='geodir_dummy_insert']")->click();
        $this->waitForPageLoadAndCheckForErrors(60000);

        //make sure Events dummy data installed
        $this->logInfo('Checking events dummy data installed properly or not......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=general_settings&active_tab=gdevent_dummy_data_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//dd[@id='gdevent_dummy_data_settings']/a")->click();
        $html = $this->byId('sub_gdevent_dummy_data_settings')->attribute('innerHTML');
        $this->assertTrue( is_int(strpos($html, 'Yes Delete Please!')), "Events Demo data not installed correctly");

        //set home page
        $this->logInfo('Setting home page......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/options-reading.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//input[@value='page']")->click();
        $this->select($this->byId("page_on_front"))->selectOptionByLabel('GD Home page');
        $this->byId("submit")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Settings saved"), "'Settings saved' text not found");

        //Enable registration
        $this->logInfo('Enabling registration......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/options-general.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId("users_can_register")->click();
        $this->byId("submit")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Settings saved"), "'Settings saved' text not found");

        //Permalinks
        $this->logInfo('Setting permalinks......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/options-permalink.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("(//input[@name='selection'])[5]")->click();
        $this->byId("submit")->click();
        $this->waitForPageLoadAndCheckForErrors();
//        echo $this->source();
        $this->assertTrue( $this->isTextPresent("Permalink structure updated"), "'Permalink structure updated' text not found");

        //create and assign menu
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/nav-menus.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Give your menu a name above, then click Create Menu."), "'Create Menu' text not found");
        $this->logInfo('Creating new menu......');
        $this->byId('menu-name')->value('Primary');
        $this->byId('save_menu_header')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $this->url(self::GDTEST_BASE_URL.'wp-admin/nav-menus.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Add menu items from the column on the left"), "'Add menu items from the column on the left' text not found");
        $this->logInfo('Setting menu location......');
        $this->byId('locations-main-nav')->click();
        $this->byId('save_menu_header')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("<strong>Primary</strong> has been updated"), "'Primary has been updated' text not found");

        // Assign menu
        $this->logInfo('Assigning menu......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=design_settings&active_tab=geodir_navigation_settings');
        $this->ExecuteScript('jQuery("#geodir_theme_location_nav").show();');
        $this->select($this->byId('geodir_theme_location_nav'))->selectOptionByLabel('The Main Menu');
        $this->byName('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        //Logout
        $this->maybeAdminLogout();
    }

    public function tearDown()
    {
        if (!$this->skipTest($this->getCurrentFileNumber(pathinfo(__FILE__, PATHINFO_FILENAME)), $this->getCompletedFileNumber())) {
            //write current file number to completed.txt
            $CurrentFileNumber = $this->getCurrentFileNumber(pathinfo(__FILE__, PATHINFO_FILENAME));
            $completed = fopen("tests/selenium/completed.txt", "w") or die("Unable to open file!");
            fwrite($completed, $CurrentFileNumber);
        }
    }

}
?>