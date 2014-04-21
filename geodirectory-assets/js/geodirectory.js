gd_infowindow = new google.maps.InfoWindow();

jQuery( document ).ready(function() {
	
	 jQuery('#geodir-post-gallery a').lightBox({
		overlayOpacity : 0.5,
		imageLoading : geodir_var.geodir_plugin_url+'/geodirectory-assets/images/lightbox-ico-loading.gif',
		imageBtnNext : geodir_var.geodir_plugin_url+'/geodirectory-assets/images/lightbox-btn-next.gif',
		imageBtnPrev : geodir_var.geodir_plugin_url+'/geodirectory-assets/images/lightbox-btn-prev.gif',
		imageBtnClose : geodir_var.geodir_plugin_url+'/geodirectory-assets/images/lightbox-btn-close.gif',
		imageBlank : geodir_var.geodir_plugin_url+'/geodirectory-assets/images/lightbox-blank.gif'
	});		
	
	 jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: false,
		directionNav: false,   
        animationLoop: false,
        slideshow: false,
        itemWidth: 75,
        itemMargin: 5,
        asNavFor: '#slider'
      });
      
      jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: true,
        animationLoop: true,
		slideshow: true,
        sync: "#carousel",
        start: function(slider){
			jQuery('.flex-loader').hide();
			jQuery('#slider').css({'visibility':'visible'});
			jQuery('#carousel').css({'visibility':'visible'});
        }
      }); 
	  
	  	// Chosen selects
		if(jQuery("select.chosen_select").length > 0)
		{
			jQuery("select.chosen_select").chosen({no_results_text: "Sorry, nothing found!"});
			
			jQuery("select.chosen_select_nostd").chosen({
				allow_single_deselect: 'true'
			});
		}
});

jQuery(document).ready(function(){
	
	
	jQuery('.geodir-delete').click(function(){
		if(confirm(geodir_all_js_msg.my_place_listing_del)){
			return true;
		}else
			return false;
	});
	
	//jQuery('#gd-content').height(jQuery('#gd-content').parent('div').height());
	
	
	jQuery('.gd-category-dd').hover(function(){
		jQuery('.gd-category-dd ul').show();
	});
	
	jQuery('.gd-category-dd ul li a').click(function(ele){
		
		jQuery('.gd-category-dd').find('input').val(jQuery(this).attr('data-slug'));
		jQuery('.gd-category-dd > a').html(jQuery(this).attr('data-name'));
		jQuery('.gd-category-dd ul').hide();
	});
	
	

});


jQuery(window).load(function(){
							 
/*-----------------------------------------------------------------------------------*/
/*	Tabs
/*-----------------------------------------------------------------------------------*/
jQuery('.geodir-tabs-content').show(); // set the tabs to show once js loaded to avoid double scroll bar in chrome
tabNoRun=false;
	function activateTab(tab) {
	//alert(tab);//return;
	if(tabNoRun){tabNoRun=false; return;}
		var activeTab = tab.closest('dl').find('dd.geodir-tab-active'),
		contentLocation = tab.find('a').attr("data-tab") + 'Tab';
		
		urlHash = tab.find('a').attr("data-tab");
		if(jQuery( tab ).hasClass( "geodir-tab-active" )){}else{
			
		if(typeof urlHash==='undefined'){
			if(window.location.hash.substring(0,8)=='#comment'){
				tab = jQuery('*[data-tab="#reviews"]').parent();
				tabNoRun = true;
			}
			
			
			}else{
		window.location.hash = urlHash;
			}
		}
		
		//Make Tab Active
		activeTab.removeClass('geodir-tab-active');
		tab.addClass('geodir-tab-active');

    	//Show Tab Content
		jQuery(contentLocation).closest('.geodir-tabs-content').children('li').hide();
		jQuery(contentLocation).fadeIn();
		jQuery(contentLocation).css({'display':'inline-block'});
		
		if(urlHash=='#post_map')
		{
			window.setTimeout(function() {	
				jQuery("#detail_page_map_canvas").goMap();
				var center = jQuery.goMap.map.getCenter(); 
				google.maps.event.trigger(jQuery.goMap.map, 'resize');
				jQuery.goMap.map.setCenter(center); 
			}, 100);
		}
	}

	jQuery('dl.geodir-tab-head').each(function () {
	
		//Get all tabs
		var tabs = jQuery(this).children('dd');
		
		tabs.click(function (e) {
			if(jQuery(this).find('a').attr('data-status') == 'enable')
			{	activateTab(jQuery(this)); 
			}
		});
		
	});
	
	

	if (window.location.hash) {
		 activateTab(jQuery('a[data-tab="' + window.location.hash + '"]').parent());
	}
	
	jQuery('p').each(function() {
	    var $this = jQuery(this);
	    if($this.html().replace(/\s|&nbsp;/g, '').length == 0)
	        $this.remove();
	});
	
	jQuery('.gd-tabs .gd-tab-next').click(function(ele){
		
		var is_validate = true;
		
		/*jQuery(this).parent('li').find(".required_field").each(function(){
			jQuery(this).find("select, textarea, input").each(function(rq_field){
				validate_field( rq_field );
			});
		});*/
		
		if(is_validate){
			var tab = jQuery('dl.geodir-tab-head').find('dd.geodir-tab-active').next();
			
			if(tab.find('a').attr('data-status') == 'enable')
			{	activateTab(tab); }
			
			
			if(!jQuery('dl.geodir-tab-head').find('dd.geodir-tab-active').next().is('dd'))
			{ jQuery(this).hide(); jQuery('#gd-add-listing-submit').show();	}
		}
		
	});
	
	jQuery('#gd-login-options input').change(function(){
		jQuery('.gd-login_submit').toggle();
	});
	
	jQuery('ul.geodir-tabs-content').css({'z-index':'0','position': 'relative'});
	jQuery('dl.geodir-tab-head dd.geodir-tab-active').trigger('click');		
	
});


/*-----------------------------------------------------------------------------------*/
/*	Auto Fill
/*-----------------------------------------------------------------------------------*/

function autofill_click(ele){
	var fill_value = jQuery(ele).html();
	
	jQuery(ele).closest('div.gd-autofill-dl').closest('div.gd-autofill').find('input[type=text]').val(fill_value);
	jQuery(ele).closest('.gd-autofill-dl').remove();
};

jQuery(document).ready(function() {
	jQuery('input[type=text]').keyup(function(){
		var input_field = jQuery(this);									  	
		
		if(input_field.attr('data-type') == 'autofill' && input_field.attr('data-fill') != '')
		{
			var data_fill = input_field.attr('data-fill');
			var fill_value = jQuery(this).val();
		
			jQuery.get(geodir_var.geodir_ajax_url,{autofill:data_fill,fill_str:fill_value},function(data){
				if(data != '')
				{
					if(input_field.closest('div.gd-autofill').length == 0)
						input_field.wrap('<div class="gd-autofill"></div>'); 
					
					input_field.closest('div.gd-autofill').find('.gd-autofill-dl').remove();	
					
					input_field.after('<div class="gd-autofill-dl"></div>'); 
					input_field.next('.gd-autofill-dl').html(data);
					input_field.focus();
				}
			});
		}
	});
	
	jQuery('input[type=text]').parent().mouseleave(function(){
		jQuery(this).find('.gd-autofill-dl').remove();										  
	});
	
	jQuery(".trigger").click(function(){

		jQuery(this).toggleClass("active").next().slideToggle("slow");

		if(jQuery(".trigger").hasClass("triggeroff")){

			jQuery(".trigger").removeClass('triggeroff');

			jQuery(".trigger").addClass('triggeron');

		} else{

			jQuery(".trigger").removeClass('triggeron');

	     	jQuery(".trigger").addClass('triggeroff');	

		}	
	});
	jQuery(".trigger_sticky").click(function(){
	
		var effect = 'slide';
		var options = { direction: 'right' };
		var duration = 500;

		
		var tigger_sticky = jQuery(this);
		
    	tigger_sticky.hide();
		
		jQuery(this).next().toggle(effect, options, duration,function() {
   			tigger_sticky.show();
  		});

		if(tigger_sticky.hasClass("triggeroff_sticky")){
			
			tigger_sticky.removeClass('triggeroff_sticky');

			tigger_sticky.addClass('triggeron_sticky');
			setCookie('geodir_stickystatus','shide',1);

		} else{
			
			tigger_sticky.removeClass('triggeron_sticky');

	     	tigger_sticky.addClass('triggeroff_sticky');
				setCookie('geodir_stickystatus','sshow',1);

		}	
	});
		
});

/* Show Hide Rating for reply */
jQuery(document).ready(function(){
	jQuery('#gd_comment_replaylink a').bind('click',function(){
		jQuery('#commentform .gd_rating').hide();
		jQuery('#respond .form-submit input#submit').val('Post Reply');
		jQuery('#respond .comment-form-comment label').html('Reply text');
	});
	
	jQuery('#gd_cancle_replaylink a').bind('click',function(){
		jQuery('#commentform .gd_rating').show();
		jQuery('#respond .form-submit input#submit').val('Post Review');
		jQuery('#respond .comment-form-comment label').html('Review text');
	});
	
});

/* Show Hide Filters Start*/
jQuery(document).ready(function(){
	
	jQuery("#showFilters").click(function () {
		jQuery("#customize_filter").slideToggle("slow",function(){
			if(jQuery('#listing_search .geodir_submit_search:first').css('visibility') == 'visible')													
				jQuery('#listing_search .geodir_submit_search:first').css({'visibility':'hidden'});
			else
				jQuery('#listing_search .geodir_submit_search:first').css({'visibility':'visible'});	
		});
	});
	
});


jQuery(document).ready(function(){

	jQuery('#search_by_post').change(function(){
		window.location = jQuery(this).find('option:selected').attr('opt_label');
	});
	
});

/* Show Hide Filters End*/

/* Hide Pinpoint If Listing MAP Not On Page*/
jQuery(window).load(function(){

	if(jQuery(".map_background").length == 0)
		jQuery('.geodir-pinpoint').hide();
	else
		jQuery('.geodir-pinpoint').show();
	
});
	

//-------count post according to term--
function geodir_count_post_term(val)
{
		var url = geodir_all_js_msg.geodir_admin_ajax_url+'/?action=geodir_ajax_action&term_id='+val+'&ajax_action=geodir_get_term_count';
	jQuery.ajax({
		url: url ,
		type: 'GET',
		success: function(html){
				if(html != "" )
				{
					var data_array = jQuery.parseJSON(html);
						 resultObj = eval (data_array);
						 for (var index in resultObj){
							jQuery('.'+index).html(resultObj[index]);
						}
				}
			}
	});
 	 
}

jQuery(document).ready(function() {
	if(jQuery('body').find('.geodir_term_class').length) {
		var terms_ids = post_category_array.post_category_array;
		geodir_count_post_term(terms_ids);
	}
   });


