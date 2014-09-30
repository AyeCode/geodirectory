<?php  

/**
* Google Map V3 Listing page *
*	
**/ 

/* Enque listing map script*/
function init_listing_map_script(){
	global $list_map_json;
	
	$list_map_json = array();
	
}

/* Create listing json for map script */
function create_list_jsondata($post){
	global $wpdb,$list_map_json,$add_post_in_marker_array;
	
	if( ( is_main_query() || $add_post_in_marker_array ) && $post->marker_json != '')
	{	$list_map_json[] = $post->marker_json; }
		
}

/* Send json data to script and show listing map */
function show_listing_widget_map(){

	global $list_map_json;
	
	if(!empty($list_map_json))
	{	
		$list_map_json = array_unique($list_map_json);
		$cat_content_info[]= implode(',',$list_map_json); 
	}
			
	$totalcount = count( array_unique($list_map_json) );
		
	
	if(!empty($cat_content_info))	
		$list_json =  '[{"totalcount":"'.$totalcount.'",'.substr(implode(',',$cat_content_info),1).']'; 
	else
		$list_json = '[{"totalcount":"0"}]';
	
	$listing_map_args = array('list_json'=>$list_json);
	
	// Pass the json data in listing map script
	wp_localize_script( 'geodir-listing-map-widget', 'listing_map_args', $listing_map_args );
	
}


class geodir_map_listingpage extends WP_Widget {

	//Constructor
	function geodir_map_listingpage() {
		
		$widget_ops = array('classname' => 'widget geodir-map-listing-page Google Map for Listing page', 'description' => __('Google Map for Listing page. It will show you google map V3 for Listing page.',GEODIRECTORY_TEXTDOMAIN) );		
		$this->WP_Widget('geodir_map_v3_listing_map', __('GD > GMap - Listing page',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		
		
		add_action('wp_head','init_listing_map_script'); // Initialize the map object and marker array
		
		add_action('the_post','create_list_jsondata'); // Add marker in json array
		
		add_action('wp_footer','show_listing_widget_map'); // Show map for listings with markers	
	
	}

	// prints the widget
	function widget($args, $instance) {
		
	if( geodir_is_page('listing') || geodir_is_page('author')  || geodir_is_page('search') 
			|| geodir_is_page('detail') ) :
			
			extract($args, EXTR_SKIP);
			$width = empty($instance['width']) ? '294' : apply_filters('widget_width', $instance['width']);
			$height = empty($instance['heigh']) ? '370' : apply_filters('widget_heigh', $instance['heigh']);
			$zoom = empty($instance['zoom']) ? '13' : apply_filters('widget_zoom', $instance['zoom']);
			$autozoom = empty($instance['autozoom']) ? '' : apply_filters('widget_autozoom', $instance['autozoom']);
			$sticky = empty($instance['sticky']) ? '' : apply_filters('widget_sticky', $instance['sticky']);
			$scrollwheel = empty($instance['scrollwheel']) ? '0' : apply_filters('widget_scrollwheel', $instance['scrollwheel']);
			$showall = empty($instance['showall']) ? '0' : apply_filters('widget_showall', $instance['showall']);
			
			$map_args = array();
			$map_args['map_canvas_name'] = str_replace('-' , '_' , $args['widget_id']);
			$map_args['width'] = $width;
			$map_args['height'] = $height;
			$map_args['scrollwheel'] = $scrollwheel;
			$map_args['showall'] = $scrollwheel;
			$map_args['child_collapse'] = '0';
			$map_args['sticky'] = $sticky;
			$map_args['enable_cat_filters'] = false;
			$map_args['enable_text_search'] = false;
			$map_args['enable_post_type_filters'] = false;
			$map_args['enable_location_filters'] = false;
			$map_args['enable_jason_on_load'] = true;
			
			if(is_single()){
				
				global $post;
				$map_default_lat = $address_latitude =$post->post_latitude;
				$map_default_lng = $address_longitude = $post->post_longitude;
				$mapview = $post->post_mapview;
				$mapzoom = $post->post_mapzoom;
				$map_args['map_class_name'] = 'geodir-map-listing-page-single';
				
			}else{
				$default_location  = geodir_get_default_location();
		
				$map_default_lat 	=  	isset($default_location->city_latitude) ? $default_location->city_latitude : '';
				$map_default_lng 	=  	isset($default_location->city_longitude) ? $default_location->city_longitude : '';
				$map_args['map_class_name'] = 'geodir-map-listing-page';
			}
			
			if(empty($mapview)) $mapview = 'ROADMAP';
			if(empty($mapzoom)) $mapzoom = $zoom;
			
			// Set default map options
			$map_args['ajax_url'] =  geodir_get_ajax_url();
			$map_args['latitude'] = $map_default_lat; 
			$map_args['longitude'] = $map_default_lng; 
			$map_args['zoom'] = $zoom ;
			//$map_args['scrollwheel'] = true; 
			$map_args['scrollwheel'] = $scrollwheel;
			$map_args['showall'] = $showall;
			$map_args['streetViewControl'] = true;
			$map_args['maptype'] = $mapview;  
			$map_args['showPreview'] = '0'; 
			$map_args['maxZoom'] = 21; 
			$map_args['autozoom'] = $autozoom;
			$map_args['bubble_size'] = 'small';	
			
			echo $before_widget;
			geodir_draw_map($map_args);
			echo $after_widget;
		
		endif;	
	}

	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;		
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['heigh'] = ($new_instance['heigh']);
		$instance['zoom'] = ($new_instance['zoom']);
		$instance['autozoom'] = isset($new_instance['autozoom']) ? $new_instance['autozoom'] : '';
		$instance['sticky'] = isset($new_instance['sticky']) ? $new_instance['sticky'] : '';
		$instance['scrollwheel'] = isset($new_instance['scrollwheel']) ? ($new_instance['scrollwheel']) : '';
		$instance['showall'] = isset($new_instance['showall']) ? ($new_instance['showall']) : '';
		
		return $instance;
	}
	
	
	function form($instance) {
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, array( 'width' => '', 'heigh' => '', 'zoom' => '', 'autozoom' => '', 'sticky' => '','scrollwheel'=>'0','showall'=>'0') );		
		$width = strip_tags($instance['width']);
		$heigh = strip_tags($instance['heigh']);
		$zoom = strip_tags($instance['zoom']);
		$autozoom = strip_tags($instance['autozoom']);
		$sticky = strip_tags($instance['sticky']);
		$scrollwheel = strip_tags($instance['scrollwheel']);
		$showall = strip_tags($instance['showall']);
		?>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Map Width <small>(Default is : 294)</small>',GEODIRECTORY_TEXTDOMAIN);?>:
            <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>" />
            </label>
        </p>
	
        <p>
            <label for="<?php echo $this->get_field_id('heigh'); ?>"><?php _e('Map Height <small>(Default is : 370)</small>',GEODIRECTORY_TEXTDOMAIN);?>:
            <input class="widefat" id="<?php echo $this->get_field_id('heigh'); ?>" name="<?php echo $this->get_field_name('heigh'); ?>" type="text" value="<?php echo esc_attr($heigh); ?>" />
            </label>
        </p>
				
				<?php
					$map_zoom_level = geodir_map_zoom_level();
				?>
        
        <p>
            <label for="<?php echo $this->get_field_id('zoom'); ?>"><?php _e('Map Zoom level',GEODIRECTORY_TEXTDOMAIN);?>:
           
						<select class="widefat" id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>" > <?php
        
					foreach($map_zoom_level as $level){
						$selected = '';
						if($level == $zoom)
							$selected = 'selected="selected"';
						
						echo '<option '.$selected.' value="'.$level.'">'.$level.'</option>';
						
					}?>
        
      		 </select>
			 
            </label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('autozoom'); ?>"><?php _e('Map Auto Zoom ?',GEODIRECTORY_TEXTDOMAIN);?>:
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('autozoom'); ?>" name="<?php echo $this->get_field_name('autozoom'); ?>"<?php if($autozoom){echo 'checked="checked"';}?> /></label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('sticky'); ?>"><?php _e('Map Sticky(should stick to the right of screen) ?',GEODIRECTORY_TEXTDOMAIN);?>:
            <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('sticky'); ?>" name="<?php echo $this->get_field_name('sticky'); ?>"<?php if($sticky){echo 'checked="checked"';}?> />      </label>
        </p>
        
        <p>
      <label for="<?php echo $this->get_field_id('scrollwheel'); ?>"><?php _e('Enable mouse scroll zoom ?',GEODIRECTORY_TEXTDOMAIN);?>:
      <input id="<?php echo $this->get_field_id('scrollwheel'); ?>" name="<?php echo $this->get_field_name('scrollwheel'); ?>" type="checkbox"  value="1"  <?php if($scrollwheel){ ?>checked="checked" <?php }?> />
      </label>
    </p>
        
   <!-- <p>
      <label for="<?php echo $this->get_field_id('showall'); ?>"><?php _e('Show all listings on map? (not just page list)',GEODIRECTORY_TEXTDOMAIN);?>:
      <input id="<?php echo $this->get_field_id('showall'); ?>" name="<?php echo $this->get_field_name('showall'); ?>" type="checkbox"  value="1"  <?php if($showall){ ?>checked="checked" <?php }?> />
      </label>
    </p> -->
    
	<?php 
	}
} 
register_widget('geodir_map_listingpage');
