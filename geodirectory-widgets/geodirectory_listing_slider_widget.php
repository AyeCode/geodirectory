<?php
/* ------------ Geodirectory listing slider widget */
class geodir_listing_slider_widget extends WP_Widget {

	function geodir_listing_slider_widget()
	{
		//Constructor
		$widget_ops = array('classname' => 'geodir_listing_slider_view', 'description' => __('GD > Listing Slider',GEODIRECTORY_TEXTDOMAIN) );
		$this->WP_Widget('listing_slider_view', __('GD > Listing Slider',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
	}
	
	
	function widget($args, $instance) 
	{
		// prints the widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'],GEODIRECTORY_TEXTDOMAIN));
		
		$post_type = empty($instance['post_type']) ? 'gd_place' : apply_filters('widget_post_type', $instance['post_type']);
		
		$category = empty($instance['category']) ? '0' : apply_filters('widget_category', $instance['category']);
		
		$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
		
		$show_title = empty($instance['show_title']) ? '' : apply_filters('widget_show_title', $instance['show_title']);
	
		$slideshow = empty($instance['slideshow']) ? 0 : apply_filters('widget_slideshow', $instance['slideshow']);
		
		$animationLoop = empty($instance['animationLoop']) ? 0 : apply_filters('widget_animationLoop', $instance['animationLoop']);
		
		$directionNav = empty($instance['directionNav']) ? 0 : apply_filters('widget_directionNav', $instance['directionNav']);
		
		$slideshowSpeed = empty($instance['slideshowSpeed']) ? 5000 : apply_filters('widget_slideshowSpeed', $instance['slideshowSpeed']);
		
		$animationSpeed = empty($instance['animationSpeed']) ? 600 : apply_filters('widget_animationSpeed', $instance['animationSpeed']);
		
		$animation = empty( $instance['animation'] ) ? 'slide' : apply_filters( 'widget_animation', $instance['animation'] );
		$list_sort = empty( $instance['list_sort'] ) ? 'latest' : apply_filters( 'widget_list_sort', $instance['list_sort'] );
		$show_featured_only = !empty( $instance['show_featured_only'] ) ? 1 : NULL;
		?>
<script type="text/javascript" >
jQuery(window).load(function(){
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
		animation: "<?php echo $animation;?>",
		selector: ".geodir-slides > li",
		namespace: "geodir-",
		controlNav: true,
		animationLoop: <?php echo $animationLoop;?>,
		slideshow: <?php echo $slideshow;?>,
		slideshowSpeed: <?php echo $slideshowSpeed;?>,  
	animationSpeed: <?php echo $animationSpeed;?>,            
		directionNav: <?php echo $directionNav;?>, 
		sync: "#geodir_widget_carousel",
		start: function(slider){
		jQuery('.geodir-listing-flex-loader').hide();
		jQuery('#geodir_widget_slider').css({'visibility':'visible'});
		jQuery('#geodir_widget_carousel').css({'visibility':'visible'});
		}
	});
});		
</script>
		<?php 				
		$query_args = array( 
							'posts_per_page' => $post_number,
							'is_geodir_loop'=> true,
							'post_type' => $post_type,
							'order_by' => $list_sort
						);
		if ( $show_featured_only ) {
			$query_args['show_featured_only'] = 1;
		}
		
		if( $category != 0 || $category != '' ) {
			$category_taxonomy = geodir_get_taxonomies($post_type); 
			$tax_query = array( 
								'taxonomy' => $category_taxonomy[0],
								'field' => 'id',
								'terms' => $category
							);
			
			$query_args['tax_query'] = array( $tax_query );					
		}
		
		query_posts( $query_args );				
		if ( have_posts() ):
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			
			$widget_main_slides = '';
			$nav_slides = '';
			$widget_slides = 0;
			 
			while ( have_posts() ) : the_post();
				global $post;
				$widget_image = geodir_get_featured_image($post->ID,'thumbnail', get_option('geodir_listing_no_img'));
			
				if ( !empty( $widget_image ) ) {
					if ( $widget_image->height >= 200 ) {
						$widget_spacer_height = 0;
					} else {
						$widget_spacer_height = ( ( 200 - $widget_image->height ) / 2 );
					}
					
					$widget_main_slides .= '<li class="geodir-listing-slider-widget"><img src="'.geodir_plugin_url()."/geodirectory-assets/images/spacer.gif".'" alt="'.$widget_image->title.'" title="'.$widget_image->title.'" style="max-height:'.$widget_spacer_height.'px;margin:0 auto;" width="100%" />';
					
					$title = '';
					if($show_title){
						$title = '<div class="geodir-slider-title"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></div>';
					}
					
					$widget_main_slides .= $title.'<img src="'.$widget_image->src.'" alt="'.$widget_image->title.'" title="'.$widget_image->title.'" style="max-height:200px;margin:0 auto;" /></li>';
					$nav_slides .= '<li><img src="'.$widget_image->src.'" alt="'.$widget_image->title.'" title="'.$widget_image->title.'" style="max-height:48px;margin:0 auto;" /></li>';			
					$widget_slides++;
				}
			endwhile;
			?>
			 <div class="flex-container" style="min-height:200px;">	
				<div class="geodir-listing-flex-loader"><i class="fa fa-refresh fa-spin"></i></div> 
				<div id="geodir_widget_slider" class="geodir_flexslider">
				  <ul class="geodir-slides clearfix"><?php echo $widget_main_slides; ?></ul>
				</div>
				<?php if( $widget_slides > 1 ) { ?>
				<div id="geodir_widget_carousel" class="geodir_flexslider">
				  <ul class="geodir-slides clearfix"><?php echo $nav_slides; ?></ul>
				</div>
				<?php } ?>
			</div>			
			<?php 
			wp_reset_query();
		endif;
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['post_type'] = strip_tags($new_instance['post_type']);
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['post_number'] = strip_tags($new_instance['post_number']);
		$instance['show_title'] = isset($new_instance['show_title']) ? $new_instance['show_title'] : '';
		$instance['slideshow'] = isset($new_instance['slideshow']) ? $new_instance['slideshow'] : '';
		$instance['animationLoop'] = isset($new_instance['animationLoop']) ? $new_instance['animationLoop'] : '';
		$instance['directionNav'] = isset($new_instance['directionNav']) ? $new_instance['directionNav'] : '';
		$instance['slideshowSpeed'] = $new_instance['slideshowSpeed'];
		$instance['animationSpeed'] = $new_instance['animationSpeed'];
		$instance['animation'] = $new_instance['animation'];
		$instance['list_sort'] = isset( $new_instance['list_sort'] ) ? $new_instance['list_sort'] : '';
		$instance['show_featured_only'] = isset( $new_instance['show_featured_only'] ) && $new_instance['show_featured_only'] ? 1 : 0;
		
		return $instance;
	}
	
	function form($instance)
	{
		
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance,
									array( 	'title' => '',
											'post_type' => '',
											'category'=>'',
											'post_number' => '5',
											'show_title'=> '',
											'slideshow'=> '',
											'animationLoop'=> '',
											'directionNav'=> '',
											'slideshowSpeed'=> 5000,
											'animationSpeed'=> 600,
											'animation'=> '',
											'list_sort' => 'latest',
											'show_featured_only' => '',
										)
								 );
		
		$title = strip_tags($instance['title']);
		
		$post_type = strip_tags($instance['post_type']);
		
		$category = strip_tags($instance['category']);
		
		$post_number = strip_tags($instance['post_number']);
		
		$show_title = $instance['show_title'];
		
		$slideshow = $instance['slideshow'];
		
		$animationLoop = $instance['animationLoop'];
		
		$directionNav = $instance['directionNav'];
		
		$slideshowSpeed = $instance['slideshowSpeed'];
		
		$animationSpeed = $instance['animationSpeed'];
		
		$animation = $instance['animation'];
		$list_sort = $instance['list_sort'];
		$show_featured_only = isset( $instance['show_featured_only'] ) && $instance['show_featured_only'] ? true : false;
		
		$sort_fields = array();
		$sort_fields[] = array( 'field' => 'latest', 'label' => __( 'Latest', GEODIRECTORY_TEXTDOMAIN ) );
		$sort_fields[] = array( 'field' => 'featured', 'label' => __( 'Featured', GEODIRECTORY_TEXTDOMAIN ) );
		$sort_fields[] = array( 'field' => 'high_review', 'label' => __( 'Review', GEODIRECTORY_TEXTDOMAIN ) );
		$sort_fields[] = array( 'field' => 'high_rating', 'label' => __( 'Rating', GEODIRECTORY_TEXTDOMAIN ) );
		$sort_fields[] = array( 'field' => 'random', 'label' => __( 'Random', GEODIRECTORY_TEXTDOMAIN ) );	
		?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:',GEODIRECTORY_TEXTDOMAIN);?>

            <?php $postypes = geodir_get_posttypes(); ?>

            <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" onchange="geodir_change_category_list(this.value)">
            	
				<?php foreach($postypes as $postypes_obj){ ?>
            		
                    <option <?php if($post_type == $postypes_obj){ echo 'selected="selected"'; } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_',$postypes_obj); echo ucfirst($extvalue[1]); ?></option>
                
				<?php } ?>
                
            </select>
            </label>
        </p>
        
        
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Post Category:',GEODIRECTORY_TEXTDOMAIN);?>

            <?php 
				$category_taxonomy = geodir_get_taxonomies('gd_place'); 
				$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
			?>

            <select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
            	<option <?php if($category == '0'){ echo 'selected="selected"'; } ?> value="0"><?php _e('All',GEODIRECTORY_TEXTDOMAIN); ?></option>
				<?php foreach($categories as $category_obj){ ?>
            		
                    <option <?php if($category == $category_obj->term_id){ echo 'selected="selected"'; } ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
                
				<?php } ?>
                
            </select>
            </label>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'list_sort' ); ?>"><?php _e( 'Sort by:', GEODIRECTORY_TEXTDOMAIN ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'list_sort' ); ?>" name="<?php echo $this->get_field_name( 'list_sort' ); ?>">
			<?php foreach ( $sort_fields as $sort_field ) { ?>
				<option value="<?php echo $sort_field['field']; ?>" <?php echo ( $list_sort == $sort_field['field'] ? 'selected="selected"' : '' ); ?>><?php echo $sort_field['label']; ?></option>
			<?php } ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:',GEODIRECTORY_TEXTDOMAIN);?>
            <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
            </label>
        </p>
				
				
				<p>
            <label for="<?php echo $this->get_field_id('animation'); ?>"><?php _e('Animation:',GEODIRECTORY_TEXTDOMAIN);?>

            <select class="widefat" id="<?php echo $this->get_field_id('animation'); ?>" name="<?php echo $this->get_field_name('animation'); ?>">
       				<option <?php if($animation == 'slide'){ echo 'selected="selected"'; } ?> value="slide">Slide</option>
							<option <?php if($animation == 'fade'){ echo 'selected="selected"'; } ?> value="fade">Fade</option>
            </select>
            </label>
        </p>
				
				<p>
            <label for="<?php echo $this->get_field_id('slideshowSpeed'); ?>"><?php _e('Slide Show Speed: (milliseconds)',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('slideshowSpeed'); ?>" name="<?php echo $this->get_field_name('slideshowSpeed'); ?>" type="text" value="<?php echo esc_attr($slideshowSpeed); ?>" />
            </label>
        </p>
				
				<p>
            <label for="<?php echo $this->get_field_id('animationSpeed'); ?>"><?php _e('Animation Speed: (milliseconds)',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('animationSpeed'); ?>" name="<?php echo $this->get_field_name('animationSpeed'); ?>" type="text" value="<?php echo esc_attr($animationSpeed); ?>" />
            </label>
        </p>
				
				<p>
        	<label for="<?php echo $this->get_field_id('slideshow'); ?>"><?php _e('SlideShow:',GEODIRECTORY_TEXTDOMAIN);?>
             
							<input type="checkbox" <?php if($slideshow){ echo 'checked="checked"'; } ?> id="<?php echo $this->get_field_id('slideshow'); ?>" value="1" name="<?php echo $this->get_field_name('slideshow'); ?>" />
							  
            </label>
        </p>
				
				<p>
        	<label for="<?php echo $this->get_field_id('animationLoop'); ?>"><?php _e('AnimationLoop:',GEODIRECTORY_TEXTDOMAIN);?>
             
							<input type="checkbox" <?php if($animationLoop){ echo 'checked="checked"'; } ?> id="<?php echo $this->get_field_id('animationLoop'); ?>" value="1" name="<?php echo $this->get_field_name('animationLoop'); ?>" />
							  
            </label>
        </p>
				
				<p>
        	<label for="<?php echo $this->get_field_id('directionNav'); ?>"><?php _e('DirectionNav:',GEODIRECTORY_TEXTDOMAIN);?>
             
							<input type="checkbox" <?php if($directionNav){ echo 'checked="checked"'; } ?> id="<?php echo $this->get_field_id('directionNav'); ?>" value="1" name="<?php echo $this->get_field_name('directionNav'); ?>" />
							  
            </label>
        </p>
			
       
       <p>
        	<label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show Title:',GEODIRECTORY_TEXTDOMAIN);?>
             
							<input type="checkbox" <?php if($show_title){ echo 'checked="checked"'; } ?> id="<?php echo $this->get_field_id('show_title'); ?>" value="1" name="<?php echo $this->get_field_name('show_title'); ?>" />
							  
            </label>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_featured_only' ); ?>"><?php _e( 'Show only featured listings:', GEODIRECTORY_TEXTDOMAIN ); ?>  <input type="checkbox" id="<?php echo $this->get_field_id( 'show_featured_only' ); ?>" name="<?php echo $this->get_field_name( 'show_featured_only' ); ?>" <?php if( $show_featured_only ) echo 'checked="checked"'; ?> value="1" />
			</label>
		</p>
		<script type="text/javascript">
			function geodir_change_category_list(post_type,selected){
				
				var ajax_url = '<?php echo geodir_get_ajax_url(); ?>'
				
				var myurl = ajax_url+"&geodir_ajax=admin_ajax&ajax_action=get_cat_dl&post_type="+post_type+"&selected="+selected;
				
				jQuery.ajax({
					type: "GET",
					url: myurl,
					success: function(data){
						jQuery('#<?php echo $this->get_field_id('category'); ?>').html(data);
					}
				});
				
			}
			
			<?php if(is_active_widget( false, false, $this->id_base, true )){ ?>
				var post_type = jQuery('#<?php echo $this->get_field_id('post_type'); ?>').val();
				
				geodir_change_category_list(post_type,'<?php echo $category;?>');
			<?php } ?>
			
		</script>
		
		        
	<?php
	} 
}
register_widget('geodir_listing_slider_widget');	
          
	