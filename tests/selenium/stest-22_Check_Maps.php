<?php
class CheckMaps extends GD_Test
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

    public function testCheckMaps()
    {
        //home map
        $this->logInfo('Checking home map......');
        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-home-page"), "Home page map not found");

        //listing map
        $this->logInfo('Checking listing map......');
        $this->url(self::GDTEST_BASE_URL.'places/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-listing-page"), "Listing page map not found");

        //detail map
        $this->logInfo('Checking detail map......');
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-detail-page"), "Detail page map not found");
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