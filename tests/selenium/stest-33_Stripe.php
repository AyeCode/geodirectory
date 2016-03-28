<?php
class Stripe extends GD_Test
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

    public function testStripe()
    {
        $this->logInfo('Testing stripe......');
        //make sure Stripe payment plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("stripe-payment-geodirectory-add-on")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory stripe payment geodirectory add on
            $this->maybeActivatePlugin("stripe-payment-geodirectory-add-on", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("stripe-payment-geodirectory-add-on")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "stripe payment geodirectory add on plugin not active");


        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=paymentmanager_fields&subtab=geodir_payment_options');
        $this->waitForPageLoadAndCheckForErrors();

        $this->url(self::GDTEST_BASE_URL.'author/admin/?geodir_dashbord=true&stype=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byClassName('geodir-upgrade')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("(//div[@class='geodir_package']/input[@type='radio'])[2]")->click();
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("Business Owner/Associate")) {
//            $elements = $this->elements($this->using('name')->value('claimed'));
//            $elements[0]->click();
//            $this->byXPath("//input[contains(@name,'claimed') and contains(@value,'0')]")->click();
            $this->ExecuteScript('jQuery("#geodir_claimed_row input:radio:first").prop("checked", true).trigger("click");');
        }
        $this->byId('geodir_accept_term_condition')->click();
        $this->byXPath("//div[@id='geodir-add-listing-submit']//input[@type='submit']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//input[@name='Submit and Pay']")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->ExecuteScript('jQuery("#gd_pmethod_stripe").prop("checked", true).trigger("click");');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gd_checkout_paynow')->click();
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byId('email')->value('test@test.com');
//        $this->byId('card_number')->value('4242424242424242');
//        $this->byId('cc-exp')->value('12 / 20');
//        $this->byId('cc-csc')->value('333');
//        $this->byId('submitButton')->click();
        $this->logInfo('Skipping stripe payment since cross-site scripting not possible......');
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