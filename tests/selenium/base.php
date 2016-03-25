<?php
class GD_Test extends PHPUnit_Extensions_Selenium2TestCase {

    const GDTEST_BASE_URL = 'http://www.test.ci/selpress/';

    public function setUp()
    {
        $this->setSeleniumServerRequestsTimeout(300);
        $this->setBrowser('firefox');
        $this->setBrowserUrl(self::GDTEST_BASE_URL);
    }

    function isTextPresent($search)
    {
        $source = $this->source();
        if ( strpos((string)$source,$search) !== FALSE) {
            return true;
        } else {
            return false;
        }
    }

    function randomEmailID()
    {
        return md5(uniqid(rand(), true)).'@gmail.com';
    }

    function waitForPageLoadAndCheckForErrors($timeout=10000)
    {
        // Wait 10 seconds
        $this->timeouts()->implicitWait($timeout);
        $this->checkForErrors();
        $this->checkForJsErrors();
    }

    function checkForErrors()
    {
        $elements = $this->elements($this->using('css selector')->value('.xdebug-error'));
        if ($elements) {
            $total = count($elements);
            fwrite(STDOUT, $total.' errors found'. PHP_EOL);
            $count = 0;
            foreach ($elements as $i => $element) {
                $count++;
                if ($errors = $element->attribute('innerHTML')) {
                    fwrite(STDOUT, "========================================================================". PHP_EOL);
                    fwrite(STDOUT, strip_tags($errors). PHP_EOL);
                    if ($count == $total) {
                        fwrite(STDOUT, "========================================================================". PHP_EOL);
                    }
                }
            }
        }
    }

    function checkForJsErrors() {
        if ($this->isElementExists('sel_js_error')) {
            fwrite(STDOUT, 'Javascript errors found'. PHP_EOL);
            fwrite(STDOUT, "========================================================================". PHP_EOL);
            echo $this->byId('sel_js_error')->attribute('innerHTML');
            fwrite(STDOUT, "========================================================================". PHP_EOL);
        }
    }

    function maybeUserLogin($redirect, $force=false) {
        if ($force) {
            $this->url(self::GDTEST_BASE_URL.'wp-admin/');
            if ($this->isTextPresent("forgetmenot")) {
                $this->byId('user_login')->value('testuser@test.com');
                $this->byId('user_pass')->value('1');
//                $this->byId('rememberme')->click();
                // Submit the form
                $this->byId('wp-submit')->submit();
                $this->waitForPageLoadAndCheckForErrors();
            }
        }
        $this->url($redirect);
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("Sign In")) {
            $this->byId('user_login')->value('testuser@test.com');
            $this->byId('user_pass')->value('1');
//            $this->byId('rememberme')->click();
            // Submit the form
            $this->byId('cus_loginform')->submit();
            $this->waitForPageLoadAndCheckForErrors();
            $this->url($redirect);
        }
    }

    function maybeAdminLogin($redirect) {
        $this->url($redirect);
        $this->waitForPageLoadAndCheckForErrors();
        if ($this->isTextPresent("forgetmenot")) {
            $this->byId('user_login')->value('admin');
            $this->byId('user_pass')->value('admin');
//            $this->byId('rememberme')->click();
            // Submit the form
            $this->byId('wp-submit')->submit();
            $this->waitForPageLoadAndCheckForErrors();
            $this->url($redirect);
        }
    }

    function isElementExists($id = false, $by = 'id') {
        if (!$id) {
            return false;
        }
        $exists = true;
        try {
            if ($by == 'id') {
                $this->byId($id);
            } elseif ($by == 'xpath') {
                $this->byXPath($id);
            }

        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            if (PHPUnit_Extensions_Selenium2TestCase_WebDriverException::NoSuchElement == $e->getCode()) {
                $exists = false;
            }
        }
        return $exists;
    }

    function ExecuteScript($script, $args=array()) {
        $this->execute( array(
            'script' => $script ,
            'args'=>$args
        ) );
    }

    function hideAdminBar() {
        //admin bar causes problem with scroll. So hide admin bar while testing.
        $this->ExecuteScript('jQuery("#wpadminbar").hide();');
    }

    function logInfo($string) {
        fwrite(STDOUT, "Info: ".$string . PHP_EOL);
    }

    function logError($string) {
        fwrite(STDOUT, "Error: ".$string . PHP_EOL);
    }

    function logWarning($string) {
        fwrite(STDOUT, "Warning: ".$string . PHP_EOL);
    }

    function getCurrentFileNumber($file) {
        preg_match('/test-([0-9]+)_/', $file, $match);
        return (int) $match[1];
    }

    function getCompletedFileNumber() {
        $completed = fopen("tests/selenium/completed.txt", "r") or die("Unable to open file!");
        $content = fgets($completed);
        fclose($completed);
        return (int) $content;
    }

    function skipTest($current, $completed) {
        if ($completed == 0 || $completed == 42 || $current == $completed) {
            return false;
        } elseif ($current < $completed) {
            return true;
        } else {
            return false;
        }
    }

    function maybeLogout() {
        $this->url(self::GDTEST_BASE_URL);
        $this->byXPath("//*[@id='gd-sidebar-wrapper']//ul[@class='geodir-loginbox-list']//a[@class='signin']")->click();
        $this->waitForPageLoadAndCheckForErrors();
    }

    function maybeAdminLogout() {
        $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->ExecuteScript('jQuery("#wp-admin-bar-my-account").addClass("hover");');
        $this->byXPath("//*[@id='wp-admin-bar-logout']//a")->click();
        $this->waitForPageLoadAndCheckForErrors();
    }

    function maybeActivatePlugin($id=false, $timeout=10000) {
        if (!$id) {
            return;
        }
        $plugin_name = ucwords(str_replace('-', ' ', $id));
        $this->logInfo('Activating '.$plugin_name.' plugin......');
        $this->url(self::GDTEST_BASE_URL.'wp-admin/plugins.php');
        $this->waitForPageLoadAndCheckForErrors();
        $this->hideAdminBar();
        if (is_int(strpos($this->byId($id)->attribute('class'), 'inactive'))) {
            $this->byXPath("//tr[@id='".$id."']//span[@class='activate']/a")->click();
        }
        $this->waitForPageLoadAndCheckForErrors($timeout);
        $this->logInfo($plugin_name.' activated......');
    }

}