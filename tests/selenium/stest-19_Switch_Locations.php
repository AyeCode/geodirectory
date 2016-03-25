<?php
class SwitchLocations extends GD_Test
{
    public function setUp()
    {
        parent::setUp();

        //skip test if already completed.
        if ($this->skipTest($this->getCurrentFileNumber(pathinfo(__FILE__, PATHINFO_FILENAME)), $this->getCompletedFileNumber())) {
            $this->markTestSkipped('Skipping '.pathinfo(__FILE__, PATHINFO_FILENAME).' since its already completed......');
            return;
        } else {
            $this->prepareSession()->currentWindow()->maximize();
        }
    }

    public function testSwitchLocations()
    {
        $this->logInfo('Switch locations......');
        //make sure multi locations plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-location-manager")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Location Manager
            $this->maybeActivatePlugin("geodirectory-location-manager", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-location-manager")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Location Manager plugin not active");


        //Make sure Show location switcher in menu checked.
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_setting');
        $this->waitForPageLoadAndCheckForErrors();
        $is_checked = $this->byId('geodir_show_changelocation_nave')->attribute('checked');
        if (!$is_checked) {
            $this->byId('geodir_show_changelocation_nave')->click();
            $this->byClassName('button-primary')->click();
            $this->waitForPageLoadAndCheckForErrors();
        }

        //Set Navigation Locations
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=design_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Navigation')->click();
        $this->ExecuteScript('jQuery("#geodir_theme_location_nav").show();');
        $this->select($this->byId('geodir_theme_location_nav'))->selectOptionByLabel('The Main Menu');
        $this->byName('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        //Add new location
        //make sure multi locations plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-location-manager")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Location Manager
            $this->maybeActivatePlugin("geodirectory-location-manager", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-location-manager")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Location Manager plugin not active");

        // Add few locations
        // Las vegas
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->prepareSession()->currentWindow()->maximize();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('Las Vegas');
        $this->byId('gd_region')->value('Nevada');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //London
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('London');
        $this->byId('gd_region')->value('Greater London');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //Glasgow
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('Glasgow');
        $this->byId('gd_region')->value('Glasgow City');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //Mexico City
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('Mexico City');
        $this->byId('gd_region')->value('Federal District');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //sydney
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('sydney');
        $this->byId('gd_region')->value('New South Wales');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //tokyo
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('tokyo');
        $this->byId('gd_region')->value('Tokyo');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");

        //Chennai
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=managelocation_fields&subtab=geodir_location_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_city')->value('Chennai');
        $this->byId('gd_region')->value('Tamil Nadu');
        $this->byId('gd_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_location_save')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Location saved successfully."), "'Location saved successfully' text not found");


        //front end switch locations
        $this->url(self::GDTEST_BASE_URL.'location/united-states/nevada/las-vegas/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Las Vegas"), "'Las Vegas' text not found");

        $this->url(self::GDTEST_BASE_URL.'location/united-states/new-york/new-york/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("New York"), "'New York' text not found");
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