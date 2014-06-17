<?php 
if(get_current_user_id()){wp_redirect( home_url(), 302 ); exit;}

// call header
get_header(); 

###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'signup-page', 'geodir-wrapper','');
	
	###### TOP CONTENT ######
	// action called before the main content and the page specific content
	do_action('geodir_top_content', 'signup-page');
	// template specific, this can add the sidebar top section and breadcrums
	do_action('geodir_signin_before_main_content');
	// action called before the main content
	do_action('geodir_before_main_content', 'signup-page');

			###### MAIN CONTENT WRAPPERS OPEN ######
			// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
			do_action( 'geodir_wrapper_content_open', 'signup-page', 'geodir-wrapper-content','geodir-content-fullwidth');
			
			// this action adds the top sidebar
			do_action( 'geodir_sidebar_signup_top');

					###### MAIN CONTENT ######
					// this call the main page content
					do_action( 'geodir_signup_forms');

			###### MAIN CONTENT WRAPPERS CLOSE ######
			// this adds the closing html tags to the wrapper_content div :: ($type='')
			do_action( 'geodir_wrapper_content_close', 'signup-page');
			   
###### WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'signup-page');
get_footer();  