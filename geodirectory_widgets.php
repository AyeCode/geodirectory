<?php 
function geodir_register_sidebar(){
	global $geodir_sidebars ;
	if ( function_exists('register_sidebar') ) {
		
		
		/*===========================*/
		/* Home page sidebars start*/
		/*===========================*/
		
		if(get_option('geodir_show_home_top_section')){
		register_sidebars(1,array('id'=> 'geodir_home_top','name' => __('GD Home Top Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_home_top' ;
		}
		
		if( get_option('geodir_show_home_contant_section') ) {
		register_sidebars(1,array('id'=> 'geodir_home_contant','name' => __('GD Home Content Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="geodir-widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_home_contant' ;
		}
		
		if( get_option('geodir_show_home_right_section') ) {
		register_sidebars(1,array('id'=> 'geodir_home_right','name' => __('GD Home Right Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_home_right' ;
		}
		
		if( get_option('geodir_show_home_left_section') ) {
		register_sidebars(1,array('id'=> 'geodir_home_left','name' => __('GD Home Left Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_home_left' ;
		}
		
		if(get_option('geodir_show_home_bottom_section')) {
		register_sidebars(1,array('id'=> 'geodir_home_bottom','name' => __('GD Home Bottom Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_home_bottom' ;
		}
		
		/*===========================*/
		/* Home page sidebars end*/
		/*===========================*/
		
		/*===========================*/
		/* Listing page sidebars start*/
		/*===========================*/
		
		if(get_option('geodir_show_listing_top_section')) {
		register_sidebars(1,array('id'=> 'geodir_listing_top','name' => __('GD Listing Top Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_listing_top' ;
		}
		
		if( get_option('geodir_show_listing_left_section') ) {
		register_sidebars(1,array('id'=> 'geodir_listing_left_sidebar','name' => __('GD Listing Left Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_listing_left_sidebar' ;
		}
		
		if( get_option('geodir_show_listing_right_section') ) {
		register_sidebars(1,array('id'=> 'geodir_listing_right_sidebar','name' => __('GD Listing Right Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_listing_right_sidebar' ;
		}
		
		if(get_option('geodir_show_listing_bottom_section')) {
		register_sidebars(1,array('id'=> 'geodir_listing_bottom','name' => __('GD Listing Bottom Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_listing_bottom' ;
		}
		
		/*===========================*/
		/* Listing page sidebars start*/
		/*===========================*/
		
		/*===========================*/
		/* Search page sidebars start*/
		/*===========================*/
		
		if(get_option('geodir_show_search_top_section')) {
		register_sidebars(1,array('id'=> 'geodir_search_top','name' => __('GD Search Top Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_search_top' ;
		}
		
		if( get_option('geodir_show_search_left_section') ) {
		register_sidebars(1,array('id'=> 'geodir_search_left_sidebar','name' => __('GD Search Left Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_search_left_sidebar' ;
		}
		
		if( get_option('geodir_show_search_right_section') ) {
		register_sidebars(1,array('id'=> 'geodir_search_right_sidebar','name' => __('GD Search Right Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_search_right_sidebar' ;
		}
		
		if(get_option('geodir_show_search_bottom_section')) {
		register_sidebars(1,array('id'=> 'geodir_search_bottom','name' => __('GD Search Bottom Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_search_bottom' ;
		}
		
		/*===========================*/
		/* Search page sidebars end*/
		/*===========================*/
		
		/*==================================*/
		/* Detail/Single page sidebars start*/
		/*==================================*/
		if(get_option('geodir_show_detail_top_section')) {
		register_sidebars(1,array('id'=> 'geodir_detail_top','name' => __('GD Detail Top Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_detail_top' ;
		}
		
		register_sidebars(1,array('id'=> 'geodir_detail_sidebar','name' => __('GD Detail Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_detail_sidebar' ;
		
		if(get_option('geodir_show_detail_bottom_section')){
		register_sidebars(1,array('id'=> 'geodir_detail_bottom','name' => __('GD Detail Bottom Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_detail_bottom' ;
		}
		
		/*==================================*/
		/* Detail/Single page sidebars end*/
		/*==================================*/
		
		/*==================================*/
		/* Author page sidebars start       */
		/*==================================*/
		
		if(get_option('geodir_show_author_top_section')) { 
		register_sidebars(1,array('id'=> 'geodir_author_top','name' => __('GD Author Top Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_author_top' ;
		}
		
		if( get_option('geodir_show_author_left_section') ) {
		register_sidebars(1,array('id'=> 'geodir_author_left_sidebar','name' => __('GD Author Left Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_author_left_sidebar' ;
		}
		
		if( get_option('geodir_show_author_right_section') ) {
		register_sidebars(1,array('id'=> 'geodir_author_right_sidebar','name' => __('GD Author Right Sidebar',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_author_right_sidebar' ;
		}
		
		if(get_option('geodir_show_author_bottom_section')) { 
		register_sidebars(1,array('id'=> 'geodir_author_bottom','name' => __('GD Author Bottom Section',GEODIRECTORY_TEXTDOMAIN),'before_widget' => '<div class="widget">','after_widget' => '</div>','before_title' => '<h3><span>','after_title' => '</span></h3>'));
		
		$geodir_sidebars[] ='geodir_author_bottom' ;
		}
		
		/*==================================*/
		/* Author page sidebars end         */
		/*==================================*/
		
	}
} 


if(!function_exists('register_geodir_widgets')){
function register_geodir_widgets(){
	
	// ====================== Geodirectory Search Widget==================================
	class geodir_search_widget extends WP_Widget {
		function geodir_search_widget() {
		//Constructor
			$widget_ops = array('classname' => 'geodir_list', 'description' => __('Geodirectory search. It should be once on the page.',GEODIRECTORY_TEXTDOMAIN) );		
			$this->WP_Widget('geodir_search_widget', __('GD > Search',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		function widget($args, $instance) {
		// prints the widget
			extract($args, EXTR_SKIP);
			$title = empty($instance['title']) ? __('Search',GEODIRECTORY_TEXTDOMAIN) : apply_filters('widget_title', $instance['title']);
			$desc1 = empty($instance['desc']) ? '&nbsp;' : apply_filters('widget_desc', $instance['desc']);
			 ?>						
				
			<div class="geodir-widget">
            	
				<?php if(!empty($title)) { ?>
                <h3 class="widget-title"><?php echo $title; ?> </h3>
            	<?php } ?>
                
                <div class="searchform">
                    <form method="get" id="searchform2" action="<?php bloginfo('home'); ?>/" > 
                       <input type="hidden" name="place_search" value="1" />
                        <span class="searchfor"><input type="text" value="<?php if(isset($_REQUEST['s']) && $_REQUEST['s']!='cal_event'){echo trim(stripslashes($_REQUEST['s']));}else{echo SEARCH_FOR_TEXT;}?>" name="s" id="sr" class="s"  onfocus="if (this.value == '<?php echo SEARCH_FOR_TEXT; ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo SEARCH_FOR_TEXT; ?>';}" /><br/>
                         <small class="text"><?php echo SEARCH_FOR_MSG;?> </small>
                         </span>
                         
                         <span id="set_NEAR ME" class="near" ><span id="set_near"></span><input name="sn" id="sn" type="text" class="s" value="<?php if(isset($_REQUEST['sn'])){echo stripslashes($_REQUEST['sn']);}else{echo NEAR_TEXT;}?>" onblur="if (this.value == '') {this.value = '<?php echo NEAR_TEXT;?>';}"  onfocus="if (this.value == '<?php echo NEAR_TEXT;?>') {this.value = '';}"   /> 						<br/>	
                          <small class="text"><?php echo SEARCH_NEAR_MSG;?></small>
                         </span>
                         <input name="Sgeo_lat" id="Sgeo_lat" type="hidden" value="" />
                         <input name="Sgeo_lon" id="Sgeo_lon" type="hidden" value="" />
                        <input type="button" class="search_btn" value="<?php echo SEARCH;?>" alt="<?php echo SEARCH;?>"  />
                      </form>
                    </div>
                    <script type="text/javascript" src="http://gmaps-samples-v3.googlecode.com/svn/trunk/geolocate/geometa.js"></script> 
                    <script type="text/javascript">
                    var latlng;
                    var Sgeocoder;
                    var address;
                    var Sgeocoder = new google.maps.Geocoder();
					jQuery(document).ready(function(){
						
						jQuery('#sr,#sn').keydown(function(event){
							if(event.keyCode == 13){set_srch();}
						});
						
						jQuery('.search_btn').click(function(){
							set_srch();
						});
						
						jQuery('#set_near').click(function() {
							jQuery('#sn').val('<?php echo NEAR_TEXT;?>');
						});
						
						function set_srch()
						{ 		    
							
							if(jQuery(".searchfor").css("display") == "none"){
								jQuery(".searchfor").slideDown(2000);
								jQuery("#set_NEAR ME").slideDown(2000);
								return false;
							}
						
							if(jQuery('#sr').val() == '' || jQuery('#sr').val() == '<?php echo SEARCH_FOR_TEXT; ?>')
								jQuery('#sr').val(' ');	
						   
							if(jQuery('#sn').val() == '<?php echo NEAR_TEXT; ?>')
								jQuery('#sn').val('<?php echo NEAR_TEXT;?>');
						   
							 geocodeAddress();
						}
					
						 function updateSearchPosition(latLng) {
							jQuery('#Sgeo_lat').val(latLng.lat());
							jQuery('#Sgeo_lon').val(latLng.lng());
							jQuery("#searchform2").submit(); // submit form after insering the lat long positions
						}
						
						function geocodeAddress() {
							Sgeocoder = new google.maps.Geocoder(); // Call the geocode function
							
							if(jQuery('#sn').val() == ''){
								jQuery("#searchform2").submit();
							}else{
							
								var address = jQuery("#sn").val();
								if(jQuery('#sn').val() == '<?php echo NEAR_TEXT;?>'){
									initialise2();
								}else{
									Sgeocoder.geocode( { 'address': address<?php //gt_advanced_near_search();?> }, 
									function(results, status) {
									  	if (status == google.maps.GeocoderStatus.OK) {
											updateSearchPosition(results[0].geometry.location);
									 	} else {
											alert("<?php _e('Search was not successful for the following reason:',GEODIRECTORY_TEXTDOMAIN);?>" + status);
									  	}
									});
								}
							}
                      	}
						
						function initialise2() {
							var latlng = new google.maps.LatLng(56.494343,-4.205446);
							var myOptions = {
							  zoom: 4,
							  mapTypeId: google.maps.MapTypeId.TERRAIN,
							  disableDefaultUI: true
							}
							//alert(latLng);
							prepareGeolocation();
							doGeolocation();
						}
						
                      function doGeolocation() {
                        if (navigator.geolocation) {
                          navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
                        } else {
                          positionError(-1);
                        }
                      }
                     
                      function positionError(err) {
                        var msg;
                        switch(err.code) {
                          case err.UNKNOWN_ERROR:
                            msg = "<?php _e('Unable to find your location',GEODIRECTORY_TEXTDOMAIN);?>";
                            break;
                          case err.PERMISSION_DENINED:
                            msg = "<?php _e('Permission denied in finding your location',GEODIRECTORY_TEXTDOMAIN);?>";
                            break;
                          case err.POSITION_UNAVAILABLE:
                            msg = "<?php _e('Your location is currently unknown',GEODIRECTORY_TEXTDOMAIN);?>";
                            break;
                          case err.BREAK:
                            msg = "<?php _e('Attempt to find location took too long',GEODIRECTORY_TEXTDOMAIN);?>";
                            break;
                          default:
                            msg = "<?php _e('Location detection not supported in browser',GEODIRECTORY_TEXTDOMAIN);?>";
                        }
                        jQuery('#info').html(msg);
                      }
                     
						function positionSuccess(position) {
							var coords = position.coords || position.coordinate || position;
							jQuery('#Sgeo_lat').val(coords.latitude);
							jQuery('#Sgeo_lon').val(coords.longitude);
							  
							jQuery("#searchform2").submit(); 
						}
                     
					
					});
				</script> 
            </div>
			
		<?php
		}
		function update($new_instance, $old_instance) {
		//save the widget
			$instance = $old_instance;		
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['desc'] = ($new_instance['desc']);
			return $instance;
		}
		function form($instance) {
		//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'desc' => '' ) );		
			$title = strip_tags($instance['title']);
			$desc = ($instance['desc']);
	?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title',GEODIRECTORY_TEXTDOMAIN);?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
	<?php
		}
	}//Class end
	//register_widget('geodir_search_widget');
	
	
	// =============================== Login Widget ======================================
	class geodir_loginwidget extends WP_Widget {
		function geodir_loginwidget() {
		//Constructor
			$widget_ops = array('classname' => 'geodir_loginbox', 'description' => __('Geodirectory Loginbox Widget',GEODIRECTORY_TEXTDOMAIN) );		
			$this->WP_Widget('geodir_loginbox', __('GD > Loginbox',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		function widget($args, $instance) {
		// prints the widget
			extract($args, EXTR_SKIP);
			$title = empty($instance['title']) ? __('My Dashboard',GEODIRECTORY_TEXTDOMAIN) : apply_filters('widget_title', $instance['title']);
			?>						
				
			<div class="geodir-widget">
			 <h3 class="widget-title"><?php echo $title; ?> </h3>	
             <?php if(is_user_logged_in()) {
			  	global $current_user;
			 	$login_url = geodir_getlink(home_url(),array('geodir_signup'=>'true'),false);
			 	$add_listurl = get_permalink( get_option('geodir_add_listing_page') );
				$add_listurl = geodir_getlink( $add_listurl, array('listing_type'=>'gd_place') );
				
                $author_link = get_author_posts_url( $current_user->data->ID );
                $author_link = geodir_getlink($author_link,array('geodir_dashbord'=>'true'),false);
                $authorfav_link = geodir_getlink($author_link,array('stype'=>'gd_place','list'=>'favourite'),false);
				echo '<ul class="blogroll">';
				ob_start();
             ?>
			 	    <li><a class="signin" href="<?php echo wp_logout_url( home_url() );?>"><?php _e('Logout',GEODIRECTORY_TEXTDOMAIN);?></a></li>
                    <?php 
					$post_types = geodir_get_posttypes('object');
					
					$show_add_listing_post_types_main_nav = get_option('geodir_add_listing_link_user_dashboard');
					
					$geodir_allow_posttype_frontend = get_option('geodir_allow_posttype_frontend');
					
					if(!empty($post_types)){
						foreach($post_types as $post_type => $args){
							if(!empty($geodir_allow_posttype_frontend)){
								if ( in_array($post_type, $geodir_allow_posttype_frontend)) {	
									if(!empty($show_add_listing_post_types_main_nav)){
										if ( in_array($post_type, $show_add_listing_post_types_main_nav)) {	
											if(geodir_get_addlisting_link( $post_type )){
											
													$menu_class = '';
													if(geodir_get_current_posttype() == $post_type && geodir_is_page('add-listing'))
													$menu_class = 'current-menu-item';
												
													echo '<li class="menu-item '.$menu_class.'">
														<a href="'. geodir_get_addlisting_link( $post_type ) .'">
															'.__('Add',GEODIRECTORY_TEXTDOMAIN).' '.$args->labels->singular_name.'
														</a>
													</li>';
											}
										}
									}
								}
							}			
						}
					}
					?>
                    <!--<li><a href="<?php echo $add_listurl;?>"><?php _e('Add Listing',GEODIRECTORY_TEXTDOMAIN);?></a></li>-->
                    <li><a href="<?php echo $authorfav_link;?>"><?php _e('My Favorites',GEODIRECTORY_TEXTDOMAIN);?></a></li>
            	<?php 
				
				$show_listing_link_user_dashboard = get_option('geodir_listing_link_user_dashboard');
				foreach($post_types as $post_type => $args){
					
					if(!empty($show_listing_link_user_dashboard)){
						if ( in_array($post_type, $show_listing_link_user_dashboard)) {
							$post_type_link = geodir_getlink($author_link,array('stype'=>$post_type),false);
							//$post_type = explode('_',$post_type);
							$name = $args->labels->name;
							echo '<li><a href="'.$post_type_link.'">'.__('My',GEODIRECTORY_TEXTDOMAIN).' '. ucfirst($name) .'</a></li>';
						}
					}
				}
				$dashboard_link = ob_get_clean();
				echo apply_filters('geodir_dashboard_links',$dashboard_link);
				echo '</ul>';
			}else{ 
			?>
                
				<form name="loginform" class="loginform1" action="<?php echo get_option('home').'/index.php?geodir_signup=true'; ?>" method="post" >
					<div class="form_row"><label><?php _e('Email', GEODIRECTORY_TEXTDOMAIN);?>  <span>*</span></label>  <input name="log" type="text" class="textfield user_login1" /> <span class="user_loginInfo"></span> </div>
					<div class="form_row"><label><?php _e('Password', GEODIRECTORY_TEXTDOMAIN);?>  <span>*</span></label>  <input name="pwd" type="password" class="textfield user_pass1" /><span class="user_passInfo"></span>  </div>
					
					<input type="hidden" name="redirect_to" value="<?php echo geodir_curPageURL(); ?>" />
					<input type="hidden" name="testcookie" value="1" />
					<div class="form_row clearfix"><label class="labelblank">&nbsp;</label>  <input type="submit" name="submit" value="<?php _e(SIGN_IN_BUTTON);?>" class="b_signin"/><p class="forgot_link">   <a href="<?php echo home_url(); ?>/?geodir_signup=true&amp;page1=sign_up"><?php _e(NEW_USER_TEXT);?></a>  <br /> <a href="<?php echo home_url(); ?>/?geodir_signup=true&amp;page1=sign_in"><?php _e(FORGOT_PW_TEXT);?></a> </p> </div>
					
				 </form>           
				<?php }?>
				</div>
			
		<?php
		}
		function update($new_instance, $old_instance) {
		//save the widget
			$instance = $old_instance;		
			$instance['title'] = strip_tags($new_instance['title']);
			
			return $instance;
		}
		function form($instance) {
		//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 't1' => '', 't2' => '', 't3' => '',  'img1' => '', 'desc1' => '' ) );		
			$title = strip_tags($instance['title']);
		
	?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title',GEODIRECTORY_TEXTDOMAIN);?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
            
		   
	<?php 
		}
	}
	register_widget('geodir_loginwidget');
	
		// =============================== GeoDirectory Social Like Widget ===================
	class geodir_social_like_widget extends WP_Widget { 
		
		
		function geodir_social_like_widget()
		{
			$widget_ops = array('classname' => 'geodir_social_like_widget', 'description' => __('GD > Twitter,Facebook and Google+ buttons',GEODIRECTORY_TEXTDOMAIN) );		
			$this->WP_Widget('social_like_widget', __('GD > Social Like',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		
		function widget($args, $instance)
		{
			// prints the widget
			extract($args, EXTR_SKIP);
			
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			
			global $current_user,$post;
			
			?>
			
			<div class="geodir-widget likethis_widget">
			
			<?php if ( get_option('gd_tweet_button') ) { ?>
			
				<a href="http://twitter.com/share" class="twitter-share-button"><?php _e('Tweet',GEODIRECTORY_TEXTDOMAIN);?></a>
				
				<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script> 
			
			<?php } ?>
			
			<?php if ( get_option('gd_facebook_button') ) { ?>
			
				<iframe <?php if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)){echo 'allowtransparency="true"'; }?> class="facebook" src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode(get_permalink($post->ID)); ?>&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light" scrolling="no" frameborder="0"  style="border:none; overflow:hidden; width:100px; height:20px"></iframe> 
			
			
			<?php } ?>
			
			<?php if ( get_option('gd_google_button') ) { ?>
			
				<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
				{ parsetags: 'explicit' }
				</script>
				
				<div id="plusone-div"></div>
				<script type="text/javascript">gapi.plusone.render('plusone-div', {"size": "medium", "count": "true" });</script>                    
			<?php } ?>
			
			</div>
			
			
			<?php
			
		}
		
		function update($new_instance, $old_instance) 
		{
			//save the widget
			$instance = $old_instance;		
			$instance['title'] = strip_tags($new_instance['title']);
			return $instance;
		}
		
		function form($instance) 
		{
		//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );		
			$title = strip_tags($instance['title']);
		?>
		<p>No settings for this widget</p>
		
		
		<?php
		}
 }
	register_widget('geodir_social_like_widget');

	// ===============================GeoDirectory Feedburner Subscribe widget ============
	class geodirsubscribeWidget extends WP_Widget {
		
		
		function geodirsubscribeWidget()
		{
			//Constructor
			$widget_ops = array('classname' => 'GD Subscribe', 'description' => __('GD > Google Feedburner Subscribe',GEODIRECTORY_TEXTDOMAIN) );		
			$this->WP_Widget('widget_subscribeWidget', __('GD > Subscribe',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		
		function widget($args, $instance) 
		{
			// prints the widget
			extract($args, EXTR_SKIP);
			
			$id = empty($instance['id']) ? '' : apply_filters('widget_id', $instance['id']);
			
			$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
			
			$text = empty($instance['text']) ? '' : apply_filters('widget_text', $instance['text']);
			
			/*$twitter = empty($instance['twitter']) ? '' : apply_filters('widget_twitter', $instance['twitter']);
			
			$facebook = empty($instance['facebook']) ? '' : apply_filters('widget_facebook', $instance['facebook']);
			
			$digg = empty($instance['digg']) ? '' : apply_filters('widget_digg', $instance['digg']);
			
			$myspace = empty($instance['myspace']) ? '' : apply_filters('widget_myspace', $instance['myspace']);
			
			$rss = empty($instance['rss']) ? '' : apply_filters('widget_rss', $instance['rss']);*/
			?>
			
			<div class="geodir-widget subscribe clearfix" >
			
			<h3><?php echo $title; ?>  <a href="<?php if($id){echo 'http://feeds2.feedburner.com/'.$id;}else{bloginfo('rss_url');} ?>" ><img  src="<?php echo geodir_plugin_url(); ?>/geodirectory-assets/images/i_rss.png" alt="" class="i_rss"  /> </a> </h3>
			
			<?php if ( $text <> "" ) { ?>	 
			
				 <p><?php echo $text; ?> </p>
			
			<?php } ?>
			
			<form class="subscribe_form"  action="http://feedburner.google.com/fb/a/mailverify" method="post" target="popupwindow"  onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $id; ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true"> 
			   
				<input type="text" class="field" onfocus="if (this.value == '<?php _e('Your Email Address',GEODIRECTORY_TEXTDOMAIN)?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php _e('Your Email Address',GEODIRECTORY_TEXTDOMAIN)?>';}" name="email" value="<?php _e('Your Email Address',GEODIRECTORY_TEXTDOMAIN)?>" />
				
				<input type="hidden" value="<?php echo $id; ?>" name="uri"/><input type="hidden" name="loc" value="en_US"/>
				
				<input class="btn_submit" type="submit" name="submit" value="Submit" /> 
				
			</form>
			</div>  <!-- #end -->
			
			<?php
			
		}
		
		function update($new_instance, $old_instance)
		{
		
			//save the widget
			$instance = $old_instance;		
			$instance['id'] = strip_tags($new_instance['id']);
			$instance['title'] = ($new_instance['title']);
			$instance['text'] = ($new_instance['text']);
			/*$instance['twitter'] = ($new_instance['twitter']);
			$instance['facebook'] = ($new_instance['facebook']);
			$instance['digg'] = ($new_instance['digg']);
			$instance['myspace'] = ($new_instance['myspace']);*/
			
			return $instance;
		}
		
		function form($instance)
		{
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '', 'advt1' => '','text' => '','twitter' => '','facebook' => '','digg' => '','myspace' => '' ) );		
			
			$id = strip_tags($instance['id']);
			
			$title = strip_tags($instance['title']);
			
			$text = strip_tags($instance['text']);
			
			/*$twitter = strip_tags($instance['twitter']);
			
			$facebook = strip_tags($instance['facebook']);
			
			$digg = strip_tags($instance['digg']);
			
			$myspace = strip_tags($instance['myspace']);*/
		
		?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title',GEODIRECTORY_TEXTDOMAIN);?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
			
			<p><label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Feedburner ID (ex :- geotheme)',GEODIRECTORY_TEXTDOMAIN);?>: <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo esc_attr($id); ?>" /></label></p>
			
			<p><label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Short Description',GEODIRECTORY_TEXTDOMAIN);?> <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_attr($text); ?></textarea></label></p>
		<?php
		}
 }
	register_widget('geodirsubscribeWidget');

	// =============================== GeoDirectory Advt Widgets  =========================
	class geodiradvtwidget extends WP_Widget {
		
		function geodiradvtwidget()
		{
		//Constructor
			$widget_ops = array('classname' => 'GeoDirectory Advertise', 'description' => __('GD > common advertise widget in sidebar, bottom section',GEODIRECTORY_TEXTDOMAIN) );
			$this->WP_Widget('advtwidget', __('GD > Advertise',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		
		
		function widget($args, $instance)
		{
		
			// prints the widget
			
			extract($args, EXTR_SKIP);
			
			$desc1 = empty($instance['desc1']) ? '&nbsp;' : apply_filters('widget_desc1', $instance['desc1']);
		?>						
			<div class="geodir-widget advt_single">    
				<?php if ( $desc1 <> "" ) { ?>	
					<?php echo $desc1; ?> 
				<?php } ?>
			</div>
		
		<?php
		}
		
		function update($new_instance, $old_instance)
		{	
			//save the widget
			$instance = $old_instance;
			$instance['desc1'] = ($new_instance['desc1']);
			return $instance;
		}
		
		function form($instance)
		{
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 't1' => '', 't2' => '', 't3' => '',  'img1' => '', 'desc1' => '' ) );	
			
			$desc1 = ($instance['desc1']);
			?>
			<p><label for="<?php echo $this->get_field_id('desc1'); ?>"><?php _e('Your Advt code (ex.google adsense, etc.)',GEODIRECTORY_TEXTDOMAIN);?> <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('desc1'); ?>" name="<?php echo $this->get_field_name('desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label></p>
		
		<?php
		}
 }
	register_widget('geodiradvtwidget');

	// =============================== GeoDirectory Flickr widget ========================
	class GeodirFlickrWidget extends WP_Widget {
		
		
		function GeodirFlickrWidget() {
			//Constructor
			$widget_ops = array('classname' => 'Geo Dir Flickr Photos ', 'description' => __('GD > Flickr Photos',GEODIRECTORY_TEXTDOMAIN) );
			$this->WP_Widget('widget_flickrwidget', __('GD > Flickr Photos',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		
		function widget($args, $instance)
		{
		
			// prints the widget
			extract($args, EXTR_SKIP);
			
			echo $before_widget;
			
			$id = empty($instance['id']) ? '&nbsp;' : apply_filters('widget_id', $instance['id']);
			
			$number = empty($instance['number']) ? '&nbsp;' : apply_filters('widget_number', $instance['number']);
		
		?> 
        	<div class="geodir-widget">
			<h3 ><span><?php _e('Photo Gallery',GEODIRECTORY_TEXTDOMAIN);?></span> </h3>
		
			<div class="flickr clearfix">
			
				<script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=<?php echo $number; ?>&amp;display=latest&amp;size=s&amp;layout=x&amp;source=user&amp;user=<?php echo $id; ?>"></script>
			
			</div>
            </div>
		
		
		<?php
		}
		
		function update($new_instance, $old_instance) 
		{
			//save the widget
			$instance = $old_instance;
			$instance['id'] = strip_tags($new_instance['id']);
			$instance['number'] = strip_tags($new_instance['number']);
			return $instance;
		}
		
		function form($instance)
		{
		
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array('title' => '',  'id' => '', 'number' => '') );
			$id = strip_tags($instance['id']);
			$number = strip_tags($instance['number']);
		?>
		
			<p>
				<label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Flickr ID',GEODIRECTORY_TEXTDOMAIN);?> (<a href="http://www.idgettr.com">idGettr</a>):
					<input class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo esc_attr($id); ?>" />
				</label>
			</p>
		
			<p>
				<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of photos:',GEODIRECTORY_TEXTDOMAIN);?>
					<input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo esc_attr($number); ?>" />
				</label>
			</p>
		<?php
		}
}
	register_widget('GeodirFlickrWidget');

	// ===============================GeoDirectory Twitter widget ========================
	
	// =============================== GeoDirectory Advt Widgets  =========================
	class geodir_twitter extends WP_Widget {
		
		function geodir_twitter()
		{
			//Constructor
			$widget_ops = array('classname' => 'Twitter', 'description' => __('GD > Twitter Feed',GEODIRECTORY_TEXTDOMAIN) );
			$this->WP_Widget('widget_Twidget', __('GD > Twitter',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
		}
		
		
		function widget($args, $instance)
		{
		
			// prints the widget
			
			extract($args, EXTR_SKIP);
			
			$desc1 = empty($instance['gd_tw_desc1']) ? '&nbsp;' : apply_filters('gd_tw_widget_desc1', $instance['gd_tw_desc1']);
		?>						
			<div class="geodir-widget gd_twitter">    
				<?php if ( $desc1 <> "" ) { ?>	
					<?php echo $desc1; ?> 
				<?php } ?>
			</div>
		
		<?php
		}
		
		function update($new_instance, $old_instance)
		{	
			//save the widget
			$instance = $old_instance;
			$instance['gd_tw_desc1'] = ($new_instance['gd_tw_desc1']);
			return $instance;
		}
		
		function form($instance)
		{
			//widgetform in backend
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 't1' => '', 't2' => '', 't3' => '',  'img1' => '', 'gd_tw_desc1' => '' ) );	
			
			$desc1 = ($instance['gd_tw_desc1']);
			?>
			<p><label for="<?php echo $this->get_field_id('gd_tw_desc1'); ?>"><?php _e('Your twitter code (ex.google adsense, etc.)',GEODIRECTORY_TEXTDOMAIN);?> <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('gd_tw_desc1'); ?>" name="<?php echo $this->get_field_name('gd_tw_desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label></p>
		
		<?php
		}
 }

	register_widget('geodir_twitter');


	
 

class geodir_advance_search_widget extends WP_Widget {

	function geodir_advance_search_widget()
	{
		//Constructor
		$widget_ops = array('classname' => 'geodir_advance_search_widget', 'description' => __('GD > Search',GEODIRECTORY_TEXTDOMAIN) );
		$this->WP_Widget('geodir_advance_search', __('GD > Search',GEODIRECTORY_TEXTDOMAIN), $widget_ops);
	}
	
	
	function widget($args, $instance) 
	{
		
		// prints the widget
		extract($args, EXTR_SKIP);
		
		echo $before_widget;
		
		$title = empty($instance['title']) ? __('Search',GEODIRECTORY_TEXTDOMAIN) : apply_filters('widget_title', $instance['title']);
		
		geodir_get_template_part('listing','filter-form'); 
		
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		//save the widget
		//Nothing to save
		return isset($instance) ? $instance : '';
	}
	
	function form($instance) 
	{
		//widgetform in backend
		echo __("This is a search widget to show advance search for gedodirectory listings.",GEODIRECTORY_TEXTDOMAIN);
	} 
}
register_widget('geodir_advance_search_widget');	
	
	
	include_once ('geodirectory-widgets/geodirectory_popular_widget.php');
	include_once ('geodirectory-widgets/geodirectory_listing_slider_widget.php');
	include_once ('geodirectory-widgets/home_map_widget.php');
	include_once ( 'geodirectory-widgets/listing_map_widget.php');
	
	include_once ('geodirectory-widgets/geodirectory_related_listing_widget.php');
}

}


