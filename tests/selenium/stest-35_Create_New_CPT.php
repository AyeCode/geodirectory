<?php
class CreateNewCPT extends GD_Test
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

    public function testCreateNewCPT()
    {
        $this->logInfo('Testing new CPT......');
        //make sure custom post types plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-custom-post-types")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Custom Post Types
            $this->maybeActivatePlugin("geodirectory-custom-post-types", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-custom-post-types")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Custom Post Types plugin not active");


        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=geodir_manage_custom_posts');
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("gd_hotel")) {
            echo "Hotel post type already found. Please delete it first";
            return;
        }

        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=geodir_manage_custom_posts&action=cp_addedit');
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("Post Type"), "Post Type text not found");
        $this->byId('geodir_custom_post_type')->value('hotel');
        $this->byId('geodir_listing_slug')->value('hotels');
        $this->byId('geodir_listing_order')->value('10');
        $this->byId('geodir_name')->value('Hotels');
        $this->byId('geodir_singular_name')->value('Hotel');
        $this->byName('geodir_save_post_type')->click();
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