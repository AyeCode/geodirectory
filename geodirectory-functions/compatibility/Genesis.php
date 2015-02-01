<?php
// REPLACE GENESIS BREADCRUMBS WITH GD BREADCRUMBS
	remove_action( 'geodir_detail_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_listings_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_author_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_search_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_home_before_main_content', 'geodir_breadcrumb', 20 );
	remove_action( 'geodir_location_before_main_content', 'geodir_breadcrumb', 20 );
	add_action( 'genesis_after_header', 'geodir_replace_breadcrumb', 20 );
	
	// make top section wide
	remove_action( 'geodir_home_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10 );
	remove_action( 'geodir_location_before_main_content', 'geodir_action_geodir_sidebar_home_top', 10 );
	remove_action( 'geodir_author_before_main_content', 'geodir_action_geodir_sidebar_author_top', 10 );
	remove_action( 'geodir_search_before_main_content', 'geodir_action_geodir_sidebar_search_top', 10 );
	remove_action( 'geodir_detail_before_main_content', 'geodir_action_geodir_sidebar_detail_top', 10 );
	remove_action( 'geodir_listings_before_main_content', 'geodir_action_geodir_sidebar_listings_top', 10 );


add_action('genesis_after_header','gd_genesis_compat_add_top_section_back',5);

function gd_genesis_compat_add_top_section_back(){

	if(is_page_geodir_home() || geodir_is_page('location')){
		geodir_action_geodir_sidebar_home_top();
	}
	elseif( geodir_is_page('listing')){
		geodir_action_geodir_sidebar_listings_top();
	}
	elseif( geodir_is_page('detail')){
		geodir_action_geodir_sidebar_detail_top();
	}
	elseif( geodir_is_page('search')){
		geodir_action_geodir_sidebar_search_top();
	}
	elseif( geodir_is_page('author')){
		geodir_action_geodir_sidebar_author_top();
	}
	
	
}

// REPLACE GENESIS BREADCRUMBS FUNCTION
function geodir_replace_breadcrumb() {
	if ( is_front_page() && get_option('geodir_set_as_home') && !isset($_GET['geodir_signup']) ) {
	} else {
		echo '<div class="geodir-breadcrumb-bar"><div class="wrap">';
		geodir_breadcrumb();
		echo '</div></div>';
	}
}

// Force Full Width on signup page
	add_action( 'genesis_meta', 'geodir_genesis_meta' );
	
// FORCE FULL WIDTH LAYOUT ON SIGNUP PAGE
function geodir_genesis_meta() {
	if ( isset($_GET['geodir_signup']) ) {
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );
	}
}



//add listing page
add_action('geodir_add_listing_page_title','geodir_add_listing_page_title_genesis_before',8);
function geodir_add_listing_page_title_genesis_before(){
	
	echo "<div class='entry' >";
}


add_action('geodir_add_listing_form','geodir_add_listing_form_genesis_after',20);
function geodir_add_listing_form_genesis_after(){
	
	echo "</div>";
}


add_action('geodir_signup_forms','geodir_add_listing_page_title_genesis_before',8);
add_action('geodir_signup_forms','geodir_add_listing_form_genesis_after',20);
