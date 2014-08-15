<?php 
$post_id = $_REQUEST['post_id']; 
$post_info = get_post($post_id);

if($_REQUEST['popuptype'] == 'b_sendtofriend'){ ?>

	<div id="basic-modal-content" class="clearfix">
	<form name="send_to_frnd" id="send_to_frnd" action="<?php echo get_permalink($post_info->ID); ?>" method="post" >
		<input type="hidden" name="sendact" value="email_frnd" />
		<input type="hidden" id="send_to_Frnd_pid" name="pid" value="<?php echo $post_info->ID;?>" />
		<h3><?php echo apply_filters('geodir_send_to_friend_page_title',SEND_TO_FRIEND);?></h3>
			<p id="reply_send_success" class="sucess_msg" style="display:none;"></p>
        <?php do_action('geodir_before_stf_form_field' , 'to_name') ;?>
		<div class="row clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Friend Name',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input class="is_required" field_type="text" name="to_name" id="to_name" type="text" value=""  />
			<span class="message_error2" id="to_nameInfo"></span></div>
		</div>
        <?php do_action('geodir_after_stf_form_field' , 'to_name') ;?>
        <?php do_action('geodir_before_stf_form_field' , 'to_email') ;?>
        <div class="row  clearfix" >
        <div class="geodir_popup_heading">
			<label> <?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input class="is_required" field_type="email" name="to_email" id="to_email" type="text" value="" />
			<span class="message_error2" id="to_emailInfo"></span></div>
		</div>
        <?php do_action('geodir_after_stf_form_field' , 'to_email') ;?>
        <?php do_action('geodir_before_stf_form_field' , 'yourname') ;?>
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Your Name',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input class="is_required" field_type="text" name="yourname" id="yourname" type="text" value="" />
			<span class="message_error2" id="yournameInfo"></span></div>
		</div>
        <?php do_action('geodir_after_stf_form_field' , 'yourname') ;?>
        <?php do_action('geodir_before_stf_form_field' , 'youremail') ;?>
		<div class="row  clearfix" >
       <div class="geodir_popup_heading">
			<label> <?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input class="is_required" field_type="email" name="youremail" id="youremail" type="text" value="" />
			<span class="message_error2" id="youremailInfo"></span></div>
		</div>
         <?php do_action('geodir_after_stf_form_field' , 'youremail') ;?>
         <?php do_action('geodir_before_stf_form_field' , 'frnd_subject') ;?> 
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Subject',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input  class="is_required" field_type="text" name="frnd_subject" value="<?php echo __('About',GEODIRECTORY_TEXTDOMAIN).' '.$post_info->post_title;?>" id="frnd_subject" type="text" value="" />
			<span class="message_error2" id="frnd_subjectInfo"></span></div>
		</div>
        <?php do_action('geodir_after_stf_form_field' , 'frnd_subject') ;?> 
        <?php do_action('geodir_before_stf_form_field' , 'frnd_comments') ;?> 
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Comments',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label></div>
			<div class="geodir_popup_field">
			<textarea class="is_required" field_type="textarea" name="frnd_comments" id="frnd_comments" cols="" rows="" ><?php echo SEND_TO_FRIEND_SAMPLE_CONTENT;?></textarea>
			<span class="message_error2" id="frnd_commentsInfo"></span></div>
		</div>
        <?php do_action('geodir_after_stf_form_field' , 'frnd_comments') ;?> 
			<?php if(function_exists('geodir_get_captch')){geodir_get_captch('-1'); }?>
		<input name="Send" type="submit" value="<?php _e('Send',GEODIRECTORY_TEXTDOMAIN)?> " class="button " />
	</form>
	</div> <?php 

}elseif($_REQUEST['popuptype'] == 'b_send_inquiry'){ ?>

	<div id="basic-modal-content2" class="clearfix">
	 <form method="post" name="agt_mail_agent" id="agt_mail_agent" action="<?php echo get_permalink($post_info->ID); ?>" >
		<input type="hidden" name="sendact" value="send_inqury" />
		<input type="hidden" name="pid" value="<?php echo $post_info->ID;?>" />
		<h3><?php echo apply_filters('geodir_send_inquiry_page_title',SEND_INQUIRY);?> </h3>
			<p id="inquiry_send_success" class="sucess_msg" style="display:none;"></p>
        <?php do_action('geodir_before_inquiry_form_field' , 'inq_name') ;?> 
		<div class="row  clearfix" >
			<div class="geodir_popup_heading"><label><?php _e('Your Name',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label>	</div>
			<div class="geodir_popup_field">		
			<input class="is_required" field_type="text" name="inq_name"  type="text" value=""  />
			<span class="message_error2" id="span_agt_mail_name"></span></div>
		</div>
        <?php do_action('geodir_after_inquiry_form_field' , 'inq_name') ;?> 
        <?php do_action('geodir_before_inquiry_form_field' , 'inq_email') ;?> 
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label></div>
			<div class="geodir_popup_field">
			<input class="is_required" field_type="email" name="inq_email" type="text" value=""  />
			<span class="message_error2" id="span_agt_mail_email"></span>
			</div>
		</div>
         <?php do_action('geodir_after_inquiry_form_field' , 'inq_email') ;?> 
         <?php do_action('geodir_before_inquiry_form_field' , 'inq_phone') ;?>
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Contact Info',GEODIRECTORY_TEXTDOMAIN);?> :</label></div>
			<div class="geodir_popup_field">
			<input name="inq_phone" id="agt_mail_phone" type="text"  value="" />
			</div>
		</div>
        <?php do_action('geodir_after_inquiry_form_field' , 'inq_phone') ;?>
        <?php do_action('geodir_before_inquiry_form_field' , 'inq_msg') ;?>
		<div class="row  clearfix" >
			<div class="geodir_popup_heading">
			<label><?php _e('Comments',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label></div>
			<div class="geodir_popup_field">
			<textarea class="is_required" field_type="textarea" name="inq_msg" cols="" rows="" ><?php echo SEND_INQUIRY_SAMPLE_CONTENT;?></textarea>
			<span class="message_error2" id="span_agt_mail_msg"></span></div>
		</div>
        <?php do_action('geodir_after_inquiry_form_field' , 'inq_msg') ;?>
			<input name="Send" type="submit"  value="<?php _e('Send',GEODIRECTORY_TEXTDOMAIN);?>" class="button clearfix" />
	 </form>
	</div> <?php
	}
?>