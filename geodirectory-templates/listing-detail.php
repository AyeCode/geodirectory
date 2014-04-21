<?php 
get_header(); 

do_action('geodir_before_main_content'); ?>

<div id="geodir_wrapper">
    
    <?php geodir_breadcrumb(); ?>
    
    <?php if(get_option('geodir_show_detail_top_section')) { ?>
    
    <div class="geodir_full_page clearfix"><?php dynamic_sidebar('geodir_detail_top'); ?></div>
    
    <?php } ?>
       
	<div class="clearfix">       
       
        <div id="geodir_content">
             
        <?php 
        
        
        if ( have_posts() ) while ( have_posts() ) : the_post(); global $post,$post_images;
            
            do_action('geodir_before_single_post', $post); 
            
            $post_type_info = get_post_type_object( $post->post_type );
            $listing_label = $post_type_info->labels->name;
           
            ?><h1><?php the_title();?></h1><?php 
             
						$main_slides = '';
            $nav_slides = '';
						   
            $post_images = geodir_get_images($post->ID,'thumbnail',get_option('geodir_listing_no_img'));
             
            $slides = 0;
            
           if(empty($post_images) && get_option('geodir_listing_no_img')){
							$post_images = (object)array((object)array('src'=>get_option('geodir_listing_no_img')));
						}
						
          
					if(!empty($post_images)){
					foreach($post_images as $image){
                
					if($image->height >= 400){
						$spacer_height = 0;
					}else{
						$spacer_height = ((400 - $image->height)/2);
					}
				    
               
					$main_slides .=	'<li><img src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'"  alt="'.$image->title.'" title="'.$image->title.'" style="max-height:'.$spacer_height.'px;margin:0 auto;" width="100%" />';
					$main_slides .=	'<img src="'.$image->src.'"  alt="'.$image->title.'" title="'.$image->title.'" style="max-height:400px;margin:0 auto;" /></li>';
						
						
					$nav_slides .=	'<li><img src="'.$image->src.'"  alt="'.$image->title.'" title="'.$image->title.'"style="max-height:48px;margin:0 auto;" /></li>';
                    
                    $slides++;
                }}// endfore
				
            if(!empty($post_images)){
            ?>
            <div class="flex-container" >	
                <div class="flex-loader"></div> 
               
                <div id="slider" class="flexslider ">
                  <ul class="slides clearfix">
                        <?php echo $main_slides;?>
                  </ul>
                </div>
                <?php if( $slides > 1){ ?>
                    <div id="carousel" class="flexslider">
                      <ul class="slides clearfix">
                            <?php echo $nav_slides;?>
                      </ul>
                    </div>
                <?php } ?>
                    
            </div>
               <?php } ?> 
                
                <p class="geodir_post_bottom clearfix">  
                    <?php the_taxonomies(array('before'=>'<span class="geodir-tags">','sep'=>'</span><span class="geodir-category">','after'=>'</span>'));?>
                </p>    
               
               <?php 
					$related_listing_array = array();
					if(get_option('geodir_add_related_listing_posttypes'))
						$related_listing_array = get_option('geodir_add_related_listing_posttypes');
					
					$related_listing = '';
					if(in_array($post->post_type, $related_listing_array))
					{	
						$request = array('post_number'=>get_option('geodir_related_post_count'),
											'relate_to'=>get_option('geodir_related_post_relate_to'),
											'layout'=>get_option('geodir_related_post_listing_view'),
											'add_location_filter'=>get_option('geodir_related_post_location_filter'),
											'list_sort'=>get_option('geodir_related_post_sortby'),
											'character_count'=>get_option('geodir_related_post_excerpt'));
									
									$related_listing = geodir_related_posts_display($request);
					}
					
			   $geodir_post_detail_fields = geodir_show_listing_info('detail');
			  
			   ?>
                <div class="geodir-tabs" id="gd-tabs" style="position:relative;">
                   <dl class="geodir-tab-head">
                   <?php do_action('geodir_before_tab_list') ; ?>
                   <?php 
				   		$video = geodir_get_video($post->ID);
						$special_offers = geodir_get_special_offers($post->ID);
				   		$arr_detail_page_tabs= array( 
														array( 
														'hash_key' => 'post_profile',
														'heading_text' =>  __('Profile',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => true,
														'is_display' => true,
														'tab_content' => '' 
														),
											  			array( 
														'hash_key' => 'post_info',
														'heading_text' =>  __('More Info',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => (!empty($geodir_post_detail_fields)) ? true : false,
														'tab_content' => ''
														),
												
														array( 
														'hash_key' => 'post_images',
														'heading_text' =>  __('Photo',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => (!empty($post_images)) ? true : false,
														'tab_content' => ''
														),	
												
														array( 
														'hash_key' => 'post_video',
														'heading_text' =>  __('Video',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => (!empty($video)) ? true : false,
														'tab_content' => ''
														),	
												
														array( 
														'hash_key' => 'special_offers',
														'heading_text' =>  __('Special Offers',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => (!empty($special_offers)) ? true : false,
														'tab_content' => ''
														),	
												
														array(
														'hash_key' => 'post_map',
														'heading_text' =>  __('Map',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => true,
														'tab_content' => ''
														),
												
														array( 
														'hash_key' => 'reviews',
														'heading_text' =>  __('Reviews',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => true,
														'tab_content' => ''
														),
														
														array( 
														'hash_key' => 'related_listing',
														'heading_text' =>  __('Related Listing',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' => (strpos($related_listing,__('No listings found which match your selection.',GEODIRECTORY_TEXTDOMAIN)) !== false || $related_listing == '') ? false : true,
														'tab_content' => ''
														),
																	
												); // end of arr_detail_page_tabs
						$arr_detail_page_tabs = apply_filters('geodir_modify_detail_page_tab_list' ,$arr_detail_page_tabs )	;
						foreach($arr_detail_page_tabs as $tab_index => $detail_page_tab)
						{
							if($detail_page_tab['is_display'])
							{
						?>
                            <dd <?php if($detail_page_tab['is_active_tab']){?>class="geodir-tab-active"<?php }?> >
                                <a data-tab="#<?php echo $detail_page_tab['hash_key']?>" data-status="enable"><?php echo $detail_page_tab['heading_text'] ;?></a>
                            </dd>
                            
							<?php
                            ob_start() // start tab content buffering 
                            ?>
							 <li id="<?php echo $detail_page_tab['hash_key']?>Tab">
                             	<div id="<?php echo $detail_page_tab['hash_key']?>"  class="hash-offset"></div>
                             <?php 
							 	do_action('geodir_before_tab_content' ,$detail_page_tab['hash_key'] );
								do_action('geodir_before_' . $detail_page_tab['hash_key'].'_tab_content');
						   		/// write a code to generate content of each tab 
								switch($detail_page_tab['hash_key']){
						   			case 'post_profile':
											do_action('geodir_before_description_on_listing_detail');
											the_content();
											do_action('geodir_after_description_on_listing_detail');
										break;
						  	 	case 'post_info':
								        echo $geodir_post_detail_fields;
                               			break;
								case 'post_images':
								        $post_images = geodir_get_images($post->ID,'thumbnail');
                                		if(!empty($post_images)){
											foreach($post_images as $image){
												$thumb_image = '';
											  
												$thumb_image .=	'<a href="'.$image->src.'">';
												$thumb_image .= geodir_show_image($image,'thumbnail',true,false);
												$thumb_image .= '</a>';
											   
												echo $thumb_image;
											
											}// endfore
										}
									break;
								case 'post_video':
									echo $video; 
									break;
								case 'special_offers':
									echo wpautop(stripslashes($special_offers));
								
                                  	break;
								case 'post_map':
									$map_args = array();
									$map_args['map_canvas_name'] = 'detail_page_map_canvas' ;
									$map_args['width'] = '600';
									$map_args['height'] = '300';
									if($post->post_mapzoom){$map_args['zoom'] = ''.$post->post_mapzoom.'';}
									$map_args['autozoom'] = false;
									$map_args['child_collapse'] = '0';
									$map_args['enable_cat_filters'] = false;
									$map_args['enable_text_search'] = false;
									$map_args['enable_post_type_filters'] = false;
									$map_args['enable_location_filters'] = false;
									$map_args['enable_jason_on_load'] = true;
									$map_args['enable_map_direction'] = true;
									geodir_draw_map($map_args);
									break;	
								case 'reviews':
									comments_template(); 
									break;
								case 'related_listing':
									echo $related_listing;
									break;		
								default:
									break;
						   	}
							do_action('geodir_after_tab_content' ,$detail_page_tab['hash_key'] );
						   	do_action('geodir_after_' . $detail_page_tab['hash_key'].'_tab_content');
							?> </li>
                           <?php 
						  	$arr_detail_page_tabs[$tab_index]['tab_content'] = apply_filters("geodir_modify_" .$detail_page_tab['tab_content']. "_tab_content"  ,  ob_get_clean()) ;
						  } // end of if for is_display
						}// end of foreach
						
						do_action('geodir_after_tab_list') ; 
					 ?>
                    </dl>
                   <ul class="geodir-tabs-content" style="z-index:-999; position:relative;">
                   		<?php 
						foreach($arr_detail_page_tabs as $detail_page_tab)
						{
							if($detail_page_tab['is_display'] && !empty($detail_page_tab['tab_content']))
							{
								echo $detail_page_tab['tab_content'] ;
                        	}// end of if 
						}// end of foreach 
						 do_action('geodir_add_tab_content') ; ?>
                    </ul> <!--gd-tabs-content ul end-->
               </div> <!--gd-tabs div end--> 
                                       
            
            <?php do_action('geodir_after_single_post', $post); ?>
            
            <div class="geodir-pos_navigation clearfix">
               <div class="geodir-post_left"><?php previous_post_link('%link',''.__('Previous',GEODIRECTORY_TEXTDOMAIN), false) ?></div>
               <div class="geodir-post_right"><?php next_post_link('%link',__('Next',GEODIRECTORY_TEXTDOMAIN).'', false) ?></div>
            </div>
            
            <?php endwhile; ?>
        
        </div>
	
   
		<?php        
            
            do_action('geodir_after_main_content');
            
            geodir_get_template_part('detail','sidebar');
            
            geodir_get_template_part('popup','forms');
        ?>
 	</div>
    
    <?php if(get_option('geodir_show_detail_bottom_section')) { ?>
    
    <div class="geodir_full_page clearfix"><?php dynamic_sidebar('geodir_detail_bottom');?></div><!-- clearfix ends here-->
    
    <?php } ?>
    
</div>  <!-- geodir-wrapper ends here-->
<?php get_footer();      