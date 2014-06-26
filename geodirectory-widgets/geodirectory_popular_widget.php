<?php
/**
* Geodirectory popular posts category widget *
**/
 
class geodir_popular_post_category extends WP_Widget {

	function geodir_popular_post_category()
	{
		//Constructor
		$widget_ops = array('classname' => 'geodir_popular_post_category', 'description' => __('GD > Popular Post Category',GEODIRECTORY_TEXTDOMAIN) );
		$this->WP_Widget('popular_post_category', __('GD > Popular Post Category',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		
		
	}
	
	
	function widget($args, $instance) 
	{
		
		// prints the widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? __('Popular Categories',GEODIRECTORY_TEXTDOMAIN) : apply_filters('widget_title', __($instance['title'],GEODIRECTORY_TEXTDOMAIN));
		
		global $plugin_prefix, $wpdb;
			
		$gd_post_type = geodir_get_current_posttype();
		/*
		if($gd_post_type):
			$post_type_info = get_post_type_object( $gd_post_type );
			$single_name = $post_type_info->labels->singular_name;
			$title = __('Popular',GEODIRECTORY_TEXTDOMAIN).' '.$single_name. __(' Categories',GEODIRECTORY_TEXTDOMAIN);
		endif;
		*/
		$taxonomy = geodir_get_taxonomies( $gd_post_type );
			
		$args = array(
				'orderby'       => 'count', 
				'order'         => 'DESC',
				'pad_counts'    => true); 
		$terms = get_terms( $taxonomy );
		
		if(!empty($terms)): ?>
		
			<div class="geodir-category-list-in clearfix">
				<div class="geodir-cat-list clearfix">
				  <?php echo $before_title.__($title).$after_title;?>
					
					<?php 
					global $geodir_post_category_str;
					$cat_count = 0;
					
					$geodir_post_category_str = array();
					
					echo '<ul class="geodir-popular-cat-list">'; 
					
					foreach($terms as $cat){ 
					
					$taxonomy_obj = get_taxonomy($cat->taxonomy);
					
					$post_type = $taxonomy_obj->object_type[0];	
						
						if($cat_count%15 == 0 )
							
							$total_post = 0;
							
							echo '<li><a href="'.get_term_link($cat,$cat->taxonomy).'"><i class="fa fa-caret-right"></i> ';
							echo ucwords($cat->name).' (<span class="geodir_term_class geodir_link_span geodir_category_class_'.$post_type.'_'.$cat->term_id.'" >'.$total_post.'</span>) ';
							
							$geodir_post_category_str[] = array('posttype'=>$post_type, 'termid'=>$cat->term_id);
							
							echo '</a></li>';
							
							$cat_count++;
					 } 
					 echo '</ul>'; 
					 
					 
					// do_action('geodir_term_array_count',$post_category_array); 
					
					?>
				  
				</div> 
             <?php
             
			 ?>
                
				<?php if($cat_count > 15)	 echo '<a class="geodir-morecat">'.__('More Categories',GEODIRECTORY_TEXTDOMAIN).'</a>'; ?>  
			</div>
     
    	<?php endif; 
		
		echo $after_widget;


	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}
	
	
	function form($instance) 
	{
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, 
									array( 	'title' => ''));
		
		$title = strip_tags($instance['title']);
		
		?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	
	<?php
	
	} 
}
register_widget('geodir_popular_post_category');	


/**
* Geodirectory popular posts widget *
**/
class geodir_popular_postview extends WP_Widget {

	function geodir_popular_postview()
	{
		//Constructor
		$widget_ops = array('classname' => 'geodir_popular_post_view', 'description' => __('GD > Popular Post View',GEODIRECTORY_TEXTDOMAIN) );
		$this->WP_Widget('popular_post_view', __('GD > Popular Post View',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
	}
	
	
	function widget($args, $instance) 
	{
		
		// prints the widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? ucwords($instance['category_title']) : apply_filters('widget_title', __($instance['title'],GEODIRECTORY_TEXTDOMAIN));
		
		$post_type = empty($instance['post_type']) ? 'gd_place' : apply_filters('widget_post_type', $instance['post_type']);
		
		$category = empty($instance['category']) ? '0' : apply_filters('widget_category', $instance['category']);
		
		$post_number = empty($instance['post_number']) ? '5' : apply_filters('widget_post_number', $instance['post_number']);
		
		$layout = empty($instance['layout']) ? 'gridview_onehalf' : apply_filters('widget_layout', $instance['layout']);
		
		$add_location_filter = empty($instance['add_location_filter']) ? '0' : apply_filters('widget_layout', $instance['add_location_filter']);
		
		$listing_width = empty($instance['listing_width']) ? '' : apply_filters('widget_layout', $instance['listing_width']);
		
		$list_sort = empty($instance['list_sort']) ? 'latest' : apply_filters('widget_list_sort', $instance['list_sort']);
		
		//$character_count = empty($instance['character_count']) ? '0' : apply_filters('widget_list_sort', $instance['character_count']);
		if(isset($instance['character_count'])){$character_count = apply_filters('widget_list_character_count', $instance['character_count']);}
		else{$character_count ='';}
		
		if(empty($title) || $title == 'All' ){
			$title .= ' '.get_post_type_plural_label($post_type);
		}
		
		/*if($post_type != ''){
			
			$all_postypes = geodir_get_posttypes();

			if(!in_array($post_type, $all_postypes))
				return false;
			
		}*/
		
		$location_url = '';
		
		$location_url = array();
		$city = get_query_var('gd_city');
		if( !empty($city) ){
			
			if(get_option('geodir_show_location_url') == 'all'){
				$country = get_query_var('gd_country');
				$region = get_query_var('gd_region');
				if(!empty($country))
					$location_url[] = $country;
				
				if(!empty($region))
					$location_url[] = $region;
			}		
			$location_url[] = $city;		
		}
			/*else{
			
				$location = geodir_get_default_location();
				
				if(get_option('geodir_show_location_url') == 'all'){
					$location_url[] = isset($location->country_slug) ? $location->country_slug : '';
					$location_url[] = isset($location->region_slug) ? $location->region_slug : '';
				}
				$location_url[] = isset($location->city_slug) ? $location->city_slug : '';
			}*/
			
			$location_url = implode("/",$location_url);			
			
		//}
		
		if ( get_option('permalink_structure') )
			$viewall_url = get_post_type_archive_link($post_type);
		else
			$viewall_url = get_post_type_archive_link($post_type);
		
		
		if(!empty($category) && $category[0] != '0'){
			global $geodir_add_location_url;
			$geodir_add_location_url = '0';
			if($add_location_filter != '0'){
				$geodir_add_location_url = '1'; 
			}	
			$viewall_url = get_term_link( (int)$category[0], $post_type.'category');
			$geodir_add_location_url = NULL; 
		}
		
		?>
			<div class="geodir_locations geodir_location_listing">
            <?php do_action('geodir_before_view_all_link_in_widget') ; ?>
							<div class="geodir_list_heading clearfix">
								<?php echo $before_title.$title.$after_title;?>
								 <a href="<?php echo $viewall_url;?>" class="geodir-viewall">
									<?php _e('View all',GEODIRECTORY_TEXTDOMAIN);?>
								 </a>
							</div>
							<?php do_action('geodir_after_view_all_link_in_widget') ; ?>	
							<?php 
								$query_args = array( 
									'posts_per_page' => $post_number,
									'is_geodir_loop' => true,
									'gd_location' 	 => ($add_location_filter) ? true : false,
									'post_type' => $post_type,
									'order_by' =>$list_sort,
									'excerpt_length' => $character_count,
									);
								
								if(!empty($category) && $category[0] != '0'){
									
									$category_taxonomy = geodir_get_taxonomies($post_type); 
									
									######### WPML #########
									if(function_exists('icl_object_id')) {
									$category = gd_lang_object_ids($category, $category_taxonomy[0]);
									}
									######### WPML #########
									
									$tax_query = array( 'taxonomy' => $category_taxonomy[0],
												 		'field' => 'id',
														'terms' => $category);
									
									$query_args['tax_query'] = array( $tax_query );					
								}
								
								global $gridview_columns;
								
								query_posts( $query_args );
								
								if(strstr($layout,'gridview')){
									
									$listing_view_exp = explode('_',$layout);
									
									$gridview_columns = $layout;
									
									$layout = $listing_view_exp[0];
									
								}
								
								
								$template = apply_filters( "geodir_template_part-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );

						
								
								include( $template );
							   
								wp_reset_query(); 
							 ?>				
						   
						</div>
		
		
		<?php 
		
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		
		if($new_instance['title'] == '')
		{
			$title = ucwords(strip_tags($new_instance['category_title']));
			//$instance['title'] = $title;
		}
		$instance['title'] = strip_tags($new_instance['title']);	
		
		$instance['post_type'] = strip_tags($new_instance['post_type']);
		//$instance['category'] = strip_tags($new_instance['category']);
		$instance['category'] = isset($new_instance['category']) ?  $new_instance['category'] : '';
		$instance['category_title'] = strip_tags($new_instance['category_title']);
		$instance['post_number'] = strip_tags($new_instance['post_number']);
		$instance['layout'] = strip_tags($new_instance['layout']);
		$instance['listing_width'] = strip_tags($new_instance['listing_width']);
		$instance['list_sort'] = strip_tags($new_instance['list_sort']);
		$instance['character_count'] = $new_instance['character_count'];
		if(isset($new_instance['add_location_filter']) && $new_instance['add_location_filter'] != '')
		$instance['add_location_filter']= strip_tags($new_instance['add_location_filter']);
		else
		$instance['add_location_filter'] = '0';
		
		
		return $instance;
	}
	
	function form($instance) 
	{
		//widgetform in backend
		$instance = wp_parse_args( (array) $instance, 
									array( 	'title' => '', 
											'post_type' => '',
											'category'=>array(),
											'category_title'=>'',
											'list_sort'=>'', 
											'list_order'=>'',
											'post_number' => '5',
											'layout'=> 'gridview_onehalf',
											'listing_width' => '',
											'add_location_filter'=>'1',
											'character_count'=>'20') 
								 );
		
		$title = strip_tags($instance['title']);
		
		$post_type = strip_tags($instance['post_type']);
		
		$category = $instance['category'];
		
		$category_title = strip_tags($instance['category_title']);
		
		$list_sort = strip_tags($instance['list_sort']);
		
		$list_order = strip_tags($instance['list_order']);
		
		$post_number = strip_tags($instance['post_number']);
		
		$layout = strip_tags($instance['layout']);
		
		$listing_width = strip_tags($instance['listing_width']);
		
		$add_location_filter = strip_tags($instance['add_location_filter']);
		
		$character_count = $instance['character_count'];
		
		?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
            </label>
        </p>
	
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:',GEODIRECTORY_TEXTDOMAIN);?>

            <?php $postypes = geodir_get_posttypes();
			$postypes = apply_filters('geodir_post_type_list_in_p_widget' ,$postypes ); ?>

            <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>" onchange="geodir_change_category_list(this)">
            	
				<?php foreach($postypes as $postypes_obj){ ?>
            		
                    <option <?php if($post_type == $postypes_obj){ echo 'selected="selected"'; } ?> value="<?php echo $postypes_obj; ?>"><?php $extvalue = explode('_',$postypes_obj); echo ucfirst($extvalue[1]); ?></option>
                
				<?php } ?>
                
            </select>
            </label>
        </p>
        
        
        <p id="post_type_cats">
            <label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Post Category:',GEODIRECTORY_TEXTDOMAIN);?>

         <?php 
				 
				 $post_type = ($post_type!= '') ? $post_type : 'gd_place';
				 
				 $all_postypes = geodir_get_posttypes();

					if(!in_array($post_type, $all_postypes))
						$post_type = 'gd_place';
				 
				$category_taxonomy = geodir_get_taxonomies($post_type); 
				$categories = get_terms( $category_taxonomy, array( 'orderby' => 'count','order' => 'DESC') );
				
			?>
					
            <select multiple="multiple" class="widefat" name="<?php echo $this->get_field_name('category'); ?>[]" onchange="geodir_popular_widget_cat_title(this)" >
            	
                <option <?php if(is_array($category)  && in_array( '0', $category)){ echo 'selected="selected"'; } ?> value="0"><?php _e('All',GEODIRECTORY_TEXTDOMAIN); ?></option>
				<?php foreach($categories as $category_obj){ 
					$selected = '';
					 if(is_array($category)  && in_array( $category_obj->term_id, $category))
					 	echo $selected = 'selected="selected"';
					 
					?>
            		
                    <option <?php echo $selected; ?> value="<?php echo $category_obj->term_id; ?>"><?php echo ucfirst($category_obj->name); ?></option>
                
				<?php } ?>
                
            </select>
						
						
           <input type="hidden" name="<?php echo $this->get_field_name('category_title'); ?>" id="<?php echo $this->get_field_id('category_title'); ?>" value="<?php if($category_title != '') echo $category_title; else echo __('All',GEODIRECTORY_TEXTDOMAIN);?>" />
					 
            </label>
        </p>
        
		<p>
        	<label for="<?php echo $this->get_field_id('list_sort'); ?>"><?php _e('Sort by:',GEODIRECTORY_TEXTDOMAIN);?>
            
           <select class="widefat" id="<?php echo $this->get_field_id('list_sort'); ?>" name="<?php echo $this->get_field_name('list_sort'); ?>">
            	
                <option <?php if($list_sort == 'latest'){ echo 'selected="selected"'; } ?> value="latest"><?php _e('Latest',GEODIRECTORY_TEXTDOMAIN); ?></option>
               
                 <option <?php if($list_sort == 'featured'){ echo 'selected="selected"'; } ?> value="featured"><?php _e('Featured',GEODIRECTORY_TEXTDOMAIN); ?></option>
                
                <option <?php if($list_sort == 'high_review'){ echo 'selected="selected"'; } ?> value="high_review"><?php _e('Review',GEODIRECTORY_TEXTDOMAIN); ?></option>
                
                <option <?php if($list_sort == 'high_rating'){ echo 'selected="selected"'; } ?> value="high_rating"><?php _e('Rating',GEODIRECTORY_TEXTDOMAIN); ?></option>
								
								<option <?php if($list_sort == 'random'){ echo 'selected="selected"'; } ?> value="random"><?php _e('Random',GEODIRECTORY_TEXTDOMAIN); ?></option>
                
            </select>
            </label>
        </p>
        
        <p>
        
            <label for="<?php echo $this->get_field_id('post_number'); ?>"><?php _e('Number of posts:',GEODIRECTORY_TEXTDOMAIN);?>
            
            <input class="widefat" id="<?php echo $this->get_field_id('post_number'); ?>" name="<?php echo $this->get_field_name('post_number'); ?>" type="text" value="<?php echo esc_attr($post_number); ?>" />
            </label>
        </p>
       
        <p>
        	<label for="<?php echo $this->get_field_id('layout'); ?>">
			<?php _e('Layout:',GEODIRECTORY_TEXTDOMAIN);?>
            <select class="widefat" id="<?php echo $this->get_field_id('layout'); ?>" name="<?php echo $this->get_field_name('layout'); ?>">
            	<option <?php if($layout == 'gridview_onehalf'){ echo 'selected="selected"'; } ?> value="gridview_onehalf"><?php _e('Grid View (Two Columns)',GEODIRECTORY_TEXTDOMAIN); ?></option>
              <option <?php if($layout == 'gridview_onethird'){ echo 'selected="selected"'; } ?> value="gridview_onethird"><?php _e('Grid View (Three Columns)',GEODIRECTORY_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefourth'){ echo 'selected="selected"'; } ?> value="gridview_onefourth"><?php _e('Grid View (Four Columns)',GEODIRECTORY_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'gridview_onefifth'){ echo 'selected="selected"'; } ?> value="gridview_onefifth"><?php _e('Grid View (Five Columns)',GEODIRECTORY_TEXTDOMAIN); ?></option>
							<option <?php if($layout == 'list'){ echo 'selected="selected"'; } ?> value="list"><?php _e('List view',GEODIRECTORY_TEXTDOMAIN); ?></option>
								
            </select>    
            </label>
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('listing_width'); ?>"><?php _e('Listing width:',GEODIRECTORY_TEXTDOMAIN);?>
            
            	<input class="widefat" id="<?php echo $this->get_field_id('listing_width'); ?>" name="<?php echo $this->get_field_name('listing_width'); ?>" type="text" value="<?php echo esc_attr($listing_width); ?>" />
            </label>
        </p>
				
				<p>
            <label for="<?php echo $this->get_field_id('character_count'); ?>"><?php _e('Post Content excerpt character count :',GEODIRECTORY_TEXTDOMAIN);?> 
            <input class="widefat" id="<?php echo $this->get_field_id('character_count'); ?>" name="<?php echo $this->get_field_name('character_count'); ?>" type="text" value="<?php echo esc_attr($character_count); ?>" />
            </label>
        </p>
        
         <p>
        	<label for="<?php echo $this->get_field_id('add_location_filter'); ?>">
			<?php _e('Enable Location Filter:',GEODIRECTORY_TEXTDOMAIN);?>
           	<input type="checkbox" id="<?php echo $this->get_field_id('add_location_filter'); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" <?php if($add_location_filter) echo 'checked="checked"';?>  value="1"  />
            </label>
        </p>
				
        
        <script type="text/javascript">
				
				function geodir_popular_widget_cat_title(val){
				
					jQuery(val).find("option:selected").each(function(i){
						if(i == 0)
							jQuery(val).closest('form').find('#post_type_cats input').val(jQuery(this).html());
						
					});
					
				}
				
			function geodir_change_category_list(obj,selected){
				var post_type = obj.value;
				
				var ajax_url = '<?php echo geodir_get_ajax_url(); ?>'
				
				var myurl = ajax_url+"&geodir_ajax=admin_ajax&ajax_action=get_cat_dl&post_type="+post_type+"&selected="+selected;
				
				jQuery.ajax({
					type: "GET",
					url: myurl,
					success: function(data){
					
						jQuery(obj).closest('form').find('#post_type_cats select').html(data);
						
					}
				});
				
			}
			
			<?php if(is_active_widget( false, false, $this->id_base, true )){ ?>
				var post_type = jQuery('#<?php echo $this->get_field_id('post_type'); ?>').val();
				
			<?php } ?>
			
		</script>
        
	<?php  
	} 
}
register_widget('geodir_popular_postview');	

