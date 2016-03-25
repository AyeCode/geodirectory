<?php
class ClaimAListing extends GD_Test
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

    public function testClaimAListing()
    {
        $this->logInfo('Claim a listing......');
        //make sure claim manager plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-claim-manager")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Claim Manager
            $this->maybeActivatePlugin("geodirectory-claim-manager", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-claim-manager")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Claim Manager plugin not active");

        //Configure claim manager
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=claimlisting_fields&subtab=geodir_claim_options');
        $this->waitForPageLoadAndCheckForErrors();
        $this->hideAdminBar();
        $this->ExecuteScript('jQuery("select#geodir_claim_enable").show();');
        $this->ExecuteScript('jQuery("select#geodir_claim_auto_approve").show();');
        $this->ExecuteScript('jQuery("select#geodir_claim_show_owner_varified").show();');
        $this->ExecuteScript('jQuery("select#geodir_claim_show_author_link").show();');
        $this->ExecuteScript('jQuery("select#geodir_post_types_claim_listing").show();');
        $this->ExecuteScript('jQuery("select#geodir_claim_force_upgrade").show();');

        $this->select($this->byId("geodir_claim_enable"))->selectOptionByLabel('Yes');
        $this->select($this->byId("geodir_claim_auto_approve"))->selectOptionByLabel('Yes');
        $this->select($this->byId("geodir_claim_show_owner_varified"))->selectOptionByLabel('Yes');
        $this->select($this->byId("geodir_claim_show_author_link"))->selectOptionByLabel('Yes');
        $this->select($this->byId("geodir_post_types_claim_listing"))->selectOptionByLabel('Place');
        $this->select($this->byId("geodir_claim_force_upgrade"))->selectOptionByLabel('No');
        $this->byName('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $this->url(self::GDTEST_BASE_URL.'places/united-states/new-york/new-york/attractions/test-listing/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('geodir_claim_enable')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_full_name')->value('Test User');
        $this->byId('geodir_user_number')->value('44444444444');
        $this->byId('geodir_user_position')->value('Business Manager');
        $this->byName('geodir_Send')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Request sent successfully"), "Success text not found");
        //Goto claim page
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=claimlisting_fields&subtab=manage_geodir_claim_listing');
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