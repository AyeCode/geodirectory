<?php

global $is_default, $mapzoom;

$is_map_restrict = apply_filters('geodir_add_listing_map_restrict' ,true );

$default_location = geodir_get_default_location();

$defaultcity = isset($default_location->city) ? $default_location->city : '';

if($lat == '' && $lng == ''){

$city = $defaultcity;

$region =isset($default_location->region) ? $default_location->region : '';

$country =isset($default_location->country) ? $default_location->country : '';

$lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';

$lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';

}

$default_lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';

$default_lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';

if(is_admin() && isset($_REQUEST['tab']) && $mapzoom == ''){
	$mapzoom = 4;
}

?>

<script type="text/javascript">

/* <![CDATA[ */

var <?php echo $prefix;?>CITY_MAP_CENTER_LAT = <?php echo ($lat) ? $lat :  '39.952484'; ?>;	

var <?php echo $prefix;?>CITY_MAP_CENTER_LNG = <?php echo ($lng) ? $lng :  '-75.163786'; ?>;

var <?php echo $prefix;?>CITY_MAP_ZOOMING_FACT = <?php echo ($mapzoom) ? $mapzoom : 12;?>;

var minZoomLevel = <?php echo ($is_map_restrict) ? 5 : 0; ?>;

var oldstr_address;

var oldstr_zip;

var strictBounds;

jQuery(function($) {

$("#<?php echo $prefix.'map';?>").goMap({

latitude: <?php echo $prefix;?>CITY_MAP_CENTER_LAT,

longitude: <?php echo $prefix;?>CITY_MAP_CENTER_LNG,

zoom: <?php echo $prefix;?>CITY_MAP_ZOOMING_FACT,

maptype: 'ROADMAP', // Map type - HYBRID, ROADMAP, SATELLITE, TERRAIN

<?php /*?>maptype: '<?php echo ($mapview) ? $mapview : 'ROADMAP';?>', <?php */?>

streetViewControl:true,

});

var geocoder = new google.maps.Geocoder();

function geocodePosition() {

	geocoder.geocode({
	
	latLng: baseMarker.getPosition()
	
	}, function(responses) {
	
		if (responses && responses.length > 0) {
			
			var getAddress = '';
			
			var getZip = '';
			
			var getCity = '';
			
			var getState = '';
			
			var getCountry = '';
			
			//alert(JSON.stringify(responses[0].address_components));
			
			console.log( responses );
			
			
			
			street_number = '';
			
			route = '';
			
			administrative_area_level_1 = '';
			
			administrative_area_level_2 = '';
			
			administrative_area_level_3 = '';
			
			postal_town = '';
			
			locality  = '';
			
			country = '';
			
			postal_code = '';
			
			rr = '';
			
			for (var i = 0; i < responses[0].address_components.length; i++)
			
			{
			
			var addr = responses[0].address_components[i];
			
			if (addr.types[0] == 'street_number'){street_number = addr;}
			
			if (addr.types[0] == 'route'){route = addr;}
			
			if (addr.types[0] == 'administrative_area_level_1'){administrative_area_level_1 = addr;}
			
			if (addr.types[0] == 'administrative_area_level_2'){administrative_area_level_2 = addr;}
			
			if (addr.types[0] == 'administrative_area_level_3'){administrative_area_level_3 = addr;}
			
			if (addr.types[0] == 'postal_town'){postal_town = addr;}
			
			if (addr.types[0] == 'locality'){locality = addr;}
			
			if (addr.types[0] == 'country'){country = addr;}
			
			if (addr.types[0] == 'postal_code'){postal_code = addr;}
			
			
			/*
			
			if (addr.types[0] == 'street_number')
			
			{
			
			getAddress += addr.long_name+', ';//street_number
			
			}
			
			if (addr.types[0] == 'route')
			
			{
			
			getAddress += addr.long_name;//route
			
			}
			
			if (addr.types[0] == 'postal_code')
			
			{
			
			getZip = addr.long_name;//postal_code
			
			}
			
			if(addr.types[0] == 'administrative_area_level_2')
			
			{
			
			if(getCity == ''){
			
			getCity = addr.long_name;//city
			
			}
			
			getState = addr.long_name;//set the region incase there is not one later on.
			
			}
			
			if(addr.types[0] == 'postal_town')
			
			{
			
			getCity = addr.long_name;//city
			
			}
			
			if(getCity == '')
			
			{
			
			if(addr.types[0] == 'locality')
			
			{
			
			getCity = addr.long_name;//city
			
			}
			
			}
			
			if(addr.types[0] == 'administrative_area_level_1')
			
			{
			
			getState = addr.long_name;//city
			
			}
			
			if(addr.types[0] == 'country')
			
			{
			
			getCountry = addr.long_name;//city
			
			}
			
			*/
			
			}
			
			
			if(street_number.long_name)
				getAddress += street_number.long_name+', ';//street_number
			
			if(route.long_name)
				getAddress += route.long_name;//route
			
			getZip = postal_code.long_name;//postal_code
			
			//getCity
			
			if(postal_town.long_name){getCity = postal_town.long_name;}
			
			else if(locality.long_name){getCity = locality.long_name;}
			
			else if(administrative_area_level_3.long_name){getCity = administrative_area_level_3.long_name;}
			
			//getCountry 
			
			if(country.long_name){getCountry = country.long_name;}
			
			//getState
			
			if(country.long_name){rr = country.long_name;}
			
			if(rr=="United States" || rr=="Canada" || rr=="India"){
			
				if(administrative_area_level_1.long_name){getState = administrative_area_level_1.long_name;}
				
				else if(administrative_area_level_2.long_name){getState = administrative_area_level_2.long_name;}
			
			}else{
			
				if(administrative_area_level_2.long_name){getState = administrative_area_level_2.long_name;}
				
				else if(administrative_area_level_1.long_name){getState = administrative_area_level_1.long_name;}
	
			}
			
			//getCountry 
			
			if(country.long_name){getCountry = country.long_name;}
			
			//getZip 
			
			if(postal_code.long_name){getZip = postal_code.long_name;}
		
			//alert(getState);
			
			<?php if($is_map_restrict){?>
			
			if(getCity != '<?php echo $city;?>'){
			
				alert('<?php printf(__('Please choose any address of the (%s) city only.',GEODIRECTORY_TEXTDOMAIN), $city);?>');
				
				$("#<?php echo $prefix.'map';?>").goMap();
				
				$.goMap.map.setCenter(new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
				
				baseMarker.setPosition(new google.maps.LatLng('<?php echo $default_lat; ?>', '<?php echo $default_lng; ?>'));
				
				updateMarkerPosition(baseMarker.getPosition());
				
				geocodePosition(baseMarker.getPosition());
			
			}
			
			<?php } ?>
			
			updateMarkerAddress(getAddress, getZip, getCity, getState, getCountry);
			
		} else {
	
			updateMarkerAddress('<?php _e('Cannot determine address at this location.',GEODIRECTORY_TEXTDOMAIN);?>');
	
		}
	
	});

}

function centerMap() { 

	$("#<?php echo $prefix.'map';?>").goMap();
	
	$.goMap.map.panTo(baseMarker.getPosition()); 

}

function centerMarker() {

	$("#<?php echo $prefix.'map';?>").goMap();
	
	var center = $.goMap.map.getCenter(); 
	
	baseMarker.setPosition(center);

}	

var baseMarker = $.goMap.createMarker({

	latitude:	<?php echo $prefix;?>CITY_MAP_CENTER_LAT,
	
	longitude:	<?php echo $prefix;?>CITY_MAP_CENTER_LNG,
	
	id: 'baseMarker',
	
	icon: {
	
	image: new google.maps.MarkerImage (
	
	'<?php echo geodir_plugin_url();?>/geodirectory-functions/map-functions/icons/pin.png',
	
	new google.maps.Size(20, 32),
	
	new google.maps.Point(0, 0)
	
	)
	
	},
	
	draggable: true

});

$("#<?php echo $prefix;?>set_address_button").click(function(){  

	var set_on_map = true;
	
	codeAddress(set_on_map);

}); 

// Add dragging event listeners.

google.maps.event.addListener(baseMarker, 'dragstart', function() {

	//updateMarkerAddress('Dragging...');

});

google.maps.event.addListener(baseMarker, 'drag', function() {

	// updateMarkerStatus('Dragging...');
	
	updateMarkerPosition(baseMarker.getPosition());

});

google.maps.event.addListener(baseMarker, 'dragend', function() {

	// updateMarkerStatus('Drag ended');
	
	centerMap();
	
	geocodePosition(baseMarker.getPosition());
	
	updateMarkerPosition(baseMarker.getPosition());

});

google.maps.event.addListener($.goMap.map, 'dragend', function() {

	// updateMarkerStatus('Drag ended');
	
	geocodePosition(baseMarker.getPosition());
	
	centerMarker();
	
	updateMarkerPosition(baseMarker.getPosition());

});

google.maps.event.addListener($.goMap.map, 'zoom_changed', function() {

	updateMapZoom($.goMap.map.zoom);

});

function updateMapZoom(zoom) {

	jQuery('#<?php echo $prefix.'mapzoom';?>').val(zoom);

}

function updateMarkerPosition(markerlatLng) {

	$("#<?php echo $prefix.'map';?>").goMap();
	
	//$.goMap.setMap({latitude:markerlatLng.lat(), longitude:markerlatLng.lng()}); 
	
	jQuery('#<?php echo $prefix.'latitude';?>').val(markerlatLng.lat());
	
	jQuery('#<?php echo $prefix.'longitude';?>').val(markerlatLng.lng());

}


function updateMarkerAddress(getAddress, getZip, getCity, getState, getCountry){
	
	var set_map_val_in_fields = '<?php echo apply_filters('geodir_auto_change_map_fields', true);?>';
	
	<?php ob_start();?>
	
	var old_country = jQuery("#<?php echo $prefix.'country';?>").val();
	
	var old_region = jQuery("#<?php echo $prefix.'region';?>").val();
	
	//if (getAddress){
	
	jQuery("#<?php echo $prefix.'address';?>").val(getAddress);
	
	if(getAddress){oldstr_address = getAddress;}
	
	// }
	
	//if (getZip){
	
	jQuery("#<?php echo $prefix.'zip';?>").val(getZip);
	
	if(getZip){oldstr_zip = getZip;}
	
	//}	
	
	if(set_map_val_in_fields){
		
		if (getCountry){
			
			if(jQuery('input[id="<?php echo $prefix.'country';?>"]').attr('id')){
			
				jQuery("#<?php echo $prefix.'country';?>").val(getCountry);
			
			}else{
				
				<?php /*?>jQuery('#<?php echo $prefix.'country';?> option[value="'+getCountry+'"]').attr("selected",true);
				
				jQuery("#<?php echo $prefix.'country';?>").trigger("chosen:updated");<?php */?>
				
				
				if(jQuery("#<?php echo $prefix.'country';?> option:contains('"+getCountry+"')").length == 0){
					jQuery("#<?php echo $prefix.'country';?>").append('<option value="'+getCountry+'">'+getCountry+'</option>');
				}
				
				jQuery('#<?php echo $prefix.'country';?> option[value="'+getCountry+'"]').attr("selected",true);
				jQuery("#<?php echo $prefix.'country';?>").trigger("chosen:updated");
				
				
			}	
		
		}
		
		if (getState){
		
			if(jQuery('input[id="<?php echo $prefix.'region';?>"]').attr('id')){
			
				jQuery("#<?php echo $prefix.'region';?>").val(getState);
			
			}
		
		}	
		
		if (getCity){
		
			if(jQuery('input[id="<?php echo $prefix.'city';?>"]').attr('id')){
			
				jQuery("#<?php echo $prefix.'city';?>").val(getCity);
			
			}
		
		}	
		
	}
	
	<?php 
	
	do_action('geodir_update_marker_address', $prefix);
	
	echo $updateMarkerAddress = ob_get_clean();
	
	?>

}

function codeAddress(set_on_map) {
	
	var address = jQuery('#<?php echo $prefix.'address';?>').val();
	
	var zip = jQuery('#<?php echo $prefix.'zip';?>').val();
	
	var city = jQuery('#<?php echo $prefix.'city';?>').val();
	
	var region = jQuery('#<?php echo $prefix.'region';?>').val();
	
	var country = jQuery('#<?php echo $prefix.'country';?>').val();
	
	if(typeof zip == "undefined"){

		zip = '';
	
	}
	
	if(typeof city == "undefined"){
	
		city = '<?php echo $city;?>';
	
	}
	
	if(typeof region == "undefined"){
	
		region = '<?php echo $region;?>';
	
	}
	
	if(typeof country == "undefined"){
	
		country = '<?php echo $country;?>';
	
	}
	
	var is_restrict = '<?php echo $is_map_restrict; ?>';
	
	<?php ob_start();


	$defaultregion =isset($default_location->region) ? $default_location->region : '';
	
	$defaultcountry =isset($default_location->country) ? $default_location->country : '';
	
	?>
	
	if(set_on_map && is_restrict){
	
		if(zip != '' && address != ''){
		
			address = address + ',' + zip;
		
		}
	
	}else{
		
		if(typeof address === 'undefined')
			address = '';
			
		<?php
		if(is_admin() && isset($_REQUEST['tab'])){?> 
		if(jQuery.trim(city) == '' || jQuery.trim(region) == ''){address = '';} <?php
		}?>
		
		address = address + ',' + zip + ',' + city + ',' + region + ',' + country; 
	
	}
	
	<?php $codeAddress = ob_get_clean();
	
	echo apply_filters('geodir_codeaddress', $codeAddress);
	
	?>
	
	geocoder.geocode( { 'address': address}, 
	
	function(results, status) {
	
		$("#<?php echo $prefix.'map';?>").goMap();
		
		if (status == google.maps.GeocoderStatus.OK) {
		
			baseMarker.setPosition(results[0].geometry.location);
			
			$.goMap.map.setCenter(results[0].geometry.location);
			
			updateMarkerPosition(baseMarker.getPosition());
			
			if(set_on_map && is_restrict){
			
				geocodePosition(baseMarker.getPosition());
			
			}
			
		} else {
		
			alert("<?php _e('Geocode was not successful for the following reason:',GEODIRECTORY_TEXTDOMAIN);?> " + status);
		
		}
	
	});
	
}

var maxMap = document.getElementById( '<?php echo $prefix;?>triggermap' );

google.maps.event.addDomListener(maxMap, 'click', showAlert);

function showAlert() {

$("#<?php echo $prefix.'map';?>").goMap();

// window.alert('DIV clicked');

jQuery('#<?php echo $prefix.'map';?>').toggleClass('map-fullscreen');

jQuery('.map_category').toggleClass('map_category_fullscreen');

jQuery('#<?php echo $prefix;?>trigger').toggleClass('map_category_fullscreen');

jQuery('body').toggleClass('body_fullscreen');

jQuery('#<?php echo $prefix;?>loading_div').toggleClass('loading_div_fullscreen');

jQuery('#<?php echo $prefix;?>advmap_nofound').toggleClass('nofound_fullscreen');

jQuery('#<?php echo $prefix;?>triggermap').toggleClass('triggermap_fullscreen');

jQuery('.TopLeft').toggleClass('TopLeft_fullscreen');

window.setTimeout(function() { 

//var center = $.goMap.getCenter(); 

google.maps.event.trigger($.goMap, 'resize'); 

//$.goMap.setCenter(center); 

}, 100);

}

<?php

if($is_map_restrict)

{	

?>

var CITY_ADDRESS = '<?php echo $city.','.$region.','.$country;?>';

geocoder.geocode( { 'address': CITY_ADDRESS}, 

function(results, status) {

$("#<?php echo $prefix.'map';?>").goMap();

if (status == google.maps.GeocoderStatus.OK) {

// Bounds for North America

var bound_lat_lng = String(results[0].geometry.bounds);

bound_lat_lng = bound_lat_lng.replace(/[()]/g,"");

bound_lat_lng = bound_lat_lng.split(',');

strictBounds = new google.maps.LatLngBounds(

new google.maps.LatLng(bound_lat_lng[0], bound_lat_lng[1]), 

new google.maps.LatLng(bound_lat_lng[2], bound_lat_lng[3])

);

} else {

alert("<?php _e('Geocode was not successful for the following reason:',GEODIRECTORY_TEXTDOMAIN);?> " + status);

}

});

<?php }?>

<?php /*?>function set_location_bound(){

$("#<?php echo $prefix.'map';?>").goMap();

if (strictBounds.contains($.goMap.map.getCenter())) return;

// We're out of bounds - Move the map back within the bounds

var c = $.goMap.map.getCenter(),

x = c.lng(),

y = c.lat(),

maxX = strictBounds.getNorthEast().lng(),

maxY = strictBounds.getNorthEast().lat(),

minX = strictBounds.getSouthWest().lng(),

minY = strictBounds.getSouthWest().lat();

if (x < minX) x = minX;

if (x > maxX) x = maxX;

if (y < minY) y = minY;

if (y > maxY) y = maxY;

$.goMap.map.setCenter(new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>));

baseMarker.setPosition(new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $lng; ?>));

updateMarkerPosition(baseMarker.getPosition());

geocodePosition(baseMarker.getPosition());	

}

// Listen for the dragend event

google.maps.event.addListener(baseMarker, 'dragend', function() {

<?php if($is_map_restrict){?>

setTimeout(function() { set_location_bound(); }, 500);

<?php } ?>

});

google.maps.event.addListener($.goMap.map, 'drag', function() {

<?php if($is_map_restrict){?>

setTimeout(function() { set_location_bound(); }, 500);

<?php } ?>

});<?php */?>

// Limit the zoom level

google.maps.event.addListener($.goMap.map, 'zoom_changed', function() {

$("#<?php echo $prefix.'map';?>").goMap();

if ($.goMap.map.getZoom() < minZoomLevel) $.goMap.map.setZoom(minZoomLevel);

});

});

/* ]]> */

</script>

<?php

$set_button_class = 'geodir_button';

if(is_admin())

$set_button_class = 'button-primary';

?>

<input type="button" id="<?php echo $prefix;?>set_address_button" class="<?php echo $set_button_class;?>"  value="<?php _e($map_title, GEODIRECTORY_TEXTDOMAIN);?>" style="float:none;"/>

<div id="<?php echo $prefix;?>d_mouseClick"></div>

<div class="top_banner_section_inn geodir_map_container clearfix" style=" margin-top:10px;">

<div class="TopLeft"><span id="<?php echo $prefix;?>triggermap" style="margin-top:-11px;margin-left:-12px;"></span></div>

<div class="TopRight"></div>

<div id="<?php echo $prefix.'map';?>" class="geodir_map" style="height:300px">

<!-- new map start -->

<div class="iprelative">     

<div id="<?php echo $prefix.'map';?>" style="float:right; height:300px;position:relative; "  class="form_row clearfix"></div>

<div id="<?php echo $prefix;?>loading_div" style="height:300px"></div> 

<div id="<?php echo $prefix;?>advmap_counter"></div>        

<div id="<?php echo $prefix;?>advmap_nofound"><?php echo MAP_NO_RESULTS; ?></div>     

</div>   

<!-- new map end -->

</div>

<div class="BottomLeft"></div>

<div class="BottomRight"></div>

</div>