<?php
class Register extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(0);
    }

    public function test_register_empty_username() {
        $errors = geodir_register_new_user(
            '',
            'hello@hi.com'
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'empty_username', $errors["errors"] );
        $this->assertContains( 'Please enter a username', $errors["errors"]["empty_username"][0] );
    }

    public function test_register_invalid_username() {
        $errors = geodir_register_new_user(
            '@#$%^',
            'hello@hi.com'
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'invalid_username', $errors["errors"] );
        $this->assertContains( 'This username is invalid', $errors["errors"]["invalid_username"][0] );
    }

    public function test_register_invalid_email() {
        $errors = geodir_register_new_user(
            'hello',
            'hello@@hi.com'
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'invalid_email', $errors["errors"] );
        $this->assertContains( 'The email address isn&#8217;t correct', $errors["errors"]["invalid_email"][0] );
    }

    public function test_register_empty_email() {
        $errors = geodir_register_new_user(
            'hello',
            ''
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'empty_email', $errors["errors"] );
        $this->assertContains( 'Please type your e-mail address', $errors["errors"]["empty_email"][0] );
    }

    public function test_register_invalid_password() {
        update_option('geodir_allow_cpass', 1);
        $_REQUEST['user_pass'] = '12345';
        $_REQUEST['user_pass2'] = '1234';
        $_POST['user_fname'] = 'Test User';
        $errors = geodir_register_new_user(
            'hello',
            'hello@hi.com'
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'pass_match', $errors["errors"] );
        $this->assertContains( 'Passwords do not match', $errors["errors"]["pass_match"][0] );

        $_REQUEST['user_pass'] = '12345';
        $_REQUEST['user_pass2'] = '12345';
        $errors = geodir_register_new_user(
            'hello',
            'hello@hi.com'
        );
        $errors = (array) $errors;
        $this->assertArrayHasKey( 'pass_match', $errors["errors"] );
        $this->assertContains( 'Password must be 7 characters or more', $errors["errors"]["pass_match"][0] );
    }

    public function test_register_success() {
        $_POST['user_fname'] = 'Test User';
        $data = geodir_register_new_user(
            'hello',
            'hello@hi.com'
        );
        $this->assertInternalType("int", $data[0]);
    }

    public function testResetPassword()
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

    public function tearDown()
    {
        parent::tearDown();
    }

}
?>