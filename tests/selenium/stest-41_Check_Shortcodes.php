<?php
class CheckShortcodes extends GD_Test
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

    public function testCheckShortcodes()
    {
        $this->logInfo('Testing shortcodes......');

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Home Page Map');
        $this->byId('content')->value('[gd_homepage_map width=100% height=300 scrollwheel=false]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-home-page"), "Home page map not found");


        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Custom Post Type Categories');
        $this->byId('content')->value('[gd_cpt_categories show_count=1]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("gd-cptcats-widget"), "Custom Post Type Categories not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Listings Page Map');
        $this->byId('content')->value('[gd_listing_map width=100% height=300 scrollwheel=false sticky=true]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-listing-page"), "Listings Page Map not found");


        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Listing Slider');
        $this->byId('content')->value('[gd_listing_slider post_number=5 category=3 slideshow=true show_featured_only=true]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir_widget_carousel"), "Listing Slider not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Login Box');
        $this->byId('content')->value('[gd_login_box]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-loginbox-list"), "Login Box not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Popular Post Category');
        $this->byId('content')->value('[gd_popular_post_category category_limit=10]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-popular-cat-list"), "Popular Post Category not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Popular Post View');
        $this->byId('content')->value('[gd_popular_post_view category=3 layout=5 add_location_filter=true character_count=0 show_featured_only=true]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir_category_list_view"), "Popular Post View not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Recent Review');
        $this->byId('content')->value('[gd_recent_reviews count=5]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir_sc_recent_reviews"), "Recent Review not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Related Listings');
        $this->byId('content')->value('[gd_related_listings relate_to=tags layout=3 add_location_filter=true list_sort=featured character_count=0]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir_location_listing"), "Related Listings not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Search');
        $this->byId('content')->value('[gd_advanced_search]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-map-home-page"), "Search not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: GD Listings');
        $this->byId('content')->value('[gd_listings post_type="gd_place" category="1,3" post_number="10" list_sort="high_review"]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-sc-gd-listings"), "GD Listings not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: GD Best of');
        $this->byId('content')->value('[gd_bestof_widget title="widget title" post_type=gd_hotel post_limit=5 categ_limit=6 character_count=50 use_viewing_post_type=true add_location_filter=true tab_layout=bestof-tabs-as-dropdown]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir_bestof_widget"), "GD Best of not found");

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/post-new.php?post_type=page');
        $this->waitForPageLoadAndCheckForErrors();
        $this->byId('title')->value('Shortcode Test: Add Listing Form');
        $this->byId('content')->value('[gd_add_listing listing_type=gd_event login_msg="Please register and login to submit listings" show_login=true]');
        $this->byId('publish')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->byLinkText('View page')->click();
        $this->waitForPageLoadAndCheckForErrors();
        $this->assertTrue( $this->isTextPresent("geodir-add-listing-submit"), "Add Listing Form not found");
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