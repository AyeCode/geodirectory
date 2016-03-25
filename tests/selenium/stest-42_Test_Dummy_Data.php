<?php
class TestDummyData extends GD_Test
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

    public function testTestDummyData()
    {
        $this->logInfo('Testing dummy data......');
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=general_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Dummy Data')->click();

        $html = $this->byId('sub_dummy_data_settings')->attribute('innerHTML');
        if (is_int(strpos($html, 'Yes Delete Please!'))) {
            //delete event data
        }


        //make sure event manager plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();
        $is_active = $this->byId("geodirectory-events")->attribute('class');
        $this->assertFalse( strpos($is_active, 'inactive'), "event manager plugin not active");
        if (strpos($is_active, 'inactive')) {
            return;
        }

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=general_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Event Dummy Data')->click();

        $html = $this->byId('sub_gdevent_dummy_data_settings')->attribute('innerHTML');
        if (is_int(strpos($html, 'Yes Delete Please!'))) {
            //delete event data
        }

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