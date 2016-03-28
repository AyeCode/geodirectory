<?php
class SearchWithNear extends GD_Test
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

    public function testSearchWithNear()
    {
        $this->logInfo('Searching with near......');
        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();
//        $this->byClassName('snear')->value('New York');
//        $this->byClassName('geodir_submit_search')->click();
        $this->url(self::GDTEST_BASE_URL.'?geodir_search=1&stype=gd_place&s=+&snear=New+York&sgeo_lat=40.7127837&sgeo_lon=-74.00594130000002');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Search Places"), "Not in search results page");
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