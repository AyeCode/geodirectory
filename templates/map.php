<?php
/**
 * Displays the map in add listing map
 *
 * @since 1.0.0
 * @since 1.5.6 Fixed breaking maps when there is an apostrophe in location name.
 * @since 1.6.11 Fix: OpenStreetMap does not working when third party plugin using the Google Maps JS API.
 * @package GeoDirectory
 */

/**
 * @global int $mapzoom Zoom level value for the map.
 * @global bool $geodir_manual_map Check if manual map.
 */
global $mapzoom, $geodir_manual_map;

/**
 * Filter the map restriction for specific address only
 *
 * @since 1.0.0
 *
 * @param bool $var Whether to ristrict the map for specific address only.
 */
$is_map_restrict = apply_filters('geodir_add_listing_map_restrict', true);

/**
 * Filter the auto change address fields values when moving the map pin
 *
 * @since 1.4.8
 *
 * @param bool $var Whether to change the country, state, city values in fields.
 */
$auto_change_address_fields_pin_move = apply_filters('geodir_auto_change_address_fields_pin_move', true);
global $geodirectory;
$default_location = $geodirectory->location->get_default_location();
$defaultcity = isset($default_location->city) ? $default_location->city : '';
$lat_lng_blank = false;
if ( (!isset($lat) || $lat == '' ) && (!isset($lat) || $lng == '')) {
    $lat_lng_blank = true;
    $city = $defaultcity;
    $region = isset($default_location->region) ? $default_location->region : '';
    $country = isset($default_location->country) ? $default_location->country : '';
    $lng = isset($default_location->longitude) ? $default_location->longitude : '';
    $lat = isset($default_location->latitude) ? $default_location->latitude : '';
}
$default_lng = isset($default_location->longitude) ? $default_location->longitude : '';
$default_lat = isset($default_location->latitude) ? $default_location->latitude : '';
if (is_admin() && isset($_REQUEST['tab']) && $mapzoom == '') {
    $mapzoom = 4;
    if (isset($_REQUEST['add_hood']))
        $mapzoom = 10;
}

/**
 * Filter the auto change address fields values
 *
 * @since 1.0.0
 *
 * @param bool $var Whether to auto fill country, state, city values in fields.
 */
$auto_change_map_fields = apply_filters('geodir_auto_change_map_fields', true);
$marker_icon = GeoDir_Maps::default_marker_icon( true );
$icon_size = GeoDir_Maps::get_marker_size($marker_icon, array('w' => 20, 'h' => 34));
?>
<script type="text/javascript">
    /* <![CDATA[ */
    <?php 
	/**
	 * Fires at the start of the add javascript on the add lsitings map.
	 *
	 * @since 1.0.0
     * @param string $prefix The prefix for all elements.
	 */
	do_action('geodir_add_listing_js_start', $prefix);
	?>
    if ((window.gdSetMap=='google' || window.gdSetMap=='auto') && window.google && typeof google.maps!=='undefined') {
        gdMaps = 'google';
    } else if ((window.gdSetMap=='osm' || window.gdSetMap=='auto') && typeof L!=='undefined' && typeof L.version!=='undefined') {
        gdMaps = 'osm';
    } else {
        gdMaps = null;
    }
    window.gdMaps = window.gdMaps || gdMaps;
    
    user_address = false;
    jQuery('#<?php echo $prefix.'street';?>').keypress(function () {
        user_address = true;
    });

    baseMarker = '';
    geocoder = '';
    var <?php echo $prefix;?>CITY_MAP_CENTER_LAT = <?php echo ($lat) ? $lat :  '39.952484'; ?>;
    var <?php echo $prefix;?>CITY_MAP_CENTER_LNG = <?php echo ($lng) ? $lng :  '-75.163786'; ?>;
    <?php if($lat_lng_blank){$lat='';$lng='';}?>
    var <?php echo $prefix;?>CITY_MAP_ZOOMING_FACT = <?php echo ($mapzoom) ? $mapzoom : 12;?>;
    var minZoomLevel = <?php echo ($is_map_restrict) ? 5 : 0; ?>;
    var oldstr_address;
    var oldstr_address2;
    var oldstr_zip;
    var strictBounds;
    var doingGeocode = false;
    function geocodePosition(latLon, address) {
        console.log(address);
        if (address && (address.locality || address.postal_town) && address.country!='TR' && address.country!='SG' ) {// turkey select address does not return enough info so we get info from GPS only.
            doGeoCode = address;
        } else {
            doGeoCode = {
                latLng: baseMarker.getPosition()
            };
        }

        geocoder.geocode(doGeoCode, function (responses) {
            geocodeResponse(responses);
        });
    }
    
    function geocodeResponse(responses) {
        console.log(responses);//keep this for debugging
        if (responses && responses.length > 0) {
            var getAddress = '';
            var getAddress2 = '';
            var getZip = '';
            var getCity = '';
            var getState = '';
            var getCountry = '';

            getCountryISO = '';

            street_number = '';
            premise = ''; // In Russian ;
            establishment = '';
            route = '';
            administrative_area_level_1 = '';
            administrative_area_level_2 = '';
            administrative_area_level_3 = '';
            sublocality_level_1 = '';
            postal_town = '';
            locality = '';
            country = '';
            postal_code = '';
            postal_code_prefix = '';
            rr = '';
            has_address_been_set = false;

            // get the proper response as somtimes the GPS results will return names in English when they should not.
            responses.forEach(function(response) {
                if(response.types[0] == "locality"){
                    for (var i = 0; i < response.address_components.length; i++) {
                        var addr = response.address_components[i];
                        if (addr.types[0] == 'administrative_area_level_1') {
                            administrative_area_level_1 = addr;
                        }
                        if (addr.types[0] == 'administrative_area_level_2') {
                            administrative_area_level_2 = addr;
                        }
                        if (addr.types[0] == 'administrative_area_level_3') {
                            administrative_area_level_3 = addr;
                        }
                        if (addr.types[0] == 'sublocality_level_1') {
                            sublocality_level_1 = addr;
                        }
                        if (addr.types[0] == 'postal_town') {
                            postal_town = addr;
                        }
                        if (addr.types[0] == 'locality') {
                            locality = addr;
                        }
                        if (addr.types[0] == 'premise') {
                            premise = addr;alert(4);
                        }
                        if (addr.types[0] == 'establishment') {
                            establishment = addr;alert(5);
                        }
                    }
                }
            });

            for (var i = 0; i < responses[0].address_components.length; i++) {
                var addr = responses[0].address_components[i];
                if (addr.types[0] == 'street_number') {
                    street_number = addr;
                }
                if (addr.types[0] == 'route') {
                    route = addr;
                }
                if (addr.types[0] == 'premise') {
                    premise = addr;
                }
                if (administrative_area_level_1 == '' && addr.types[0] == 'administrative_area_level_1') {
                    administrative_area_level_1 = addr;
                }
                if (administrative_area_level_2 == '' && addr.types[0] == 'administrative_area_level_2') {
                    administrative_area_level_2 = addr;
                }
                if (administrative_area_level_3 == '' && addr.types[0] == 'administrative_area_level_3') {
                    administrative_area_level_3 = addr;
                }
                if (sublocality_level_1 == '' && addr.types[0] == 'sublocality_level_1') {
                    sublocality_level_1 = addr;
                }
                if (postal_town == '' && addr.types[0] == 'postal_town') {
                    postal_town = addr;
                }
                if (locality == '' && addr.types[0] == 'locality') {
                    locality = addr;
                }
                if (addr.types[0] == 'country') {
                    country = addr;
                }
                if (addr.types[0] == 'postal_code') {
                    postal_code = addr;
                }
                if (addr.types[0] == 'postal_code_prefix') {
                    postal_code_prefix = addr;
                }

                if (addr.types[0] == 'establishment') {
                    establishment = addr;
                }

                if (postal_code == '') {
                    postal_code = postal_code_prefix;
                }
                if (responses[0].formatted_address != '') {
                    address_array = responses[0].formatted_address.split(",", 2);
                    
                    if (address_array.length > 1) {
                        if (!(typeof(street_number.long_name) == 'undefined' || street_number.long_name == null) && street_number.long_name.toLowerCase() == address_array[0].toLowerCase().trim()) {
                            getAddress = street_number.long_name + ', ' + address_array[1];
                        }

                        if (getAddress == '' && !(typeof(street_number.long_name) == 'undefined' || street_number.long_name == null) && street_number.long_name.toLowerCase() == address_array[1].toLowerCase().trim()) {
                            getAddress = address_array[0] + ', ' + street_number.long_name;
                        }

                        if (getAddress == '' && !(typeof(street_number.short_name) == 'undefined' || street_number.short_name == null) && street_number.short_name.toLowerCase() == address_array[0].toLowerCase().trim()) {
                            getAddress = street_number.short_name + ', ' + address_array[1];
                        }

                        if (getAddress == '' && !(typeof(street_number.short_name) == 'undefined' || street_number.short_name == null) && street_number.short_name.toLowerCase() == address_array[1].toLowerCase().trim()) {
                            getAddress = address_array[0] + ', ' + street_number.short_name;
                        }

                        if (getAddress == '' && !(typeof(premise.long_name) == 'undefined' || premise.long_name == null) && premise.long_name.toLowerCase() == address_array[0].toLowerCase().trim()) {
                            getAddress = premise.long_name + ', ' + address_array[1];
                        }

                        if (getAddress == '' && !(typeof(premise.long_name) == 'undefined' || premise.long_name == null) && premise.long_name.toLowerCase() == address_array[1].toLowerCase().trim()) {
                            getAddress = address_array[0] + ', ' + premise.long_name;
                        }

                        if (getAddress == '' && !(typeof(premise.short_name) == 'undefined' || premise.short_name == null) && premise.short_name.toLowerCase() == address_array[0].toLowerCase().trim()) {
                            getAddress = premise.short_name + ', ' + address_array[1];
                        }

                        if (getAddress == '' && !(typeof(premise.short_name) == 'undefined' || premise.short_name == null) && premise.short_name.toLowerCase() == address_array[1].toLowerCase().trim()) {
                            getAddress = address_array[0] + ', ' + premise.short_name;
                        }

                        if (getAddress == '') {
                            getAddress = 'none'
                        }
                    }
                }
            }

            // if establishment then grab second arr
            if (getAddress == 'none' && typeof(establishment.long_name) !== 'undefined' && typeof(address_array[1]) !== 'undefined') {
                getAddress = address_array[1];
                getAddress2 = address_array[0];
            } else if(getAddress == 'none' ) {/* added to fix street number for RU locations */
                getAddress = address_array[0];
            }

            // address2
            if(premise.long_name && premise.long_name != getAddress){
                getAddress2 = premise.long_name;
            }

            if (getAddress == '') {
                if (street_number.long_name)
                    getAddress += street_number.long_name + ' ';//street_number
                if (route.long_name)
                    getAddress += route.long_name;//route
            }

            getZip = postal_code.long_name;//postal_code

            //getCountry
            if (country.long_name) {
                getCountry = country.long_name;
            }
            if (country.short_name) {
                getCountryISO = country.short_name;
            }

            //getState
            if (country.short_name) {
                rr = country.short_name;
            }

            //$country_arr = ["US", "CA", "IN","DE","NL"];
            // fix for regions in GB
            $country_arr = <?php
            /**
             * Filter the regions array that uses administrative_area_level_2 instead of administrative_area_level_1.
             *
             * @since 1.6.16
             */
            echo apply_filters("geodir_geocode_region_level",'["GB","ES"]');?>;
            if (jQuery.inArray(rr, $country_arr) !== -1) {
                if (administrative_area_level_2.long_name) {
                    getState = administrative_area_level_2.long_name;
                }
                else if (administrative_area_level_1.long_name) {
                    getState = administrative_area_level_1.long_name;
                }
            } else {
                if (administrative_area_level_1.long_name) {
                    getState = administrative_area_level_1.long_name;
                }
                else if (administrative_area_level_2.long_name) {
                    getState = administrative_area_level_2.long_name;
                }
            }

            // fix some countries without regions, Isle of Man, Singapore
            if(getCountryISO=='IM'){
                getState = "Isle of Man";
            }else if(getCountryISO=='SG'){
                getState = "Singapore";
            }

            /* Fix region name for ‎Belgium */
            if (getState == 'Brussels Hoofdstedelijk Gewest') {
                getState = 'Brussels';
            }

            //getCity
            // fix for cities in Ireland
            $country_arr2 = ["IE"];
            if (jQuery.inArray(rr, $country_arr2) !== -1) {
                if (administrative_area_level_2.long_name && administrative_area_level_2.long_name.indexOf(" City") >= 0) {
                    getCity = administrative_area_level_2.long_name;
                }
                else if (locality.long_name) {
                    getCity = locality.long_name;
                }
                else if (postal_town.long_name) {
                    getCity = postal_town.long_name;
                }
                else if (sublocality_level_1.long_name) {
                    getCity = sublocality_level_1.long_name;
                }
                else if (administrative_area_level_3.long_name) {
                    getCity = administrative_area_level_3.long_name;
                }
            } else if(rr=="TR") {
                if (locality.long_name) {
                    getCity = locality.long_name;
                }else if (postal_town.long_name) {
                    getCity = postal_town.long_name;
                }
                else if (sublocality_level_1.long_name) {
                    getCity = sublocality_level_1.long_name;
                }
                else if (administrative_area_level_3.long_name) {
                    getCity = administrative_area_level_3.long_name;
                }
                else if (administrative_area_level_1.long_name) {
                    getCity = administrative_area_level_1.long_name;
                }
            }else if(rr=="FR") {
                if (administrative_area_level_2.long_name=='Paris') {
                    getCity = administrative_area_level_2.long_name;
                }else{
                    if (locality.long_name) {
                        getCity = locality.long_name;
                    }else if (postal_town.long_name) {
                        getCity = postal_town.long_name;
                    }
                    else if (sublocality_level_1.long_name) {
                        getCity = sublocality_level_1.long_name;
                    }
                    else if (administrative_area_level_3.long_name) {
                        getCity = administrative_area_level_3.long_name;
                    }
                    else if (administrative_area_level_1.long_name) {
                        getCity = administrative_area_level_1.long_name;
                    }
                }
            }else {
                if (locality.long_name) {
                    getCity = locality.long_name;
                }else if (postal_town.long_name) {
                    getCity = postal_town.long_name;
                }
                else if (sublocality_level_1.long_name) {
                    getCity = sublocality_level_1.long_name;
                }
                else if (administrative_area_level_3.long_name) {
                    getCity = administrative_area_level_3.long_name;
                }
            }

            //getCountry 
            if (country.long_name) {
                getCountry = country.long_name;
            }
            //getZip 
            if (postal_code.long_name) {
                getZip = postal_code.long_name;
            }
            console.log(getAddress+', '+getCity+', '+getState+', '+getCountry);
            <?php 
            /**
             * Fires to add javascript variable to use in google map.
             *
             * @since 1.0.0
             */
            do_action('geodir_add_listing_geocode_js_vars');
            ?>
            <?php if ($is_map_restrict) { ?>
            if (getCity.toLowerCase() != '<?php echo geodir_strtolower(addslashes_gpc($city));?>') {
                alert('<?php echo addslashes_gpc(wp_sprintf(__('Please choose any address of the (%s) city only.','geodirectory'), $city));?>');
                jQuery("#<?php echo $prefix.'map';?>").goMap();
                jQuery.goMap.map.setCenter(new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
                baseMarker.setPosition(new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
                updateMarkerPosition(baseMarker.getPosition());
                //geocodePosition(baseMarker.getPosition());
                setTimeout(function(){jQuery('#address_street,#address_zip').val('');}, 100);
            }
            <?php } ?>
            updateMarkerAddress(getAddress, getZip, getCity, getState, getCountry, getAddress2);
        } else {
            <?php 
            /**
             * Fires to add javascript variable to use in google map.
             *
             * @since 1.0.0
             */
            do_action( 'geodir_add_listing_geocode_response_fail' );
            ?>
			updateMarkerAddress('<?php echo addslashes_gpc(__('Cannot determine address at this location.','geodirectory'));?>');
        }
    }
    function centerMap(latlng) {
        jQuery("#<?php echo $prefix.'map';?>").goMap();
        if (window.gdMaps == 'google') {
            jQuery.goMap.map.panTo(baseMarker.getPosition());
        } else if (window.gdMaps == 'osm') {
            latlng = latlng ? latlng : baseMarker.getLatLng();
            jQuery.goMap.map.panTo(latlng);
        }
    }
    function centerMarker() {
        jQuery("#<?php echo $prefix.'map';?>").goMap();
        var center = jQuery.goMap.map.getCenter();
        if (window.gdMaps == 'google') {
            baseMarker.setPosition(center);
        } else if (window.gdMaps == 'osm') {
            baseMarker.setLatLng(center);
        }
    }
    function updateMapZoom(zoom) {
        jQuery('#<?php echo $prefix.'mapzoom';?>').val(zoom);
    }
    function updateMarkerPosition(markerlatLng) {
        jQuery("#<?php echo $prefix.'map';?>").goMap();
        jQuery('#<?php echo $prefix.'latitude';?>').val(markerlatLng.lat()).trigger('change');
        jQuery('#<?php echo $prefix.'longitude';?>').val(markerlatLng.lng()).trigger('change');
    }
    function updateMarkerPositionOSM(markerlatLng) {
        jQuery('#<?php echo $prefix.'latitude';?>').val(markerlatLng.lat).trigger('change');
        jQuery('#<?php echo $prefix.'longitude';?>').val(markerlatLng.lng).trigger('change');
    }
    function updateMarkerAddress(getAddress, getZip, getCity, getState, getCountry, getAddress2) {
        var set_map_val_in_fields = '<?php echo addslashes_gpc($auto_change_map_fields);?>';
        <?php ob_start();?>
        var old_country = jQuery("#<?php echo $prefix.'country';?>").val();
        var old_region = jQuery("#<?php echo $prefix.'region';?>").val();
        var old_city = jQuery("#<?php echo $prefix.'city';?>").val();
        var old_zip = jQuery("#<?php echo $prefix.'zip';?>").val();

        if (user_address == false || jQuery('#<?php echo $prefix.'street';?>').val() == '') {
            jQuery("#<?php echo $prefix.'street';?>").val(getAddress).trigger("blur");
        }
        if (getAddress) {
            oldstr_address = getAddress;
        }
        if (user_address == false || jQuery('#<?php echo $prefix.'street2';?>').val() == '') {
            jQuery("#<?php echo $prefix.'street2';?>").val(getAddress2);
        }
        if (getAddress2) {
            oldstr_address2 = getAddress2;
        }

        var updateZip = true;
        if (!getZip && old_zip && old_city && old_city == getCity) {
            updateZip = false;
        }
        if (updateZip) {
            jQuery("#<?php echo $prefix.'zip';?>").val(getZip);
        }
        if (getZip) {
            oldstr_zip = getZip;
        }
        if (set_map_val_in_fields) {
            if (getCountry) {
               setCountry = jQuery('#<?php echo $prefix . 'country'; ?> option[data-country_code="' + getCountryISO + '"]').val();
               if (!setCountry) {
                   setCountry = getCountry;
               } else {
                   getCountry = setCountry;
               }
               jQuery("#<?php echo $prefix . 'country'; ?>").val(setCountry).trigger('change.select2');
            }
            if (getState) {
                if (jQuery("input#<?php echo $prefix . 'region'; ?>").length) {
                    jQuery("#<?php echo $prefix . 'region'; ?>").val(getState);
                }
            }
            if (getCity) {
                if (jQuery("input#<?php echo $prefix . 'city'; ?>").length) {
                    jQuery("#<?php echo $prefix . 'city'; ?>").val(getCity);
                }
            }
        }
        <?php
        /**
         * Fires when marker address updated on map.
         *
         * @since 1.0.0
         * @param string $prefix Identifier used as a prefix for field name
         */
        do_action('geodir_update_marker_address', $prefix);
        echo $updateMarkerAddress = ob_get_clean();
        ?>
    }
    function geodir_codeAddress(set_on_map) {
        var address = jQuery('#<?php echo $prefix.'street';?>').val();
        var zip = jQuery('#<?php echo $prefix.'zip';?>').val();
        var city = jQuery('#<?php echo $prefix.'city';?>').val();
        var region = jQuery('#<?php echo $prefix.'region';?>').val();
        var country = jQuery('#<?php echo $prefix.'country';?>').val();
        var country_selected = jQuery('#<?php echo $prefix.'country';?>').find('option:selected');
        var ISO2 = country_selected.data('country_code');
        if (!ISO2 && jQuery('#<?php echo $prefix.'country';?>').data('country_code')) {
            ISO2 = jQuery('#<?php echo $prefix.'country';?>').data('country_code');
        }
        if(!ISO2){
            <?php
            if(!defined('GEODIRLOCATION_TEXTDOMAIN')){
                global $wpdb;
                $location_result = $geodirectory->location->get_default_location();
                if(!empty($location_result)){
                    $ISO2 = wp_country_database()->get_country_iso2($location_result->country);
                    echo "ISO2 = '$ISO2';";
                }
            }


            ?>
        }
        if (ISO2 == '--') {
            ISO2 = '';
        }
        
        if (typeof zip == "undefined") {
            zip = '';
        }
        if (typeof city == "undefined") {
            city = '<?php echo addslashes_gpc($city);?>';
        }
        if (typeof region == "undefined") {
            region = '<?php echo addslashes_gpc($region);?>';
        }
        if (typeof country == "undefined") {
            country = '<?php echo addslashes_gpc($country);?>';
        }
        var is_restrict = '<?php echo $is_map_restrict; ?>';
        <?php ob_start();
        $defaultregion = isset($default_location->region) ? $default_location->region : '';
        $defaultcountry = isset($default_location->country) ? $default_location->country : '';
        ?>
        if (set_on_map && is_restrict) {
            if (zip != '' && address != '') {
                address = address + ',' + zip;
            }
        } else {
            if (typeof address === 'undefined')
                address = '';

            if( address == city || address == region || address == country || address == zip )
                address = '';
            <?php
            if(is_admin() && isset($_REQUEST['tab'])){?>
            if (jQuery.trim(city) == '' || jQuery.trim(region) == '') {
                address = '';
            }
            <?php
               }?>

            if (ISO2 == 'GB') {
                address = address + ',' + city + ',' + country; // UK is funny with regions
            } else {
                address = address + ',' + city + ',' + region + ',' + country;
            }

            if(zip!=''){
                address = address + ',' + zip;
            }

            // incase there are any null values
            address =  address.replace(",null,", ",");
        }
        if (address) {
            address =  address.replace(",null,", ",");
        }
        <?php $codeAddress = ob_get_clean();
        /**
         * Filter the address variable
         *
         * @since 1.0.0
         *
         * @param string $codeAddress Row of address to use in google map.
         */
        echo apply_filters('geodir_codeaddress', $codeAddress);
        ?>
        if (!window.gdMaps) { // No Google Map Loaded
            return;
        }
        if (address && address != '') {
            // Replace one or more commas in a row.
            address = address.replace(/,+/g,',');
            address = address.replace(/(^,)|(,$)/g, "");
        }
        if ( window.gdMaps == 'osm' ) {
            if (address != '') {
                if (zip != '') {
                    searchZip = "," + zip;
                    var nAddress = address.toLowerCase().lastIndexOf(searchZip.toLowerCase());
                    address = address.slice(0, nAddress) + address.slice(nAddress).replace(new RegExp(searchZip, 'i'), "");
                }
				<?php 
				/**
				 * Fires before set geocode position.
				 *
				 * @since 1.0.0
				 */
				do_action('geodir_add_listing_codeaddress_before_geocode');
				?>
                geocodePositionOSM('', address, ISO2, true);
            }
        } else {
            geocoder.geocode({'address': address, 'country': ISO2},
                function (results, status) {
                    console.log(status);
                    jQuery("#<?php echo $prefix.'map';?>").goMap();
                    if (status == google.maps.GeocoderStatus.OK) {
                        console.log(results[0]);
                        baseMarker.setPosition(results[0].geometry.location);
                        jQuery.goMap.map.setCenter(results[0].geometry.location);
                        updateMarkerPosition(baseMarker.getPosition());
                        //if(set_on_map && is_restrict) {
                        <?php 
                        /**
                         * Fires before set geocode position.
                         *
                         * @since 1.0.0
                         */
                        do_action('geodir_add_listing_codeaddress_before_geocode');
                        ?>
                        geocodePosition(baseMarker.getPosition(), {'address': address, 'country': ISO2});
                        //}
                    } else {
                        alert('<?php echo addslashes_gpc(__('Geocode was not successful for the following reason:','geodirectory'));?> ' + status);
                    }
                });
        }
    }
    function gdMaxMap() {
        jQuery("#<?php echo $prefix.'map';?>").goMap();

        jQuery('#<?php echo $prefix.'map';?>').toggleClass('map-fullscreen');
        jQuery('.map_category').toggleClass('map_category_fullscreen');
        jQuery('#<?php echo $prefix;?>trigger').toggleClass('map_category_fullscreen');
        jQuery('body').toggleClass('body_fullscreen');
        jQuery('#<?php echo $prefix;?>loading_div').toggleClass('loading_div_fullscreen');
        jQuery('#<?php echo $prefix;?>advmap_nofound').toggleClass('nofound_fullscreen');
        jQuery('#<?php echo $prefix;?>triggermap').toggleClass('triggermap_fullscreen');
        jQuery('.TopLeft').toggleClass('TopLeft_fullscreen');
        window.setTimeout(function () {
            if (window.gdMaps == 'google') {
                google.maps.event.trigger($.goMap, 'resize');
            } else if (window.gdMaps == 'osm') {
                jQuery.goMap.map.invalidateSize();
                jQuery.goMap.map._onResize();
            }                
        }, 100);
    }

    function gd_get_street2($response){
        var $street2 = '';

        if($response.address.building){
            $street2 = $response.address.building;
        }else if($response.address.department_store){
            $street2 = $response.address.department_store;
        }else if($response.address.hotel){
            $street2 = $response.address.hotel;
        }

        return $street2;
    }
    
    function geocodeResponseOSM(response, updateMap) {
        console.log(response);
        if (response.display_address) {
            var getAddress = response.display_address;
            var getAddress2 = gd_get_street2(response);
            var getZip = response.postcode;
            var getCity = response.city;
            var getState = response.state;
            var getCountry = response.country;
            getCountryISO = response.country_code;
            console.log(getAddress+', '+getCity+', '+getState+', '+getCountry);
            if (updateMap && response.lat && response.lon) {
                var newLatLng = new L.latLng(response.lat, response.lon);
                baseMarker.setLatLng(newLatLng);
                centerMap(newLatLng);
                updateMarkerPositionOSM(baseMarker.getLatLng());
            }
            <?php
            /**
             * Fires to add javascript variable to use in google map.
             *
             * @since 1.0.0
             */
            do_action('geodir_add_listing_geocode_js_vars');
            ?>
            <?php if ($is_map_restrict) { ?>
            if (getCity.toLowerCase() != '<?php echo geodir_strtolower(addslashes_gpc($city));?>') {
                alert('<?php echo addslashes_gpc(wp_sprintf(__('Please choose any address of the (%s) city only.','geodirectory'), $city));?>');
                jQuery("#<?php echo $prefix.'map';?>").goMap();
                centerMap(new L.latLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
                baseMarker.setLatLng(new L.latLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
                updateMarkerPositionOSM(baseMarker.getLatLng());
                geocodePositionOSM(baseMarker.getLatLng());
            }
            <?php } ?>
            updateMarkerAddress(getAddress, getZip, getCity, getState, getCountry,getAddress2);
        } else {
            <?php 
            /**
             * Fires to add javascript variable to use in google map.
             *
             * @since 1.0.0
             */
            do_action( 'geodir_add_listing_geocode_response_fail' );
            ?>
			alert('<?php echo addslashes_gpc(__('Cannot determine address at this location.','geodirectory'));?>');
        }
    }

    <?php $geodir_map_name = GeoDir_Maps::active_map();
    if($geodir_map_name!='none'){ ?>
    jQuery(function ($) {
		<?php if ( geodir_lazy_load_map() ) { ?>
		jQuery("#<?php echo $prefix.'map';?>").geodirLoadMap({
		loadJS: true,
		forceLoad: <?php echo ( isset( $geodir_manual_map ) && $geodir_manual_map ? 'true' : 'false' ); ?>,
		callback: function() {<?php } ?>
        var $addressMap = $("#<?php echo $prefix.'map';?>").goMap({
            latitude: <?php echo $prefix;?>CITY_MAP_CENTER_LAT,
            longitude: <?php echo $prefix;?>CITY_MAP_CENTER_LNG,
            zoom: <?php echo $prefix;?>CITY_MAP_ZOOMING_FACT,
            maptype: 'ROADMAP', // Map type - HYBRID, ROADMAP, SATELLITE, TERRAIN
            streetViewControl: true,
            <?php if(geodir_get_option('geodir_add_listing_mouse_scroll')) { echo 'scrollwheel: false,';}?>
			<?php do_action( 'geodir_template_render_map_js_params' ); ?>
        });

        if (window.gdMaps) {
            geocoder = window.gdMaps == 'google' ? new google.maps.Geocoder() : [];

            baseMarker = $.goMap.createMarker({
                latitude: <?php echo $prefix;?>CITY_MAP_CENTER_LAT,
                longitude: <?php echo $prefix;?>CITY_MAP_CENTER_LNG,
                id: 'baseMarker',
                icon: '<?php echo $marker_icon;?>',
                draggable: true,
                addToMap: true, // For OSM
                w: parseFloat('<?php echo $icon_size['w'];?>'),
                h: parseFloat('<?php echo $icon_size['h'];?>'),
            });
        } else {
            jQuery('#<?php echo $prefix.'advmap_nofound';?>').hide();
            jQuery('#<?php echo $prefix.'advmap_notloaded';?>').show();
        }
        
        $("#<?php echo $prefix;?>set_address_button").click(function () {
            var set_on_map = true;
            geodir_codeAddress(set_on_map);
        });
        
        if (window.gdMaps == 'google') {
            // Add dragging event listeners.
            google.maps.event.addListener(baseMarker, 'dragstart', function () {
            //updateMarkerAddress('Dragging...');
            });
            google.maps.event.addListener(baseMarker, 'drag', function () {
                // updateMarkerStatus('Dragging...');
                updateMarkerPosition(baseMarker.getPosition());
            });
            google.maps.event.addListener(baseMarker, 'dragend', function () {
                // updateMarkerStatus('Drag ended');
                centerMap();
                <?php if ($auto_change_address_fields_pin_move) { ?>
                geocodePosition(baseMarker.getPosition());
                <?php } ?>
                updateMarkerPosition(baseMarker.getPosition());
            });
            google.maps.event.addListener($.goMap.map, 'dragend', function () {
                // updateMarkerStatus('Drag ended');
                centerMarker();
                <?php if ($auto_change_address_fields_pin_move) { ?>
                geocodePosition(baseMarker.getPosition());
                <?php } ?>

                updateMarkerPosition(baseMarker.getPosition());
            });
            google.maps.event.addListener($.goMap.map, 'zoom_changed', function () {
				if (typeof $.goMap.map === 'undefined') {
					$.goMap.map = $addressMap;
				}
                updateMapZoom($.goMap.map.zoom);
            });

            var maxMap = document.getElementById('<?php echo $prefix;?>triggermap');
            google.maps.event.addDomListener(maxMap, 'click', gdMaxMap);

            <?php if ($is_map_restrict) { ?>
            var CITY_ADDRESS = '<?php echo addslashes_gpc($city).','.addslashes_gpc($region).','.addslashes_gpc($country);?>';
            geocoder.geocode({'address': CITY_ADDRESS},
                function (results, status) {
                    $("#<?php echo $prefix.'map';?>").goMap();
                    if (status == google.maps.GeocoderStatus.OK) {
                        // Bounds for North America
                        var bound_lat_lng = String(results[0].geometry.bounds);
                        bound_lat_lng = bound_lat_lng.replace(/[()]/g, "");
                        bound_lat_lng = bound_lat_lng.split(',');
                        strictBounds = new google.maps.LatLngBounds(
                            new google.maps.LatLng(bound_lat_lng[0], bound_lat_lng[1]),
                            new google.maps.LatLng(bound_lat_lng[2], bound_lat_lng[3])
                        );
                    } else {
                        alert("<?php _e('Geocode was not successful for the following reason:','geodirectory');?> " + status);
                    }
                });
            <?php } ?>
            // Limit the zoom level
            google.maps.event.addListener($.goMap.map, 'zoom_changed', function () {
                $("#<?php echo $prefix.'map';?>").goMap();
                if ($.goMap.map.getZoom() < minZoomLevel) $.goMap.map.setZoom(minZoomLevel);
            });
        } else if (window.gdMaps == 'osm') {
            // Add dragging event listeners.
            baseMarker.on('drag', function(e) {
                updateMarkerPositionOSM(baseMarker.getLatLng());
            });
            baseMarker.on('dragend', function(e) {
                centerMap();
                <?php if ($auto_change_address_fields_pin_move) { ?>
                geocodePositionOSM(baseMarker.getLatLng());
                <?php } ?>
                updateMarkerPositionOSM(baseMarker.getLatLng());
            });
            $.goMap.map.on('dragend', function(e) {
                <?php if ($auto_change_address_fields_pin_move) { ?>
                geocodePositionOSM(baseMarker.getLatLng());
                <?php } ?>
                centerMarker();
                updateMarkerPositionOSM(baseMarker.getLatLng());
            });
            $.goMap.map.on('zoom', function(e) {
				if (typeof $.goMap.map === 'undefined') {
					$.goMap.map = $addressMap;
				}
                updateMapZoom($.goMap.map.getZoom());
            });

            L.DomEvent.addListener($('<?php echo $prefix;?>triggermap'), 'click', gdMaxMap);

            <?php if ($is_map_restrict) { ?>
            var CITY_ADDRESS = '<?php echo addslashes_gpc($city).', '.addslashes_gpc($region).', '.addslashes_gpc($country);?>';
            <?php } ?>
            // Limit the zoom level
            $.goMap.map.on('zoom', function(e) {
                if ($.goMap.map.getZoom() < minZoomLevel) {
                    $.goMap.map.setZoom(minZoomLevel);
                }
            });
        }<?php if ( geodir_lazy_load_map() ) { ?>
		}
	});<?php } ?>
    });
    <?php }?>
    /* ]]> */
</script>
<?php
$set_button_class = 'geodir_button';
if (is_admin())
    $set_button_class = 'button-primary';
?>
<input type="button" id="<?php echo $prefix; ?>set_address_button" class="<?php echo $set_button_class; ?>" value="<?php esc_attr_e($map_title, 'geodirectory'); ?>" style="float:none;"/>
<div id="<?php echo $prefix; ?>d_mouseClick"></div>
<div class="top_banner_section_inn geodir_map_container clearfix" style="margin-top:10px;">
    <div class="TopLeft"><span id="<?php echo $prefix; ?>triggermap" style="margin-top:-11px;margin-left:-12px;"></span></div>
    <div class="TopRight"></div>
    <div id="<?php echo $prefix . 'map'; ?>" class="geodir_map" style="height:300px">
        <!-- new map start -->
        <div class="iprelative">
            <div id="<?php echo $prefix . 'map'; ?>" style="float:right;height:300px;position:relative;" class="form_row clearfix"></div>
            <div id="<?php echo $prefix; ?>loading_div" style="height:300px"></div>
            <div id="<?php echo $prefix; ?>advmap_counter"></div>
            <div id="<?php echo $prefix; ?>advmap_nofound"><?php _e('<h3>No Records Found</h3><p>Sorry, no records were found. Please adjust your search criteria and try again.</p>', 'geodirectory'); ?></div>
            <div id="<?php echo $prefix;?>advmap_notloaded" class="advmap_notloaded"><?php _e('<h3>Map Not Loaded</h3><p>Sorry, unable to load Maps API.', 'geodirectory'); ?></div>
        </div>
        <!-- new map end -->
    </div>
    <div class="BottomLeft"></div>
    <div class="BottomRight"></div>
</div>