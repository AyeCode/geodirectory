<?php
class BrowseGDPages extends GD_Test
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

    public function testBrowseGDPages()
    {
        //Browse all GD pages and catch errors and warnings
        $this->logInfo('Testing home page......');
        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing listing page......');
        $this->url(self::GDTEST_BASE_URL.'places/');
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing listing country page......');
        $this->url(self::GDTEST_BASE_URL.'places/united-states/');
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing add listing page......');
        $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing add event page......');
        $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_event');
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing detail page......');
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