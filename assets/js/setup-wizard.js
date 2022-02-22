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
            jQuery( ".geodir-wizard-widgets-top-result" ).html('<div class="spinner-border text-dark" role="status"></div>');

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
            jQuery( ".geodir-wizard-widgets-result" ).html('<div class="spinner-border text-dark" role="status"></div>');

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
            jQuery( ".geodir-wizard-menu-result" ).html('<div class="spinner-border text-dark" role="status"></div>');

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

function gd_wizard_install_plugin($slug,$nonce,$activate){
    // alert($slug);

    var data = {
        'action':           'install-plugin',
        '_ajax_nonce':       $nonce,
        'slug':              $slug
    };

    $id =  jQuery('#'+$slug).data("id");
    if($id) {
        $licence =  jQuery('#'+$slug).data("key");

        if($licence && $licence!='free'){
            data.license = $licence;
            data.wpeu_activate = 1; // activate the licence first or it won't allow download from the url.
        }else if($licence=='free'){
            data.free_download = '1'; // requires EDD free downloads to work
        }

        data.item_id = $id;
        data.update_url = 'https://wpgeodirectory.com/';
    }

    $ajaxurl = ajaxurl;
    if($activate){
        data = {};
        $ajaxurl = $activate;
    }else if($id){
        $url = jQuery('#'+$slug).data("activateurl");
    }

    console.log(data);

    jQuery.ajax({
        type: "POST",
        url: $ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery( "."+$slug + " .gd-plugin-status").removeClass('d-none').html(jQuery('#gd-installing-text').val());

        },
        success: function(data)
        {
            // if(data.data){
            //     jQuery( ".geodir-wizard-widgets-result" ).text(data.data);
            // }
            console.log(data);
            if(data.success || $activate){
                jQuery( "."+$slug + " .gd-plugin-status").html(jQuery('#gd-installed-text').val());
                jQuery( "."+$slug + " input:checkbox").removeClass('gd_install_plugins').prop("disabled", true);
                gd_wizard_install_plugins($nonce);
                gd_wizard_check_plugins();

                if($id && $url){
                    gd_wizard_activate_plugin($url,$slug);
                }else if(!$activate && data.data.activateUrl){
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

    // disable buttons
    jQuery('.gd-install-recommend,.gd-install-skip').addClass('d-none');
    jQuery('.gd-installing').removeClass('d-none');

    // return;

    var $result = '';
    jQuery('.gd_install_plugins').each(function() {
        if(this.checked){
            console.log(this.id);
            $status = jQuery(this).data("status");
            $slug = jQuery(this).data("slug");
            $url = jQuery(this).data("activateurl");
            if($status=='install'){
                $result = gd_wizard_install_plugin(this.id,$nonce);
            }else{
                $result = gd_wizard_install_plugin(this.id,$nonce,$url);
            }

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
        jQuery('.gd-install-recommend').removeClass('d-none');
        jQuery('.gd-continue-recommend').addClass('d-none');
    }else{
        jQuery('.gd-installing').addClass('d-none');
        jQuery('.gd-install-recommend').addClass('d-none');
        jQuery('.gd-continue-recommend').removeClass('d-none');
    }
}

jQuery(function() {
    gd_wizard_check_plugins();

    jQuery('.gd_install_plugins').on("click",function() {
        gd_wizard_check_plugins();
    });

    jQuery('#gd-wizard-save-map-key').submit(function(event) {
        $api_key = jQuery('#google_maps_api_key').val();
        if($api_key && $api_key!=jQuery('#google_maps_api_key').data("key-original")){
            jQuery('#geodir_validate_google_api_key_error_project,#geodir_validate_google_api_key_error_invalid,#geodir_validate_google_api_key_error_referer').toast('hide');
            geodir_validate_google_api_key($api_key,'google_maps_api_key');
            event.preventDefault();

            setTimeout(function (){

                // if no error then try to submit again
                if( gd_has_map_error ){
                    $btn_val = jQuery('.submit-btn').data("continue-text");
                    jQuery('.submit-btn').removeClass('disabled').html( $btn_val );
                }else{
                    jQuery('#google_maps_api_key').data("key-original",$api_key);
                    jQuery('#gd-wizard-save-map-key .submit-btn').trigger("click");
                }

            }, 8000);

        }
    });

});