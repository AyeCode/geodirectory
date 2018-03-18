/**
 * Setup wizard functions.
 */

/**
 * Add widgets from setup wizard.
 *
 * @param $security
 * @returns {boolean}
 */
function gd_wizard_add_widgets($security){
    var $sidebar_id = jQuery( "#geodir-wizard-widgets" ).val();

    var data = {
        'action':           'geodir_wizard_insert_widgets',
        'security':          $security,
        'sidebar_id':        $sidebar_id
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery( ".geodir-wizard-widgets-result" ).html('<i class="fa fa-refresh fa-spin" style="font-size:18px"></i>');

        },
        success: function(data)
        {
            if(data.data){
                jQuery( ".geodir-wizard-widgets-result" ).text(data.data);
            }
        }
    });

    return false;
}

/**
 * Add menu items during setup wizard.
 *
 * @param $security
 * @returns {boolean}
 */
function gd_wizard_setup_menu($security){
    var $menu_id = jQuery( "#geodir-wizard-menu-id" ).val();
    var $menu_location = jQuery( "#geodir-wizard-menu-location" ).val();

    var data = {
        'action':           'geodir_wizard_setup_menu',
        'security':          $security,
        'menu_id':           $menu_id,
        'menu_location':     $menu_location
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery( ".geodir-wizard-menu-result" ).html('<i class="fa fa-refresh fa-spin" style="font-size:18px"></i>');

        },
        success: function(data)
        {
            if(data.data){
                jQuery( ".geodir-wizard-menu-result" ).text(data.data);
            }
        }
    });

    return false;
}