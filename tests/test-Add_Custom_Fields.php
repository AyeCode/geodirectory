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

    public function testAddMoreCustomFields() {
        $fieldsets  = array();
        $fields  = array();
        $filters = array();

        // Place Details
        $fieldsets[] = array(
            'listing_type'  => 'gd_place',
            'data_type'     => '',
            'field_type'    => 'fieldset',
            'admin_title'   => __( 'Place Details', 'directory_starter' ),
            'admin_desc'    => __( 'Place Details.', 'directory_starter' ),
            'site_title'    => __( 'Place Details', 'directory_starter' ),
            'default_value' => '',
            'option_values' => '',
            'is_default'    => '1',
            'is_admin'      => '1',
            'clabels'       => __( 'Place Details', 'directory_starter' )
        );

        // Salary
        $fields[] = array(
            'listing_type' => 'gd_place',
            'data_type' => 'INT',
            'field_type' => 'text',
            'admin_title' => __('Salary', 'directory_starter'),
            'admin_desc' => __('Enter salary.', 'directory_starter'),
            'site_title' => __('Salary', 'directory_starter'),
            'htmlvar_name' => 'job_salary',
            'default_value' => '',
            'option_values' => '',
            'is_default' => '1',
            'is_admin' => '1',
            'clabels' => __('Salary', 'directory_starter')
        );

        // Salary Filter
        $filters[] = array(
            'create_field' => 'true',
            'listing_type' => 'gd_place',
            'field_id' => '',
            'field_type' => 'text',
            'data_type' => 'RANGE',
            'is_active' => '1',
            'site_field_title' => 'Salary',
            'field_data_type' => 'INT',
            'data_type_change' => 'TEXT',
            'search_condition_select' => 'FROM',
            'search_min_value' => '',
            'search_max_value' => '',
            'search_diff_value' => '',
            'first_search_value' => '',
            'first_search_text' => '',
            'last_search_text' => '',
            'search_condition' => 'FROM',
            'site_htmlvar_name' => 'geodir_job_salary',
            'field_title' => 'geodir_job_salary',
            'expand_custom_value' => '',
            'front_search_title' => 'Salary',
            'field_desc' => ''
        );

        // Field Set
        if (!empty($fieldsets)) {
            foreach ($fieldsets as $fieldset_index => $fieldset) {
                geodir_custom_field_save($fieldset);
            }
        }

        // Custom Fields
        if (!empty($fields)) {
            foreach ($fields as $field_index => $field) {
                $lastid = geodir_custom_field_save( $field );
                $this->assertTrue(is_int($lastid));
            }
        }

    }
    public function tearDown()
    {
        parent::tearDown();
    }
}
?>