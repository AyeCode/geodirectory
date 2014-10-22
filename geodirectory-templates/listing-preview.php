<?php 
header("X-XSS-Protection: 0");
get_header(); 

do_action('geodir_before_main_content','listing-preview-page'); 

	
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
							$term_icon = get_option('geodir_default_marker_icon');
							if(isset($post->post_default_category) && $post->post_default_category == $cat_id)
							{
								if($term_icon_url = get_tax_meta($cat_id, 'ct_cat_icon', false, $post_type)){
									if(isset($term_icon_url['src']) && $term_icon_url['src'] != '')
									 $term_icon = $term_icon_url['src'];
										break;
								}
							}
						}
						
					}
				}
		}							
	}
	
	$post_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
	$post_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
	
	$srcharr = array("'","/","-",'"','\\');
	$replarr = array("&prime;","&frasl;","&ndash;","&ldquo;",'');
	
	$json_title = str_replace($srcharr,$replarr,$post->post_title);
	
	$json ='{';
	$json .= '"post_preview": "1",';
	$json .= '"t": "'.$json_title.'",';
	$json .= '"lt": "'.$post_latitude.'",';
	$json .= '"ln": "'.$post_longitude.'",';
	$json .= '"i":"'.$term_icon.'"';
	$json .= '}';
	
	$post->marker_json = $json;
	
	$_SESSION['listing'] = serialize($_REQUEST);
	
	
	?>
    		
<div id="geodir_wrapper">
    
	<?php geodir_get_template_part('preview','buttons'); ?>
    
    <?php geodir_breadcrumb();?>
    
    <div class="clearfix geodir-common">
        <div id="geodir_content">                        
             
            <?php do_action('geodir_before_post_preview', $post); ?>
        
            <h1><?php echo (stripslashes($post->post_title)); ?></h1>   
            
            <!-- Post Images slider start --> 
            <?php 
			
						if(isset($post->post_images))
            	$post->post_images = trim($post->post_images,",");
							
            if(isset($post->post_images) && !empty($post->post_images))
		    	$post_images = explode(",",$post->post_images);
       
			 
			 $main_slides = '';
			 $nav_slides = '';     
			
			if(empty($post_images)){
				$default_img = '';
				
				$default_cat = '';
				if(isset($post->post_default_category))
					$default_cat = $post->post_default_category;
				
				if($default_catimg = geodir_get_default_catimage($default_cat,$post_type))
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
							
						$main_slides .=	'<li><img src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'"  alt="'.$image_title.'" title="'.$image_title.'" style="max-height:'.$spacer_height.'px;margin:0 auto;" />';
						$main_slides .=	'<img src="'.$image->src.'"  alt="'.$image_title.'" title="'.$image_title.'" style="max-height:400px;margin:0 auto;" /></li>';
							
							
						$nav_slides .=	'<li><img src="'.$image->src.'"  alt="'.$image_title.'" title="'.$image_title.'" style="max-height:48px;margin:0 auto;" /></li>';
						
					
											
											$slides++;
						}
					}					
                }// endfore
            } //end if
			
			
			if(!empty($post_images)){
            ?>
            <div class="geodir_flex-container" >	
                <div class="geodir_flex-loader"></div> 
                <div id="geodir_slider" class="geodir_flexslider">
                  <ul class="slides">
                        <?php echo $main_slides;?>
                  </ul>
                </div>
                <?php if( $slides > 1){ ?>
                    <div id="geodir_carousel" class="geodir_flexslider">
                      <ul class="slides">
                            <?php echo $nav_slides;?>
                      </ul>
                    </div>
                <?php } ?>
            </div>
            <!-- Post Images slider end --> 
             <?php } ?>
            
            <!-- Post terms start --> 
            <p class="geodir_post_taxomomies clearfix"> 
                <?php 
                    $taxonomies = array();
                    
                    if(!empty($post->post_tags)){
                       
                        if(taxonomy_exists($post_type.'_tags')):
                            $links = array();
                            $terms = array();
                        	
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
									//$links[] = "<a href='" . esc_attr( get_tag_link($term->term_id) ) . "'>$term->name</a>";
									// fix tag link on detail page
									$links[] = "<a href='" . esc_attr( get_term_link($term->term_id, $term->taxonomy) ) . "'>$term->name</a>";
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
										
					if ( isset( $taxonomies[$post_type.'_tags'] ) ) {		
                   		echo '<span class="geodir-tags">' . $taxonomies[$post_type.'_tags'] . '</span>';
					}
                    ?>   
            </p>
            <!-- Post terms end -->
			<?php if( (int)get_option( 'geodir_disable_gb_modal' ) != 1 ) { ?>
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
			<?php } ?>			   
            <!-- Post info tabs start -->
			<?php geodir_show_detail_page_tabs(); ?>
            <?php do_action('geodir_after_post_preview', $post);?>
        </div>		
		<?php     
        
        do_action('geodir_after_main_content');
    
        geodir_get_template_part('detail','sidebar');
		
        ?>
    </div>
</div>    
<?php get_footer();   