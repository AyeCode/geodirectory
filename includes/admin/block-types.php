<?php
return;// dont load file
add_action( 'init', 'gb_tests_register_block_typesx' );

function gb_tests_register_block_typesx() {

	register_block_type(
		'geodir/home-map',
		array(
			'render_callback' => 'gb_tests_render_example_02x'
		)
	);
}

function gb_tests_render_example_02x( $attr, $content ) {

	//print_r($attr);
	
	

	if ( is_user_logged_in() )
		return $content;


	return sprintf(
		'<p class="notice-error">%s</p>',
		esc_html__( 'You must be logged into view this content.', 'members' )
	);
}
