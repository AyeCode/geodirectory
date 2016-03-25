<?php
class SortListing extends GD_Test
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

    public function testSortListing()
    {
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=custom_fields&listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gt-text')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $link = $this->byXPath("//li[@id='licontainer_new9']/div[contains(@class,'titlenew9')]");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='admin_title']")->value('Price');
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='site_title']")->value('Price');
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='htmlvar_name']")->value('price');
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='clabels']")->value('Price');
        $this->select($this->byXPath("//div[@id='field_frmnew9']//select[@id='is_active']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew9']//select[@id='show_on_listing']"))->selectOptionByLabel('Yes');
        $this->select($this->byXPath("//div[@id='field_frmnew9']//select[@id='show_on_detail']"))->selectOptionByLabel('Yes');
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='cat_sort']")->click();
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='cat_filter']")->click();
        $this->byXPath("//div[@id='field_frmnew9']//input[@id='save']")->click();
//        $this->byId('admin_title')->value('Price');
//        $this->byId('site_title')->value('Price');
//        $this->byId('htmlvar_name')->value('price');
//        $this->byId('clabels')->value('Price');
//        $this->select($this->byId("is_active"))->selectOptionByLabel('Yes');
//        $this->select($this->byId("show_on_listing"))->selectOptionByLabel('Yes');
//        $this->select($this->byId("show_on_detail"))->selectOptionByLabel('Yes');
//        $this->byId('cat_sort')->click();
//        $this->byId('cat_filter')->click();
//        $this->byId('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $this->logInfo('Testing list sorting......');
        //Make sure sorting options available
//        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=sorting_options&listing_type=gd_place');
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byId('gt-text-_-geodir_price')->click();
//        $this->waitForPageLoadAndCheckForErrors();
//        $link = $this->byId('licontainer_new-1');
//        $this->moveto($link);
//        $this->doubleclick();
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byXPath("//div[@id='field_frmnew-1']//input[@id='asc_title']")->value('Price ASC');
//        $this->byXPath("//div[@id='field_frmnew-1']//input[contains(@name,'is_default') and contains(@value,'geodir_price_asc')]")->click();
//        $this->byXPath("//div[@id='field_frmnew-1']//input[@id='desc_title']")->value('Price ASC');
//        $this->byXPath("//div[@id='field_frmnew-1']//input[@id='save']")->click();
//        $this->waitForPageLoadAndCheckForErrors();

        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=sorting_options&listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('gt-float-_-overall_rating')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $link = $this->byXPath("//li[@id='licontainer_new-1']/div");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//li[@id='licontainer_new-1']//input[@id='asc_title']")->value('Rating ASC');
        $this->byXPath("//li[@id='licontainer_new-1']//input[@id='asc']")->click();
        $this->byXPath("//li[@id='licontainer_new-1']//input[contains(@name,'is_default') and contains(@value,'overall_rating_asc')]")->click();
        $this->byXPath("//li[@id='licontainer_new-1']//input[@id='desc']")->click();
        $this->byXPath("//li[@id='licontainer_new-1']//input[@id='desc_title']")->value('Rating DESC');
        $this->byXPath("//li[@id='licontainer_new-1']//input[@id='save']")->click();
        $this->waitForPageLoadAndCheckForErrors();


        $this->url(self::GDTEST_BASE_URL.'places/');
        $this->waitForPageLoadAndCheckForErrors();
        $this->select($this->byId('sort_by'))->selectOptionByLabel('Rating DESC');
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