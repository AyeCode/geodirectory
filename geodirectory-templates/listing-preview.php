<?php 
header("X-XSS-Protection: 0");
get_header(); 

do_action('geodir_before_main_content'); 
	
	
	foreach($_REQUEST as $pkey=>$pval){
	if($pkey=='geodir_video'){$tags= '<iframe>';}
  elseif($pkey=='post_desc'){$tags= '<p><a><b><i><em><h1><h2><h3><h4><h5><ul><ol><li><img><div><del><ins><span><cite><code><strike><strong><blockquote>';}
  elseif($pkey=='geodir_special_offers'){$tags= '<p><a><b><i><em><h1><h2><h3><h4><h5><ul><ol><li><img><div><del><ins><span><cite><code><strike><strong><blockquote>';}
  elseif(is_array($_REQUEST[$pkey])){$tags='skip_field';}
  else{$tags='';}

		$tags = apply_filters('geodir_save_post_key', $tags,$pkey);
		if($tags!='skip_field'){
			$_REQUEST[$pkey] = strip_tags($_REQUEST[$pkey], $tags);
		}
		
 }
	

	$post = (object)$_REQUEST;
	
	if(isset($post->video))
	$post->video = stripslashes($post->video);
	
	if(isset($post->Video2))
		$post->Video2 = stripslashes($post->Video2);
	
	$post_type = $post->listing_type;
	$post_type_info = get_post_type_object( $post_type );
	
	
	$listing_label = $post_type_info->labels->singular_name;
	
	$term_icon = '';
	
	if(!empty($post->post_category)){
		foreach($post->post_category as $post_taxonomy => $post_term){
		
			if($post_term != '' && !is_array($post_term))
				$post_term = explode(',', trim($post_term,','));
			
			
			$post_term = array_unique($post_term);
			
				if(!empty($post_term)){
					foreach($post_term as $cat_id){
						$cat_id = trim($cat_id);
						
						if($cat_id != ''){
							/*if($term_icon_url = get_tax_meta($cat_id,'ct_cat_icon'))
								$term_icon = $term_icon_url['src'];*/
							
							if(isset($post->post_default_category) && $post->post_default_category == $cat_id)
							{
								if($term_icon_url = get_tax_meta($cat_id,'ct_cat_icon'))
									$term_icon = $term_icon_url['src'];
								break;
							}
						}
						
					}
				}
		}							
	}
	
	$post_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
	$post_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
	
	$json ='{';
	$json .= '"post_preview": "1",';
	$json .= '"lat_pos": "'.$post_latitude.'",';
	$json .= '"long_pos": "'.$post_longitude.'",';
	$json .= '"icon":"'.$term_icon.'",';
	$json .= '"group":""';
	$json .= '}';
	
	$post->marker_json = $json;
	
	$_SESSION['listing'] = serialize($_REQUEST);
	
	
	?>
    		
<div id="geodir_wrapper">
    
	<?php geodir_get_template_part('preview','buttons'); ?>
    
    <div class="clearfix">
        <div id="geodir_content">                        
             
            <?php do_action('geodir_before_post_preview', $post); 
            
            geodir_breadcrumb();?>
        
            <h1><?php echo (stripslashes($post->post_title)); ?></h1>   
            
            <!-- Post Images slider start --> 
            <?php 
			
            $post->post_images = trim($post->post_images,",");
            if(!empty($post->post_images))
		    	$post_images = explode(",",$post->post_images);
       
			 
			 $main_slides = '';
			 $nav_slides = '';     
			
			if(empty($post_images)){
				$default_img = '';
				
				$default_cat = '';
				if(isset($post->post_default_category))
					$default_cat = $post->post_default_category;
				
				if($default_catimg = geodir_get_default_catimage($default_cat))
					$default_img = $default_catimg['src'];
				elseif($no_images = get_option('geodir_listing_no_img')){
					$default_img = $no_images;
				}
				
				if(!empty($default_img)){
					$post_images[] = $default_img;
				}
			}        
            $slides = 0;
            
            if(!empty($post_images)){
                foreach($post_images as $image){
                 if(!empty($image)){
					@list($width, $height) = getimagesize(trim($image));
		
					if ( $image && $width && $height )
							$image = (object)array( 'src' => $image, 'width' => $width, 'height' => $height );    
					
					
					if(isset($image->src)){
					
						if($image->height >= 400){
							$spacer_height = 0;
						}else{
							$spacer_height = ((400-$image->height)/2);
						}
							
							$image_title = isset($image->title) ? $image->title : '';
							
						$main_slides .=	'<li><img src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'"  alt="'.$image_title.'" title="'.$image_title.'" style="max-height:'.$spacer_height.'px;margin:0 auto;" width="100%" />';
						$main_slides .=	'<img src="'.$image->src.'"  alt="'.$image_title.'" title="'.$image_title.'" style="max-height:400px;margin:0 auto;" /></li>';
							
							
						$nav_slides .=	'<li><img src="'.$image->src.'"  alt="'.$image_title.'" title="'.$image_title.'"style="max-height:48px;margin:0 auto;" /></li>';
						
					
											
											$slides++;
						}
					}					
                }// endfore
            } //end if
			
			
			if(!empty($post_images)){
            ?>
            <div class="flex-container" >	
                <div class="flex-loader"></div> 
                <div id="slider" class="flexslider">
                  <ul class="slides">
                        <?php echo $main_slides;?>
                  </ul>
                </div>
                <?php if( $slides > 1){ ?>
                    <div id="carousel" class="flexslider">
                      <ul class="slides">
                            <?php echo $nav_slides;?>
                      </ul>
                    </div>
                <?php } ?>
            </div>
            <!-- Post Images slider end --> 
             <?php } ?>
            
            <!-- Post terms start --> 
            <p class="geodir_post_bottom clearfix"> 
                <?php 
                    $taxonomies = array();
                    
                    if(!empty($post->post_tags)){
                       
                        if(taxonomy_exists($post_type.'_tags')):
                            $links = array();
                            $terms = array();
                            $post_tags = explode(",",trim($post->post_tags,","));
                        	
							foreach($post_tags as $post_term){
        						
								$post_term = trim($post_term);
							   
							    if($insert_term = term_exists( $post_term, $post_type.'_tags' )){
                                    $term = get_term_by( 'name', $post_term, $post_type.'_tags'); 
                                }else{
                                    $insert_term = wp_insert_term($post_term, $post_type.'_tags');
                                     $term = get_term_by( 'name', $post_term, $post_type.'_tags');
                                }	
                                
                                if(! is_wp_error( $term ))
                                {	
                                    $links[] = "<a href='" . esc_attr( get_tag_link($term->term_id) ) . "'>$term->name</a>";
                                    $terms[] = $term;
                                }
                            }
                        
                            $taxonomies[$post_type.'_tags'] = wp_sprintf('%s: %l.', ucwords($listing_label.' Tags'), $links, (object)$terms);
                        endif;	
                        
                    }
                    
					if(!empty($post->post_category)){
					
						$links = array();
                        $terms = array();
                        
                        foreach($post->post_category as $post_taxonomy => $post_term){
														
														if($post_term != '' && !is_array($post_term))
															$post_term = explode(',', trim($post_term,','));
														
                            $post_term = array_unique($post_term);
							
							if(!empty($post_term)){
								foreach($post_term as $post_term){
									$post_term = trim($post_term);
									
									if($post_term != ''):	
										$term = get_term_by( 'id', $post_term, $post_taxonomy); 
									  
										$links[] = "<a href='".esc_attr( get_term_link($term,$post_taxonomy) ) . "'>$term->name</a>";
										$terms[] = $term;
									endif;
								}
							}
							break;
						}
                        $taxonomies[$post_taxonomy] = wp_sprintf('%s: %l.', ucwords($listing_label.' Category'), $links, (object)$terms);
                        
                    }
                    
                    
                    echo '<span class="geodir-category">' . $taxonomies[$post_taxonomy] . '</span>';	
										
										if(isset($taxonomies[$post_type.'_tags']))		
                    echo '<span class="geodir-tags">' . $taxonomies[$post_type.'_tags'] . '</span>';
                    ?>
                    
            </p>
            <!-- Post terms end --> 
            
            <!-- Post info tabs start -->     
            <script type="text/javascript">
               jQuery(function() {
                    jQuery('#post-gallery a').lightBox({
                        overlayOpacity : 0.5,
                        imageLoading : '<?php echo geodir_plugin_url().'/geodirectory-assets/images/lightbox-ico-loading.gif';?>',
                        imageBtnNext : '<?php echo geodir_plugin_url().'/geodirectory-assets/images/lightbox-btn-next.gif';?>',
                        imageBtnPrev : '<?php echo geodir_plugin_url().'/geodirectory-assets/images/lightbox-btn-prev.gif';?>',
                        imageBtnClose : '<?php echo geodir_plugin_url().'/geodirectory-assets/images/lightbox-btn-close.gif';?>',
                        imageBlank : '<?php echo geodir_plugin_url().'/geodirectory-assets/images/lightbox-blank.gif';?>'
                    });
                });
            </script>
            <?php
						 $geodir_post_detail_fields = geodir_show_listing_info('detail');
						
						?>
               
			   
			  
			   
			   
               
            <div class="geodir-tabs" id="gd-tabs">
                <dl class="geodir-tab-head">
                    <dd class="geodir-tab-active" >
                        <a data-tab="#post_profile" data-status="enable"><?php _e('Profile',GEODIRECTORY_TEXTDOMAIN);?></a>
                    </dd>
                    <?php if(!empty($geodir_post_detail_fields)){?>
                    <dd>
                        <a data-tab="#post_info" data-status="enable"><?php _e('More Info',GEODIRECTORY_TEXTDOMAIN);?></a>
                    </dd>
                    <?php }?>
                    <dd><a data-tab="#post_images" data-status="enable"><?php _e('Photo',GEODIRECTORY_TEXTDOMAIN);?></a></dd>
                    
                    <?php $video = isset($post->geodir_video) ? $post->geodir_video : ''; if($video){?>
                    <dd><a data-tab="#post_video" data-status="enable"><?php _e('Video',GEODIRECTORY_TEXTDOMAIN);?></a></dd>
                    <?php } ?>
                    
                    <?php $special_offers = '';
										if(isset($_POST['geodir_special_offers']) && $_POST['geodir_special_offers'] != ''){ $special_offers = stripslashes($_POST['geodir_special_offers']);} if($special_offers){?>
                    <dd><a data-tab="#special_offers" data-status="enable"><?php _e('Special Offers',GEODIRECTORY_TEXTDOMAIN);?></a></dd>
                    <?php } ?>
                    
                    <dd><a data-tab="#post_map" data-status="enable"><?php _e('Map',GEODIRECTORY_TEXTDOMAIN);?></a></dd>
                </dl>
                <ul class="geodir-tabs-content" style="z-index:-999; position:relative;">
                    <li id="post_profileTab">
                    <?php 
										do_action('geodir_before_description_on_listing_preview');
										echo apply_filters( 'the_content', stripslashes($post->post_desc) ); 
										do_action('geodir_after_description_on_listing_preview');
										?>
                    </li>
                    
                    <?php if(!empty($geodir_post_detail_fields)){?>
                    <li id="post_infoTab">
                    <div id="post_info" class="hash-offset"></div>
                    <?php 
					echo '<div class="geodir-company_info field-group">'.$geodir_post_detail_fields.'</div>';
                    ?>
                    </li>
                    <?php } ?>
                    
                    <?php	if($video){ ?>
                    <li id="post_videoTab">
                    <div id="post_video" class="hash-offset"></div>
                        <div id="post_video-wrap" class="clearfix"> 
                        <?php	echo stripslashes($video); ?></div>
                    </li>
                    <?php } ?>
                    
                    <?php	if($special_offers){ ?>
                    <li id="special_offersTab">
                     <div id="special_offers" class="hash-offset"></div>
                        <div id="special_offers-wrap" class="clearfix"> 
                        <?php	echo wpautop(stripslashes($special_offers)); ?></div>
                    </li>
                    <?php } ?>
                    
                    <li id="post_imagesTab">
                    <div id="post_images" class="hash-offset"></div>
                        <div id="geodir-post-gallery" class="clearfix" >
                        <?php 
                        
                        if(!empty($post_images)){
							foreach($post_images as $image){
								if($image != ''){	   
								$thumb_image = '';
								$thumb_image .=	'<a href="'.$image.'">';
								$thumb_image .= geodir_show_image(array('src'=>$image),'thumbnail',true,false);
								$thumb_image .= '</a>';	
								echo $thumb_image;
								}
							}// endfore
						}
                        ?>
                        </div>
                    </li>
                    <li id="post_mapTab">
                    <div id="post_map" class="hash-offset"></div>
                        <?php //include_once( geodir_plugin_path().'/map/single_post_maptab.php'); 
								global $map_jason ;
								$map_jason[] = $post->marker_json;
								
								
								$address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
								$address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
								$mapview = isset($post->post_mapview) ? $post->post_mapview : '';
								$mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
								if(!$mapzoom){$mapzoom=12;}
								
								
								$map_args = array();
								$map_args['map_canvas_name'] = 'preview_map_canvas' ;
								$map_args['width'] = '950';
								$map_args['height'] = '300';
								$map_args['child_collapse'] = '0';
								$map_args['maptype'] = $mapview;
								$map_args['autozoom'] =  false;
								$map_args['zoom'] =  "$mapzoom";
								$map_args['latitude'] = $address_latitude;
								$map_args['longitude'] = $address_longitude;
								$map_args['enable_cat_filters'] = false;
								$map_args['enable_text_search'] = false;
								$map_args['enable_post_type_filters'] = false;
								$map_args['enable_location_filters'] = false;
								$map_args['enable_jason_on_load'] = true;
								$map_args['enable_map_direction'] = true;
								geodir_draw_map($map_args);
							
							?>   
						  
                    </li>
                </ul> <!--gd-tabs-content ul end-->
            </div> <!--gd-tabs div end--> 
            <!-- Post info tabs start -->                        
         
            <?php do_action('geodir_after_post_preview', $post);?>
            
        </div>		
		<?php     
        
        do_action('geodir_after_main_content');
    
        geodir_get_template_part('detail','sidebar');
		
        ?>
    </div>
</div>    
<?php get_footer();   