<?php
class SendEnquiry extends GD_Test
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

    public function testSendEnquiry()
    {
        $this->logInfo('Send Enquiry......');
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/test-listing/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('b_send_inquiry')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byName('inq_name')->value('Test User');
        $this->byName('inq_email')->value('test@test.com');
        $this->byId('agt_mail_phone')->value('44444444444');
        $this->byName('Send')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Enquiry sent successfully"), "Success text not found");
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