<?php
class UpgradeListing extends GD_Test
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

    public function testUpgradeListing()
    {
        $this->logInfo('Upgrading GD Place listing......');
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();
        //check payment manager plugin active
        $is_active = $this->byId("geodirectory-payment-manager")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory payment manager
            $this->maybeActivatePlugin("geodirectory-payment-manager", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-payment-manager")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Payment Manager plugin not active");

        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Geo Directory Manage Price"), "Not in manage price page");
        $text = $this->byId("gd_price_table")->text();
        $count = substr_count($text, 'gd_place');
        if ($count == 1) {
            $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_manager&gd_pagetype=addeditprice');
            $this->waitForPageLoadAndCheckForErrors();
            $this->assertTrue( $this->isTextPresent("Add Price"), "Add Price text not found");
            $this->byId('title')->value('Premium');
            $this->byId('days')->value('0');
            $this->byName('gd_amount')->value('5');
            $this->select($this->byName('gd_status'))->selectOptionByValue(1);
            $this->byName('submit')->click();
        }
        $this->url(self::GDTEST_BASE_URL.'author/admin/?geodir_dashbord=true&stype=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('geodir-upgrade')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("(//div[@class='geodir_package']/input[@type='radio'])[2]")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('geodir_accept_term_condition')->click();
        $this->byXPath("//div[@id='geodir-add-listing-submit']//input[@type='submit']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//input[@name='Submit and Pay']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_pmethod_prebanktransfer')->click();
        $this->byId('gd_checkout_paynow')->click();
        $this->waitForPageLoadAndCheckForErrors();
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