<?php
class AddCustomFields extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testAddCustomFields()
    {
        $field = array(
            'listing_type'  => 'gd_place',
            'data_type'     => '',
            'field_type'    => 'select',
            'admin_title'   => __( 'Place Type', 'test' ),
            'admin_desc'    => __( 'Select the place type.', 'test' ),
            'site_title'    => __( 'Place Type', 'test' ),
            'htmlvar_name'  => 'test_place_type',
            'default_value' => '',
            'option_values' => 'Hotel,Bar,Restaurant,Pub',
            'is_default'    => '1',
            'is_admin'      => '1',
            'clabels'       => __( 'Place Type', 'test' )
        );

        $lastid = geodir_custom_field_save( $field );

        $this->assertTrue(is_int($lastid));


        $field2 = array(
            'listing_type'  => 'gd_place',
            'data_type'     => 'VARCHAR',
            'field_type'    => 'url',
            'admin_title'   => __( 'Website Link', 'test' ),
            'admin_desc'    => __( 'Enter the website link.', 'test' ),
            'site_title'    => __( 'Website Link', 'test' ),
            'htmlvar_name'  => 'test_ws_link',
            'default_value' => '',
            'option_values' => '',
            'is_default'    => '1',
            'is_admin'      => '1',
            'clabels'       => __( 'Website Link', 'test' )
        );

        $lastid2 = geodir_custom_field_save( $field2 );

        $this->assertTrue(is_int($lastid2));

        //test error
        $field3 = array(
            'listing_type'  => 'gd_place',
            'data_type'     => '',
            'field_type'    => 'select',
            'admin_title'   => __( 'Place Type', 'test' ),
            'admin_desc'    => __( 'Select the place type.', 'test' ),
            'site_title'    => __( 'Place Type', 'test' ),
            'htmlvar_name'  => 'test_place_type',
            'default_value' => '',
            'option_values' => 'Hotel,Bar,Restaurant,Pub',
            'is_default'    => '1',
            'is_admin'      => '1',
            'clabels'       => __( 'Place Type', 'test' )
        );

        $error = geodir_custom_field_save( $field3 );

        $this->assertContains( 'HTML Variable Name should be a unique name', $error );
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>