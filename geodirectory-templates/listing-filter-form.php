<?php global $wp_query,$current_term,$query;

$curr_post_type = geodir_get_current_posttype();	

?>


 <form class="geodir-listing-search" name="geodir-listing-search" action="<?php echo home_url();?>" method="get" >
    <input type="hidden" name="geodir_search" value="1"  />
    
    <div class="geodir-loc-bar">
				
				<?php do_action('geodir_before_search_form') ?>
				
       <div class="clearfix geodir-loc-bar-in">
           
					  <div class="geodir-search">
                               
				<?php 
				
				$default_search_for_text = SEARCH_FOR_TEXT;
				if(get_option('geodir_search_field_default_text'))
					$default_search_for_text = __(get_option('geodir_search_field_default_text'), GEODIRECTORY_TEXTDOMAIN);
				
				$default_near_text = NEAR_TEXT;
				if(get_option('geodir_near_field_default_text'))
						$default_near_text = __(get_option('geodir_near_field_default_text'), GEODIRECTORY_TEXTDOMAIN);	
				
				$default_search_button_label = __('Search', GEODIRECTORY_TEXTDOMAIN);
				if(get_option('geodir_search_button_label'))
						$default_search_button_label = __(get_option('geodir_search_button_label'), GEODIRECTORY_TEXTDOMAIN);	
				
				$post_types = geodir_get_posttypes('object'); 
				
				if(!empty($post_types) && count((array)$post_types) > 1 ):
				?>
                <select name="stype" class="search_by_post" >
				<?php foreach( $post_types as $post_type => $info ): 
				global $wpdb;
				$has_posts = '';
				$has_posts = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s LIMIT 1",$post_type) ) ;
				if(!$has_posts){continue;}
				?>
                        
                    <option opt_label="<?php echo get_post_type_archive_link($post_type);?>" value="<?php echo $post_type;?>" <?php if(isset($_REQUEST['stype'])){if($post_type == $_REQUEST['stype']){echo 'selected="selected"';}}elseif($curr_post_type==$post_type){echo 'selected="selected"';}?>><?php _e(ucfirst($info->labels->name),GEODIRECTORY_TEXTDOMAIN);?></option>
                        
               	<?php endforeach; ?>
                </select>
                <?php elseif(!empty($post_types)):
					echo '<input type="hidden" name="stype" value="'. key($post_types) .'"  />';    
                endif; ?>
                
                <input class="search_text" name="s" value="<?php if(isset($_REQUEST['s']) && trim($_REQUEST['s']) != '' ){ echo $_REQUEST['s'];}else{echo $default_search_for_text;} ?>" type="text" onblur="if (this.value == '') {this.value = '<?php echo $default_search_for_text;?>';}"  onfocus="if (this.value == '<?php echo $default_search_for_text;?>') {this.value = '';}" onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);">
                
                
                <?php 
				if(isset($_REQUEST['snear']) && $_REQUEST['snear']!=''){
					$near = stripslashes($_REQUEST['snear']);
				}else{$near = $default_near_text;}
				
				?>
                <input name="snear" class="snear" type="text" value="<?php echo $near;?>" onblur="if (this.value == '') {this.value = '<?php echo $default_near_text;?>';}"  onfocus="if (this.value == '<?php echo $default_near_text;?>') {this.value = '';}" onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);"  />
               
                <input type="button" value="<?php echo $default_search_button_label; ?>" class="geodir_submit_search">
               <?php do_action('geodir_after_search_button');?> 
            </div>    
						
						 
						  
           	
            
        </div>
				
				<?php do_action('geodir_after_search_form') ?>
				
		             
        
    </div>				
	<input name="sgeo_lat" class="sgeo_lat" type="hidden" value="" />
    <input name="sgeo_lon" class="sgeo_lon" type="hidden" value="" />   
</form>
