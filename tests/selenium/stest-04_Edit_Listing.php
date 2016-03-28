<?php
class EditListing extends GD_Test
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

    public function testEditListing()
    {
        $this->logInfo('Editing GD Place listing as user......');
        $this->maybeUserLogin(self::GDTEST_BASE_URL.'author/test-user/?geodir_dashbord=true&stype=gd_place', true);
        $this->assertTrue( $this->isTextPresent("Places by"), "'Places by' text not found");
        $this->byClassName('geodir-edit')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Edit Place"), "Edit Place text not found");
        $this->byId('post_desc')->value('Test Desc modified');
        $this->byId('geodir_accept_term_condition')->click();
        // Submit the form
        $this->byXPath("//div[@id='geodir-add-listing-submit']//input[@type='submit']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("This is a preview of your listing"), "Not in preview page.");
        // Submit the form
        $this->byClassName('geodir_publish_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Thank you, your information has been successfully received"), "Not in success page");
        $this->maybeLogout();
    }

    public function testEditAdminListing()
    {
        $this->logInfo('Editing GD Place listing as admin......');
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/edit.php?post_type=gd_place');
        $this->assertTrue( $this->isTextPresent("post-type-gd_place"), "Not in Places post type");
        $this->byLinkText("Test Listing")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Edit Place"), "Edit Place text not found");
        $this->byId('title')->value('Test Listing modified');
        // Submit the form
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Place updated."), "updated text not found.");
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