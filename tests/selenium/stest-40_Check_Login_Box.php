<?php
class CheckLoginBox extends GD_Test
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

    public function testCheckLoginBox()
    {
        $this->logInfo('Testing login box......');
        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("gdf_welcome_login_wrap"), "Login welcome widget not found");
        $this->byName('log')->value('testuser@test.com');
        $this->byName('pwd')->value('1');
        // Submit the form
        $this->byClassName('b_signin')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertFalse( $this->isTextPresent("Invalid Username/Password."), "Invalid Username/Password.");
        $this->assertTrue( $this->isTextPresent("Welcome,"), "Welcome text not found");
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