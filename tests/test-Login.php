<?php
class LoginUser extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        deactivate_plugins('buddypress/bp-loader.php');
    }

    public function testLoginEmptyPassword()
    {
        global $errors;
        $_REQUEST['action'] = 'login';
        $_POST['log'] = 'admin';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'empty_password', $errors["errors"] );
        $this->assertContains( 'The password field is empty', $errors["errors"]["empty_password"][0] );
    }

    public function testLoginIncorrectPassword()
    {
        global $errors;
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

    public function testLoginInvalidUsername()
    {
        global $errors;
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

//    public function testLoginValid()
//    {
//        global $errors;
//        $_REQUEST['action'] = 'login';
//        $_POST['log'] = 'testuser';
//        $_POST['pwd'] = '12345';
//        add_filter('wp_redirect', '__return_false');
//        geodir_user_signup();
//        remove_filter('wp_redirect', '__return_false');
//    }

    public function testForgotPassEmptyData()
    {
        global $errors;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = 'retrievepassword';
        $_POST['user_login'] = '';
        $_POST['user_email'] = '';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'empty_username', $errors["errors"] );
        $this->assertContains( 'Enter a username or e-mail address', $errors["errors"]["empty_username"][0] );
    }

    public function testForgotPassInvalidEmail()
    {
        global $errors;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = 'retrievepassword';
        $_POST['user_login'] = 'holla@hello.com';
        $_POST['user_email'] = '';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'invalid_email', $errors["errors"] );
        $this->assertContains( 'There is no user registered with that email address', $errors["errors"]["invalid_email"][0] );
    }

    public function testForgotPassInvalidUsername()
    {
        global $errors;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = 'retrievepassword';
        $_POST['user_login'] = 'holla';
        $_POST['user_email'] = 'holla@hello.com';
        add_filter('wp_redirect', '__return_false');
        geodir_user_signup();
        remove_filter('wp_redirect', '__return_false');
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'invalidcombo', $errors["errors"] );
        $this->assertContains( 'Invalid username or e-mail', $errors["errors"]["invalidcombo"][0] );
    }

    public function tearDown()
    {
        parent::tearDown();
    }

}
?>