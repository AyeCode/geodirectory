<?php
class MarkerCluster extends GD_Test
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

    public function testMarkerCluster()
    {
        $this->logInfo('Testing marker cluster......');
        //make sure marker cluster plugin active
        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();

        $is_active = $this->byId("geodirectory-marker-cluster")->attribute('class');
        if (is_int(strpos($is_active, 'inactive'))) {
            //Activate Geodirectory Marker Cluster
            $this->maybeActivatePlugin("geodirectory-marker-cluster", 20000);
            //go back to plugin page
            $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        }

        $is_active1 = $this->byId("geodirectory-marker-cluster")->attribute('class');
        $this->assertFalse( strpos($is_active1, 'inactive'), "Marker Cluster plugin not active");


        //make sure marker cluster enabled in home map
        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=design_settings&active_tab=geodir_marker_cluster_settings');
        $this->byName('save')->click();
        $this->waitForPageLoadAndCheckForErrors();

        $this->maybeAdminLogin(self::GDTEST_BASE_URL.'location/');
        if (!$this->isElementExists('//div[text()=2]', 'xpath')) {
            $this->logError('Marker cluster not found in home page.');
        }
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