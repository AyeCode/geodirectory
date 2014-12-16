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
	$zoom = empty($instance['zoom']) ? '13' : apply_filters('widget_zoom', $instance['zoom']);
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
	$map_args['zoom'] = $zoom ;
	$map_args['autozoom'] = $autozoom;
	$map_args['child_collapse'] = $child_collapse;
	$map_args['enable_cat_filters'] = true;
	$map_args['enable_text_search'] = true;
	$map_args['enable_post_type_filters'] = true;
	$map_args['enable_location_filters'] = apply_filters('geodir_home_map_enable_location_filters', false);
	$map_args['enable_jason_on_load'] = false;
	$map_args['enable_marker_cluster'] = false;
	$map_args['enable_map_resize_button'] = true;
	$map_args['map_class_name'] = 'geodir-map-home-page';
	
	$is_geodir_home_map_widget = true;
	$map_args['is_geodir_home_map_widget'] = $is_geodir_home_map_widget;
	
	geodir_draw_map($map_args);
	
	/* home map post type slider */
	if ($is_geodir_home_map_widget) {
		add_action( 'wp_footer', array($this, 'geodir_home_map_add_script'), 100 );
	}
	
}
	function update($new_instance, $old_instance) {
	//save the widget
		$instance = $old_instance;		
		$instance['width'] = strip_tags($new_instance['width']);
		$instance['heigh'] = ($new_instance['heigh']);
		$instance['maptype'] = ($new_instance['maptype']);
		$instance['zoom'] = ($new_instance['zoom']);
		$instance['autozoom'] = isset($new_instance['autozoom']) ? $new_instance['autozoom'] : '';
		$instance['child_collapse'] = isset($new_instance['child_collapse']) ? ($new_instance['child_collapse']) : '';
		$instance['scrollwheel'] = isset($new_instance['scrollwheel']) ? ($new_instance['scrollwheel']) : '';

		return $instance;
	}
	function form($instance) {
	//widgetform in backend
	
		$instance = wp_parse_args( (array) $instance, array( 'width' => '', 'heigh' => '', 'maptype' =>'', 'zoom' => '', 'autozoom' => '','child_collapse'=>'0','scrollwheel'=>'0') );		
		$width = strip_tags($instance['width']);
		$heigh = strip_tags($instance['heigh']);
		$maptype = strip_tags($instance['maptype']);
		$zoom = strip_tags($instance['zoom']);
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
      <label for="<?php echo $this->get_field_id('heigh'); ?>"><?php _e('Map Height <small>(Default is : 425px)</small>',GEODIRECTORY_TEXTDOMAIN);?>:
      <input class="widefat" id="<?php echo $this->get_field_id('heigh'); ?>" name="<?php echo $this->get_field_name('heigh'); ?>" type="text" value="<?php echo esc_attr($heigh); ?>" />
      </label>
    </p>
		
		<p>
      <label for="<?php echo $this->get_field_id('maptype'); ?>"><?php _e(' Select Map View',GEODIRECTORY_TEXTDOMAIN);?>:
      <select class="widefat" id="<?php echo $this->get_field_id('maptype'); ?>" name="<?php echo $this->get_field_name('maptype'); ?>" >
        
					<option <?php if(isset($maptype) && $maptype=='ROADMAP'){ echo 'selected="selected"';} ?> value="ROADMAP"><?php _e('Road Map',GEODIRECTORY_TEXTDOMAIN);?></option>
					<option <?php if(isset($maptype) && $maptype=='SATELLITE'){ echo 'selected="selected"';} ?> value="SATELLITE"><?php _e('Satellite Map',GEODIRECTORY_TEXTDOMAIN);?></option>
					<option <?php if(isset($maptype) && $maptype=='HYBRID'){ echo 'selected="selected"';} ?> value="HYBRID"><?php _e('Hybrid Map',GEODIRECTORY_TEXTDOMAIN);?></option>
                
       </select>
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
	}
	
	function geodir_home_map_add_script() {
		echo '<style>.geodir-map-home-page .geodir-map-posttype-list li{margin-left:0;} .geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list{padding-left:0;margin-left:0;}.geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list > li{display:inline-block;float:none}.geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list>li:first-child{padding-left:0;}.geodir-map-home-page .geodir-map-posttype-list{display:block;overflow:hidden;white-space:nowrap;width:100%;word-wrap:normal;position:relative;}</style>';
		?>
<script type="text/javascript">
jQuery(document).ready(function(){	
	geoDirMapSlide();	
	jQuery(window).resize(function() {
		jQuery('.geodir_map_container.geodir-map-home-page').each(function(){
			jQuery(this).find('.geodir-map-posttype-list').css({'width':'auto'});
			jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left':'0px'});
			geoDirMapPrepare(this);
		});
	});
});
function geoDirMapPrepare($thisMap) {
	var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
	var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
	var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
	var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
	var ptw1 = parseFloat($objMpList.outerWidth(true));
	$objMpList.css({'margin-left':wArrL+'px'});
	$objMpList.attr('data-width', ptw1);
	ptw1 = ptw1 - (wArrL + wArrR);
	$objMpList.width(ptw1);
	var ptw = $objPlList.width();
	var ptw2 = 0;
	$objPlList.find('li').each(function(){
		var ptw21 = jQuery(this).outerWidth(true);
		ptw2 += parseFloat(ptw21);
	});
	var doMov = parseFloat( ptw * 0.75 );
	ptw2 = ptw2 + ( ptw2 * 0.05 );
	var maxMargin = ptw2 - ptw;
	$objPlList.attr('data-domov', doMov);
	$objPlList.attr('data-maxMargin', maxMargin);
}
function geoDirMapSlide() {
	jQuery('.geodir_map_container.geodir-map-home-page').each(function(){
		var $thisMap = this;
		geoDirMapPrepare($thisMap);
		var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
		jQuery($thisMap).find('.geodir-leftarrow a').click(function(e){
			e.preventDefault();
			var cm = $objPlList.css('margin-left');	
			var doMov = parseFloat($objPlList.attr('data-domov'));
			var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
			cm = parseFloat(cm);
			if ( cm == 0 || maxMargin < 0 ) {
				return;
			}
			domargin = cm + doMov;
			if ( domargin > 0 ) {
				domargin = 0;
			}
			$objPlList.animate({'margin-left':domargin+'px'}, 1000);
		});
		jQuery($thisMap).find('.geodir-rightarrow a').click(function(e){
			e.preventDefault();
			var cm = $objPlList.css('margin-left');	
			var doMov = parseFloat($objPlList.attr('data-domov'));
			var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));	
			cm = parseFloat(cm);
			domargin = cm - doMov;
			if ( cm == ( maxMargin * -1 ) || maxMargin < 0 ) {
				return;
			}
			if ( ( domargin * -1 ) > maxMargin ) {
				domargin = maxMargin * -1;
			}
			$objPlList.animate({'margin-left':domargin+'px'}, 1000);		
		});
	});
}
</script>
		<?php
	}	
}
register_widget('geodir_homepage_map');
?>