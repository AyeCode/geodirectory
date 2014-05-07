
function geodir_get_popup_forms(e, clk_class, popup_id){
	
	var ajax_url = geodir_var.geodir_ajax_url;
	var post_id = jQuery('input[name="geodir_popup_post_id"]').val()
	
	var append_class = jQuery('.'+clk_class).closest('.geodir-company_info');
	
	jQuery.modal('<div id="basic-modal-content" class="clearfix simplemodal-data" style="display: block;"><div id="modal-loading"></div></div>');// show popup right away

jQuery.post( ajax_url, { popuptype: clk_class, post_id: post_id })
	.done(function( data ) {
		
		append_class.find('.geodir_display_popup_forms').append(data);
		e.preventDefault();
		jQuery.modal.close();// close popup and show new one with new data, will be son fast user will not see it
		jQuery('#'+popup_id).modal({
									  persist:true,
									onClose: function(){
											  jQuery.modal.close({
												  overlayClose:true
											  });
											  append_class.find('.geodir_display_popup_forms').html('');
									  },
								   });
		
	});
	
}

jQuery(document).ready(function () {
	
	var geodir_popup_timer;
	
	jQuery('a.b_sendtofriend').click(function (e) {
																						 
		//clearInterval(geodir_popup_timer);
		//geodir_popup_timer = setTimeout(function() {
				
			geodir_get_popup_forms(e, 'b_sendtofriend', 'basic-modal-content');
			
		//}, 1000);
		
	});
	
	jQuery('a.b_send_inquiry' ).click(function (e) {
		
		//clearInterval(geodir_popup_timer);
		//geodir_popup_timer = setTimeout(function() {
				
			geodir_get_popup_forms(e, 'b_send_inquiry', 'basic-modal-content2');
			
		//}, 1000);
		
	});
	
	/*jQuery('a.b_claim_listing').click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content4').modal({persist:true});
	});
	
	jQuery('p.links a.a_image_sort').click(function (e) {
		e.preventDefault();
		jQuery('#basic-modal-content3').modal({persist:true});
	});*/
	
});




function geodir_popup_validate_field(field){

	var is_error = true;
	erro_msg = '';
	switch( jQuery(field).attr('field_type') )
	{
		
		case 'email':
			var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
   			
				if(field.value == ''){erro_msg = geodir_all_js_msg.geodir_field_id_required;}
				
				if(field.value !='' && !filter.test(field.value)){erro_msg = geodir_all_js_msg.geodir_valid_email_address_msg;}
		
				if(field.value !='' && filter.test(field.value))
				{ is_error = false; }
				
		break;
		
		case 'text':
		case 'textarea':
			if(field.value != '')
			{ is_error = false;	}else{erro_msg = geodir_all_js_msg.geodir_field_id_required;}
		break;
	}
	
	if(is_error)
	{
		if(erro_msg)
		{jQuery(field).closest('div').find('span.message_error2').html(erro_msg)}
		
		jQuery(field).closest('div').find('span.message_error2').fadeIn();
		
		return false;
	}else
	{
		
		jQuery(field).closest('div').find('span.message_error2').html('');
		jQuery(field).closest('div').find('span.message_error2').fadeOut();
		
		return true;
	}
}


jQuery(document).ready(function(){
	
	jQuery(document).delegate("#agt_mail_agent .is_required:visible,#send_to_frnd .is_required:visible", "blur", function(ele){
		
		geodir_popup_validate_field(this);
		
	});
	
	
	jQuery(document).delegate("#agt_mail_agent, #send_to_frnd", "submit", function(ele){
		
		var popup_is_validate = true;
		
		jQuery(this).find(".is_required:visible").each(function(){
			
			if(!geodir_popup_validate_field( this ))
				popup_is_validate = geodir_popup_validate_field( this );
		
		});
		
		if(popup_is_validate){
			return true;
		}else{
			return false;
		}
		
	});
	
});



