<?php global $wp_query,$current_term,$query;

$curr_post_type = geodir_get_current_posttype();	

?>


 <form id="listing_search" name="listing_search" action="<?php echo home_url();?>" method="get" >
    <input type="hidden" name="geodir_search" value="1"  />
    
    <div class="geodir-loc-bar">
				
				<?php do_action('geodir_before_search_form') ?>
				
       <div class="clearfix geodir-loc-bar-in">
           
					  <div class="geodir-search">
                               
				<?php 
				$post_types = geodir_get_posttypes('object'); 
				
				if(!empty($post_types) && count((array)$post_types) > 1 ):
				?>
                <select name="stype" id="search_by_post" >
				<?php foreach( $post_types as $post_type => $info ): ?>
                        
                    <option opt_label="<?php echo get_post_type_archive_link($post_type);?>" value="<?php echo $post_type;?>" <?php if(isset($_REQUEST['stype'])){if($post_type == $_REQUEST['stype']){echo 'selected="selected"';}}elseif($curr_post_type==$post_type){echo 'selected="selected"';}?>><?php _e(ucfirst($info->labels->singular_name),GEODIRECTORY_TEXTDOMAIN);?></option>
                        
               	<?php endforeach; ?>
                </select>
                <?php elseif(!empty($post_types)):
					echo '<input type="hidden" name="stype" value="'. key($post_types) .'"  />';    
                endif; ?>
                
                <input id="search_text" name="s" value="<?php if(isset($_REQUEST['s']) && trim($_REQUEST['s']) != '' ){ echo $_REQUEST['s'];}else{echo SEARCH_FOR_TEXT;} ?>" type="text" onblur="if (this.value == '') {this.value = '<?php echo SEARCH_FOR_TEXT;?>';}"  onfocus="if (this.value == '<?php echo SEARCH_FOR_TEXT;?>') {this.value = '';}" >
                
                
                <?php 
				if(isset($_REQUEST['snear']) && $_REQUEST['snear']!=''){
					$near = stripslashes($_REQUEST['snear']);
				}else{$near = NEAR_TEXT;}
				
				?>
                <input name="snear" id="snear" type="text" value="<?php echo $near;?>" onblur="if (this.value == '') {this.value = '<?php echo NEAR_TEXT;?>';}"  onfocus="if (this.value == '<?php echo NEAR_TEXT;?>') {this.value = '';}"   />
               
                <input type="button" value="Search" class="geodir_submit_search">
               
            </div>    
						
						<?php do_action('geodir_after_search_button');?>  
						  
           	
            
        </div>
				
				<?php do_action('geodir_after_search_form') ?>
				
		             
        
    </div>				
	<input name="sgeo_lat" id="sgeo_lat" type="hidden" value="" />
    <input name="sgeo_lon" id="sgeo_lon" type="hidden" value="" />   
</form>
