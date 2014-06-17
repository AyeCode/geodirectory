<?php
    global $wpdb,$post;
    
    $post_type = $post->listing_type;
    
	if(isset($_REQUEST['preview']) && $_REQUEST['preview'] && isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
		$form_action_url = geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=update&listing_type='.$post_type;
	}elseif(isset($_REQUEST['preview']) && $_REQUEST['preview']){
		$form_action_url = geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=publish&listing_type='.$post_type;
	}
	
	$form_action_url = apply_filters('geodir_publish_listing_form_action' , $form_action_url ) ;
	
	
?>
<?php do_action('geodir_before_publish_listing_form') ;
ob_start()// start publish listing form buffering 
?>
<div class="geodir_preview_section" >

	<form action="<?php echo $form_action_url; ?>" name="publish_listing" id="publish_listing" method="post">
    	<div class="clearfix">
		<input type="hidden" name="pid" value="<?php if(isset($post->pid)){  echo $post->pid;}?>">
        <?php do_action('geodir_publish_listing_form_before_msg') ;?>    
        <?php
                        $alive_days = UNLIMITED;
                        $type_title = '';
						ob_start();
                        echo '<h5 class="geodir_information">';
                        
                        if(!isset($_REQUEST['pid']) )
                            printf(GOING_TO_FREE_MSG, $type_title,$alive_days);
                        else
                            printf(GOING_TO_UPDATE_MSG, $type_title ,$alive_days);
                        
                        echo '</h5>';
						$publish_listing_form_message = ob_get_clean();
						$publish_listing_form_message = apply_filters('geodir_publish_listing_form_message',$publish_listing_form_message ) ;
						echo $publish_listing_form_message ;
					
					do_action('geodir_publish_listing_form_after_msg') ;
						
						ob_start(); // start action button buffering
		?>
              <?php if(isset($_REQUEST['pid']) && $_REQUEST['pid']!='') { ?> 
            
            <input type="submit" name="Submit and Pay" value="<?php echo PRO_UPDATE_BUTTON;?>" class="geodir_button geodir_publish_button" />
            <?php } else { ?>
				<input type="submit" name="Submit and Pay" value="<?php echo PRO_SUBMIT_BUTTON;?>" class=" geodir_button geodir_publish_button" />
				<?php		
				}
					$publish_listing_form_button = ob_get_clean();
					$publish_listing_form_button = apply_filters('geodir_publish_listing_form_button',$publish_listing_form_button) ;
					echo $publish_listing_form_button ;
						
					
			
					$post_id = '';
					if(isset($post->pid)){
						$post_id = $post->pid;
					}elseif(isset($_REQUEST['pid'])){
						$post_id = $_REQUEST['pid'];
					}
					$postlink = get_permalink( get_option('geodir_add_listing_page') );
					$postlink = geodir_getlink($postlink,array('pid'=>$post_id,'backandedit'=>'1','listing_type'=>$post_type),false);
					
					ob_start(); // start go back and edit / cancel buffering		 
            ?>
            <a href="<?php echo $postlink;?>" class="geodir_goback" ><?php echo PRO_BACK_AND_EDIT_TEXT;?></a>
           <input type="button" name="Cancel" value="<?php echo (PRO_CANCEL_BUTTON);?>" class="geodir_button geodir_cancle_button"  onclick="window.location.href='<?php echo geodir_get_ajax_url().'&geodir_ajax=add_listing&ajax_action=cancel&pid='.$post_id.'&listing_type='.$post_type;?>'" />
        	<?php
            	
					$publish_listing_form_go_back = ob_get_clean();
					$publish_listing_form_go_back = apply_filters('geodir_publish_listing_form_go_back',$publish_listing_form_go_back) ;
					echo $publish_listing_form_go_back ;
						
			?>
        </div>
    </form> 
</div>
<?php 
$publish_listing_form = ob_get_clean();
$publish_listing_form = apply_filters('geodir_publish_listing_form',$publish_listing_form) ;
echo $publish_listing_form ;

do_action('geodir_after_publish_listing_form') ;
?>