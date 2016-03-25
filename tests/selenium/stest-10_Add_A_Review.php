<?php
class AddReview extends GD_Test
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

    public function testAddReview()
    {
        $this->logInfo('Adding a review......');
        $this->maybeUserLogin(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/', true);
        $this->waitForPageLoadAndCheckForErrors();
        $this->prepareSession()->currentWindow()->maximize();
        $this->ExecuteScript('jQuery(".geodir-tab-head").css("overflow", "hidden");');
        try {
            $this->byXPath("//a[text()='Reviews']")->click();
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            if (PHPUnit_Extensions_Selenium2TestCase_WebDriverException::ElementNotVisible == $e->getCode()) {
                $this->ExecuteScript('jQuery("#geodir-tab-mobile-menu").click();');
                $this->byXPath("//a[text()='Reviews']")->click();
            }
        }
        $this->ExecuteScript('jQuery("#geodir_overallrating").val("4");');
        $this->byId('comment')->value('Cool xyz');
        $this->byId('submit')->click();
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