		
function geodir_click_search($this){
jQuery($this).parent().find('.geodir_submit_search').click();
}
  
function addToFavourite(post_id,action)
{
    
	var fav_url; 
	
	if(action == 'add')
	{	fav_url = geodir_all_js_msg.geodir_admin_ajax_url+'/?action=geodir_ajax_action&geodir_ajax=favorite&ajax_action=add&pid='+post_id; }
	else
	{	fav_url = geodir_all_js_msg.geodir_admin_ajax_url+'/?action=geodir_ajax_action&geodir_ajax=favorite&ajax_action=remove&pid='+post_id; 
	}
	
	jQuery.ajax({
		url: fav_url ,
		type: 'GET',
		dataType: 'html',
		timeout: 20000,
		error: function(){
			alert(geodir_all_js_msg.loading_listing_error_favorite);
		},
		success: function(html){	
			jQuery('.favorite_property_'+post_id).html(html);
		}
	});
	return false;
}


	
	jQuery(document).ready(function(){
	
		jQuery('.gd_rating').jRating({
			/** String vars **/
			//bigStarsPath : geodir_all_js_msg.geodir_plugin_url+'/geodirectory-assets/images/stars.png',
			bigStarsPath : geodir_all_js_msg.geodir_default_rating_star_icon,
			smallStarsPath : geodir_all_js_msg.geodir_plugin_url+'/geodirectory-assets/images/small.png', 
			phpPath : geodir_all_js_msg.geodir_plugin_url+ '/jRating.php',
			type : 'big', // can be set to 'small' or 'big'
			
			/** Boolean vars **/
			step:true, // if true,  mouseover binded star by star,
			isDisabled:false,
			showRateInfo: true,
			canRateAgain : true,
	
			/** Integer vars **/
			length:5, // number of star to display
			decimalLength : 0, // number of decimals.. Max 3, but you can complete the function 'getNote'
			rateMax : 5, // maximal rate - integer from 0 to 9999 (or more)
			rateInfosX : -45, // relative position in X axis of the info box when mouseover
			rateInfosY : 5, // relative position in Y axis of the info box when mouseover
			nbRates : 100,
	
			/** Functions **/
			onSuccess : function(element, rate){
				jQuery('#geodir_overallrating').val(rate);
			},
			onError : function(){
				alert(geodir_all_js_msg.rating_error_msg);
			}
		});
		
	});
	
	jQuery(document).ready(function(){
		jQuery('.button-primary').click(function(){
			var error = false;
			var characterReg = /^\s*[a-zA-Z0-9,\s]+\s*$/;
			var listing_prefix = jQuery('#geodir_listing_prefix').val();
			var location_prefix = jQuery('#geodir_location_prefix').val();
			var listingurl_separator = jQuery('#geodir_listingurl_separator').val();
			var detailurl_separator = jQuery('#geodir_detailurl_separator').val();
						
			if(listing_prefix == ''){
				
				alert(geodir_all_js_msg.listing_url_prefix_msg);
				jQuery('#geodir_listing_prefix').focus();
				error = true; }
			
			if (/^[a-z0-90\_9_-]*$/.test(listing_prefix) == false && listing_prefix!=''){
				jQuery('#geodir_listing_prefix').focus();
				alert(geodir_all_js_msg.invalid_listing_prefix_msg);
				error = true; }
				
			if(location_prefix==''){
				alert(geodir_all_js_msg.location_url_prefix_msg);
				jQuery('#geodir_location_prefix').focus();
				error = true; }
				
			if (!characterReg.test(location_prefix) && location_prefix!=''){
				alert(geodir_all_js_msg.invalid_location_prefix_msg);
				jQuery('#geodir_location_prefix').focus();
				error = true; }
			
			/*if(listingurl_separator==''){
				alert(geodir_all_js_msg.location_and_cat_url_separator_msg);
				jQuery('#geodir_listingurl_separator').focus();
				error = true; }
			
			if (!characterReg.test(listingurl_separator) && listingurl_separator!=''){
				
				alert(geodir_all_js_msg.invalid_char_and_cat_url_separator_msg);
				
				jQuery('#geodir_listingurl_separator').focus();
				error = true; }
			
			if(detailurl_separator==''){
				alert(geodir_all_js_msg.listing_det_url_separator_msg);
				jQuery('#geodir_detailurl_separator').focus();
				error = true; }
				
			if (!characterReg.test(detailurl_separator) && detailurl_separator!=''){
				
				alert(geodir_all_js_msg.invalid_char_listing_det_url_separator_msg);
				
				jQuery('#geodir_detailurl_separator').focus();
				error = true; }
				*/
				
			if(error==true){
				return false;
			}else{
				return true;
			}
		});
	
	});
	 	
	jQuery(document).ready(function(){
		jQuery('.map_post_type').click(function(){
		
			var divshow = jQuery(this).val();
			
			if(jQuery(this).is(':checked'))
			{
				jQuery('#'+divshow+' input').each(function(){
					jQuery(this).attr('checked','checked');
				});
			}else{
				jQuery('#'+divshow+' input').each(function(){
					jQuery(this).removeAttr('checked');
				});	
			}
			
		});
	});