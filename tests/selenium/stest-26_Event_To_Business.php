<?php
class EventToBusiness extends GD_Test
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

    public function testEventToBusiness()
    {
        //make sure event manager plugin active
        $this->logInfo('Testing events......');
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-events")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Events
            $this->maybeActivatePlugin("geodirectory-events", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-events")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Events plugin not active");


        // Event post type -> Allow post type to add from frontend
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=design_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Navigation')->click();
        $this->ExecuteScript('jQuery("#geodir_allow_posttype_frontend").show();');
        $this->select($this->byId('geodir_allow_posttype_frontend'))->selectOptionByLabel('Event');
        $this->byName('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_event');
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("Sign In")) {
            $this->byId('user_login')->value('test@test.com');
            $this->byId('user_pass')->value('12345');
            $this->byId('rememberme')->click();
            // Submit the form
            $this->byId('cus_loginform')->submit();
            $this->waitForPageLoadAndCheckForErrors();
            $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_event');
        }
        $this->assertTrue( $this->isTextPresent("Add Event"), "Add Event text not found");
        //event info
        $this->byId('is_recurring_n')->click();
        $this->byId('event_start')->value('2016-01-01');
        $this->byId('event_end')->value('2016-01-31');
        $this->byId('all_day')->click();


        if ($this->isTextPresent("Business Owner/Associate")) {
//            $elements = $this->elements($this->using('name')->value('claimed'));
//            $elements[0]->click();
//            $this->byXPath("//input[contains(@name,'claimed') and contains(@value,'0')]")->click();
            $this->ExecuteScript('jQuery("#geodir_claimed_row input:radio:first").prop("checked", true).trigger("click");');
        }

        $this->byId('post_title')->value('Test Event');
        $this->byId('post_desc')->value('Test Desc');
        $this->byId('post_tags')->value('tag1,tag2');
        $this->ExecuteScript('jQuery("select#gd_eventcategory").show();');
        $this->select($this->byXPath("//select[@id='gd_eventcategory']"))->selectOptionByLabel('Events');
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