<?php
class SearchNearMe extends GD_Test
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

    public function testSearchNearMe()
    {
        $this->logInfo('Test search near me......');
        $this->logInfo('Skipping since user need to share the location manually......');
//        $this->logInfo('Test search near me......');
//        //make sure event manager plugin active
//        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
//        $this->waitForPageLoadAndCheckForErrors();
//
//        $is_active = $this->byId("geodirectory-advance-search-filters")->attribute('class');
//        if (is_int(strpos($is_active, 'inactive'))) {
//            //Activate Geodirectory Advance Search Filters
//            $this->maybeActivatePlugin("geodirectory-advance-search-filters", 20000);
//            //go back to plugin page
//            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
//        }
//
//        $is_active1 = $this->byId("geodirectory-advance-search-filters")->attribute('class');
//        $this->assertFalse( strpos($is_active1, 'inactive'), "Advance Search Filters plugin not active");
//
//        $this->url(self::GDTEST_BASE_URL);
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->ExecuteScript("jQuery('.near-compass').trigger('click');");
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byClassName('gt_near_me_s')->click();
//        $this->acceptAlert();
//        $this->byClassName('geodir_submit_search')->click();
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->assertTrue( $this->isTextPresent("Search Places For"), "Not in search results page");

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