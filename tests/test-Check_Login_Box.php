<?php
class CheckLoginBox extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testLoginBoxEmptyPassword()
    {
        global $errors;
        $_POST['testcookie'] = 1;
        $_POST['log'] = 'admin';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'empty_password', $errors["errors"] );
        $this->assertContains( 'The password field is empty', $errors["errors"]["empty_password"][0] );
    }

    public function testLoginBoxIncorrectPassword()
    {
        global $errors;
        $_POST['testcookie'] = 1;
        $_REQUEST['action'] = 'login';
        $_POST['log'] = 'testuser';
        $_POST['pwd'] = 'admin';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'incorrect_password', $errors["errors"] );
        $this->assertContains( 'The password you entered for the username', $errors["errors"]["incorrect_password"][0] );
    }

    public function testLoginBoxInvalidUsername()
    {
        global $errors;
        $_POST['testcookie'] = 1;
        $_REQUEST['action'] = 'login';
        $_POST['log'] = 'adm##@in';
        $_POST['pwd'] = 'admin';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'invalid_username', $errors["errors"] );
        $this->assertContains( 'Invalid username', $errors["errors"]["invalid_username"][0] );
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>