<?php
class SocialImporter extends GD_Test
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

    public function testSocialImporter()
    {
        $this->logInfo('Testing social importer......');
        //make sure Social Importer plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-social-importer")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Social Importer
            $this->maybeActivatePlugin("geodirectory-social-importer", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-social-importer")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Social Importer plugin not active");

        $stop_script = false;

        $fb_app_id  = getenv( 'FACEBOOK_APP_ID' );
        $fb_app_secret  = getenv( 'FACEBOOK_APP_SECRET' );
        $yelp_key  = getenv( 'YELP_KEY' );
        $yelp_key_secret  = getenv( 'YELP_KEY_SECRET' );
        $yelp_token  = getenv( 'YELP_TOKEN' );
        $yelp_token_secret  = getenv( 'YELP_TOKEN_SECRET' );

        if (!$fb_app_id) {
            $this->logError("ENV variable FACEBOOK_APP_ID not available");
            $stop_script = true;
        }

        if (!$fb_app_secret) {
            $this->logError("ENV variable FACEBOOK_APP_SECRET not available");
            $stop_script = true;
        }

        if (!$yelp_key) {
            $this->logError("ENV variable YELP_KEY not available");
            $stop_script = true;
        }

        if (!$yelp_key_secret) {
            $this->logError("ENV variable YELP_KEY_SECRET not available");
            $stop_script = true;
        }

        if (!$yelp_token) {
            $this->logError("ENV variable YELP_TOKEN not available");
            $stop_script = true;
        }

        if (!$yelp_token_secret) {
            $this->logError("ENV variable YELP_TOKEN_SECRET not available");
            $stop_script = true;
        }

        if($stop_script) {
            $this->logInfo("Stopping the script. Please fix the errors to continue");
            return;
        }

        // facebook
        $this->url(self::GDTEST_BASE_URL.'admin.php?page=geodirectory&tab=facebook_integration&subtab=geodir_gdfi_options');
        $this->byId('gdfi_app_id')->value($fb_app_id);
        $this->byId('gdfi_app_secret')->value($fb_app_secret);
        $this->byName('gdfi_facebook_integration_options_save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        //yelp
        $this->url(self::GDTEST_BASE_URL.'admin.php?page=geodirectory&tab=facebook_integration&subtab=manage_gdfi_options_yelp');
        $this->byId('gdfi_yelp_key')->value($yelp_key);
        $this->byId('gdfi_yelp_key_secret')->value($yelp_key_secret);
        $this->byId('gdfi_yelp_token')->value($yelp_token);
        $this->byId('gdfi_yelp_token_secret')->value($yelp_token_secret);
        $this->byName('gdfi_yelp_integration_options_save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        // import listing
        $this->url(self::GDTEST_BASE_URL.'add-listing/?listing_type=gd_place');
        $this->byId('gdfi_import_url')->value('http://www.yelp.com/biz/mcdonalds-mountain-view-3');
        $this->byId('gd_facebook_import')->click();


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