<?php 
header("X-XSS-Protection: 0"); // IE requirement
// call header
get_header();

###### WRAPPER OPEN ######
// this adds the opening html tags to the primary div, this required the closing tag below :: ($type='',$id='',$class='')
do_action( 'geodir_wrapper_open', 'details-page', 'geodir-wrapper','');
	
	###### TOP CONTENT ######
	// action called before the main content and the page specific content
	do_action('geodir_top_content', 'details-page');
	// template specific, this can add the sidebar top section and breadcrums
	do_action('geodir_detail_before_main_content');
	// action called before the main content
	do_action('geodir_before_main_content', 'details-page');

	
			###### MAIN CONTENT WRAPPERS OPEN ######
			// this adds the opening html tags to the content div, this required the closing tag below :: ($type='',$id='',$class='')
			do_action( 'geodir_wrapper_content_open', 'details-page', 'geodir-wrapper-content','');
			// this adds the opening html tags to the <article>, this required the closing tag below :: ($type='',$id='',$class='',$itemtype='')
			do_action( 'geodir_article_open', 'details-page', 'post-'.get_the_ID(),get_post_class(),'http://schema.org/LocalBusiness');
			
					###### MAIN CONTENT ######
					// this call the main page content
					if ( have_posts() && !$preview ){ 					
					the_post(); 
		   			global $post,$post_images;
					do_action( 'geodir_details_main_content', $post);
					}elseif($preview){
						do_action( 'geodir_action_geodir_set_preview_post'); // set the $post to the preview values
						//print_r($post);
						do_action( 'geodir_details_main_content', $post);
					}
			
			
			###### MAIN CONTENT WRAPPERS CLOSE ######
			// this adds the closing html tags to the </article> :: ($type='')
			do_action( 'geodir_article_close', 'details-page');
			// action called after the main content
			do_action('geodir_after_main_content');
			// this adds the closing html tags to the wrapper_content div :: ($type='')
			do_action( 'geodir_wrapper_content_close', 'details-page');
			
	###### SIDEBAR ######
	do_action('geodir_detail_sidebar'); 
	
		
	###### BOTTOM SECTION WIDGET AREA ######
	// adds the details bottom section widget area, you can add more classes via ''
	do_action( 'geodir_sidebar_detail_bottom_section', '' );

###### WRAPPER CLOSE ######	
// this adds the closing html tags to the wrapper div :: ($type='')
do_action( 'geodir_wrapper_close', 'details-page');


get_footer();      