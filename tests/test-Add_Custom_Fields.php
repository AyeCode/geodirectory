<?php
class AddCustomFields extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
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

    public function testTextCusField() {
        $_REQUEST = array (
            'action' =>  'geodir_ajax_action',
            'create_field' =>  'true',
            'field_ins_upd' =>  'submit',
            '_wpnonce' =>  wp_create_nonce('custom_fields_text_field_99'),
            'listing_type' =>  'gd_place',
            'field_type' =>  'text',
            'field_id' =>  'text_field_99',
            'data_type' =>  'XVARCHAR',
            'is_active' =>  '1',
            'site_title' => "Text Field",
            'admin_title' => "Text Field",
            'admin_desc' => "Text Field",
            'site_field_title' =>  'Category',
            'field_data_type' =>  'VARCHAR',
            'search_condition' =>  'SINGLE',
            'htmlvar_name' =>  'text_field_99',
            'for_admin_use' => '0',
            'is_required' => '0',
            'required_msg'=> '',
            'validation_pattern' => '',
            'validation_msg' => '',
            'decimal_point' => '',
            'clabels' => 'Text Field',
            'is_default' => '1',
            'field_title' =>  'Text Field',
            'expand_custom_value' =>  '',
            'search_operator' =>  'AND',
            'front_search_title' =>  'Category',
            'field_desc' =>  'Cat',
            'geodir_ajax' => 'admin_ajax',
            'manage_field_type' => 'custom_fields',
            'default_value' =>  '' ,
            'sort_order' =>  '11' ,
            'show_on_listing' =>  '1' ,
            'show_on_detail' =>  '1' ,
            'show_as_tab' =>  '0' ,
            'field_icon' =>  '' ,
            'css_class' =>  '' ,
        );
        add_filter('wp_redirect', '__return_false');
        ob_start();
        geodir_ajax_handler();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Double Click to toggle', $output);
        remove_filter('wp_redirect', '__return_false');

    }

    public function texstDateCusField() {
        $_REQUEST = array (
            'action' =>  'geodir_ajax_action',
            'create_field' =>  'true',
            'field_ins_upd' =>  'submit',
            '_wpnonce' =>  wp_create_nonce('custom_fields_date_field'),
            'listing_type' =>  'gd_place',
            'field_type' =>  'datepicker',
            'field_id' =>  'date_field',
            'data_type' =>  '',
            'is_active' =>  '1',
            'site_title' => "Text Field",
            'admin_title' => "Text Field",
            'admin_desc' => "Text Field",
            'site_field_title' =>  'Category',
            'field_data_type' =>  'VARCHAR',
            'search_condition' =>  'SINGLE',
            'htmlvar_name' =>  'date_field',
            'for_admin_use' => '0',
            'is_required' => '0',
            'required_msg'=> '',
            'validation_pattern' => '',
            'validation_msg' => '',
            'decimal_point' => '',
            'clabels' => 'Text Field',
            'is_default' => '0',
            'field_title' =>  'Text Field',
            'expand_custom_value' =>  '',
            'search_operator' =>  'AND',
            'front_search_title' =>  'Category',
            'field_desc' =>  'Cat',
            'geodir_ajax' => 'admin_ajax',
            'manage_field_type' => 'custom_fields',
            'default_value' =>  '' ,
            'sort_order' =>  '12' ,
            'show_on_listing' =>  '1' ,
            'show_on_detail' =>  '1' ,
            'show_as_tab' =>  '0' ,
            'field_icon' =>  '' ,
            'css_class' =>  '' ,
            'extra' => array(
                'date_format' => 'mm/dd/yy'
            )
        );
        add_filter('wp_redirect', '__return_false');
        ob_start();
        geodir_ajax_handler();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Double Click to toggle', $output);
        remove_filter('wp_redirect', '__return_false');

    }

    public function texstTextAreaCusField() {
        $_REQUEST = array (
            'action' =>  'geodir_ajax_action',
            'create_field' =>  'true',
            'field_ins_upd' =>  'submit',
            '_wpnonce' =>  wp_create_nonce('custom_fields_textarea_field'),
            'listing_type' =>  'gd_place',
            'field_type' =>  'textarea',
            'field_id' =>  'textarea_field',
            'data_type' =>  '',
            'is_active' =>  '1',
            'site_title' => "Text Field",
            'admin_title' => "Text Field",
            'admin_desc' => "Text Field",
            'htmlvar_name' =>  'textarea_field',
            'for_admin_use' => '0',
            'is_required' => '0',
            'required_msg'=> '',
            'validation_pattern' => '',
            'validation_msg' => '',
            'decimal_point' => '',
            'clabels' => 'Text Field',
            'is_default' => '1',
            'field_title' =>  'Text Field',
            'expand_custom_value' =>  '',
            'search_operator' =>  'AND',
            'front_search_title' =>  'Category',
            'field_desc' =>  'Cat',
            'geodir_ajax' => 'admin_ajax',
            'manage_field_type' => 'custom_fields',
            'default_value' =>  '' ,
            'sort_order' =>  '12' ,
            'show_on_listing' =>  '1' ,
            'show_on_detail' =>  '1' ,
            'show_as_tab' =>  '0' ,
            'field_icon' =>  '' ,
            'css_class' =>  '' ,
        );
        add_filter('wp_redirect', '__return_false');
        ob_start();
        geodir_ajax_handler();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains('Double Click to toggle', $output);
        remove_filter('wp_redirect', '__return_false');

    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>