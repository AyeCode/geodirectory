/**
 * Setup wizard functions.
 */

/**
 * Add widgets from setup wizard.
 *
 * @param $security
 * @returns {boolean}
 */
function gd_wizard_add_widgets_top($security){
    var $sidebar_id = jQuery( "#geodir-wizard-widgets-top" ).val();

    var data = {
        'action':           'geodir_wizard_insert_widgets_top',
        'security':          $security,
        'sidebar_id':        $sidebar_id
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery( ".geodir-wizard-widgets-top-result" ).html('<i class="fas fa-sync fa-spin" style="font-size:18px"></i>');

        },
        success: function(data)
        {
            if(data.data){
                jQuery( ".geodir-wizard-widgets-top-result" ).html(data.data);
            }
        }
    });

    return false;
}

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
            jQuery( ".geodir-wizard-widgets-result" ).html('<i class="fas fa-sync fa-spin" style="font-size:18px"></i>');

        },
        success: function(data)
        {
            if(data.data){
                jQuery( ".geodir-wizard-widgets-result" ).html(data.data);
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
            jQuery( ".geodir-wizard-menu-result" ).html('<i class="fas fa-sync fa-spin" style="font-size:18px"></i>');

        },
        success: function(data)
        {
            if(data.data){
                jQuery( ".geodir-wizard-menu-result" ).html(data.data);
            }
        }
    });

    return false;
}

function gd_wizard_install_plugin($slug,$nonce){
    //alert($slug);

    var data = {
        'action':           'install-plugin',
        '_ajax_nonce':       $nonce,
        'slug':              $slug
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
           jQuery( "."+$slug + " .gd-plugin-status").html(jQuery('#gd-installing-text').val());

        },
        success: function(data)
        {
            // if(data.data){
            //     jQuery( ".geodir-wizard-widgets-result" ).text(data.data);
            // }
            console.log(data);
            if(data.success){
                jQuery( "."+$slug + " .gd-plugin-status").html(jQuery('#gd-installed-text').val());
                jQuery( "."+$slug + " input:checkbox").removeClass('gd_install_plugins').prop("disabled", true);
                gd_wizard_check_plugins();
                gd_wizard_install_plugins($nonce);
                if(data.data.activateUrl){
                    gd_wizard_activate_plugin(data.data.activateUrl,$slug);
                }
            }else{
                alert('something went wrong');
            }
        }
    });
}

/**
 * Try to silently activate the plugin after install.
 *
 * @param $url
 */
function gd_wizard_activate_plugin($url,$slug){
    jQuery.post($url, function(data, status){
        console.log($slug+'plugin activated')
    });
}

function gd_wizard_install_plugins($nonce){
    //alert($slug);

    var $result = '';
    jQuery('.gd_install_plugins').each(function() {
        if(this.checked){
            console.log(this.id);
            $result = gd_wizard_install_plugin(this.id,$nonce);
            jQuery('.gd-install-recommend').prop("disabled", true);
            return false;// break so we run from next function
        }
    });
}

function gd_wizard_check_plugins(){
    var $install = '';
    jQuery('.gd_install_plugins').each(function() {
        $install += this.checked ? "1," : "";
    });
    console.log($install);

    if($install){
        jQuery('.gd-install-recommend').show();
        jQuery('.gd-continue-recommend').hide();
    }else{
        jQuery('.gd-install-recommend').hide();
        jQuery('.gd-continue-recommend').show();
    }
}

jQuery(function() {
    gd_wizard_check_plugins();

    jQuery('.gd_install_plugins').click(function() {
        gd_wizard_check_plugins();
    });
});