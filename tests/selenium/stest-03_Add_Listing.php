<?php
class AddListing extends GD_Test
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

    public function testAddListing()
    {
        $this->logInfo('Add GD Place listing......');
        $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("Sign In")) {
            $this->byId('user_login')->value('testuser@test.com');
            $this->byId('user_pass')->value('1');
            $this->byId('rememberme')->click();
            // Submit the form
            $this->byId('cus_loginform')->submit();
            $this->waitForPageLoadAndCheckForErrors();
            $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_place');
        }
        $this->assertTrue( $this->isTextPresent("Add Place"), "Add Place text not found");
        $this->byId('post_title')->value('Test Listing');
        $this->byId('post_desc')->value('Test Desc');
        $this->byId('post_tags')->value('tag1,tag2');
        $this->ExecuteScript('jQuery("select#gd_placecategory").show();');
        $this->select($this->byXPath("//select[@id='gd_placecategory']"))->selectOptionByLabel('Attractions');
        $this->waitForPageLoadAndCheckForErrors(2000);
        $this->byId('post_address')->value('wall street');
        $this->byId('post_address')->value('10006');
        $this->byId('post_set_address_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_timing')->value('10.00 am to 6 pm every day');
        $this->byId('geodir_contact')->value('444444444444');
        $this->byId('geodir_email')->value('test@test.com');
        $this->byId('geodir_website')->value('http://test.com');
        $this->byId('geodir_twitter')->value('http://twitter.com/test');
        $this->byId('geodir_facebook')->value('http://facebook.com/test');
        $this->byId('geodir_special_offers')->value('Test Offer');
        $this->byId('geodir_accept_term_condition')->click();
        // Submit the form
        $this->byXPath("//div[@id='geodir-add-listing-submit']//input[@type='submit']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("This is a preview of your listing"), "Not in preview page.");
        // Submit the form
        $this->byClassName('geodir_publish_button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Thank you, your information has been successfully received"), "Not in success page");
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