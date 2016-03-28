<?php
class ImportExport extends GD_Test
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

    public function testImportExport()
    {
        $this->logInfo('Import Export......');
        $this->logInfo('Skipping Import Export since its not possible......');
//        //export listing
//        $this->url(self::GDTEST_BASE_URL.'wp-admin/admin.php?page=geodirectory&tab=import_export');
//        $this->waitForPageLoadAndCheckForErrors();
//        $this->byId('gd_ie_exposts_submit')->click();
//        $this->waitForPageLoadAndCheckForErrors();
//
//        //export cats
//        $this->byId('gd_ie_excats_submit')->click();
//        $this->waitForPageLoadAndCheckForErrors();

        //Todo: find a way to test import
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