<?php
class AddCustomFields extends GD_Test
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

    public function testAddCustomFields()
    {
        $this->logInfo('Add custom fields......');
        $this->prepareSession()->currentWindow()->maximize();
        //Field 1
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=custom_fields&listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gt-text')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $link = $this->byXPath("//li[@id='licontainer_new10']/div[contains(@class,'titlenew10')]");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='admin_title']")->value('Text Field 1');
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='site_title']")->value('Text Field 1');
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='htmlvar_name']")->value('text_field_1');
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='clabels']")->value('Text Field 1');
        $this->select($this->byXPath("//div[@id='field_frmnew10']//select[@id='is_active']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew10']//select[@id='show_on_listing']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew10']//select[@id='show_on_detail']"))->selectOptionByLabel('Yes');
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='cat_sort']")->click();
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='cat_filter']")->click();
        $this->byXPath("//div[@id='field_frmnew10']//input[@id='save']")->click();
        $this->waitForPageLoadAndCheckForErrors();

        //Field 2
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=custom_fields&listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gt-text')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $link = $this->byXPath("//li[@id='licontainer_new11']/div[contains(@class,'titlenew11')]");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='admin_title']")->value('Text Field 2');
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='site_title']")->value('Text Field 2');
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='htmlvar_name']")->value('text_field_2');
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='clabels']")->value('Text Field 2');
        $this->select($this->byXPath("//div[@id='field_frmnew11']//select[@id='is_active']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew11']//select[@id='show_on_listing']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew11']//select[@id='show_on_detail']"))->selectOptionByLabel('Yes');
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='cat_sort']")->click();
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='cat_filter']")->click();
        $this->byXPath("//div[@id='field_frmnew11']//input[@id='save']")->click();
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