<?php

// =============================== Google Map V3 Home page======================================
class geodir_homepage_map extends WP_Widget {
	
	function geodir_homepage_map() {
	//Constructor
		$widget_ops = array('classname' => 'widget Google Map in Home page', 'description' => __('Google Map in Home page. It will show you google map V3 for Home page with category checkbox selection.',GEODIRECTORY_TEXTDOMAIN) );		
		$this->WP_Widget('geodir_map_v3_home_map', __('GD > GMap - Home page',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		
	}
		
	function widget($args, $instance) {
	extract($args, EXTR_SKIP);
	$width = empty($instance['width']) ? '960' : apply_filters('widget_width', $instance['width']);
	$height = empty($instance['heigh']) ? '425' : apply_filters('widget_heigh', $instance['heigh']);
	$maptype = empty($instance['maptype']) ? 'ROADMAP' : apply_filters('widget_maptype', $instance['maptype']);
	$autozoom = empty($instance['autozoom']) ? '' : apply_filters('widget_autozoom', $instance['autozoom']);
	$child_collapse = empty($instance['child_collapse']) ? '0' : apply_filters('widget_child_collapse', $instance['child_collapse']);
	$scrollwheel = empty($instance['scrollwheel']) ? '0' : apply_filters('widget_scrollwheel', $instance['scrollwheel']);
	
	//$str = createRandomString();
	
	$map_args = array();
	$map_args['map_canvas_name'] = str_replace('-' , '_' , $args['widget_id']); //'home_map_canvas'.$str ;
	$map_args['width'] = $width;
	$map_args['height'] = $height;
	$map_args['maptype'] = $maptype;
	$map_args['scrollwheel'] = $scrollwheel;
	$map_args['autozoom'] = $autozoom;
	$map_args['child_collapse'] = $child_collapse;
	$map_args['enable_cat_filters'] = true;
	$map_args['enable_text_search'] = true;
	$map_args['enable_post_type_filters'] = true;
	$map_args['enable_location_filters'] = apply_filters('geodir_home_map_enable_location_filters', false);
	$map_args['enable_jason_on_load'] = false;
	$map_args['enable_marker_cluster'] = false;
	$map_args['enable_map_resize_button'] = true;
	
	geodir_draw_map($map_args);
	
}
	function update($new_instance, $old_instance) {
	//save the widget
		$instance = $old_instance;		
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['heigh'] = ($new_instance['heigh']);
		$instance['maptype'] = ($new_instance['maptype']);
		$instance['autozoom'] = isset($new_instance['autozoom']) ? $new_instance['autozoom'] : '';
		$instance['child_collapse'] = isset($new_instance['child_collapse']) ? ($new_instance['child_collapse']) : '';
		$instance['scrollwheel'] = isset($new_instance['scrollwheel']) ? ($new_instance['scrollwheel']) : '';

		return $instance;
	}
	function form($instance) {
	//widgetform in backend
	
		$instance = wp_parse_args( (array) $instance, array( 'width' => '', 'heigh' => '', 'maptype' =>'', 'autozoom' => '','child_collapse'=>'0','scrollwheel'=>'0') );		
		$width = strip_tags($instance['width']);
		$heigh = strip_tags($instance['heigh']);
		$maptype = strip_tags($instance['maptype']);
		$autozoom = strip_tags($instance['autozoom']);
		$child_collapse = strip_tags($instance['child_collapse']);
		$scrollwheel = strip_tags($instance['scrollwheel']);
	?> 	

    <p>
      <label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Map Width <small>(Default is : 960px)</small>',GEODIRECTORY_TEXTDOMAIN);?>:
      <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr($width); ?>" />
      </label>
    </p>
     <p>
      <label for="<?php echo $this->get_field_id('heigh'); ?>"><?php _e('Map Heigh <small>(Default is : 425px)</small>',GEODIRECTORY_TEXTDOMAIN);?>:
      <input class="widefat" id="<?php echo $this->get_field_id('heigh'); ?>" name="<?php echo $this->get_field_name('heigh'); ?>" type="text" value="<?php echo esc_attr($heigh); ?>" />
      </label>
    </p>
		
		<p>
      <label for="<?php echo $this->get_field_id('maptype'); ?>"><?php _e(' Select Map View',GEODIRECTORY_TEXTDOMAIN);?>:
      <select class="widefat" id="<?php echo $this->get_field_id('maptype'); ?>" name="<?php echo $this->get_field_name('maptype'); ?>" >
        
					<option <?php if(isset($maptype) && $maptype=='ROADMAP'){ echo 'selected="selected"';} ?> value="ROADMAP">Road Map</option>
					<option <?php if(isset($maptype) && $maptype=='SATELLITE'){ echo 'selected="selected"';} ?> value="SATELLITE">Satellite Map</option>
					<option <?php if(isset($maptype) && $maptype=='HYBRID'){ echo 'selected="selected"';} ?> value="HYBRID">Hybrid Map</option>
                
       </select>
      </label>
    </p>
    
		<p>
				<label for="<?php echo $this->get_field_id('autozoom'); ?>"><?php _e('Map Auto Zoom ?',GEODIRECTORY_TEXTDOMAIN);?>:
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('autozoom'); ?>" name="<?php echo $this->get_field_name('autozoom'); ?>"<?php if($autozoom){echo 'checked="checked"';}?> /></label>
		</p>
		
    <p>
      <label for="<?php echo $this->get_field_id('child_collapse'); ?>"><?php _e('Collapse child/sub categories ?',GEODIRECTORY_TEXTDOMAIN);?>:
      <input id="<?php echo $this->get_field_id('child_collapse'); ?>" name="<?php echo $this->get_field_name('child_collapse'); ?>" type="checkbox"  value="1"  <?php if($child_collapse){ ?>checked="checked" <?php }?> />
      </label>
    </p>
    
    <p>
      <label for="<?php echo $this->get_field_id('scrollwheel'); ?>"><?php _e('Enable mouse scroll zoom ?',GEODIRECTORY_TEXTDOMAIN);?>:
      <input id="<?php echo $this->get_field_id('scrollwheel'); ?>" name="<?php echo $this->get_field_name('scrollwheel'); ?>" type="checkbox"  value="1"  <?php if($scrollwheel){ ?>checked="checked" <?php }?> />
      </label>
    </p>
    
    <?php
	}}
register_widget('geodir_homepage_map');
?>