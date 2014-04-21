<?php 
if(!isset($_REQUEST['backandedit'])){ unset($_SESSION['listing']); }

get_header(); 

global $cat_display,$post_cat, $current_user;
 
 $title = '';
 $desc = '';
 $kw_tags = '';
 $required_msg = '';
 $submit_button = '';
	
	if(isset($_REQUEST['ajax_action'])) $ajax_action = $_REQUEST['ajax_action'];  else $ajax_action = 'add';
	
	$thumb_img_arr = array();
	$curImages = '';
	
	if(isset($_REQUEST['backandedit'])){
		$post = (object)unserialize($_SESSION['listing']);
		$listing_type = $post->listing_type;	
		$title = $post->post_title;
		$desc = $post->post_desc;
		/*if(is_array($post->post_category) && !empty($post->post_category))
			$post_cat = $post->post_category;
		else*/
			$post_cat = $post->post_category;	
			
		$kw_tags = $post->post_tags;
		$curImages = $post->post_images;
	}elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
		
		global $post,$post_images;
		/*query_posts(array('p'=>$_REQUEST['pid']));
		if ( have_posts() ) while ( have_posts() ) the_post(); global $post,$post_images;*/
		
		$post = geodir_get_post_info($_REQUEST['pid']);
		$thumb_img_arr = geodir_get_images($post->ID);
		if($thumb_img_arr){
			foreach($thumb_img_arr as $post_img){	
				$curImages .= $post_img->src.',';
			}
		}
		
		$listing_type = $post->post_type;
		$title = $post->post_title;
		$desc = $post->post_content;
		$post_cat = $post->categories;
		$kw_tags = $post->post_tags;
		$kw_tags = implode(",",wp_get_object_terms($post->ID,$listing_type.'_tags' ,array('fields'=>'names')));
	}else{
		$listing_type = $_REQUEST['listing_type'];
	}
		
	if($current_user->ID != '0'){$user_login = true;} 
?>


<div id="geodir_wrapper">
<div class="clearfix">
    <div id="geodir_content">
                          
        <h1><?php 
            
            if(isset($_REQUEST['pid']) && $_REQUEST['pid']!= ''){
                $post_type_info = geodir_get_posttype_info($listing_type);	
                echo apply_filters('geodir_add_listing_page_title',( ucwords(__('Edit ',GEODIRECTORY_TEXTDOMAIN).$post_type_info['labels']['singular_name'])));
            }elseif(isset($listing_type)){ 
                $post_type_info = geodir_get_posttype_info($listing_type);	
                 echo apply_filters('geodir_add_listing_page_title',( ucwords(__('Add ',GEODIRECTORY_TEXTDOMAIN).$post_type_info['labels']['singular_name'])));
            }else{ apply_filters('geodir_add_listing_page_title',the_title()); } ?>
            
        </h1>
    
        <p class="geodir-note ">
            <span class="geodir-required">*</span>&nbsp;<?php echo INDICATES_MANDATORY_FIELDS_TEXT;?>
        </p>
        
            <!-- ######## Listing Form Start ########   -->
            
            <form name="propertyform" id="propertyform" action="<?php echo get_page_link(get_option('geodir_preview_page'));?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="preview" value="<?php echo $listing_type;?>" />
                <input type="hidden" name="listing_type" value="<?php echo $listing_type;?>" />
               	<?php if(isset($_REQUEST['pid']) && $_REQUEST['pid'] !='') { ?>
                <input type="hidden" name="pid" value="<?php echo $_REQUEST['pid'];?>" />
                <?php } ?>
                <?php if(isset($_REQUEST['backandedit'])) { ?>
                <input type="hidden" name="backandedit" value="<?php echo $_REQUEST['backandedit'];?>" />
                <?php } ?>
                   <?php do_action('geodir_before_detail_fields');?>
                        
                    <h5><?php echo LISTING_DETAILS_TEXT ;?></h5>
                   
									  <?php do_action('geodir_before_main_form_fields');?>
										
                    <div class="required_field geodir_form_row clearfix">
                        <label><?php echo PLACE_TITLE_TEXT;?><span>*</span> </label>
                        <input type="text" field_type="text" name="post_title" id="post_title" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($title)); ?>"  />
                       <span class="geodir_message_error"><?php echo $required_msg?></span>
                    </div>
                    
										<?php do_action('geodir_before_description_field'); ?>
										
                    <div class="required_field geodir_form_row clearfix">
                        <label><?php echo PLACE_DESC_TEXT;?><span>*</span> </label>
												
												<?php
												$show_editor = get_option('geodir_tiny_editor_on_add_listing');
												
												if(!empty($show_editor) && in_array($listing_type,$show_editor)){
													
													$editor_settings = array('media_buttons'=>false, 'textarea_rows'=>10);?>
													
													<div class="editor" field_id="post_desc" field_type="editor">
													<?php wp_editor( stripslashes($desc), "post_desc", $editor_settings ); ?>
													</div><?php
												
												}else{
												
                       		?><textarea field_type="textarea" name="post_desc" id="post_desc" class="geodir_textarea" ><?php echo esc_attr(stripslashes($desc)); ?></textarea><?php
											 
												} ?>
											 
                       <span class="geodir_message_error"><?php echo $required_msg?></span>
                    </div>
										
										<?php do_action('geodir_after_description_field'); ?>
                  
                    <div class="geodir_form_row clearfix" >
                        <label><?php echo TAGKW_TEXT; ?></label>
                         <input name="post_tags" id="post_tags" value="<?php echo esc_attr(stripslashes($kw_tags)); ?>" type="text" class="geodir_textfield" maxlength="<?php echo TAGKW_TEXT_COUNT;?>"  />
                         <span class="geodir_message_note"><?php echo TAGKW_MSG;?></span>
                    </div>
                  
                   <?php 
							
							$package_info = array() ;
							
							$package_info = geodir_post_package_info($package_info , $post);
					
				   		geodir_get_custom_fields_html($package_info->pid,'all',$listing_type);?>
              
                    
                    <?php 
                    // adjust values here
                    $id = "post_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls
                     
                    $multiple = true; // allow multiple files upload
                     
                    $width = 800; // If you want to automatically resize all uploaded images then provide width here (in pixels)
                     
                    $height = 800; // If you want to automatically resize all uploaded images then provide height here (in pixels)
					
					$thumb_img_arr = array();
					$totImg = 0;
					if(isset($_REQUEST['backandedit']) && empty($_REQUEST['pid']))
					{
						$post = (object)unserialize($_SESSION['listing']);
						$curImages  = trim($post->post_images,",");
						
						
						if($curImages != ''){
							$curImages_array = explode(',',$curImages);						
							$totImg = count($curImages_array);
						}
						
						$listing_type = $post->listing_type;
					
					}elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
					{
						$post = geodir_get_post_info($_REQUEST['pid']);
						$listing_type = $post->post_type;
						$thumb_img_arr = geodir_get_images($_REQUEST['pid']);
					
					}else
					{
						$listing_type = $_REQUEST['listing_type'];
					}
		
	
					if(!empty($thumb_img_arr))
					{
						foreach($thumb_img_arr as $img){
							//$curImages = $img->src.",";
						}	
						
						$totImg = count((array)$thumb_img_arr);
					}	
					
					if($curImages != '')
					$svalue = $curImages; // this will be initial value of the above form field. Image urls.
					else
					$svalue = '';		
                    
					$image_limit = $package_info->image_limit;
                    ?>
                    
					<h5 class="geodir-form_title"> <?php echo  PRO_PHOTO_TEXT;?>
                         <?php if($image_limit!=0 && $image_limit==1 ){echo '<br /><small>('.__('You can upload',GEODIRECTORY_TEXTDOMAIN).' '.$image_limit.' '.__('image with this package',GEODIRECTORY_TEXTDOMAIN).')</small>';} ?>
                         <?php if($image_limit!=0 && $image_limit>1 ){echo '<br /><small>('.__('You can upload',GEODIRECTORY_TEXTDOMAIN).' '.$image_limit.' '.__('images with this package',GEODIRECTORY_TEXTDOMAIN).')</small>';} ?>
                         <?php if($image_limit==0){echo '<br /><small>('.__('You can upload unlimited images with this package',GEODIRECTORY_TEXTDOMAIN).')</small>';} ?>
                    </h5>
					 
                    <div class="geodir_form_row clearfix" id="<?php echo $id; ?>dropbox" align="center" style="border:1px solid #ccc; min-height:100px; height:auto; padding:10px;">
                        <input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>" />
						<input type="hidden" name="<?php echo $id; ?>image_limit" id="<?php echo $id; ?>image_limit" value="<?php echo $image_limit; ?>" />
						<input type="hidden" name="<?php echo $id; ?>totImg" id="<?php echo $id; ?>totImg" value="<?php echo $totImg; ?>" />
                        <div class="plupload-upload-uic hide-if-no-js <?php if ($multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $id; ?>plupload-upload-ui">
                            <h4><?php _e('Drop files to upload',GEODIRECTORY_TEXTDOMAIN);?></h4><br/>
                            <input id="<?php echo $id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files',GEODIRECTORY_TEXTDOMAIN); ?>" class="geodir_button"  />
                            <span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($id.'pluploadan'); ?>"></span>
                            <?php if ($width && $height): ?>
                                <span class="plupload-resize"></span>
                                <span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
                                <span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
                            <?php endif; ?>
                            <div class="filelist"></div>
                        </div>
                    
                        <div class="plupload-thumbs <?php if ($multiple): ?>plupload-thumbs-multiple<?php endif; ?> clearfix" id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
                        </div>
                        <span id="upload-msg" ><?php _e('Please drag &amp; drop the images to rearrange the order',GEODIRECTORY_TEXTDOMAIN);?></span>
                        <span id="<?php echo $id; ?>upload-error" style="display:none"></span>
                    </div>
                
								<?php do_action('geodir_after_main_form_fields');?>
								
                    <div id="geodir-add-listing-submit" class="geodir_form_row clear_both" align="center" style="padding:2px;">            			<input type="submit" value="<?php echo PRO_PREVIEW_BUTTON;?>" class="geodir_button" <?php echo $submit_button;?>/>
                        <span class="geodir_message_note" style="padding-left:0px;"> <?php _e('Note: You will be able to see a preview in the next page',GEODIRECTORY_TEXTDOMAIN);?></span>
                    </div>
             
              </form>
    </div><!-- content ends here-->
	<?php wp_reset_query();?>
    <div id="gd-sidebar-wrapper">
    <div class="geodir-sidebar-main" >
        <div class="geodir-gd-sidebar">
            <?php do_action('geodir_sidebar'); ?>
        </div>
    </div>
</div>  <!-- gd-sidebar-wrapper ends here-->
</div>             
</div>  <!-- geodir-wrapper ends here-->
<?php get_footer();     