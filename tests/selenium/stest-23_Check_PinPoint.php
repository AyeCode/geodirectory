<?php
class CheckPinpoint extends GD_Test
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

    public function testCheckPinpoint()
    {
        $this->logInfo('Checking pin point......');
        $this->url(self::GDTEST_BASE_URL.'places/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->ExecuteScript('jQuery("a.geodir-pinpoint-link").trigger("mouseover");');
        $this->waitForPageLoadAndCheckForErrors(5000);
        $this->ExecuteScript('jQuery("a.geodir-pinpoint-link").trigger("mouseout");');
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