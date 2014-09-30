function initMap(map_options){ 
	// alert(map_options)
	map_options = eval(map_options) ;
	map_options.zoom = parseInt(map_options.zoom);
	
	
	options = map_options;
	var pscaleFactor;
	var pstartmin;
	var ajax_url =  options.ajax_url;
	var token  = options.token;
	var search_string = options.token;
	var mm = 0; // marker array
	var maptype = options.maptype;
	var zoom = options.zoom;
	var latitude = options.latitude;
	var longitude = options.longitude;
	var maxZoom = options.maxZoom;
	var etype = options.etype;
	var autozoom =  options.autozoom;
	var scrollwheel =  options.scrollwheel;
	var streetview =  options.streetViewControl;
	var bubble_size =  options.bubble_size;
	var map_canvas = options.map_canvas_name;
	var enable_map_direction = options.enable_map_direction;
	var enable_cat_filters = options.enable_cat_filters ;
	var enable_marker_cluster = options.enable_marker_cluster ;
	options.token = '68f48005e256696074e1da9bf9f67f06';
	options.navigationControlOptions = { position:	'TOP_LEFT', style:'ZOOM_PAN'};

	// Create map 
	jQuery("#" + map_canvas).goMap(options);
	// set max zoom
	
	var styles = [
	{
			 featureType: "poi.business",
			 elementType: "labels",
			 stylers: [
			   { visibility: "off" }
			 ]
		   }
	];
	
	if(!(typeof geodir_custom_map_style ==='undefined' ))
		styles = geodir_custom_map_style ;
		
	jQuery.goMap.map.setOptions({styles: styles});
	
	
	
	google.maps.event.addListenerOnce(jQuery.goMap.map, 'idle', function(){
		jQuery("#"  + map_canvas).goMap();
		for(var i in google.maps.MapTypeId){
			jQuery.goMap.map.mapTypes[google.maps.MapTypeId[i]].maxZoom = options.maxZoom;
		}
	});
	
	
	var maxMap = document.getElementById(map_canvas + '_triggermap' );
	google.maps.event.addDomListener(maxMap, 'click', showAlert);
	function showAlert() {
	
		jQuery('#' + map_canvas).toggleClass('map-fullscreen');
		jQuery('.' + map_canvas +'_map_category').toggleClass('map_category_fullscreen');
		jQuery('#' + map_canvas +'_trigger').toggleClass('map_category_fullscreen');
		jQuery('body').toggleClass('body_fullscreen');
		jQuery('#' + map_canvas+'_loading_div').toggleClass('loading_div_fullscreen');
		jQuery('#' + map_canvas +'_map_nofound').toggleClass('nofound_fullscreen');
		jQuery('#' + map_canvas +'_triggermap').toggleClass('triggermap_fullscreen');
		jQuery('.trigger').toggleClass('triggermap_fullscreen');
		jQuery('.map-places-listing').toggleClass('triggermap_fullscreen');
		jQuery('.' + map_canvas +'_TopLeft').toggleClass('TopLeft_fullscreen');
		jQuery('#' + map_canvas +'_triggermap').closest('.geodir_map_container').toggleClass('geodir_map_container_fullscreen');
		
		window.setTimeout(function() { 
			var center = jQuery.goMap.map.getCenter(); 
			jQuery("#"+map_canvas).goMap();
			google.maps.event.trigger(jQuery.goMap.map, 'resize');
			jQuery.goMap.map.setCenter(center); 
			setGeodirMapSize(true);
		}, 100);
	}
}

function build_map_ajax_search_param(map_canvas_var,reload_cat_list)
{
	var child_collapse = jQuery('#'+map_canvas_var+'_child_collapse').val() ;
	
	var ptype  = new Array(), search_string   = '' , stype   = ''
	
	var gd_posttype = '';
	var gd_cat_posttype = '';
	
	if(jQuery('#'+map_canvas_var +'_posttype').val() != '' && jQuery('#'+map_canvas_var +'_posttype').val() != '0'){	
		gd_posttype = jQuery('#'+map_canvas_var +'_posttype').val();
		gd_cat_posttype = jQuery('#'+map_canvas_var +'_posttype').val();
		gd_posttype = '&gd_posttype='+gd_posttype;
	}
	
	
		
	if( jQuery('#'+map_canvas_var+'_jason_enabled').val()==1)
	{
	
		parse_marker_jason(eval(map_canvas_var+'_jason_args.' + map_canvas_var + '_jason' ), map_canvas_var)
		return false;	
	}
	
	if(reload_cat_list) // load the category listing in map canvas category list panel 
	{
		jQuery.get(eval(map_canvas_var).ajax_url,{geodir_ajax:'map_ajax',ajax_action:'homemap_catlist',post_type:gd_cat_posttype, map_canvas:map_canvas_var, child_collapse:child_collapse},function(data){
			
			if(data){
				jQuery('#'+map_canvas_var+'_cat .toggle').html(data);
				//show_category_filter(map_canvas_var);
				geodir_show_sub_cat_collapse_button();
				build_map_ajax_search_param(map_canvas_var,false) ;
				return false;
			}
																																	   		});
		return false;
	}
	
	
	
	
	
	
	search_string = (jQuery('#'+map_canvas_var+'_search_string').val() != eval(map_canvas_var).inputText) ? jQuery('#'+map_canvas_var+'_search_string').val() : '';
	
	
	var location_string = '';
	
	if(jQuery('#'+map_canvas_var +'_country').val() != ''){
		var $gd_country = jQuery('#'+map_canvas_var +'_country').val();
		location_string = location_string+'&gd_country='+$gd_country;
	}
		
	if(jQuery('#'+map_canvas_var +'_region').val() != ''){
		var $gd_region = jQuery('#'+map_canvas_var +'_region').val();
		location_string = location_string+'&gd_region='+$gd_region;
	}
		
	if(jQuery('#'+map_canvas_var +'_city').val() != ''){	
		var $gd_city = jQuery('#'+map_canvas_var +'_city').val();	
		location_string = location_string+'&gd_city='+$gd_city;
	}
	
	
	//loop through available categories
	mapcat = document.getElementsByName( map_canvas_var +"_cat[]");
	
	var checked = "";
	for(i = 0; i < mapcat.length; i++){
		if(mapcat[i].checked){
			checked += mapcat[i].value+",";
		}
	}
	
	var strLen = checked.length;
	checked    = checked.slice(0,strLen-1);
	
	
	var search_query_string = '' ;
	search_query_string = '&geodir_ajax=map_ajax&ajax_action=cat&cat_id='+checked+"&search="+search_string
	if(gd_posttype != '')
		search_query_string = search_query_string+gd_posttype;	
	
//	if(location_string != '')
	//	search_query_string = search_query_string+location_string;	
	
	
	map_ajax_search(map_canvas_var , search_query_string, ''); 
}

function geodir_show_sub_cat_collapse_button()
{
	jQuery('ul.main_list li').each(function(i){
		var sub_cat_list = jQuery(this).find('ul.sub_list')	;
		//alert((typeof sub_cat_list.attr('class') ==='undefined')) ;
		if(!(typeof sub_cat_list.attr('class') ==='undefined') )
		{
			
			if(sub_cat_list.is(':visible'))
			{
				jQuery(this).find('i').removeClass('fa-long-arrow-down');
				jQuery(this).find('i').addClass('fa-long-arrow-up');
			}
			else
			{
				jQuery(this).find('i').removeClass('fa-long-arrow-up');
				jQuery(this).find('i').addClass('fa-long-arrow-down');	
			}
			
				
			jQuery(this).find('i').show();/**/
		}
		else
			jQuery(this).find('i').hide();/**/
	})	
	geodir_activate_collapse_pan();
}

function geodir_activate_collapse_pan()
{
	jQuery('ul.main_list').find('i').click(function(){
		jQuery(this)
		.parent('li')
		.find('ul.sub_list')
		.toggle(200 , 
				function(){
								if(jQuery(this).is(':visible'))
								{
									jQuery(this).parent('li').find('i').removeClass('fa-long-arrow-down');
									jQuery(this).parent('li').find('i').addClass('fa-long-arrow-up');
								}
								else
								{
									jQuery(this).parent('li').find('i').removeClass('fa-long-arrow-up');
									jQuery(this).parent('li').find('i').addClass('fa-long-arrow-down');
								}
						   });	
		
	});	
}

function map_ajax_search(map_canvas_var, search_query_string, marker_jason)
{
	
	document.getElementById( map_canvas_var+'_loading_div').style.display="block";
	if(marker_jason!='')
	{
		parse_marker_jason(marker_jason, map_canvas_var)	
		document.getElementById( map_canvas+'_loading_div').style.display="none";
		return;
	}
	
	var query_url = eval(map_canvas_var).ajax_url+search_query_string;
	
	jQuery.ajax({
		type: "GET",
		url: query_url,
		success: function(data){
		//	alert(map_canvas) ;
			document.getElementById( map_canvas_var+'_loading_div').style.display="none";
			parse_marker_jason( data, map_canvas_var );
		//	document.dispatchEvent(event_marker_reloaded);
		}
	});
	
	return;
} // End  map_ajax_search

// read the data, create markers
function parse_marker_jason(data, map_canvas_var)
{
if(jQuery('#'+map_canvas_var).val()==''){// if map not loaded then load it
initMap(map_canvas_var);
}	

	jQuery("#"+ map_canvas_var).goMap();
	
	// get the bounds of the map
	bounds = new google.maps.LatLngBounds();
	
	if(eval(map_canvas_var).enable_marker_cluster)
	{
		if (typeof remove_cluster_markers == 'function') { 
			remove_cluster_markers(map_canvas_var) 
		}
	}
	
	// clear old markers
	jQuery.goMap.clearMarkers(); //deleteMarkers();
	
	//json evaluate returned data
	jsonData = jQuery.parseJSON(data);
	
	// if no markers found, display home_map_nofound div with no search criteria met message
	if (jsonData[0].totalcount <= 0) {
		document.getElementById( map_canvas_var+'_map_nofound').style.display = 'block';
		var mapcenter = new google.maps.LatLng(eval(map_canvas_var).latitude,eval(map_canvas_var).longitude);
		list_markers(jsonData ,map_canvas_var);
		jQuery.goMap.map.setCenter(eval(map_canvas_var).mapcenter);
		jQuery.goMap.map.setZoom(eval(map_canvas_var).zoom);
	}else{
		document.getElementById(map_canvas_var+'_map_nofound').style.display = 'none';
		var mapcenter = new google.maps.LatLng(eval(map_canvas_var).latitude,eval(map_canvas_var).longitude);
		list_markers(jsonData,map_canvas_var);
		var center = bounds.getCenter();
		if(eval(map_canvas_var).autozoom){jQuery.goMap.map.fitBounds(bounds);}
		else{jQuery.goMap.map.setCenter(center);}
		
		
		//if(eval(map_canvas_var).autozoom){jQuery.goMap.map.setCenter(center);}//else{map.setCenter(mapcenter);}
		if ( jQuery.goMap.map.getZoom() > eval(map_canvas_var).maxZoom ){jQuery.goMap.map.setZoom(eval(map_canvas_var).maxZoom);}
	}
	
	if(eval(map_canvas_var).enable_marker_cluster)
	{
		if (typeof create_marker_cluster == 'function') { 
			create_marker_cluster(map_canvas_var)
		}
	}
	
	jQuery('#' + map_canvas_var + '_loading_div').hide();
}	

	
function list_markers(input, map_canvas_var) 
{

	var totalcount = input[0].totalcount;
	
	if(totalcount > 0){
		for (var i = 0; i < input.length; i++) {
		
			var marker = create_marker(input[i],map_canvas_var);
		}
	}

}	


function geodir_htmlEscape(str) {
    return String(str)
            .replace(/&prime;/g,"'")
            .replace(/&frasl;/g, '/')
            .replace(/&ndash;/g, '-')
            .replace(/&ldquo;/g, '"')
            .replace(/&gt;/g, '>');
}
gd_single_marker_lat = '';	
gd_single_marker_lon = '';	
// create the marker and set up the event window
function create_marker(input,map_canvas_var ) 
{
	gd_single_marker_lat = input.lt;
	gd_single_marker_lon = input.ln;
	jQuery("#"+ map_canvas_var).goMap();
	
	if(input.lt && input.ln){
		var coord = new google.maps.LatLng(input.lt,input.ln);
		var marker_id = 0 ;
		if(eval(map_canvas_var).enable_cat_filters)
			marker_id = input.mk_id
		else
			marker_id = input.id
			
			
		var title = geodir_htmlEscape(input.t);
		
		//if(!input.i){return;}
		if(!input.i){input.i = geodir_all_js_msg.geodir_default_marker_icon;}
		
		var marker  = jQuery.goMap.createMarker({
							id: marker_id ,
							title: title ,
							position: coord,
							visible: true,
							clickable: true,
							icon: input.i
						});


		bounds.extend(coord);

		// Adding a click event to the marker
		google.maps.event.addListener(marker, 'click', function() {
			
			jQuery("#"+ map_canvas_var).goMap();											
			
			var preview_query_str = '';
			if(input.post_preview){
				preview_query_str = '&post_preview='+input.post_preview;
			}
			
			if(eval(map_canvas_var).bubble_size){
				var marker_url = eval(map_canvas_var).ajax_url+"&geodir_ajax=map_ajax&ajax_action=info&m_id="+input.id+"&small=1"+preview_query_str; 								            }else{
				var marker_url = eval(map_canvas_var).ajax_url+"&geodir_ajax=map_ajax&ajax_action=info&m_id="+input.id+preview_query_str;
			}
			
			var  loading = '<div id="map_loading"></div>';
			gd_infowindow.open(jQuery.goMap.map, marker);
			gd_infowindow.setContent(loading);
			
			jQuery.ajax({
				type: "GET",
				url: marker_url,
				cache: false,
				dataType: "html",
				error: function(xhr, error){
						alert(error);
				},
				success: function(response){
					jQuery("#"+ map_canvas_var).goMap();
					gd_infowindow.setContent(response);
					gd_infowindow.open(jQuery.goMap.map, marker);
				}
			});
			
			return;
			
		});

		// Adding a visible_changed event to the marker
		google.maps.event.addListener(marker, 'visible_changed', function(){
			gd_infowindow.close(jQuery.goMap.map, marker);
		});
	
		return true;
	}else{
		//no lat & long, return no marker
		return false;
	}
}

function openMarker(map_canvas ,id){
	jQuery("#"+ map_canvas).goMap();
	//for (var i = 0, l = jQuery.goMap.markers.length; i < l; i++) {
	//	alert(jQuery.goMap.markers[i])
	//}
	google.maps.event.trigger(jQuery.goMap.mapId.data(id), 'click'); 
}

function animate_marker(map_canvas ,id){
		jQuery("#"+ map_canvas).goMap();
		//alert(jQuery.goMap.mapId.data(id))
		jQuery.goMap.mapId.data(id).setAnimation(google.maps.Animation.BOUNCE);
	}
	
function stop_marker_animation(map_canvas ,id){
	jQuery("#"+ map_canvas).goMap();
	
	if (jQuery.goMap.mapId.data(id).getAnimation() != null) {
		jQuery.goMap.mapId.data(id).setAnimation(null);
	}
}

// Listing map sticky script //

function getCookie(cname)
{
var name = cname + "=";
var ca = document.cookie.split(';');
for(var i=0; i<ca.length; i++)
  {
 	 var c = ca[i].trim();
  	if (c.indexOf(name)==0) return c.substring(name.length,c.length);
  }
return "";
} 

function setCookie(cname,cvalue,exdays)
{
	var d = new Date();
	d.setTime(d.getTime()+(exdays*24*60*60*1000));
	var expires = "expires="+d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires+";Path=/";
} 

	
function map_sticky(map_options) {
	var optionsname = map_options;
	map_options = eval(map_options) ;

	if(map_options.sticky && jQuery(window).width()>1250){
	
		jQuery.fn.scrollBottom = function() { 
			return this.scrollTop() + this.height(); 
		};			
	
		//var content = jQuery("#geodir_wrapper").closest('div').scrollBottom();
		//var content = jQuery("#geodir-main-content").closest('div').scrollBottom();
		var content = jQuery(".geodir-sidebar-wrap").scrollBottom();
		
		var stickymap = jQuery("#sticky_map_"+optionsname+"").scrollBottom();
		var catcher = jQuery('#catcher_'+optionsname+'');
		var sticky = jQuery('#sticky_map_'+optionsname+'');		
		var sticky_show_hide_trigger = sticky.closest('.stick_trigger_container').find('.trigger_sticky');
		var mapheight = jQuery("#sticky_map_"+optionsname+"").height();
		//alert(mapheight)
		jQuery(window).scroll(function() {
			
			jQuery("#"+optionsname+"").goMap();
			
			// get the bounds of the map
			bounds = new google.maps.LatLngBounds();
			
			var wheight = jQuery(window).height();
			
			
				//alert(catcher.offset().top);
			//sticky.css({'min-width':'300px'});
			//alert(content);
			//alert(stickymap);
			//if(content > stickymap ) {alert(1);}
			//if(jQuery(window).scrollTop() >= catcher.offset().top ) {alert(2);}
				
			if(jQuery(window).scrollTop() >= catcher.offset().top && content > stickymap ) {
				if(!sticky.hasClass('stickymap')){ 	
					sticky.addClass('stickymap');
					sticky.hide();
					sticky.css({'position':'fixed','right':'0','border':'1px solid red'});
					//sticky.css({'top':'25%','width':'25%'});
					sticky.css({'top':'25%'});
					catcher.css({'height' :mapheight }) ;
					var cstatus = getCookie('geodir_stickystatus');
					if(cstatus!='shide'){
						sticky.show('slow');
						
						sticky_show_hide_trigger.removeClass('triggeron_sticky');
						sticky_show_hide_trigger.addClass('triggeroff_sticky');
					}else{
						sticky_show_hide_trigger.removeClass('triggeroff_sticky');
						sticky_show_hide_trigger.addClass('triggeron_sticky');
					}
					
					
					
				}	
				
				sticky_show_hide_trigger.css({'top':'25%','width':'1%','padding-right':'3px','padding-left':'0px'});
				sticky_show_hide_trigger.css({'position':'fixed','right':'0'});
				
				sticky_show_hide_trigger.show();
				
			}
			
			if(jQuery(window).scrollTop() < catcher.offset().top){	
				if(sticky.hasClass('stickymap')){ 	
					sticky.hide();
					sticky.removeClass('stickymap');
					sticky.css({'position':'relative','border':'none'});
					sticky.css({'top':'0','width':'auto'});
					sticky.fadeIn('slow');
					catcher.css({'height' :'0' }) ;
					sticky_show_hide_trigger.removeClass('triggeroff_sticky');
					sticky_show_hide_trigger.addClass('triggeron_sticky');
				}
				
				
				sticky_show_hide_trigger.hide();
			}
			
			
		});
	
		jQuery(window).resize(function(){jQuery(window).scroll();});

	} // sticky if end
	
	// bind a click event on listing_map_show_hide_all_markers 'Click here to see all other markers.'
	// first check if this div exists or not.
	/*if (jQuery('#listing_map_show_hide_all_markers') != null ) 
	{
		jQuery('#listing_map_show_hide_all_markers a').hide();
		jQuery('#listing_map_show_hide_all_markers a').click(function() {
			show_all_markers();
			jQuery(this).hide();
		});
	   // alert('Found with Not Null');
	}*/
	
}



		var rendererOptions = {draggable: true };
		var directionsDisplay = new google.maps.DirectionsRenderer(rendererOptions);
		var directionsService = new google.maps.DirectionsService();
function calcRoute(map_canvas){
		 
		initMap(map_canvas);
		 
		var optionsname = map_canvas;
		var map_options = eval(optionsname);
		
		// Direction map
		
		directionsDisplay.setMap(jQuery.goMap.map);
		directionsDisplay.setPanel(document.getElementById(map_canvas+"_directionsPanel"));	
		
		google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
			computeTotalDistance(directionsDisplay.directions, map_canvas);
		});
		
		jQuery('#directions-options').show();
		
		var from_address = document.getElementById(map_canvas+'_fromAddress').value;
		
		var request = {
			origin: from_address,
			destination: gd_single_marker_lat +','+ gd_single_marker_lon,
			travelMode: gdGetTravelMode(),
     	    unitSystem: gdGetTravelUnits()
		};
		directionsService.route(request, function(response, status) {
			if (status == google.maps.DirectionsStatus.OK) {
				directionsDisplay.setDirections(response);
				
				//map = new google.maps.Map(document.getElementById(map_canvas), map_options);
				//directionsDisplay.setMap(map);
				
			}else {alert(geodir_all_js_msg.address_not_found_on_map_msg + from_address);}
		});
	
}




function gdGetTravelMode(){
  var mode = jQuery('#travel-mode').val();
    if (mode=='driving'){return google.maps.DirectionsTravelMode.DRIVING;}
    else if (mode=='walking') {return google.maps.DirectionsTravelMode.WALKING;}
    else if(mode=='bicycling') {return google.maps.DirectionsTravelMode.BICYCLING;} 
    else if (mode=='transit') {return google.maps.DirectionsTravelMode.TRANSIT;}
    else{return google.maps.DirectionsTravelMode.DRIVING;}
  }
  
  function gdGetTravelUnits(){
  var mode = jQuery('#travel-units').val();
    if (mode=='kilometers'){return google.maps.DirectionsUnitSystem.METRIC;}
    else{return google.maps.DirectionsUnitSystem.IMPERIAL;}
  }

function computeTotalDistance(result, map_canvas) {
		var total = 0;
		var myroute = result.routes[0];
		for (i = 0; i < myroute.legs.length; i++) {
			total += myroute.legs[i].distance.value;
		}
		totalk = total / 1000
		totalk_round = Math.round(totalk * 100)/100
		totalm = total / 1609.344
		totalm_round = Math.round(totalm * 100)/100
		//document.getElementById(map_canvas+"_directionsPanel").innerHTML = "<p>Total Distance: <span id='totalk'>" + totalk_round + " km</span></p><p>Total Distance: <span id='totalm'>" + totalm_round + " miles</span></p>";
	} 

jQuery(function($){
	setGeodirMapSize(false);
	$(window).resize(function() {
		setGeodirMapSize(true);
	});
})
function setGeodirMapSize(resize) {
	var isAndroid = navigator.userAgent.toLowerCase().indexOf("android")>-1 ? true : false;
	var dW = parseInt(jQuery(window).width());
	var dH = parseInt(jQuery(window).height());
	if(GeodirIsiPhone() || ( isAndroid && (((dW>dH && dW==640 && dH==360) || (dH>dW && dW==360 && dH==640)) || ((dW>dH && dW==533 && dH==320) || (dH>dW && dW==320 && dH==533)) || ((dW>dH && dW==960 && dH==540) || (dH>dW && dW==540 && dH==960))))) {
		jQuery(document).find('.geodir_map_container').each(function(){
			jQuery(this).addClass('geodir-map-iphone');
		});
	}
	else {
		jQuery(document).find('.geodir_map_container').each(function(){
			var $this = this;
			var gmcW = parseInt(jQuery($this).width());
			var gmcH = parseInt(jQuery($this).height());
			if (gmcW>=400 && gmcH>=350) {
				jQuery($this).removeClass('geodir-map-small').addClass('geodir-map-full');
			} else {
				jQuery($this).removeClass('geodir-map-full').addClass('geodir-map-small');
			}
		});
		if (resize) {
			jQuery(document).find('.geodir_map_container_fullscreen').each(function(){
				var $this = this;
				var gmcW = parseInt(jQuery(this).find('.gm-style').width());
				var gmcH = parseInt(jQuery(this).find('.gm-style').height());
				if (gmcW>=400 && gmcH>=370) {
					jQuery($this).removeClass('geodir-map-small').addClass('geodir-map-full');
				} else {
					jQuery($this).removeClass('geodir-map-full').addClass('geodir-map-small');
				}
			});
		}
	}
}

function GeodirIsiPhone(){
	if ((navigator.userAgent.toLowerCase().indexOf("iphone")>-1) || (navigator.userAgent.toLowerCase().indexOf("ipod")>-1) || (navigator.userAgent.toLowerCase().indexOf("ipad")>-1)) {
		return true;
	} else {
		return false;
	}
}