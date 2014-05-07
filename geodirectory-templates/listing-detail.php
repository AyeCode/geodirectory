<?php 
get_header(); 

do_action('geodir_before_main_content'); ?>

<article  id="geodir_wrapper" class="hentry vCard" itemscope itemtype="http://schema.org/LocalBusiness">
    
    <?php if(get_option('geodir_show_detail_top_section')) { ?>
    
    <div class="geodir_full_page clearfix"><?php dynamic_sidebar('geodir_detail_top'); ?></div>
    
    <?php } ?>
    
    <?php geodir_breadcrumb(); ?>
       
	<div class="clearfix geodir-common">       
       
        <div id="geodir_content" >
             
        <?php 
        
        
        if ( have_posts() ) while ( have_posts() ) : the_post(); global $post,$post_images;
            
            do_action('geodir_before_single_post', $post); 
            
            $post_type_info = get_post_type_object( $post->post_type );
            $listing_label = $post_type_info->labels->name;
           
            ?><h1 class="entry-title fn"><?php the_title();?></h1><?php 
             
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
				    
               
					$main_slides .=	'<li><img src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'"  alt="'.$image->title.'" title="'.$image->title.'" style="max-height:'.$spacer_height.'px;margin:0 auto;"  />';
					$main_slides .=	'<img src="'.$image->src.'"  alt="'.$image->title.'" title="'.$image->title.'" style="max-height:400px;margin:0 auto;" itemprop="image"/></li>';
						
						
					$nav_slides .=	'<li><img src="'.$image->src.'"  alt="'.$image->title.'" title="'.$image->title.'" style="max-height:48px;margin:0 auto;" /></li>';
                    
                    $slides++;
                }}// endfore
				
            if(!empty($post_images)){
            ?>
            <div class="geodir_flex-container" >	
                <div class="geodir_flex-loader"></div> 
               
                <div id="geodir_slider" class="geodir_flexslider ">
                  <ul class="slides clearfix">
                        <?php echo $main_slides;?>
                  </ul>
                </div>
                <?php if( $slides > 1){ ?>
                    <div id="geodir_carousel" class="geodir_flexslider">
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
<span style="display:none;" class="url"><?php echo $post->guid;?></span>
<span class="updated" style="display:none;" ><?php the_modified_date('c');?></span>
<span class="vcard author" style="display:none;"><span class="fn"><?php echo get_the_author() ?></span></span>
<meta itemprop="name" content="<?php the_title();?>" />
<meta itemprop="url" content="<?php echo $post->guid;?>" />
<?php if($post->geodir_contact){echo '<meta itemprop="telephone" content="'.$post->geodir_contact.'" />'; }?>

               
               
               <?php 
					
			  
			  		geodir_show_detail_page_tabs();
				
						do_action('geodir_after_single_post', $post); ?>
            
            <div class="geodir-pos_navigation clearfix">
               <div class="geodir-post_left"><?php previous_post_link('%link',''.__('Previous',GEODIRECTORY_TEXTDOMAIN), false) ?></div>
               <div class="geodir-post_right"><?php next_post_link('%link',__('Next',GEODIRECTORY_TEXTDOMAIN).'', false) ?></div>
            </div>
            
            <?php endwhile; ?>
        
        </div>
	
   
		<?php        
            
            do_action('geodir_after_main_content');
            
            geodir_get_template_part('detail','sidebar');
            
        ?>
 	</div>
    
    <?php if(get_option('geodir_show_detail_bottom_section')) { ?>
    
    <div class="geodir_full_page clearfix"><?php dynamic_sidebar('geodir_detail_bottom');?></div><!-- clearfix ends here-->
    
    <?php } ?>
    
</article>  <!-- geodir-wrapper ends here-->
<?php get_footer();      