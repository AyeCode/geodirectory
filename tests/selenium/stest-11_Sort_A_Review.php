<?php
class SortReview extends GD_Test
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

    public function testSortReview()
    {
        $this->logInfo('sorting reviews......');
        //make sure advance search filters plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-review-rating-manager")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Review Rating Manager
            $this->maybeActivatePlugin("geodirectory-review-rating-manager", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-review-rating-manager")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Review Rating Manager plugin not active");

        //Make sure Enable comment list sorting checked.
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=multirating_fields&subtab=geodir_multirating_options');
        $this->waitForPageLoadAndCheckForErrors();
        $is_checked = $this->byId('geodir_reviewrating_enable_sorting')->attribute('checked');
        if (!$is_checked) {
            $this->byId('geodir_reviewrating_enable_sorting')->click();
            $this->byName('save')->click();
            $this->waitForPageLoadAndCheckForErrors();
        }
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/test-listing/?comment_sorting=high_rating');
        $this->waitForPageLoadAndCheckForErrors();
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