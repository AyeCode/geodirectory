<?php
class ComplexAdvancedSearch extends GD_Test
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

    public function testComplexAdvancedSearch()
    {
        $this->logInfo('Testing complex advanced search......');
        //make sure advance search filters plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-advance-search-filters")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Advance Search Filters
            $this->maybeActivatePlugin("geodirectory-advance-search-filters", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-advance-search-filters")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Advance Search Filters plugin not active");

        //Add search fields
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=custom_fields&listing_type=gd_place');
//        $this->ExecuteScript('jQuery("#field_frm1").show();');
        $this->waitForPageLoadAndCheckForErrors();
        $this->hideAdminBar();
        $link = $this->byXPath("//li[@id='licontainer_1']/div");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//li[@id='licontainer_1']//input[@id='cat_filter']")->click();
        $this->byXPath("//li[@id='licontainer_1']//input[@id='save']")->click();
//        $this->byId('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        //category
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=gd_place_fields_settings&subtab=advance_search&listing_type=gd_place');
        $this->waitForPageLoadAndCheckForErrors();
        $this->hideAdminBar();
        $this->byId('gt-gd_placecategory')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $link = $this->byXPath("//li[@id='licontainer_gd_placecategory']/div");
        $this->moveto($link);
        $this->doubleclick();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//div[@id='field_frmgd_placecategory']//input[@id='front_search_title']")->value('Category');
        $this->byXPath("//div[@id='field_frmgd_placecategory']//input[@id='save']")->click();
        $this->waitForPageLoadAndCheckForErrors();

//        $link = $this->byXPath("//li[@id='licontainer_dist']/div[contains(@class,'titledist')]");
//        $this->moveto($link);
//        $this->doubleclick();

//        $this->byXPath("//li[@id='licontainer_dist']/div[contains(@class,'titledist')]")->click();
//        $this->ExecuteScript('jQuery("#field_frmgd_placecategory").show();');
//        $this->byId('front_search_title')->value('Category');
//        $this->byId('save')->click();
//        $this->waitForPageLoadAndCheckForErrors();


        $this->url(self::GDTEST_BASE_URL);
        $this->waitForPageLoadAndCheckForErrors();
        $this->byXPath("//section[contains(@class,'geodir_advance_search_widget')]//input[@name='s']")->value('Test');
        $this->byXPath("//section[contains(@class,'geodir_advance_search_widget')]//input[@class='showFilters']")->click();
        $this->select($this->byXPath("//section[contains(@class,'geodir_advance_search_widget')]//select[@class='cat_select']"))->selectOptionByLabel('Attractions');
//        $this->byName('sgd_placecategory[]')->value("2");
        $this->byXPath("(//section[contains(@class,'geodir_advance_search_widget')]//input[@class='geodir_submit_search'])[2]")->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Search Places For"), "Not in search results page");
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