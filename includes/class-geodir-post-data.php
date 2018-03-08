<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Post Data.
 *
 * Standardises certain post data on save.
 *
 * @class        GeoDir_Post_Data
 * @version        2.0.0
 * @package        GeoDIrectory/Classes/Data
 * @category    Class
 * @author        AyeCode
 */
class GeoDir_Post_Data {

	/*
	 * Temporarily save the GD post data.
	 *
	 * @var array
	 */
	private static $post_temp = null;

	/**
	 * Editing term.
	 *
	 * @var object
	 */
	private static $editing_term = null;

	/**
	 * Hook in methods.
	 */
	public static function init() {

		add_filter( 'wp_insert_post_data', array( __CLASS__, 'wp_insert_post_data' ), 10, 2 );

		// Status transitions
		add_action( 'before_delete_post', array( __CLASS__, 'delete_post' ) );

		// Add hook to post insert
		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

		// set up $gd_post;
		add_action( 'wp', array( __CLASS__, 'init_gd_post' ), 5 );

		if(!is_admin()){
			add_filter( 'pre_get_posts', array( __CLASS__, 'show_public_preview' ) );
			add_filter( 'posts_results', array( __CLASS__, 'set_closed_status' ), 999, 2 );
			add_filter( 'the_posts', array( __CLASS__, 'reset_closed_status' ), 999, 2 );
		}

		/*
		 * We most likely don't need this yet, it will also fire twice on normal post saves so we will only add it if needed.
		 */
		//add_action( 'set_object_terms', array( __CLASS__, 'set_object_terms' ),10,6);


		// add mandatory not to add listing page
		add_action( 'geodir_add_listing_form_start',  array( __CLASS__, 'add_listing_mandatory_note'), -10, 3 );



	}

	/**
	 * Init the global $gd_post variable.
	 */
	public static function init_gd_post(){
		global $post,$gd_post;
		
		if(isset($post->post_type) && in_array( $post->post_type, geodir_get_posttypes() ) ){
			$gd_post = geodir_get_post_info($post->ID);
		}
			
	}


	public static function save_auto_draft( $post_info ) {

		// check if we already have an auto draft
		if ( isset( $post_info['ID'] ) && $post_info['ID'] ) {

		}
		$result = wp_insert_post( $post_info, true ); // we hook into the save_post hook
	}


	/**
	 * Save post metadata when a post is saved.
	 *
	 * @param int $post_id The post ID.
	 * @param WP_Post $post The post object.
	 * @param bool $update Whether this is an existing post being updated or not.
	 */
	public static function save_post( $post_id, $post, $update ) {
		global $wpdb, $plugin_prefix;

	//echo '###';print_r($_REQUEST);print_r(self::$post_temp);print_r($post);exit;

		// only fire if $post_temp is set
		if ( $gd_post = self::$post_temp ) {
			// POST REVISION :  grab the original info
			if ( isset( $gd_post['ID'] ) && $gd_post['ID'] === 0 && $gd_post['post_type'] == 'revision' ) {
				$gd_post = (array) geodir_get_post_info( $gd_post['post_parent'] );
			} elseif ( $gd_post['post_type'] == 'revision' ) {
				$gd_post['post_type'] = get_post_type( $gd_post['post_parent'] );
			}
			$post_type = esc_attr($gd_post['post_type']); // set the post type early

			// unhook this function so it doesn't loop infinitely
			remove_action( 'save_post', array( __CLASS__, 'save_post' ), 10 );

			$postarr = array();
			$table   = $plugin_prefix . sanitize_key( $post_type ) . "_detail";


			// Set the custom fields info
			$custom_fields = GeoDir_Settings_Cpt_Cf::get_cpt_custom_fields( $post_type );
			foreach ( $custom_fields as $cf ) {

				if ( isset( $gd_post[ $cf->htmlvar_name ] ) ) {
					$gd_post_value = $gd_post[ $cf->htmlvar_name ];
					if ( is_array( $gd_post_value ) ) {
						$gd_post_value = ! empty( $gd_post_value ) ? implode( ',', $gd_post_value ) : '';
					}
					$postarr[ $cf->htmlvar_name ] = apply_filters("geodir_custom_field_value_{$cf->field_type}", $gd_post_value, $cf, $post_id, $post, $update);
				}

			}

			// Set the defaults.
			$postarr['post_id']     = $post_id;
			$postarr['post_status'] = $post->post_status;
			if ( isset( $gd_post['link_business'] ) ) {
				$postarr['link_business'] = $gd_post['link_business'];
			}
			if ( isset( $gd_post['is_featured'] ) ) {
				$postarr['is_featured'] = $gd_post['is_featured'];
			}
			if ( ! $update ) {
				$postarr['submit_ip'] = $_SERVER['REMOTE_ADDR'];
			}

			// unset the post content as we don't save it here
			unset( $postarr['post_content'] );



			//check for dummy data categories
			if ( isset( $gd_post['post_dummy'] ) && $gd_post['post_dummy'] && isset($gd_post['post_category'])) {
				$categories = array_map( 'sanitize_text_field', $gd_post['post_category'] );
				$cat_ids = array();
				foreach($categories as $cat_name){
					$temp_term = get_term_by( 'name', $cat_name, $post_type.'category' );
					if(isset($temp_term->term_id)) {
						$cat_ids[] = $temp_term->term_id;
					}
				}
				if(!empty($cat_ids)){ $categories = $cat_ids;}
				$post_categories = array_map( 'trim', $categories );
				wp_set_post_terms( $post_id, $categories, $post_type.'category' );
			}

			// Set categories
			if( isset($gd_post['tax_input'][$post_type.'category']) && !empty($gd_post['tax_input'][$post_type.'category'])){
				$post_categories = $gd_post['tax_input'][$post_type.'category'];
			}
			if ( empty( $post_categories ) && isset( $gd_post['post_category'] ) ) {
                $post_categories = $gd_post['post_category'];
            }
			
			// default category
			if ( isset( $gd_post['default_category'] ) ) {
				$postarr['default_category'] = absint( $gd_post['default_category'] );
			}

			if ( isset($post_categories) ) {
				$categories = array_map( 'absint', $post_categories );
				$categories = array_filter(array_unique($categories));// remove duplicates and empty values
				$postarr['post_category'] = "," . implode( ",", $categories ) . ",";

				if ( empty( $postarr['default_category'] ) && ! empty( $categories[0] ) ) {
					$postarr['default_category'] = $categories[0]; // set first category as a default if default category not found
				}

				// if logged out user we need to manually add cats
				if(!get_current_user_id()){
					wp_set_post_terms( $post_id, $categories, $post_type.'category' );
				}
			}

			// Set tags

			// check fro dummy data tags
			if( empty($gd_post['post_tags']) && isset($gd_post['tax_input'][$post_type.'_tags']) && !empty($gd_post['tax_input'][$post_type.'_tags'])){

				// quick edit returns tag ids, we need the strings
				if( isset( $_REQUEST['action'] ) && $_REQUEST['action']=='inline-save' ){
					$post_tags = isset($_REQUEST['tax_input'][$post_type.'_tags']) ? $_REQUEST['tax_input'][$post_type.'_tags'] : '';
					if($post_tags){$post_tags = explode(",",$post_tags );}
				}else{
					$post_tags = $gd_post['tax_input'][$post_type.'_tags'];
				}

			}elseif( isset( $gd_post['post_tags'] ) && is_array( $gd_post['post_tags'] ) ){
				$post_tags = $gd_post['post_tags'];
			}else{
				$post_tags = '';
			}

			if ( $post_tags ) {

				if ( !get_current_user_id() || (isset( $gd_post['post_dummy'] ) && $gd_post['post_dummy']) ) {
					$tags = array_map( 'sanitize_text_field', $post_tags );
					$tags = array_map( 'trim', $tags );
					wp_set_post_terms( $post_id, $tags,$post_type.'_tags');
				} else {
					$tag_terms = wp_get_object_terms( $post_id, $post_type . '_tags', array( 'fields' => 'names' ) ); // Save tag names in detail table.
					if ( ! empty( $tag_terms ) && ! is_wp_error( $tag_terms ) ) {
						$post_tags = $tag_terms;
					} else {
						$post_tags = array();
					}

					$tags = array_map( 'trim', $post_tags );
				}
				$tags = array_filter(array_unique($tags));
				// we need tags as a string
				$postarr['post_tags'] = implode( ",", $tags );
			}

			// Save location info
			if ( isset( $gd_post['street'] ) ) {
				$postarr['street'] = $gd_post['street'];
			}
			if ( isset( $gd_post['city'] ) ) {
				$postarr['city'] = $gd_post['city'];
			} else {
				$default_location = geodir_get_default_location();
				$postarr['city'] = $default_location->city;
				$postarr['region'] = $default_location->region;
				$postarr['country'] = $default_location->country;
			}
			if ( isset( $gd_post['region'] ) ) {
				$postarr['region'] = $gd_post['region'];
			}
			if ( isset( $gd_post['country'] ) ) {
				$postarr['country'] = $gd_post['country'];
			}
			if ( isset( $gd_post['zip'] ) ) {
				$postarr['zip'] = $gd_post['zip'];
			}
			if ( isset( $gd_post['latitude'] ) ) {
				$postarr['latitude'] = $gd_post['latitude'];
			}
			if ( isset( $gd_post['longitude'] ) ) {
				$postarr['longitude'] = $gd_post['longitude'];
			}
			if ( isset( $gd_post['mapview'] ) ) {
				$postarr['mapview'] = $gd_post['mapview'];
			}
			if ( isset( $gd_post['mapzoom'] ) ) {
				$postarr['mapzoom'] = $gd_post['mapzoom'];
			}
			if ( isset( $gd_post['post_dummy'] ) ) {
				$postarr['post_dummy'] = $gd_post['post_dummy'];
			}


			// set post images
			if ( isset( $gd_post['post_images'] ) ) {
				$featured_image = self::save_post_images( $post_id, $gd_post['post_images'], isset( $gd_post['post_dummy'] ) );
				if ( $featured_image !== false ) {
					$postarr['featured_image'] = $featured_image;
				}
			}
			unset( $postarr['post_images'] ); // unset the post_images as we save it in another table.


			//$postarr['featured_image'] = $post['featured_image'];// @todo we need to find a way to set default cat on add listing

			geodir_error_log( $postarr, 'postarr', __FILE__, __LINE__ );

			$format = array_fill( 0, count( $postarr ), '%s' );

			//print_r($gd_post);print_r( $postarr );exit;

			if ( $update ) {// update
				$wpdb->update(
					$table,
					$postarr,
					array( 'post_id' => $post_id ),
					$format
				);
			} else { // insert
				$wpdb->insert(
					$table,
					$postarr,
					$format
				);
			}

			// re-hook this function
			add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 3 );

		}

	}



	/*
	 * Not needed at present.
	 */
	public static function set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append = false, $old_tt_ids ) {

	}


	/**
	 * Save post attachments.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global object $current_user Current user object.
	 *
	 * @param int $post_id The post ID.
	 * @param array $post_image Post image urls as an array.
	 * @param bool $dummy Optional. Is this a dummy listing? Default false.
	 */
	public static function save_post_images( $post_id = 0, $post_images = array(), $dummy = false ) {

		// check for changes, maybe we don't need to run the whole function
		$curImages = GeoDir_Media::get_post_images_edit_string( $post_id );
		if ( $curImages == $post_images ) {
			return false;
		}

		$featured_image = '';

		// if no post id then bail
		if ( ! $post_id ) {
			return null;
		}

		// If array is empty then we delete all images.
		if ( empty( $post_images ) ) {
			if ( GeoDir_Media::delete_post_images( $post_id ) ) {
				return '';
			} else {
				return false;
			}
		} else {

			// convert to array if not already an array
			if ( ! is_array( $post_images ) ) {
				$post_images = explode( ",", $post_images );
			}

			$image_ids = array();



			foreach ( $post_images as $order => $image_string ) {
				$image_info = array();
				// check if the string contains more info
				if ( strpos( $image_string, '|' ) !== false ) {
					$image_info = explode( "|", $image_string );
				} else {
					$image_info[0] = $image_string;
				}

				/*
				 * $image_info[0] = image_url;
				 * $image_info[1] = image_id;
				 * $image_info[2] = image_title;
				 * $image_info[3] = image_caption;
				 */
				$image_url     = isset( $image_info[0] ) ? sanitize_text_field( $image_info[0] ) : '';
				$image_id      = ! empty( $image_info[1] ) ? absint( $image_info[1] ) : '';
				$image_title   = ! empty( $image_info[2] ) ? sanitize_text_field( $image_info[2] ) : '';
				$image_caption = ! empty( $image_info[3] ) ? sanitize_text_field( $image_info[3] ) : '';
				$approved      = 1; // we approve all images on save

				// check if we already have the image.
				if ( $image_url && $image_id ) { // we already have the image so just update the title, caption and order id
					// update the image
					$file        = GeoDir_Media::update_image_texts( $image_id, $post_id, $image_url, $image_title, $image_caption, $order, $approved );
					$image_ids[] = $image_id;
				} else { // its a new image we have to insert.
					
					
					if(defined('GEODIR_DOING_IMPORT') && strpos($image_url, 'http') !== 0){// if doing import and its not a full url then add placeholder attachment
						// insert the image
						$file = GeoDir_Media::insert_placeholder_image_attachment( $post_id, $image_url, $image_title, $image_caption, $order , $approved );
					}else{
						// insert the image
						$file = GeoDir_Media::insert_image_attachment( $post_id, $image_url, $image_title, $image_caption, $order , $approved );
					}
				}


				// check for error
				if ( is_wp_error( $file ) ) {
					// fail silently so the rest of the post data can be inserted
				} else {
					// its featured so assign it
					if ( $order == 0 ) {
						$featured_image = $file;
					}
				}

			}


			// Check if there are any missing image ids we need to delete
			if ( ! empty( $curImages ) && ! empty( $post_images ) && ! empty( $image_ids ) ) {
				$curImages_arr = explode( ",", $curImages );

				foreach ( $curImages_arr as $curImage ) {
					$curImage_arr = explode( "|", $curImage );
					if ( isset( $curImage_arr[1] ) && $curImage_arr[1] && ! in_array( $curImage_arr[1], $image_ids ) ) {
						GeoDir_Media::delete_attachment( $curImage_arr[1], $post_id );
					}
				}
			}


		}


		return $featured_image;

	}

	/**
	 * If is a GD post then save the post data to temp array for later `save_post` hook.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function wp_insert_post_data( $data, $postarr ) {
		// check its a GD CPT first
		if (
			( isset( $data['post_type'] ) && in_array( $data['post_type'], geodir_get_posttypes() ) )
			|| ( isset( $data['post_type'] ) && $data['post_type'] == 'revision' && in_array( get_post_type( $data['post_parent'] ), geodir_get_posttypes() ) )
		) {
			self::$post_temp = $postarr;
		}

		return $data;
	}

	public static function update_post_meta() {

	}

	public static function get_post_autosave( $post_id ) {

	}

	/**
	 * Outputs the add listing form HTML content.
	 *
	 * Other things are needed to output a working add listing form, you should use the add listing shortcode if needed.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $current_user Current user object.
	 * @global object $post The current post object.
	 * @global object $post_images Image objects of current post if available.
	 * @global object $gd_session GeoDirectory Session object.
	 * @todo make the form work in sections with fieldsets, all collapsed apart from the one ur on.
	 */
	public static function add_listing_form() {

		global $cat_display, $post_cat, $current_user, $gd_session, $gd_post;
		$page_id       = get_the_ID();
		$post          = '';
		$submit_button = '';
		$post_id       = '';
		$post_parent   = '';
		$user_notes    = array();


		$user_id = get_current_user_id();

		// if we have the post id.
		if ( $user_id && isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
			global $post;

			$post_id        = absint( $_REQUEST['pid'] );
			$post           = $gd_post = geodir_get_post_info( $post_id );
			$listing_type   = $post->post_type;
			$post_revisions = wp_get_post_revisions( $post_id, array( 'check_enabled' => false ) );

			// if we have a post revision
			if ( ! empty( $post_revisions ) ) {
				$revision                   = reset( $post_revisions );
				$post_parent                = $post_id;
				$post_id                    = absint( $revision->ID );
				$post                       = $gd_post = geodir_get_post_info( $post_id );

				$user_notes['has-revision'] = sprintf( __('Hey, we found some unsaved changes from earlier and are showing them below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'plugin-domain'), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );

			} // create a post revision
			else {
				$revision_id = _wp_put_post_revision( $post );
				$post_parent = $post_id;
				$post_id     = absint( $revision_id );
				$post        = $gd_post = geodir_get_post_info( $post_id );
			}

		} // New post
		elseif ( isset( $_REQUEST['listing_type'] ) && $_REQUEST['listing_type'] != '' ) {

			$listing_type = sanitize_text_field( $_REQUEST['listing_type'] );
			$auto_drafts  = self::get_user_auto_drafts( $user_id, $listing_type );
			//$auto_drafts  = $user_id ? self::get_user_auto_drafts( $user_id, $listing_type ) : '';

			// if we have a user auto-draft then populate it
			if ( ! empty( $auto_drafts ) && isset( $auto_drafts[0] ) ) {
				$post        = $auto_drafts[0];
				$post_parent = $post_id;
				$post_id     = absint( $post->ID );
				$post        = $gd_post = geodir_get_post_info( $post_id );

				$user_notes['has-auto-draft'] = sprintf( __('Hey, we found a post you started earlier and are showing it below. If you would prefer to start again then please %sclick here%s to remove this revision.', 'plugin-domain'), "<a href='javascript:void(0)' onclick='geodir_delete_revision();'>", "</a>" );
			} else {
				// Create the auto draft
				$post    = $gd_post = self::create_auto_draft( $listing_type );
				$post_id = absint( $post->ID );
				$post    = $gd_post = geodir_get_post_info( $post_id );
			}

		} else {
			echo '### a post type could not be determined.';

			return;
		}


		$post_type_info = geodir_get_posttype_info( $listing_type );

		$cpt_singular_name = ( isset( $post_type_info['labels']['singular_name'] ) && $post_type_info['labels']['singular_name'] ) ? __( $post_type_info['labels']['singular_name'], 'geodirectory' ) : __( 'Listing', 'geodirectory' );

		$package_info = array();
		$package_info = geodir_post_package_info( $package_info, $post );


		// user notes
		if ( ! empty( $user_notes ) ) {
			echo self::output_user_notes( $user_notes );
		}

		/*
		 * Create the security nonce, we also use this for logged out user preview.
		 */
		$security_nonce = wp_create_nonce( "geodir-save-post" );


		do_action( 'geodir_before_add_listing_form', $listing_type, $post, $package_info );
		?>
		<form name="geodirectory-add-post" id="geodirectory-add-post"
		      action="<?php echo get_page_link( geodir_preview_page_id() ); ?>" method="post"
		      enctype="multipart/form-data">
			<input type="hidden" name="action" value="geodir_save_post"/>
			<input type="hidden" name="preview" value="<?php echo sanitize_text_field( $listing_type ); ?>"/>
			<input type="hidden" name="post_type" value="<?php echo sanitize_text_field( $listing_type ); ?>"/>
			<input type="hidden" name="post_parent" value="<?php echo sanitize_text_field( $post_parent ); ?>"/>
			<input type="hidden" name="ID" value="<?php echo sanitize_text_field( $post_id ); ?>"/>
			<input type="hidden" name="security"
			       value="<?php echo sanitize_text_field( $security_nonce ); ?>"/>


			<?php if ( $page_id ) { ?>
				<input type="hidden" name="add_listing_page_id" value="<?php echo $page_id; ?>"/>
			<?php }
			if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) { ?>
			<?php }

			do_action( 'geodir_add_listing_form_start', $listing_type, $post, $package_info );



			/*
			 * Add the register fields if no user_id
			 */
			if(!$user_id && geodir_get_option("post_logged_out") && get_option( 'users_can_register' )){
				?>
				<h5 id="geodir_fieldset_details" class="geodir-fieldset-row"
				      gd-fieldset="user_details"><?php _e("Your Details","geodirectory");?></h5>

				<div id="post_title_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e("Name","geodirectory");?> <span>*</span></label>
					<input field_type="text" name="user_login" id="user_login" value="" type="text" class="geodir_textfield">
					<span class="geodir_message_note"><?php _e("Enter your name.","geodirectory");?></span>
					<span class="geodir_message_error"></span>
				</div>
				<div id="post_title_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
					<label><?php _e("Email","geodirectory");?> <span>*</span></label>
					<input field_type="text" name="user_email" id="user_email" value="" type="text" class="geodir_textfield">
					<span class="geodir_message_note"><?php _e("Enter your email address.","geodirectory");?></span>
					<span class="geodir_message_error"></span>
				</div>
				<?php
			}

			/**
			 * Called at the very top of the add listing page form for frontend.
			 *
			 * This is called just before the "Enter Listing Details" text.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_before_detail_fields' );
			?>
			<h5 id="geodir_fieldset_details" class="geodir-fieldset-row"
			    gd-fieldset="details"><?php echo LISTING_DETAILS_TEXT; ?></h5>
			<?php
			/**
			 * Called at the top of the add listing page form for frontend.
			 *
			 * This is called after the "Enter Listing Details" text.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_before_main_form_fields' );


			$package_info = array();
			$package_info = geodir_post_package_info( $package_info, $post );

			geodir_get_custom_fields_html( $package_info->pid, 'all', $listing_type );

			/**
			 * Called on the add listing page form for frontend just after the image upload field.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_after_main_form_fields' ); ?>


			<!-- add captcha code -->

			<script>
				/*<!--<script>-->*/
				document.write('<inp' + 'ut type="hidden" id="geodir_sp' + 'amblocker_top_form" name="geodir_sp' + 'amblocker" value="64"/>');
			</script>
			<noscript>
				<div>
					<label><?php _e( 'Type 64 into this box', 'geodirectory' ); ?></label>
					<input type="text" id="geodir_spamblocker_top_form" name="geodir_spamblocker" value=""
					       maxlength="10"/>
				</div>
			</noscript>
			<input type="text" id="geodir_filled_by_spam_bot_top_form" name="geodir_filled_by_spam_bot" value=""/>


			<!-- end captcha code -->

			<div id="geodir-add-listing-submit" class="geodir_form_row clear_both"
			     style="padding:2px;text-align:center;">
				<input type="submit" value="<?php echo __( 'Submit Listing', 'geodirectory' ); ?>"
				       class="geodir_button" <?php echo $submit_button; ?>/>

				<?php
				/*
				 * Show the preview button is its set to show.
				 */
				if(geodir_get_option('post_preview')){
					$preview_link = self::get_preview_link( $post );
					$preview_id = !empty($post->post_parent) ? $post->post_parent : $post->ID;
					echo "<a href='$preview_link' target='wp-preview-".$preview_id."' class='geodir_button geodir_preview_button'>". __( 'Preview Listing', 'geodirectory' )."</a>";
				}
				?>
            <span class="geodir_message_note"
                  style="padding-left:0px;"> <?php //_e( 'Note: You will be able to see a preview in the next page', 'geodirectory' ); ?></span>
			</div>
			<?php do_action( 'geodir_add_listing_form_end', $listing_type, $post, $package_info ); ?>
		</form>

		<?php


		do_action( 'geodir_after_add_listing_form', $listing_type, $post, $package_info );
		wp_reset_query();
	}

	/**
	 * Get the auto drafts for the user.
	 *
	 * @param string $user_id
	 * @param string $post_type
	 * @param int $post_parent
	 *
	 * @return array
	 */
	public static function get_user_auto_drafts( $user_id = '', $post_type = '', $post_parent = 0 ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}


		if($user_id){
			$args        = array(
				'posts_per_page'   => - 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => $post_type,
				'post_parent'      => $post_parent,
				'author'           => $user_id,
				'post_status'      => 'auto-draft',
				'suppress_filters' => true
			);
			$posts_array = get_posts( $args );
		}else{
			global $gd_session;
			// if its a logged out user the add the session id as post meta
			$session_id = $gd_session->get_id();
			$args        = array(
				'posts_per_page'   => - 1,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'post_type'        => $post_type,
				'meta_key'         => '_gd_logged_out_post_author',
				'meta_value'       => $session_id,
				'post_status'      => 'auto-draft',
				'suppress_filters' => true
			);
			$posts_array = get_posts( $args );
		}

		//print_r($posts_array);echo '#####';exit;



		return $posts_array;
	}

	/**
	 * Check if the user owns the post.
	 *
	 * @param $post_id
	 * @param $user_id
	 *
	 * @return bool
	 */
	public static function owner_check($post_id,$user_id){
		$owner = false;
		if(!$post_id){ return false;}
		$author_id = get_post_field( 'post_author', $post_id );

		if(!$user_id){// check if the current session owns the post with no author
			global $gd_session;
			$post_session_id = get_post_meta($post_id,'_gd_logged_out_post_author',true);
			if($post_session_id && $post_session_id == $gd_session->get_id()){
				$owner = true;
			}
		}elseif($author_id == $user_id){
			$owner = true;
		}
		return $owner;
	}

	/**
	 * Delete the post revision.
	 * 
	 * @param $post_data
	 */
	public static function delete_revision( $post_data ){

		if(!self::owner_check($post_data['ID'],get_current_user_id())){
			return new WP_Error( 'gd-not-owner', __( "You do not own this post", "geodirectory" ) );
		}


		//print_r($post_data );exit;

		$result = wp_delete_post( $post_data['ID'], true);
		if($result == false){
			return new WP_Error( 'gd-delete-failed', __( "Delete revision failed.", "geodirectory" ) );
		}else{
			return true;
		}
	}

	/**
	 * Try to get the preview id from the post parent id.
	 *
	 * @param $parent_id
	 *
	 * @return int|null|string
	 */
	public static function get_post_preview_id($parent_id){
		$parent_id = absint($parent_id);
		if($parent_id){
			global $wpdb;
			$sql = "SELECT $wpdb->posts.ID
					FROM $wpdb->posts 
					WHERE 1=1 
					AND $wpdb->posts.post_parent = %d 
					AND $wpdb->posts.post_type = 'revision'
					AND (($wpdb->posts.post_status = 'inherit')) 
					ORDER BY $wpdb->posts.post_date DESC, $wpdb->posts.ID DESC";
			$post_id = $wpdb->get_var($wpdb->prepare($sql,$parent_id));

			if($post_id){
				return $post_id;
			}else{
				return $parent_id;
			}

		}


	}

	/**
	 * Create the auto draft and return the post object with the title blank.
	 *
	 * @param $post_type
	 *
	 * @return WP_Post
	 */
	public static function create_auto_draft( $post_type ) {
		global $gd_session;
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		$post = get_default_post_to_edit( $post_type, true );

		// if its a logged out user the add the session id as post meta
		if($post->post_author == 0 && $session_id = $gd_session->get_id()){
			update_post_meta($post->ID,'_gd_logged_out_post_author',$session_id);
		}

		$post->post_title = ''; // don't show title as "Auto Draft"
		return $post;
	}

	/**
	 * Output the add lsiting user notes.
	 *
	 * @param $user_notes
	 */
	public static function output_user_notes( $user_notes ) {
		$notes = '';
		foreach ( $user_notes as $key => $user_note ) {
			$notes .= "<div class='gd-notification $key'>";
			$notes .= $user_note;
			$notes .= "</div>";
		}

		return $notes;
	}

	/**
	 * Get the preview link for the post.
	 *
	 * @param $post
	 *
	 * @return null|string
	 */
	public static function get_preview_link( $post ) {

		$query_args = array();

		if ( isset( $post->post_parent ) && $post->post_parent ) {
			$query_args['preview_id']    = $post->post_parent;
			$query_args['preview_nonce'] = wp_create_nonce( 'post_preview_' . $post->post_parent );
			$post_id                     = $post->post_parent;
		} else {
			$post_id = $post->ID;
		}

		// logged out user check
		if(empty($post->post_author) && !get_current_user_id()){
			$query_args['preview'] = true;
		}

		return get_preview_post_link( $post_id, $query_args );
	}

	/**
	 * Function to auto save a post if auto-draft or revision.
	 *
	 * @param array $post_data The post info, usually POST data.
	 */
	public static function auto_save_post( $post_data ) {
		// its a post revision
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			$post_data['post_type'] = 'revision'; //  post type is not sent but we know if it has a parent then its a revision.
			$post_data['post_name'] = $post_data['post_parent']."-autosave-v1";
			return wp_update_post( $post_data );
		} // its a new auto draft
		else {
			return wp_update_post( $post_data );
		}
	}


	/**
	 * Save the post from frontend ajax.
	 *
	 * @param $post_data
	 *
	 * @return int|WP_Error
	 */
	public static function ajax_save_post( $post_data ) {

		//print_r($_REQUEST);exit;

		//if its a revision we need to swap the post ids.
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {
			$post_data['ID'] = $post_data['post_parent'];
		}

		// get current status
		$post_status = get_post_status( $post_data['ID'] );

		// new post
		if($post_status == 'auto-draft'){
			$post_data['post_status'] = geodir_new_post_default_status();

			/*
			 * Check if its a logged out user and if we have details to register the user
			 */
			if(!get_current_user_id()
			   && geodir_get_option("post_logged_out")
			   && get_option( 'users_can_register' )
			   && isset($post_data['user_login'])
			   && isset($post_data['user_email'])
			   && $post_data['user_login']
			   && $post_data['user_email']
			   && is_email($post_data['user_email'])
			){

				$user_name = sanitize_user( $post_data['user_login'] );
				$user_email = sanitize_email( $post_data['user_email']);
				$user_id_from_username = username_exists( $user_name );
				$user_id_from_email = username_exists( $user_name );

				if($user_id_from_username && $user_id_from_email && $user_id_from_username == $user_id_from_email){ // user already exists
					$post_data['post_author'] = $user_id_from_email;
				}elseif($user_id_from_email){ // user exists from email
					$post_data['post_author'] = $user_id_from_email;
				}else{ // if username already exists but email does not then we change username
					$user_name = geodir_generate_unique_username( $user_name );
					$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
					$user_id = wp_create_user( $user_name, $random_password, $user_email );
					$post_data['post_author'] = $user_id;
				}
			}

		}else{
			$post_data['post_status'] = $post_status;
		}

		// Save the post.
		$result = wp_update_post( $post_data, true);

		// If the post saved then do some house keeping.
		if(!is_wp_error($result) && $user_id = get_current_user_id()){
			self::remove_post_revisions($post_data['ID'],$user_id);
		}

		// get the message response.
		if(!is_wp_error($result)){
			do_action( 'geodir_ajax_post_saved', $post_data, ! empty( $post_data['post_parent'] ) );
			return self::ajax_save_post_message($post_data);
		}

		return $result;

	}

	/**
	 * Get the message to display on ajax post save.
	 *
	 * @param $post_data
	 */
	public static function ajax_save_post_message($post_data){

		$message = '';

		// if its an update.
		if ( isset( $post_data['post_parent'] ) && $post_data['post_parent'] ) {

			// live changes have been made.
			if($post_data['post_status']=='publish'){
				$link = get_permalink($post_data['ID']);
				$message = sprintf( __('Update received, your changes are now live and can be viewed %shere%s.', 'geodirectory'), "<a href='$link' >", "</a>" );
			}
			// changes are not live
			else{
				//$message = sprintf( __('Update received, your changes may need to be reviewed before going live.', 'geodirectory'), "<a href='$link' >", "</a>" );
				$message = __('Update received, your changes may need to be reviewed before going live.', 'geodirectory');
			}

		}
		// if its a new post.
		else{

			// post published
			if($post_data['post_status']=='publish'){
				$link = get_permalink($post_data['ID']);
				$message = sprintf( __('Post received, your listing is now live and can be viewed %shere%s.', 'plugin-domain'), "<a href='$link' >", "</a>" );
			}
			// post needs review
			else{
				$post = new stdClass();
				$post->ID = $post_data['ID'];
				$preview_link = self::get_preview_link( $post );
				$message = sprintf( __('Post received, your listing may need to be reviewed before going live, you can preview it %shere%s.', 'plugin-domain'), "<a href='$preview_link' >", "</a>" );
			}
		}

		return self::output_user_notes(array('gd-info'=>$message));
	}

	/**
	 * Remove any old post revisions.
	 *
	 * @param $post_id
	 * @param $user_id
	 */
	public static function remove_post_revisions($post_id,$user_id){
		$posts = wp_get_post_revisions( $post_id, array( 'check_enabled' => false, 'author' => $user_id  ) );

		if(!empty($posts)){
			foreach($posts as $post){
				if($post->ID){
					wp_delete_post( $post->ID, true);
				}
			}
		}
	}

	/**
	 * Get the default status for new listings.
	 *
	 * @return mixed|string
	 */
	public static function get_post_default_status(){
		return geodir_get_option('default_status', 'publish');
	}


	/**
	 * Removes the post meta and attachments.
	 *
	 * @param $id
	 *
	 * @return bool|void
	 */
	public static function delete_post( $id ) {
		if ( ! current_user_can( 'delete_posts' ) || ! $id ) {
			return;
		}

		global $wpdb, $plugin_prefix;

		// check for multisite deletions
		if (strpos($plugin_prefix, $wpdb->prefix) !== false) {
		} else {
			return;
		}

		$post_type = get_post_type($id);

		// check for revisions
		if($post_type == 'revision'){
			$post_type = get_post_type(wp_get_post_parent_id($id));
		}

		$all_postypes = geodir_get_posttypes();

		if (!in_array($post_type, $all_postypes))
			return false;

		$table = $plugin_prefix . $post_type . '_detail';

		/* Delete custom post meta*/
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . $table . " WHERE `post_id` = %d",
				array($id)
			)
		);

		/* Delete Attachments*/
		GeoDir_Media::delete_post_images($id);

		return true;
	}

	/**
	 * Outputs the add listing page mandatory message.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 */
	public static function add_listing_mandatory_note( $listing_type = '', $post = array(), $package_info = array() ) {
		?><p class="geodir-note "><span class="geodir-required">*</span>&nbsp;<?php echo __('Indicates mandatory fields', 'geodirectory'); ?></p><?php
	}


######################## functions to show preview to logged out user ###########################

	/**
	 * Registers the filter to handle a public preview.
	 *
	 * Filter will be set if it's the main query, a preview, a singular page
	 * and the query var `_ppp` exists.
	 *
	 * @since 2.0.0
	 *
	 * @param object $query The WP_Query object.
	 * @return object The WP_Query object, unchanged.
	 */
	public static function show_public_preview( $query ) {
		if (
			$query->is_main_query() &&
			$query->is_preview() &&
			$query->is_singular()
		) {
			if ( ! headers_sent() ) {
				nocache_headers();
			}

			add_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10, 2 );
		}

		return $query;
	}
	/**
	 * Sets the post status of the first post to publish, so we don't have to do anything
	 * *too* hacky to get it to load the preview.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $posts The post to preview.
	 * @return array The post that is being previewed.
	 */
	public static function set_post_to_publish( $posts ) {
		// Remove the filter again, otherwise it will be applied to other queries too.
		remove_filter( 'posts_results', array( __CLASS__, 'set_post_to_publish' ), 10 );

		if ( empty( $posts ) ) {
			return;
		}
		//$posts[0]->post_status = 'publish';

		// check id post has no author and if the current user owns it (via session)
		if(!get_current_user_id() && self::owner_check($posts[0]->ID,0)){
			$posts[0]->post_status = 'publish';

			// Disable comments and pings for this post.
			add_filter( 'comments_open', '__return_false' );
			add_filter( 'pings_open', '__return_false' );
		}


		return $posts;
	}
	
	public static function set_closed_status( $posts, $wp_query ) {
		global $wp_post_statuses, $gd_reset_closed;
		
		if ( isset( $wp_post_statuses['gd-closed'] ) && !empty( $wp_query->is_single ) && !empty( $posts ) && ! empty( $posts[0]->post_type ) && geodir_is_gd_post_type( $posts[0]->post_type ) && !empty( $posts[0]->post_status ) && geodir_post_is_closed( $posts[0] ) ) {
			$wp_post_statuses['gd-closed']->public = true;
			$gd_reset_closed = true;
		}
		
		return $posts;
	}

	public static function reset_closed_status( $posts, $wp_query ) {
		global $wp_post_statuses, $gd_reset_closed;
		
		if ( $gd_reset_closed && isset( $wp_post_statuses['gd-closed'] ) ) {
			$wp_post_statuses['gd-closed']->public = false;
			$gd_reset_closed = false;
		}
		
		return $posts;
	}

}
//GeoDir_Post_Data::init();