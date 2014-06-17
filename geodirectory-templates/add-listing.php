<?php 
if(!isset($_REQUEST['backandedit'])){ unset($_SESSION['listing']); }
// call header
get_header(); 
	
###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'add-listing-page', 'geodir-wrapper','');
	
	###### TOP CONTENT ######
	// action called before the main content and the page specific content
	do_action('geodir_top_content', 'add-listing-page');
	// template specific, this can add the sidebar top section and breadcrums
	do_action('geodir_add_listing_before_main_content');
	// action called before the main content
	do_action('geodir_before_main_content', 'add-listing-page');
	
			###### MAIN CONTENT WRAPPERS OPEN ######
			// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
			do_action( 'geodir_wrapper_content_open', 'add-listing-page', 'geodir-wrapper-content','');
			
			
					###### MAIN CONTENT ######
					// this adds the page title
					do_action( 'geodir_add_listing_page_title');
					// this adds th manditory message
					do_action( 'geodir_add_listing_page_mandatory');
					// this adds the add listing form
					do_action( 'geodir_add_listing_form');
					
			###### MAIN CONTENT WRAPPERS CLOSE ######
			// action called after the main content
			do_action('geodir_after_main_content');
			// this adds the closing html tags to the wrapper_content div :: ($type='')
			do_action( 'geodir_wrapper_content_close', 'add-listing-page');	
	
	
	###### SIDEBAR ######
	do_action('geodir_add_listing_sidebar');

###### WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'add-listing-page');

// call footer
get_footer();     