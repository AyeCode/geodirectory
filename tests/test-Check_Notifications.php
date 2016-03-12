<?php
class CheckNotifications extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testSendFriendEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'send_friend');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'thought you might be interested in', $output );
        $this->assertContains( 'Your friend has sent you a message from', $output );
        $this->assertContains( 'test@test.com', $output );
        $this->assertContains( 'ADMIN BCC COPY', $output );
    }

    public function testSendEnquiryEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'send_enquiry');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'Website Enquiry', $output );
        $this->assertContains( 'An enquiry has been sent from', $output );
        $this->assertContains( 'test@test.com', $output );
        $this->assertContains( 'ADMIN BCC COPY', $output );
    }

    public function testForgotPassEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'forgot_password');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'Your new password', $output );
        $this->assertContains( 'You requested a new password for', $output );
        $this->assertContains( 'test@test.com', $output );
    }

    public function testRegistrationEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'registration');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'Your Log In Details', $output );
        $this->assertContains( 'You can log in  with the following information', $output );
        $this->assertContains( 'test@test.com', $output );
        $this->assertContains( 'ADMIN BCC COPY', $output );
    }

    public function testPostSubmitEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'post_submit');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'Post Submitted Successfully', $output );
        $this->assertContains( 'You submitted the below listing information', $output );
        $this->assertContains( 'test@test.com', $output );
        $this->assertContains( 'ADMIN BCC COPY', $output );
        $this->assertContains( 'A new  listing has been published', $output );
    }

    public function testListingPublishedEmail()
    {
        add_filter('wp_mail', 'print_mail');
        ob_start();
        geodir_sendEmail('','', 'test@test.com', 'Test User', 'Test subject', 'Test message', '', 'listing_published');
        $output = ob_get_clean();
        remove_filter('wp_mail', 'print_mail');
        $this->assertContains( 'Listing Published Successfully', $output );
        $this->assertContains( 'Your listing  has been published', $output );
        $this->assertContains( 'test@test.com', $output );
        $this->assertContains( 'ADMIN BCC COPY', $output );
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>