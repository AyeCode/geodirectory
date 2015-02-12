<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
require_once( 'geodirectory-functions/shortcode_functions.php' );


add_shortcode( 'add_listing', 'geodir_sc_add_listing' );
function geodir_sc_add_listing( $atts ) {
	$defaults = array(
		'pid'          => '',
		'listing_type' => 'gd_place',
	);

	$params = shortcode_atts( $defaults, $atts );

	foreach ( $params as $key => $value ) {
		$_REQUEST[ $key ] = $value;
	}

	###### MAIN CONTENT ######
	// this adds the mandatory message
	do_action( 'geodir_add_listing_page_mandatory' );
	// this adds the add listing form
	do_action( 'geodir_add_listing_form' );
}

add_shortcode( 'homepage_map', 'geodir_sc_home_map' );
function geodir_sc_home_map( $atts ) {
	ob_start();
	$defaults = array(
		'width'          => '960',
		'height'         => '425',
		'maptype'        => 'ROADMAP',
		'zoom'           => '13',
		'autozoom'       => '',
		'child_collapse' => '0',
		'scrollwheel'    => '0',
	);

	$params = shortcode_atts( $defaults, $atts );

	$params = gdsc_validate_map_args( $params );

	$map_args = array(
		'map_canvas_name'           => 'gd_home_map',
		'width'                     => apply_filters( 'widget_width', $params['width'] ),
		'height'                    => apply_filters( 'widget_heigh', $params['height'] ),
		'maptype'                   => apply_filters( 'widget_maptype', $params['maptype'] ),
		'scrollwheel'               => apply_filters( 'widget_scrollwheel', $params['scrollwheel'] ),
		'zoom'                      => apply_filters( 'widget_zoom', $params['zoom'] ),
		'autozoom'                  => apply_filters( 'widget_autozoom', $params['autozoom'] ),
		'child_collapse'            => apply_filters( 'widget_child_collapse', $params['child_collapse'] ),
		'enable_cat_filters'        => true,
		'enable_text_search'        => true,
		'enable_post_type_filters'  => true,
		'enable_location_filters'   => apply_filters( 'geodir_home_map_enable_location_filters', false ),
		'enable_jason_on_load'      => false,
		'enable_marker_cluster'     => false,
		'enable_map_resize_button'  => true,
		'map_class_name'            => 'geodir-map-home-page',
		'is_geodir_home_map_widget' => true,
	);

	geodir_draw_map( $map_args );

	add_action( 'wp_footer', 'geodir_home_map_add_script', 100 );

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'listing_map', 'geodir_sc_listing_map' );
function geodir_sc_listing_map( $atts ) {
	ob_start();
	add_action( 'wp_head', 'init_listing_map_script' ); // Initialize the map object and marker array

	add_action( 'the_post', 'create_list_jsondata' ); // Add marker in json array

	add_action( 'wp_footer', 'show_listing_widget_map' ); // Show map for listings with markers

	$defaults = array(
		'width'          => '294',
		'height'         => '370',
		'zoom'           => '13',
		'autozoom'       => '',
		'sticky'         => '',
		'showall'        => '0',
		'scrollwheel'    => '0',
		'maptype'        => 'ROADMAP',
		'child_collapse' => 0,
	);

	$params = shortcode_atts( $defaults, $atts );

	$params = gdsc_validate_map_args( $params );

	$map_args = array(
		'map_canvas_name'          => 'gd_listing_map',
		'width'                    => $params['width'],
		'height'                   => $params['height'],
		'zoom'                     => $params['zoom'],
		'autozoom'                 => $params['autozoom'],
		'sticky'                   => $params['sticky'],
		'showall'                  => $params['showall'],
		'scrollwheel'              => $params['scrollwheel'],
		'child_collapse'           => 0,
		'enable_cat_filters'       => false,
		'enable_text_search'       => false,
		'enable_post_type_filters' => false,
		'enable_location_filters'  => false,
		'enable_jason_on_load'     => true,
	);

	if ( is_single() ) {

		global $post;
		$map_default_lat            = $address_latitude = $post->post_latitude;
		$map_default_lng            = $address_longitude = $post->post_longitude;
		$mapview                    = $post->post_mapview;
		$map_args['zoom']           = $post->post_mapzoom;
		$map_args['map_class_name'] = 'geodir-map-listing-page-single';

	} else {
		$default_location = geodir_get_default_location();

		$map_default_lat            = isset( $default_location->city_latitude ) ? $default_location->city_latitude : '';
		$map_default_lng            = isset( $default_location->city_longitude ) ? $default_location->city_longitude : '';
		$map_args['map_class_name'] = 'geodir-map-listing-page';
	}

	if ( empty( $mapview ) ) {
		$mapview = 'ROADMAP';
	}

	// Set default map options
	$map_args['ajax_url']          = geodir_get_ajax_url();
	$map_args['latitude']          = $map_default_lat;
	$map_args['longitude']         = $map_default_lng;
	$map_args['streetViewControl'] = true;
	$map_args['maptype']           = $mapview;
	$map_args['showPreview']       = '0';
	$map_args['maxZoom']           = 21;
	$map_args['bubble_size']       = 'small';

	geodir_draw_map( $map_args );

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'listing_slider', 'geodir_sc_listing_slider' );
function geodir_sc_listing_slider( $atts ) {
	ob_start();
	$defaults = array(
		'post_type'          => 'gd_place',
		'category'           => '0',
		'post_number'        => '5',
		'slideshow'          => '0',
		'animation_loop'     => 0,
		'direction_nav'      => 0,
		'slideshow_speed'    => 5000,
		'animation_speed'    => 600,
		'animation'          => 'slide',
		'order_by'           => 'latest',
		'show_title'         => '',
		'show_featured_only' => '',
	);

	$params = shortcode_atts( $defaults, $atts );

	/*
	 *
	 * Now we begin the validation of the attributes.
	 */
	// Check we have a valid post_type
	if ( ! ( gdsc_is_post_type_valid( $params['post_type'] ) ) ) {
		$params['post_type'] = 'gd_place';
	}

	// Check we have a valid sort_order
	$params['order_by'] = gdsc_validate_sort_choice( $params['order_by'] );

	// Match the chosen animation to our options
	$animation_list = array( 'slide', 'fade' );
	if ( ! ( in_array( $params['animation'], $animation_list ) ) ) {
		$params['animation'] = 'slide';
	}

	// Post_number needs to be a positive integer
	$params['post_number'] = absint( $params['post_number'] );
	if( 0 == $params['post_number'] ){
		$params['post_number'] = 1;
	}

	// Manage the entered categories
	if ( 0 != $params['category'] || '' != $params['category'] ) {
		$params['category'] = gdsc_manage_category_choice( $params['post_type'], $params['category'] );
	}

	// Convert show_title to a bool
	$params['show_title'] = intval( gdsc_to_bool_val( $params['show_title'] ) );

	// Convert show_featured_only to a bool
	$params['show_featured_only'] = intval( gdsc_to_bool_val( $params['show_featured_only'] ) );

	/*
	 * Hopefully all attributes are now valid, and safe to pass forward
	 */

	$query_args = array(
		'posts_per_page' => $params['post_number'],
		'is_geodir_loop' => true,
		'post_type'      => $params['post_type'],
		'order_by'       => $params['order_by']
	);

	if ( 1 == $params['show_featured_only'] ) {
		$query_args['show_featured_only'] = 1;
	}

	if ( 0 != $params['category'] && '' != $params['category'] ) {
		$category_taxonomy = geodir_get_taxonomies( $params['post_type'] );
		$tax_query         = array(
			'taxonomy' => $category_taxonomy[0],
			'field'    => 'id',
			'terms'    => $params['category'],
		);

		$query_args['tax_query'] = array( $tax_query );
	}
	?>
	<script type="text/javascript">
		jQuery(window).load(function () {
			jQuery('#geodir_widget_carousel').flexslider({
				animation: "slide",
				selector: ".geodir-slides > li",
				namespace: "geodir-",
				controlNav: false,
				directionNav: false,
				animationLoop: false,
				slideshow: false,
				itemWidth: 75,
				itemMargin: 5,
				asNavFor: '#geodir_widget_slider'
			});

			jQuery('#geodir_widget_slider').flexslider({
				animation: "<?php echo $params['animation'];?>",
				selector: ".geodir-slides > li",
				namespace: "geodir-",
				controlNav: true,
				animationLoop: <?php echo intval( gdsc_to_bool_val( $params['animation_loop'] ) );?>,
				slideshow: <?php echo intval( gdsc_to_bool_val( $params['slideshow'] ) );?>,
				slideshowSpeed: <?php echo absint( $params['slideshow_speed'] );?>,
				animationSpeed: <?php echo absint( $params['animation_speed'] );?>,
				directionNav: <?php echo absint( gdsc_to_bool_val( $params['direction_nav'] ) );?>,
				sync: "#geodir_widget_carousel",
				start: function (slider) {
					jQuery('.geodir-listing-flex-loader').hide();
					jQuery('#geodir_widget_slider').css({'visibility': 'visible'});
					jQuery('#geodir_widget_carousel').css({'visibility': 'visible'});
				}
			});
		});
	</script>
	<?php
	add_action( 'pre_get_posts', 'gdsc_listing_loop_filter', 1 );
	query_posts( $query_args );

	if ( have_posts() ) {
		$widget_main_slides = '';
		$nav_slides         = '';
		$widget_slides      = 0;

		while ( have_posts() ) : the_post();
			global $post;
			$widget_image = geodir_get_featured_image( $post->ID, 'thumbnail', get_option( 'geodir_listing_no_img' ) );

			if ( ! empty( $widget_image ) ) {
				if ( $widget_image->height >= 200 ) {
					$widget_spacer_height = 0;
				} else {
					$widget_spacer_height = ( ( 200 - $widget_image->height ) / 2 );
				}
				$widget_main_slides .= '<li class="geodir-listing-slider-widget"><img class="geodir-listing-slider-spacer" src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'" alt="'.$widget_image->title.'" title="'.$widget_image->title.'" style="max-height:'.$widget_spacer_height.'px !important;margin:0 auto;" width="100%" />';

				$title = '';
				if ( $params['show_title'] ) {
					$title = '<div class="geodir-slider-title"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></div>';
				}

				$widget_main_slides .= $title . '<img src="' . $widget_image->src . '" alt="' . $widget_image->title . '" title="' . $widget_image->title . '" style="max-height:200px;margin:0 auto;" /></li>';
				$nav_slides .= '<li><img src="' . $widget_image->src . '" alt="' . $widget_image->title . '" title="' . $widget_image->title . '" style="max-height:48px;margin:0 auto;" /></li>';
				$widget_slides ++;
			}
		endwhile; // End foreach $results
		?>
		<div class="flex-container" style="min-height:200px;">
			<div class="geodir-listing-flex-loader"><i class="fa fa-refresh fa-spin"></i></div>
			<div id="geodir_widget_slider" class="geodir_flexslider">
				<ul class="geodir-slides clearfix"><?php echo $widget_main_slides; ?></ul>
			</div>
			<?php if ( $widget_slides > 1 ) { ?>
				<div id="geodir_widget_carousel" class="geodir_flexslider">
					<ul class="geodir-slides clearfix"><?php echo $nav_slides; ?></ul>
				</div>
			<?php } ?>
		</div>
	<?php
	} // End if not empty $results
	wp_reset_query();

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'login_box', 'geodir_sc_login_box' );
function geodir_sc_login_box( $atts ) {
	ob_start();
	// @todo: Extract most of this into a set of re-usable functions so it's not duplicated with the widget
	if ( is_user_logged_in() ) {
		global $current_user;

		$login_url   = geodir_getlink( home_url(), array( 'geodir_signup' => 'true' ), false );
		$add_listurl = get_permalink( get_option( 'geodir_add_listing_page' ) );
		$add_listurl = geodir_getlink( $add_listurl, array( 'listing_type' => 'gd_place' ) );
		$author_link = get_author_posts_url( $current_user->data->ID );
		$author_link = geodir_getlink( $author_link, array( 'geodir_dashbord' => 'true' ), false );

		echo '<ul class="geodir-loginbox-list">';
		ob_start();
		?>
		<li><a class="signin" href="<?php echo wp_logout_url( home_url() ); ?>"><?php _e( 'Logout', GEODIRECTORY_TEXTDOMAIN ); ?></a></li>
		<?php
		$post_types                           = geodir_get_posttypes( 'object' );
		$show_add_listing_post_types_main_nav = get_option( 'geodir_add_listing_link_user_dashboard' );
		$geodir_allow_posttype_frontend       = get_option( 'geodir_allow_posttype_frontend' );

		if ( ! empty( $show_add_listing_post_types_main_nav ) ) {
			$addlisting_links = '';
			foreach ( $post_types as $key => $postobj ) {

				if ( in_array( $key, $show_add_listing_post_types_main_nav ) ) {

					if ( $add_link = geodir_get_addlisting_link( $key ) ) {

						$name = $postobj->labels->name;

						$selected = '';
						if ( geodir_get_current_posttype() == $key && geodir_is_page( 'add-listing' ) ) {
							$selected = 'selected="selected"';
						}

						$addlisting_links .= '<option ' . $selected . ' value="' . $add_link . '">' . __( ucfirst( $name ), GEODIRECTORY_TEXTDOMAIN ) . '</option>';

					}
				}

			}

			if ( $addlisting_links != '' ) {
				?>

				<li><select id="geodir_add_listing" class="chosen_select" onchange="window.location.href=this.value" option-autoredirect="1" name="geodir_add_listing" option-ajaxchosen="false">
						<option value="<?php echo home_url(); ?>"><?php _e( 'Add Listing', GEODIRECTORY_TEXTDOMAIN ); ?></option>
						<?php echo $addlisting_links; ?>
					</select></li> <?php

			}

		}
		// My Favourites in Dashboard
		$show_favorite_link_user_dashboard = get_option( 'geodir_favorite_link_user_dashboard' );
		$user_favourite                    = geodir_user_favourite_listing_count();

		if ( ! empty( $show_favorite_link_user_dashboard ) && ! empty( $user_favourite ) ) {
			$favourite_links = '';

			foreach ( $post_types as $key => $postobj ) {
				if ( in_array( $key, $show_favorite_link_user_dashboard ) && array_key_exists( $key, $user_favourite ) ) {
					$name           = $postobj->labels->name;
					$post_type_link = geodir_getlink( $author_link, array( 'stype' => $key, 'list' => 'favourite' ), false );

					$selected = '';

					if ( isset( $_REQUEST['list'] ) && $_REQUEST['list'] == 'favourite' && isset( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $key && isset( $_REQUEST['geodir_dashbord'] ) ) {
						$selected = 'selected="selected"';
					}

					$favourite_links .= '<option ' . $selected . ' value="' . $post_type_link . '">' . __( ucfirst( $name ), GEODIRECTORY_TEXTDOMAIN ) . '</option>';
				}
			}

			if ( $favourite_links != '' ) {
				?>
				<li>
					<select id="geodir_my_favourites" class="chosen_select" onchange="window.location.href=this.value" option-autoredirect="1" name="geodir_my_favourites" option-ajaxchosen="false">
						<option value="<?php echo home_url(); ?>"><?php _e( 'My Favorites', GEODIRECTORY_TEXTDOMAIN ); ?></option>
						<?php echo $favourite_links; ?>
					</select>
				</li>
			<?php
			}
		}


		$show_listing_link_user_dashboard = get_option( 'geodir_listing_link_user_dashboard' );
		$user_listing                     = geodir_user_post_listing_count();

		if ( ! empty( $show_listing_link_user_dashboard ) && ! empty( $user_listing ) ) {
			$listing_links = '';

			foreach ( $post_types as $key => $postobj ) {
				if ( in_array( $key, $show_listing_link_user_dashboard ) && array_key_exists( $key, $user_listing ) ) {
					$name         = $postobj->labels->name;
					$listing_link = geodir_getlink( $author_link, array( 'stype' => $key ), false );

					$selected = '';
					if ( ! isset( $_REQUEST['list'] ) && isset( $_REQUEST['geodir_dashbord'] ) && isset( $_REQUEST['stype'] ) && $_REQUEST['stype'] == $key ) {
						$selected = 'selected="selected"';
					}

					$listing_links .= '<option ' . $selected . ' value="' . $listing_link . '">' . __( ucfirst( $name ), GEODIRECTORY_TEXTDOMAIN ) . '</option>';
				}
			}

			if ( $listing_links != '' ) {
				?>
				<li>
					<select id="geodir_my_listings" class="chosen_select" onchange="window.location.href=this.value" option-autoredirect="1" name="geodir_my_listings" option-ajaxchosen="false">
						<option value="<?php echo home_url(); ?>"><?php _e( 'My Listings', GEODIRECTORY_TEXTDOMAIN ); ?></option>
						<?php echo $listing_links; ?>
					</select>
				</li>
			<?php
			}
		}

		$dashboard_link = ob_get_clean();

		echo apply_filters( 'geodir_dashboard_links', $dashboard_link );
		echo '</ul>';
	} else {
		?>
		<form name="loginform" class="loginform1" action="<?php echo get_option( 'home' ) . '/index.php?geodir_signup=true'; ?>" method="post">
			<div class="geodir_form_row"><input placeholder="<?php _e( 'Email', GEODIRECTORY_TEXTDOMAIN ); ?>" name="log" type="text" class="textfield user_login1"/> <span
					class="user_loginInfo"></span></div>
			<div class="geodir_form_row"><input placeholder="<?php _e( 'Password', GEODIRECTORY_TEXTDOMAIN ); ?>" name="pwd" type="password" class="textfield user_pass1"/><span
					class="user_passInfo"></span></div>

			<input type="hidden" name="redirect_to" value="<?php echo geodir_curPageURL(); ?>"/>
			<input type="hidden" name="testcookie" value="1"/>

			<div class="geodir_form_row clearfix"><input type="submit" name="submit" value="<?php echo SIGN_IN_BUTTON; ?>" class="b_signin"/>

				<p class="geodir-new-forgot-link">
					<a href="<?php echo home_url(); ?>/?geodir_signup=true&amp;page1=sign_up" class="goedir-newuser-link"><?php echo NEW_USER_TEXT; ?></a>

					<a href="<?php echo home_url(); ?>/?geodir_signup=true&amp;page1=sign_in" class="goedir-forgot-link"><?php echo FORGOT_PW_TEXT; ?></a></p></div>
		</form>
	<?php
	}

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'popular_post_category', 'geodir_sc_popular_post_category' );
function geodir_sc_popular_post_category( $atts ) {
	ob_start();
	global $geodir_post_category_str;
	$defaults = array(
		'category_limit' => 15,
		'before_widget'=> '',
		'after_widget'=> '',
		'before_title'=> '',
		'after_title'=> '',
		'title'=> '',
	);

	$params = shortcode_atts( $defaults, $atts ,'popular_post_category');
	$params['category_limit'] = absint( $params['category_limit'] );
	geodir_popular_post_category_output($params,$params);

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'popular_post_view', 'geodir_sc_popular_post_view' );
function geodir_sc_popular_post_view( $atts ) {
	ob_start();
	$defaults = array(
		'post_type'             => 'gd_place',
		'category'              => '0',
		'post_number'           => '5',
		'layout'                => 'gridview_onehalf',
		'add_location_filter'   => '0',
		'list_sort'             => 'latest',
		'use_viewing_post_type' => '1',
		'character_count'       => '20',
		'listing_width'         => '',
		'show_featured_only'    => '0',
		'show_special_only'     => '0',
		'with_pics_only'        => '0',
		'with_videos_only'      => '0',
	);

	$params = shortcode_atts( $defaults, $atts );

	/**
	 * Validate our incoming params
	 */

	// Validate the selected post type, default to gd_place on fail
	if ( ! ( gdsc_is_post_type_valid( $params['post_type'] ) ) ) {
		$params['post_type'] = 'gd_place';
	}

	// Validate the selected category/ies - Grab the current list based on post_type
	$category_taxonomy = geodir_get_taxonomies( $params['post_type'] );
	$categories        = get_terms( $category_taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'fields' => 'ids' ) );

	// Make sure we have an array
	if ( ! ( is_array( $params['category'] ) ) ) {
		$params['category'] = explode( ',', $params['category'] );
	}

	// Array_intersect returns only the items in $params['category'] that are also in our category list
	// Otherwise it becomes empty and later on that will mean "All"
	$params['category'] = array_intersect( $params['category'], $categories );

	// Post_number needs to be a positive integer
	$params['post_number'] = absint( $params['post_number'] );
	if( 0 == $params['post_number'] ){
		$params['post_number'] = 1;
	}

	// Validate our layout choice
	// Outside of the norm, I added some more simple terms to match the existing
	// So now I just run the switch to set it properly.
	$params['layout'] = gdsc_validate_layout_choice( $params['layout'] );

	// Validate our sorting choice
	$params['list_sort'] = gdsc_validate_sort_choice( $params['list_sort'] );

	// Validate character_count
	$params['character_count'] = absint( $params['character_count'] );
	if ( 20 > $params['character_count'] ) {
		$params['character_count'] = 20;
	}

	// Validate Listing width, used in the template widget-listing-listview.php
	// The context is in width=$listing_width% - So we need a positive number between 0 & 100
	$params['listing_width'] = gdsc_validate_listing_width( $params['listing_width'] );

	// Validate the checkboxes used on the widget
	$params['add_location_filter']   = gdsc_to_bool_val( $params['add_location_filter'] );
	$params['show_featured_only']    = gdsc_to_bool_val( $params['show_featured_only'] );
	$params['show_special_only']     = gdsc_to_bool_val( $params['show_special_only'] );
	$params['with_pics_only']        = gdsc_to_bool_val( $params['with_pics_only'] );
	$params['with_videos_only']      = gdsc_to_bool_val( $params['with_videos_only'] );
	$params['use_viewing_post_type'] = gdsc_to_bool_val( $params['use_viewing_post_type'] );

	/**
	 * End of validation
	 */

	// set post type to current viewing post type
	if ( $params['use_viewing_post_type'] ) {
		$current_post_type = geodir_get_current_posttype();
		if ( $current_post_type != '' && $current_post_type != $params['post_type'] ) {
			$params['post_type'] = $current_post_type;
			$category            = array(); // old post type category will not work for current changed post type
		}
	}

	$city = get_query_var( 'gd_city' );
	if ( ! empty( $city ) ) {
		if ( get_option( 'geodir_show_location_url' ) == 'all' ) {
			$country = get_query_var( 'gd_country' );
			$region  = get_query_var( 'gd_region' );

			if ( ! empty( $country ) ) {
				$location_url[] = $country;
			}

			if ( ! empty( $region ) ) {
				$location_url[] = $region;
			}
		}
	}

	$viewall_url = get_post_type_archive_link( $params['post_type'] );

	if ( ! empty( $params['category'] ) && $params['category'][0] != '0' ) {
		global $geodir_add_location_url;

		$geodir_add_location_url = '0';

		if ( $params['add_location_filter'] != '0' ) {
			$geodir_add_location_url = '1';
		}

		$viewall_url = get_term_link( (int) $params['category'][0], $params['post_type'] . 'category' );

		$geodir_add_location_url = null;
	}
	$query_args = array(
		'posts_per_page' => $params['post_number'],
		'is_geodir_loop' => true,
		'gd_location'    => $params['add_location_filter'] ? true : false,
		'post_type'      => $params['post_type'],
		'order_by'       => $params['list_sort'],
	);

	if ( $params['character_count'] ) {
		$query_args['excerpt_length'] = $params['character_count'];
	}

	if ( ! empty( $params['show_featured_only'] ) ) {
		$query_args['show_featured_only'] = 1;
	}

	if ( ! empty( $params['show_special_only'] ) ) {
		$query_args['show_special_only'] = 1;
	}

	if ( ! empty( $params['with_pics_only'] ) ) {
		$query_args['with_pics_only'] = 1;
	}

	if ( ! empty( $params['with_videos_only'] ) ) {
		$query_args['with_videos_only'] = 1;
	}
	$with_no_results = ! empty( $params['without_no_results'] ) ? false : true;

	if ( ! empty( $params['category'] ) && $params['category'][0] != '0' ) {
		$category_taxonomy = geodir_get_taxonomies( $params['post_type'] );

		######### WPML #########
		if ( function_exists( 'icl_object_id' ) ) {
			$category = gd_lang_object_ids( $params['category'], $category_taxonomy[0] );
		}
		######### WPML #########

		$tax_query = array(
			'taxonomy' => $category_taxonomy[0],
			'field'    => 'id',
			'terms'    => $params['category']
		);

		$query_args['tax_query'] = array( $tax_query );
	}

	global $gridview_columns, $geodir_is_widget_listing;

	$widget_listings = geodir_get_widget_listings( $query_args );

	if ( ! empty( $widget_listings ) || $with_no_results ) {
		?>
		<div class="geodir_locations geodir_location_listing">
			<?php do_action( 'geodir_before_view_all_link_in_widget' ); ?>
			<div class="geodir_list_heading clearfix">
				<a href="<?php echo $viewall_url; ?>" class="geodir-viewall"><?php _e( 'View all', GEODIRECTORY_TEXTDOMAIN ); ?></a>
			</div>
			<?php do_action( 'geodir_after_view_all_link_in_widget' ); ?>
			<?php
			if ( strstr( $params['layout'], 'gridview' ) ) {
				$listing_view_exp = explode( '_', $params['layout'] );
				$gridview_columns = $params['layout'];
				$layout           = $listing_view_exp[0];
			} else {
				$gridview_columns = '';
			}

			// The $character_count is used in the template. Do not remove from here.
			$character_count = $params['character_count'];

			$template = apply_filters( "geodir_template_part-widget-listing-listview", geodir_plugin_path() . '/geodirectory-templates/widget-listing-listview.php' );

			global $post, $map_jason, $map_canvas_arr;

			$current_post             = $post;
			$current_map_jason        = $map_jason;
			$current_map_canvas_arr   = $map_canvas_arr;
			$geodir_is_widget_listing = true;

			include( $template );

			$geodir_is_widget_listing = false;

			$GLOBALS['post'] = $current_post;
			setup_postdata( $current_post );
			$map_jason      = $current_map_jason;
			$map_canvas_arr = $current_map_canvas_arr;
			?>
		</div>
	<?php
	}
	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'recent_reviews', 'geodir_sc_recent_reviews' );
function geodir_sc_recent_reviews( $atts ) {
	ob_start();
	$defaults = array(
		'count' => 5,
	);

	$params = shortcode_atts( $defaults, $atts );

	$count = absint( $params['count'] );
	if ( 0 == $count ) {
		$count = 1;
	}

	$comments_li = geodir_get_recent_reviews( 30, $count, 100, false );

	if ( $comments_li ) {
		?>
		<div class="geodir_sc_recent_reviews_section">
			<ul class="geodir_sc_recent_reviews"><?php echo $comments_li; ?></ul>
		</div>
	<?php
	}
	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'related_listings', 'geodir_sc_related_listings' );
function geodir_sc_related_listings( $atts ) {
	ob_start();
	$defaults = array(
		'post_number'         => 5,
		'relate_to'           => 'category',
		'layout'              => 'gridview_onehalf',
		'add_location_filter' => 0,
		'listing_width'       => '',
		'list_sort'           => 'latest',
		'character_count'     => 20,
		'is_widget'         => 1,
		'before_title'      => '<style type="text/css">.geodir_category_list_view li{margin:0px!important}</style>',
	);
	// The "before_title" code is an ugly & terrible hack. But it works for now. I should enqueue a new stylesheet.

	$params = shortcode_atts( $defaults, $atts );

	/**
	 * Begin validating parameters
	 */

	// Validate that post_number is a number and is 1 or higher
	$params['post_number'] = absint( $params['post_number'] );
	if ( 0 === $params['post_number'] ) {
		$params['post_number'] = 1;
	}

	// Validate relate_to - only category or tags
	$params['relate_to'] = strtolower( $params['relate_to'] );
	if ( 'category' != $params['relate_to'] && 'tags' != $params['relate_to'] ) {
		$params['relate_to'] = 'category';
	}

	// Validate layout selection
	$params['layout'] = gdsc_validate_layout_choice( $params['layout'] );

	// Validate sorting option
	$params['list_sort'] = gdsc_validate_sort_choice( $params['list_sort'] );

	// Validate add_location_filter
	$params['add_location_filter'] = gdsc_to_bool_val( $params['add_location_filter'] );

	// Validate listing_width
	$params['listing_width'] = gdsc_validate_listing_width( $params['listing_width'] );

	// Validate character_count
	$params['character_count'] = absint( $params['character_count'] );
	if ( 20 > $params['character_count'] ) {
		$params['character_count'] = 20;
	}

	if ( $related_display = geodir_related_posts_display( $params ) ) {
		echo $related_display;
	}
	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'advanced_search', 'geodir_sc_advanced_search' );
function geodir_sc_advanced_search( $atts ){
	ob_start();
	geodir_get_template_part('listing','filter-form');
	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

