<?php get_header(); 


global $term,$post,$current_term, $gridview_columns;
//global $wp_query; echo $wp_query->request;
$gd_post_type = geodir_get_current_posttype();
$post_type_info = get_post_type_object( $gd_post_type );

	
if(is_search())
{
	$list_title = __('Search',GEODIRECTORY_TEXTDOMAIN).' '.$post_type_info->labels->name. __(' For :',GEODIRECTORY_TEXTDOMAIN)." '".get_search_query()."'";

}	

?>

<div id="geodir_wrapper">    
    
    <?php if(get_option('geodir_show_search_top_section')) { ?>
    
    <div class="geodir_full_page clearfix">
   	    <?php dynamic_sidebar('geodir_search_top');?>
	</div><!-- clearfix ends here-->
    
    <?php } ?>
    
    <?php geodir_breadcrumb(); ?>
    
    <h1><?php echo apply_filters('geodir_search_page_title',wptexturize($list_title)); ?></h1>
    
    <?php if (isset($current_term->description) && $current_term->description) : ?>
        <div class="term_description"><?php _e( wpautop(wptexturize($current_term->description)), GEODIRECTORY_TEXTDOMAIN ) ; ?></div>
    <?php endif; ?>
    
    <div class="clearfix geodir-common">
    	
        <?php if( get_option('geodir_show_search_left_section') ) { ?> 
        <div class="geodir-onethird gd-third-left" <?php if($width = get_option('geodir_width_search_left_section') ) { echo 'style="width:'.$width.'%;"'; } ?> >
           <div class="geodir-content-left">
		   <?php dynamic_sidebar('geodir_search_left_sidebar');?>
           </div>
        </div>
        <?php } ?>
        
        <div class="geodir-onethird gd-third-middle" <?php if($width = get_option('geodir_width_search_contant_section') ) { echo'style="width:'.$width.'%;"';} ?> >
       		<div class="geodir-content-content">
					
					<div class="clearfix">
					<?php do_action('geodir_before_listing'); ?>
			</div> 
			<?php
			//do_action('geodir_tax_sort_options');
			
			$listing_view = get_option('geodir_search_view');
			
			if(strstr($listing_view,'gridview')){
				
				$gridview_columns = $listing_view;
				
				$listing_view_exp = explode('_',$listing_view);
				
				$listing_view = $listing_view_exp[0];
				
			}
			
			$new_days = get_option('geodir_listing_new_days');
			
			
			
			geodir_get_template_part('listing',$listing_view);
			
			do_action('geodir_pagination');
			
			do_action('geodir_after_listing');
		?>
        	</div>
      	</div>
        
        
        <?php
	    	
		 if( get_option('geodir_show_search_right_section') ) { ?> 
        <div class="geodir-onethird gd-third-right" <?php if($width = get_option('geodir_width_search_right_section') ) { echo 'style="width:'.$width.'%;"';} ?> >
        	<div class="geodir-content-right">
            <?php   
			dynamic_sidebar('geodir_search_right_sidebar');?>
            </div>
        </div>
        <?php  } ?>
        
    </div> 
    
    
    <?php if(get_option('geodir_show_search_bottom_section')) { ?>
    
    <div class="geodir_full_page clearfix">
   	    <?php dynamic_sidebar('geodir_search_bottom');?>
	</div><!-- clearfix ends here-->
    
    <?php } ?>   
    
</div> 
<?php get_footer();  