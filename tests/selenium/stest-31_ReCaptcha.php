<?php
class ReCaptcha extends GD_Test
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

    public function testReCaptcha()
    {
        $this->logInfo('Testing recaptcha......');
        //make sure ReCaptcha plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-re-captcha")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory ReCaptcha
            $this->maybeActivatePlugin("geodirectory-re-captcha", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-re-captcha")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "ReCaptcha plugin not active");

        //make sure BuddyPress core plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("buddypress")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory buddypress
            $this->maybeActivatePlugin("buddypress", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("buddypress")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "buddypress plugin not active");

        //make sure BuddyPress Integration plugin active
        $is_active = $this->byId("geodirectory-buddypress-integration")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory buddypress integration
            $this->maybeActivatePlugin("geodirectory-buddypress-integration", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-buddypress-integration")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "geodirectory buddypress integration plugin not active");


        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=geodir_recaptcha&subtab=gdcaptcha_settings');
        $this->waitForPageLoadAndCheckForErrors();
        $this->prepareSession()->currentWindow()->maximize();

        $stop_script = false;

        $public_key  = getenv( 'GOOGLE_RECAPTCHA_KEY' );
        $private_key  = getenv( 'GOOGLE_RECAPTCHA_SECRET' );

        if (!$public_key) {
            $this->logError("ENV variable GOOGLE_RECAPTCHA_KEY not available");
            $stop_script = true;
        }

        if (!$private_key) {
            $this->logError("ENV variable GOOGLE_RECAPTCHA_KEY not available");
            $stop_script = true;
        }

        if($stop_script) {
            $this->logInfo("Stopping the script. Please fix the errors to continue");
            return;
        }

        $value = $this->byId('geodir_recaptcha_site_key')->value();
        if (empty($value)) {
            $this->byId('geodir_recaptcha_site_key')->value($public_key);
        }

        $value = $this->byId('geodir_recaptcha_secret_key')->value();
        if (empty($value)) {
            $this->byId('geodir_recaptcha_secret_key')->value($private_key);
        }


        $to_save = false;

        $options = array(
            'geodir_recaptcha_registration',
            'geodir_recaptcha_add_listing',
            'geodir_recaptcha_claim_listing',
            'geodir_recaptcha_comments',
            'geodir_recaptcha_send_to_friend',
            'geodir_recaptcha_send_enquery',
            'geodir_recaptcha_buddypress'
        );

        foreach ($options as $option) {
            $is_checked = $this->byId($option)->attribute('checked');
            if (!$is_checked) {
                $this->byId($option)->click();
                $to_save = true;
            }
        }

        if ($to_save) {
            $this->byName('save')->click();
            $this->waitForPageLoadAndCheckForErrors();
        }

        $this->maybeLogout();
        //Signup
        $this->url(self::GDTEST_BASE_URL.'gd-login/?signup=1');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Sign Up Now"), "Not in signup page");
        $this->waitForPageLoadAndCheckForErrors();
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in signup page");

        //Add Listing
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
        $this->assertTrue( $this->isTextPresent("Add Place"), "Not in Add Listing page");
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in Add Listing page");

        //claim
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd-claim-button')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in claim form");

        //reviews
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('Reviews')->click();
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in reviews form");

        //send enquiry
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('b_send_inquiry')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in Send Enquiry form");

        //send to friend
        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/franklin-square/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('b_sendtofriend')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->frame($this->byXPath("//div[@class='gd-captcha-render']//iframe"));
        $this->assertTrue( $this->isTextPresent("I'm not a robot"), "Recaptcha field not found in Send To Friend form");

        //buddypress
        //Todo: assert buddypress page

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