<?php get_header(); ?>

<div id="geodir_wrapper">
    
    <?php geodir_breadcrumb(); 
    	
		if(is_page() && get_query_var('page_id') == get_option( 'geodir_locations_page' ))		
     		geodir_get_template_part('location','locations');
		else
			geodir_get_template_part('location','populars'); 
			
	 
    ?>    
</div>  <!-- geodir-wrapper ends here-->
    
<?php get_footer();      