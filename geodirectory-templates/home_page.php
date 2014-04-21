<?php get_header(); ?>

<div id="geodir_wrapper" class="geodir-home">
	
    <?php geodir_breadcrumb(); 
		//geodir_draw_map('home_map_canvas');
	?>
    
    <?php if(get_option('geodir_show_home_top_section')) { ?>
    
    <div class="geodir_full_page clearfix">
   	    <?php 
		dynamic_sidebar('geodir_home_top');?>
	</div><!-- clearfix ends here-->
    
    <?php } ?>
    
    <div class="clearfix geodir-common">
    	
        <?php if( get_option('geodir_show_home_left_section') ) { ?> 
        <div class="geodir-onethird" <?php if($width = get_option('geodir_width_home_left_section') ) { echo 'style="width:'.$width.'%;"'; } ?> >
        	<div class="geodir-content-left">
           <?php dynamic_sidebar('geodir_home_left');?>
           </div>
        </div>
        <?php } ?>
        
        <?php if( get_option('geodir_show_home_contant_section') ) { ?> 
        <div class="geodir-onethird" <?php if($width = get_option('geodir_width_home_contant_section') ) { echo'style="width:'.$width.'%;"';} ?> >
        	<div class="geodir-content-content">
            <?php dynamic_sidebar('geodir_home_contant');?>
            </div>
        </div>
        <?php } ?>
      
         <?php if( get_option('geodir_show_home_right_section') ) { ?> 
        <div class="geodir-onethird" <?php if($width = get_option('geodir_width_home_right_section') ) { echo 'style="width:'.$width.'%;"';} ?> >
        	<div class="geodir-content-right">
            <?php dynamic_sidebar('geodir_home_right');?>
            </div>
        </div>
        <?php } ?>
        
    </div> 
    
    
    <?php if(get_option('geodir_show_home_bottom_section')) { ?>
    
    <div class="geodir_full_page clearfix">
   	    <?php dynamic_sidebar('geodir_home_bottom');?>
	</div><!-- clearfix ends here-->
    
    <?php } ?>   
    
</div> 
<?php get_footer();    