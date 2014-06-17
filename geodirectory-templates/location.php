<?php 
// get header
get_header(); 

###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'location-page', 'geodir-wrapper','');

do_action('geodir_breadcrumb'); 
    	
		if(is_page() && get_query_var('page_id') == get_option( 'geodir_locations_page' ))		
     		geodir_get_template_part('location','locations');
		else
			geodir_get_template_part('location','populars'); 
			
	 

# WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'location-page');

// get footer
get_footer();      