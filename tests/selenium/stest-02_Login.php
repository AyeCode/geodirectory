<?php
class LoginUser extends GD_Test
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

    public function testLogin()
    {
        $this->logInfo('Logging in new user......');
        $this->url(self::GDTEST_BASE_URL.'gd-login/?signup=1');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Sign In"), "No text found");
        $this->byId('user_login')->value('testuser@test.com');
        $this->byId('user_pass')->value('1');
//        $this->byId('rememberme')->click();
        // Submit the form
        $this->byId('cus_loginform')->submit();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertFalse( $this->isTextPresent("Invalid Username/Password."), "Invalid Username/Password.");
        $this->assertTrue( $this->isTextPresent("Add Listing"), "Add Listing text not found");

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