<?php
class GoogleAnalytics extends GD_Test
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

    public function testGoogleAnalytics()
    {
        $this->logInfo('Testing google analytics......');
        //make sure Google Analytics Authorized
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=general_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Google Analytics')->click();

        $value = $this->byId('geodir_ga_id')->value();
        if (empty($value)) {
            echo "Google Analytics not configured";
        }

        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
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