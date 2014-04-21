<?php 
get_header(); 

do_action('geodir_before_main_content'); 
	
	?>
    		
<div id="geodir_wrapper">
    
	
    
    <div class="clearfix">
        <div id="geodir_content">                        
            
						<?php geodir_get_template_part('preview','success'); ?> 
            
        </div>		
		<?php     
        
        do_action('geodir_after_main_content');
    
        geodir_get_template_part('detail','sidebar');
		
        ?>
    </div>
</div>    
<?php get_footer();   