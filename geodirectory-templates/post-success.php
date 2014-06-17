<?php 
get_header(); 

do_action('geodir_before_main_content'); 

###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'success-page', 'geodir-wrapper','');	
			
	###### TOP CONTENT ######
	// action called before the main content and the page specific content
	do_action('geodir_top_content', 'success-page');
	// action called before the main content
	do_action('geodir_before_main_content', 'success-page');

			###### MAIN CONTENT WRAPPERS OPEN ######
			// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
			do_action( 'geodir_wrapper_content_open', 'success-page', 'geodir-wrapper-content','');
			
			
			
			geodir_get_template_part('preview','success'); 
			
			
        ###### MAIN CONTENT WRAPPERS CLOSE ######
			// this adds the closing html tags to the </article> :: ($type='')
			do_action( 'geodir_article_close', 'success-page');
			
        do_action('geodir_after_main_content');
    
        ###### SIDEBAR ######
		do_action('geodir_detail_sidebar'); 
		

# WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'success-page');
get_footer();   