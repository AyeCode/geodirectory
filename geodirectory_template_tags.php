<?php 
include_once('geodirectory-functions/map-functions/map_template_tags.php');
function geodir_templates_scripts()
{

		wp_enqueue_script( 'jquery' ); 
		
		
		wp_enqueue_script( 'geodirectory-jquery-ui-timepicker-js',geodir_plugin_url().'/geodirectory-assets/ui/jquery.ui.timepicker.js',array('jquery-ui-datepicker','jquery-ui-slider','jquery-effects-core','jquery-effects-slide' ),'',true  );
		
		//if(get_option('geodir_enqueue_flexslider_script')==1)
		{
			wp_register_script( 'geodirectory-jquery-flexslider-js', geodir_plugin_url().'/geodirectory-assets/js/jquery.flexslider.js' );
			wp_enqueue_script( 'geodirectory-jquery-flexslider-js' );
		}
		
		wp_register_script( 'geodirectory-lightbox-jquery', geodir_plugin_url().'/geodirectory-assets/js/jquery.lightbox-0.5.js' );
		wp_enqueue_script( 'geodirectory-lightbox-jquery' );
		
		wp_register_script( 'geodirectory-jquery-simplemodal', geodir_plugin_url().'/geodirectory-assets/js/jquery.simplemodal.js' );
		wp_enqueue_script( 'geodirectory-jquery-simplemodal' );
	
	
		//if( get_option('geodir_enqueue_google_api_script')==1)
		
		$map_lang="&language=" . geodir_get_map_default_language() ; 
		wp_enqueue_script('geodirectory-googlemap-script', '//maps.google.com/maps/api/js?sensor=false'.$map_lang,'',NULL);
	/*	{	
			wp_register_script( 'geodirectory-googlemap-script', "//maps.google.com/maps/api/js?sensor=false&language=en" );
			wp_enqueue_script( 'geodirectory-googlemap-script' );
       	}
		*/
		wp_register_script( 'geodirectory-goMap-script', geodir_plugin_url().'/geodirectory-assets/js/goMap.js' );
		wp_enqueue_script( 'geodirectory-goMap-script' );
		
		
		wp_register_script( 'geodirectory-chosen-jquery', geodir_plugin_url().'/geodirectory-assets/js/chosen.jquery.js' );
		wp_enqueue_script( 'geodirectory-chosen-jquery' );
		
		wp_register_script( 'geodirectory-choose-ajax', geodir_plugin_url().'/geodirectory-assets/js/ajax-chosen.js' );
		wp_enqueue_script( 'geodirectory-choose-ajax' );

		if(is_page() && get_query_var('page_id') == get_option( 'geodir_add_listing_page' ) ){
		
		// SCRIPT FOR UPLOAD
		wp_enqueue_script('plupload-all');
		wp_enqueue_script( 'jquery-ui-sortable' );
		
		wp_register_script( 'geodirectory-plupload-script', geodir_plugin_url().'/geodirectory-assets/js/geodirectory-plupload.js' );
		wp_enqueue_script( 'geodirectory-plupload-script' );
		
		// SCRIPT FOR UPLOAD END
		
		
		
		// place js config array for plupload
		$plupload_init = array(
			 'runtimes' => 'html5,silverlight,flash,browserplus,gears,html4',
			'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
			'container' => 'plupload-upload-ui', // will be adjusted per uploader
			'drop_element' => 'dropbox', // will be adjusted per uploader
			'file_data_name' => 'async-upload', // will be adjusted per uploader
			'multiple_queues' => true,
			'max_file_size' => geodir_max_upload_size(),
			'url' => admin_url('admin-ajax.php'),
			'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
			'filters' => array(array('title' => __('Allowed Files',GEODIRECTORY_TEXTDOMAIN), 'extensions' => '*')),
			'multipart' => true,
			'urlstream_upload' => true,
			'multi_selection' => false, // will be added per uploader
			 // additional post data to send to our ajax hook
			'multipart_params' => array(
				'_ajax_nonce' => "", // will be added per uploader
				'action' => 'plupload_action', // the ajax action name
				'imgid' => 0 // will be added per uploader
			)
		);
		$base_plupload_config = json_encode($plupload_init);
					
		$gd_plupload_init = array( 	'base_plupload_config' => $base_plupload_config,
									'upload_img_size' => geodir_max_upload_size() );
		
		wp_localize_script( 'geodirectory-plupload-script', 'gd_plupload', $gd_plupload_init );
		
		
		wp_enqueue_script( 'geodirectory-listing-validation-script', geodir_plugin_url().'/geodirectory-assets/js/listing_validation.js' );
		
		
		} // End if for add place page
		
	
		wp_register_script( 'geodirectory-post-custom-js', geodir_plugin_url().'/geodirectory-assets/js/post.custom.js' );
		wp_enqueue_script( 'geodirectory-post-custom-js' );		
		       
		wp_register_script( 'geodirectory-script', geodir_plugin_url().'/geodirectory-assets/js/geodirectory.js' );
		wp_enqueue_script( 'geodirectory-script' );
		
		$ajax_cons_data = array( 'url' => __( get_option('siteurl').'?geodir_ajax=true' ) ); 
		
		
		wp_localize_script( 'geodirectory-script', 'geodir_ajax', $ajax_cons_data );
		
		$geodir_cons_data = array( 	'siteurl' => get_option('siteurl'),
									'geodir_plugin_url'=>geodir_plugin_url(), 
									'geodir_ajax_url'=>geodir_get_ajax_url()); 
		wp_localize_script( 'geodirectory-script', 'geodir_var', $geodir_cons_data );
		
		wp_register_script( 'geodir-jRating-js', geodir_plugin_url() .'/geodirectory-assets/js/jRating.jquery.js' );
		wp_enqueue_script( 'geodir-jRating-js' );
		
		wp_register_script( 'geodir-on-document-load', geodir_plugin_url() .'/geodirectory-assets/js/on_document_load.js' );
		wp_enqueue_script( 'geodir-on-document-load' );
		
} 

function geodir_header_scripts()
{ 
	echo '<style>'.stripslashes(get_option('geodir_coustem_css')) . '</style>';
		
	echo stripslashes(get_option('geodir_header_scripts'));
		
}

function geodir_footer_scripts()
{ 
	echo stripslashes(get_option('geodir_ga_tracking_code'));
	echo stripslashes(get_option('geodir_footer_scripts'));
} 

function geodir_templates_styles()
{
	wp_register_style( 'geodirectory-frontend-style', geodir_plugin_url().'/geodirectory-assets/css/style.css' );
	wp_enqueue_style( 'geodirectory-frontend-style' );
	
	wp_register_style( 'geodirectory-media-style', geodir_plugin_url().'/geodirectory-assets/css/media.css');
	wp_enqueue_style( 'geodirectory-media-style' );
	
	
	wp_register_style( 'geodirectory-jquery-ui-css', geodir_plugin_url().'/geodirectory-assets/ui/jquery-ui.css' );
	wp_enqueue_style( 'geodirectory-jquery-ui-css' );
	
	wp_register_style( 'geodirectory-jquery-ui-timepicker-css', geodir_plugin_url().'/geodirectory-assets/ui/jquery.ui.timepicker.css' );
	wp_enqueue_style( 'geodirectory-jquery-ui-timepicker-css' );
	
	wp_register_style( 'geodirectory-flexslider-css', geodir_plugin_url().'/geodirectory-assets/css/flexslider.css' );
	wp_enqueue_style( 'geodirectory-flexslider-css' );
	
	wp_register_style( 'geodirectory-thic-box-css', geodir_plugin_url().'/geodirectory-assets/css/thic-box.css' );
	wp_enqueue_style( 'geodirectory-thic-box-css' );
	
	wp_register_style( 'geodirectory-pluplodar-css', geodir_plugin_url().'/geodirectory-assets/css/pluploader.css' );
	wp_enqueue_style( 'geodirectory-pluplodar-css' );
	
	wp_register_style( 'geodirectory-lightbox-css', geodir_plugin_url().'/geodirectory-assets/css/jquery.lightbox-0.5.css' );
	wp_enqueue_style( 'geodirectory-lightbox-css' );
	
	
	wp_register_style( 'geodir-rating-style', geodir_plugin_url() .'/geodirectory-assets/css/jRating.jquery.css' );
	wp_enqueue_style( 'geodir-rating-style' );
	
	wp_register_style( 'geodir-jslider-style', geodir_plugin_url() .'/geodirectory-assets/css/jslider.css' );
	wp_enqueue_style( 'geodir-jslider-style' );
	
	wp_register_style( 'geodir-chosen-style', geodir_plugin_url() .'/geodirectory-assets/css/chosen.css' );
	wp_enqueue_style( 'geodir-chosen-style' );
} 

function geodir_get_sidebar() 
{
	get_sidebar('geodirectory');
}

function geodir_pagination($before = '', $after = '', $prelabel = '', $nxtlabel = '', $pages_to_show = 5, $always_show = false) 
{

	global $wp_query, $posts_per_page, $wpdb, $paged,$blog_id;
	
	if(empty($prelabel)) {
		$prelabel  = '<strong>&laquo;</strong>';
	}
	
	if(empty($nxtlabel)) {
		$nxtlabel = '<strong>&raquo;</strong>';
	}

	$half_pages_to_show = round($pages_to_show/2);


	if (!is_single()) {
		
		$numposts = $wp_query->found_posts;
		
		
		$max_page = ceil($numposts /$posts_per_page);
		
		if(empty($paged)) {
			$paged = 1;
		}
		
		if($max_page > 1 || $always_show) {
			echo "$before <div class='Navi'>";
			if ($paged >= ($pages_to_show-1)) {
				echo '<a href="'.str_replace('&paged','&amp;paged',get_pagenum_link()).'">&laquo;</a>';
			}
			previous_posts_link($prelabel);
			for($i = $paged - $half_pages_to_show; $i  <= $paged + $half_pages_to_show; $i++) {
				if ($i >= 1 && $i <= $max_page) {
					if($i == $paged) {
						echo "<strong class='on'>$i</strong>";
					} else {
						echo ' <a href="'.str_replace('&paged','&amp;paged',get_pagenum_link($i)).'">'.$i.'</a> ';
					}
				}
			}
			next_posts_link($nxtlabel, $max_page);
			if (($paged+$half_pages_to_show) < ($max_page)) {
				echo '<a href="'.str_replace('&paged','&amp;paged',get_pagenum_link($max_page)).'">&raquo;</a>';
			}
			echo "</div> $after";
		}
	}
}

function geodir_listingsearch_scripts()
{ 
	if(get_option('gd_search_dist')!=''){$dist = get_option('gd_search_dist');}else{$dist = 500;}
	$dist_dif = 1000;
	
	if($dist <= 5000) $dist_dif = 500;
	if($dist <= 1000) $dist_dif = 100;
	if($dist <= 500) $dist_dif = 50;
	if($dist <= 100) $dist_dif = 10;
	if($dist <= 50) $dist_dif = 5;
	
	?>
	<script type="text/javascript">
	
		jQuery(function($) {
			$( "#distance_slider" ).slider({
				range: true,
				values: [ 0,<?php echo ($_REQUEST['sdist']!='') ? $_REQUEST['sdist'] : "0"; ?> ],
				min: 0,
				max: <?php echo $dist; ?>,
				step: <?php echo $dist_dif; ?>,
				slide: function( event, ui ) {
					$( "#sdist" ).val( ui.values[1] );
					$( "#sdist_span" ).html( ui.values[1] );
				}
			});
			
			$( "#sdist" ).val( $( "#distance_slider" ).slider( "values", 1) );
			$( "#sdist_span" ).html( $( "#distance_slider" ).slider( "values", 1 ) );
			
		});
	
		jQuery(".showFilters").click(function () {
			jQuery(".gdFilterOptions").slideToggle("slow");
		});
	
		jQuery("#cat_all").click(function () {
			jQuery('.cat_check').attr('checked', this.checked);
		});
	
		jQuery(".cat_check").click(function(){
			if(jQuery(".cat_check").length == jQuery(".cat_check:checked").length) {
				jQuery("#cat_all").attr("checked", "checked");
			} else {
				jQuery("#cat_all").removeAttr("checked");
			}
		});
	
	
		jQuery(window).load(function () {
			if(jQuery(".cat_check").length == jQuery(".cat_check:checked").length) {
				jQuery("#cat_all").attr("checked", "checked");
			} else {
				jQuery("#cat_all").removeAttr("checked");
			}
		});	
		
	</script>
        <?php 
}

function geodir_add_sharelocation_scripts()
{  ?>
		
    <script type="text/javascript" src="http://gmaps-samples-v3.googlecode.com/svn/trunk/geolocate/geometa.js"></script> 
    
    <script type="text/javascript">
    var default_location = '<?php if($search_location = geodir_get_default_location())  echo $search_location->city ;?>';
    var latlng;
    var Sgeocoder;
    var address;
    var dist = 0;
    var Sgeocoder = new google.maps.Geocoder();
    jQuery(document).ready(function(){
        
        /*jQuery('#sort_by').change(function(){
						
            jQuery('.geodir_submit_search:first').click();
				
        });*/
        
        
        jQuery('.geodir_submit_search').click(function(){
            var s = ' ';
			
			var $form = jQuery(this).closest('form');

           
            if(jQuery("#sdist input[type='radio']:checked").length != 0)
                dist = jQuery("#sdist input[type='radio']:checked").val();
            
            if(jQuery('.search_text',$form).val() == '' || jQuery('.search_text',$form ).val() == '<?php echo SEARCH_FOR_TEXT;?>')
                jQuery('.search_text',$form).val(s);            
				if(dist > 0 || (jQuery('select[name="sort_by"]').val() == 'nearest' || jQuery('select[name="sort_by"]',$form).val() == 'farthest')  || ( jQuery(".snear",$form ).val() != '' && jQuery(".snear",$form ).val() != '<?php echo NEAR_TEXT;?>') )
            { geodir_setsearch($form); }
            else
            { 
                jQuery(".snear",$form ).val('');
                jQuery($form).submit(); 
            }
            
        });
        
        function geodir_setsearch($form)
        {            if( ( dist > 0 || (jQuery('select[name="sort_by"]',$form).val() == 'nearest' || jQuery('select[name="sort_by"]',$form).val() == 'farthest')) && (jQuery(".snear",$form).val() == '' || jQuery(".snear",$form).val() == '<?php echo NEAR_TEXT;?>' ) )
                jQuery(".snear",$form).val(default_location);
                
            geocodeAddress($form);
        }
        
        function updateSearchPosition(latLng,$form) {
            jQuery('.sgeo_lat').val(latLng.lat());
            jQuery('.sgeo_lon').val(latLng.lng());
            jQuery($form).submit(); // submit form after insering the lat long positions
        }
        
        function geocodeAddress($form) {
            Sgeocoder = new google.maps.Geocoder(); // Call the geocode function
            
            if(jQuery('.snear',$form).val() == ''){
                jQuery($form).submit();
            }else{
            
                var address = jQuery(".snear",$form).val();
                
                if(jQuery('.snear',$form).val() == '<?php echo NEAR_TEXT;?>'){
                    initialise2();
                }else{
                
                    Sgeocoder.geocode( { 'address': address<?php //gt_advanced_near_search();?> }, 
                    function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            updateSearchPosition(results[0].geometry.location,$form);
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
            jQuery('.sgeo_lat').val(coords.latitude);
            jQuery('.sgeo_lon').val(coords.longitude);
              
            jQuery($form).submit(); 
        }
     
    
    });
    </script> 
<?php
}	



function geodir_location_header_scripts()
{ 
	
	?>
	
	<script  type="text/javascript">

	jQuery(document).ready(function () {
		jQuery(".chosen_select").each(function(){
			
			var curr_chosen = jQuery(this);
			var autoredirect = curr_chosen.attr('option-autoredirect');
			var countrySearch= curr_chosen.attr('option-countrySearch');
			if(curr_chosen.attr('option-ajaxChosen') === true || curr_chosen.attr('option-ajaxChosen') === undefined){
				if(curr_chosen.closest('.geodir_location_tab_contenor').find('.selected_location').length > 0)
					var listfor = curr_chosen.closest('.geodir_location_tab_contenor').find('.selected_location').val();
				else
					var listfor = curr_chosen.attr('option-listfore');
				
				var show_everywhere = curr_chosen.attr('option-showEveryWhere');
				
				curr_chosen.ajaxChosen({
					addSearchTermOnNorecord: curr_chosen.attr('option-addSearchTermOnNorecord'),
					noLocationUrl: curr_chosen.attr('option-noLocationUrl'),
					jsonTermKeyObject: listfor,
					keepTypingMsg: "Please wait...",
					lookingForMsg: "We are searching for",
					type: 'GET',
					url: '<?php echo geodir_get_ajax_url(); ?>&autofill=ajax_chosen_search_locations&autoredirect='+autoredirect+'&location_type='+listfor+'&show_everywhere='+show_everywhere+'&countrySearch='+countrySearch,
					dataType: 'json'
				}, 
				function (data) {
					var terms = {};
					jQuery.each(data.states, function (i, val) {
						terms[i] = val;
					});
					return terms;
				},
				{   // option for chosen jquery plugin..
					no_results_text: "Translated No results matched",
					placeholder_text: "Translate place holder text"
				});
			}	
			
		});
	});
	
	
	jQuery(document).ready(function(){
	
		jQuery('.chosen_select').bind('change',function(){
			
			var obj_id = jQuery(this).prop('id');
			var obbj_info = obj_id.split('_');
			var prefix = obbj_info[0];
			var update_for = obbj_info[1];
			var update_for_val = jQuery(this).val();
			
			var $loader = '<div id="location_dl_loader" align="center" style="width:100%;"><img src="'+geodir_all_js_msg.geodir_plugin_url+'/geodirectory-assets/images/loadingAnimation.gif"  /></div>';
			
			
			if(update_for == 'country'){
				
				if(jQuery(this).attr('name') != 'post_country')
					return false;
					
				jQuery('#'+prefix+'_region_container').hide();  
				jQuery('#'+prefix+'_region_container').after($loader);
				
				jQuery.get(geodir_all_js_msg.geodir_ajax_url,
					{	autofill:'fill_post_locations',
						fill:'region',
						autoredirect:0,
						update_for:update_for,
						update_for_val:update_for_val
					},
					 function(data){
					 
						if(data){
							jQuery('#'+prefix+'_region_container').next('#location_dl_loader').remove();
							jQuery('#'+prefix+'_region_container').show();
							jQuery('#'+prefix+'_region').html(data).chosen().trigger("chosen:updated");
							jQuery('#'+prefix+'_region').trigger("change");	
						}
				});
			}
			
			if(update_for == 'region'){
				jQuery('#'+prefix+'_city_container').hide();
				jQuery('#'+prefix+'_city_container').after($loader);
				
				
				jQuery.get(geodir_all_js_msg.geodir_ajax_url,
					{	autofill:'fill_post_locations',
						fill:'city',
						autoredirect:0,
						update_for:update_for,
						update_for_val:update_for_val
					},
					 function(data){
					 
						if(data){
							jQuery('#'+prefix+'_city_container').next('#location_dl_loader').remove();
							jQuery('#'+prefix+'_city_container').show();
							jQuery('#'+prefix+'_city').html(data).chosen().trigger("chosen:updated");	
							jQuery('#'+prefix+'_city').trigger("change");	
						}
				});
			}	
			
		})	
								
	});
	
	</script>

	<?php 

		
}

?>