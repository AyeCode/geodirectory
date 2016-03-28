<?php
class Register extends GD_Test
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

    public function testRegister()
    {
        $this->logInfo('Registering new user......');
        $this->url(self::GDTEST_BASE_URL.'gd-login/?signup=1');
        $this->waitForPageLoadAndCheckForErrors();
//        echo $this->source();
        $this->assertTrue( $this->isTextPresent("Sign Up Now"), "'Sign Up Now' text found");
        $this->byId('user_email')->value('testuser@test.com');
        $this->byId('user_fname')->value('Test User');
        // Submit the form
        $this->byId('cus_registerform')->submit();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertFalse( $this->isTextPresent("New user registration disabled."), "New user registration disabled.");
        $this->assertFalse( $this->isTextPresent("This email is already registered."), "This email is already registered.");
        $this->assertTrue( $this->isTextPresent("Add Listing"), "Add Listing text not found");
//        echo $this->source();
        $this->byXPath("//*[@id='gd-sidebar-wrapper']//ul[@class='geodir-loginbox-list']//a[@class='signin']")->click();
        $this->waitForPageLoadAndCheckForErrors();

        //change password for the test user
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/users.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("testuser@test.com"), "Test user not found");
        $user_id = $this->byXPath("//*[text()[contains(.,'testuser@test.com')]]/ancestor::tr")->attribute('id');
        $user_id = str_replace('user-', '', $user_id);
        $this->url(self::GDTEST_BASE_URL.'wp-admin/user-edit.php?user_id='.$user_id);
//        $this->select($this->byId("role"))->selectOptionByLabel('Administrator');
        $this->byClassName('wp-generate-pw')->click();
        $this->waitForPageLoadAndCheckForErrors(5000);
        $this->byId('pass1-text')->value('1');
        $this->waitForPageLoadAndCheckForErrors(10000);
        $this->byClassName('pw-checkbox')->click();
        $this->byId('submit')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("User updated"), "User updated text not found");

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