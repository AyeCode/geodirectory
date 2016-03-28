<?php
class SearchWithKeyword extends GD_Test
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

    public function testSearchWithKeyword()
    {
        $this->logInfo('Searching with keyword......');
        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();
//        $this->byClassName('search_text')->value('Test');
//        $this->byClassName('geodir_submit_search')->click();
        $this->url(self::GDTEST_BASE_URL.'?geodir_search=1&stype=gd_place&s=test&snear=&sgeo_lat=&sgeo_lon=');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Search Places For"), "Not in search results page");
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