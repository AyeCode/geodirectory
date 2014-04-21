<?php global $wp_query,$post; $post = $wp_query->post; ?>
<div id="basic-modal-content" class="clearfix">
<form name="send_to_frnd" id="send_to_frnd" action="<?php echo get_permalink($post->ID); ?>" method="post" >
	<input type="hidden" name="sendact" value="email_frnd" />
	<input type="hidden" id="send_to_Frnd_pid" name="pid" value="<?php echo $post->ID;?>" />
	<h3><?php echo apply_filters('geodir_send_to_friend_page_title',SEND_TO_FRIEND);?></h3>
    <p id="reply_send_success" class="sucess_msg" style="display:none;"></p>
	<div class="row clearfix" ><label><?php _e('Friend Name',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label><input name="to_name" id="to_name" type="text" value=""  /><span id="to_nameInfo"></span></div>
 	<div class="row  clearfix" ><label> <?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label><input name="to_email" id="to_email" type="text" value="" /><span id="to_emailInfo"></span></div>
	<div class="row  clearfix" ><label><?php _e('Your Name',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label><input name="yourname" id="yourname" type="text" value="" /><span id="yournameInfo"></span></div>
 	<div class="row  clearfix" ><label> <?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label><input name="youremail" id="youremail" type="text" value="" /><span id="youremailInfo"></span></div>
	<div class="row  clearfix" ><label><?php _e('Subject',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label><input name="frnd_subject" value="<?php echo __('About ',GEODIRECTORY_TEXTDOMAIN).$post->post_title;?>" id="frnd_subject" type="text" value="" /><span id="frnd_subjectInfo"></span></div>
	<div class="row  clearfix" ><label><?php _e('Comments',GEODIRECTORY_TEXTDOMAIN);?> : <span>*</span></label>
     <textarea name="frnd_comments" id="frnd_comments" cols="" rows="" ><?php echo SEND_TO_FRIEND_SAMPLE_CONTENT;?></textarea>
     <span id="frnd_commentsInfo"></span></div>
    <?php if(function_exists('geodir_get_captch')){geodir_get_captch('-1'); }?>
	<input name="Send" type="submit" value="<?php _e('Send',GEODIRECTORY_TEXTDOMAIN)?> " class="button " />
</form>
</div>
		
<div id="basic-modal-content2" class="clearfix">
 <form method="post" name="agt_mail_agent" id="agt_mail_agent" action="<?php echo get_permalink($post->ID); ?>" >
  <input type="hidden" name="sendact" value="send_inqury" />
  <input type="hidden" name="pid" id="agt_mail_agent_pid" value="<?php echo $post->ID;?>" />
  <h3><?php echo apply_filters('geodir_send_inquiry_page_title',SEND_INQUIRY);?> </h3>
    <p id="inquiry_send_success" class="sucess_msg" style="display:none;"></p>
	<div class="row  clearfix" ><label><?php _e('Your Name',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label><input name="inq_name" id="agt_mail_name" type="text" value=""  /><span id="span_agt_mail_name"></span></div>
 	<div class="row  clearfix" ><label><?php _e('Email',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label><input name="inq_email" id="agt_mail_email" type="text" value=""  /><span id="span_agt_mail_email"></span></div>
	<div class="row  clearfix" ><label><?php _e('Contact Info',GEODIRECTORY_TEXTDOMAIN);?> :</label><input name="inq_phone" id="agt_mail_phone" type="text"  value="" /></div>
	<div class="row  clearfix" ><label><?php _e('Comments',GEODIRECTORY_TEXTDOMAIN);?> :  <span>*</span></label>
     <textarea name="inq_msg" id="agt_mail_msg" cols="" rows="" ><?php echo SEND_INQUIRY_SAMPLE_CONTENT;?></textarea>
     <span id="span_agt_mail_msg"></span>
    </div>
    <input name="Send" type="submit"  value="<?php _e('Send',GEODIRECTORY_TEXTDOMAIN);?>" class="button clearfix" />
 </form>
</div>