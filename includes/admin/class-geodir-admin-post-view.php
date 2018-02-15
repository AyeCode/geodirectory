<?php
/**
 * GeoDirectory Admin Post View
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Admin_Post_View', false ) ) {

	/**
	 * GeoDir_Admin_Post_View Class.
	 */
	class GeoDir_Admin_Post_View {

		/**
		 * Start the action.
		 */
		public static function init() {

			// add listing settings
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );

			// remove default featured image settings and revisions meta boxes
			add_action('do_meta_boxes', array( __CLASS__, 'remove_wp_meta_box'));

			// remove the default category selector
			add_action('admin_menu', array( __CLASS__,'remove_cat_meta_box'));
			
			add_action('admin_footer-edit.php', array( __CLASS__,'posts_footer'));
			add_action('admin_footer-post.php', array( __CLASS__,'post_form_footer'));
			add_action('admin_footer-post-new.php', array( __CLASS__,'post_form_footer'));
			add_action('post_date_column_status', array( __CLASS__,'posts_column_status'), 10, 4);

			self::add_post_type_view_filters();
		}

		/**
		 * Adds custom columns on geodirectory post types.
		 *
		 * @package GeoDirectory
		 */
		public static function add_post_type_view_filters() {

			if ( $post_types = geodir_get_posttypes() ) {
				foreach ( $post_types as $post_type ) :
					add_filter( "manage_edit-{$post_type}_columns", array( __CLASS__, 'edit_post_columns' ), 100 );
					//Filter-Payment-Manager to show Package
					add_action( "manage_{$post_type}_posts_custom_column", array(
						__CLASS__,
						'manage_post_columns'
					), 10, 2 );

					add_filter( "manage_edit-{$post_type}_sortable_columns", array(
						__CLASS__,
						'post_sortable_columns'
					) );

				endforeach;
			}
		}

		/**
		 * Modify admin post listing page columns.
		 *
		 * @param array $columns The column array.
		 *
		 * @return array Altered column array.
		 * @todo we need to make this better
		 */
		public static function edit_post_columns( $columns ) {

			//print_r($columns);echo '###';

			$new_columns = array(
				'location'  => __( 'Location', 'geodirectory' ),
				'gd_categories' => __( 'Categories', 'geodirectory' )
			);
			$offset = 2;


			$columns = array_merge( array_slice( $columns, 0, $offset ), $new_columns, array_slice( $columns, $offset ) );


			return $columns;
		}

		/**
		 * Adds content to our custom post listing page columns.
		 *
		 * @global object $wpdb WordPress Database object.
		 * @global object $post WordPress Post object.
		 *
		 * @param string $column The column name.
		 * @param int $post_id The post ID.
		 */
		public static function manage_post_columns( $column, $post_id ) {
			global $post, $wpdb,$gd_post;

			switch ( $column ):
				/* If displaying the 'city' column. */
				case 'location' :
					$location    = $gd_post;
					/* If no city is found, output a default message. */
					if ( empty( $location ) ) {
						_e( 'Unknown', 'geodirectory' );
					} else {
						/* If there is a city id, append 'city name' to the text string. */
						echo( __( $location->country, 'geodirectory' ) . ', ' . $location->region . ', ' . $location->city );
					}
					break;


				/* If displaying the 'categories' column. */
				case 'gd_categories' :

					/* Get the categories for the post. */


					$terms = wp_get_object_terms( $post_id, get_object_taxonomies( $post ) );

					/* If terms were found. */
					if ( ! empty( $terms ) ) {
						$out = array();
						/* Loop through each term, linking to the 'edit posts' page for the specific term. */
						foreach ( $terms as $term ) {
							if ( ! strstr( $term->taxonomy, 'tag' ) ) {
								$out[] = sprintf( '<a href="%s">%s</a>',
									esc_url( add_query_arg( array(
										'post_type'     => $post->post_type,
										$term->taxonomy => $term->slug
									), 'edit.php' ) ),
									esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $term->taxonomy, 'display' ) )
								);
							}
						}
						/* Join the terms, separating them with a comma. */
						echo( join( ', ', $out ) );
					} /* If no terms were found, output a default message. */
					else {
						_e( 'No Categories', 'geodirectory' );
					}
					break;

			endswitch;
		}

		/**
		 * Makes admin post listing page columns sortable.
		 *
		 * @param array $columns The column array.
		 *
		 * @return array Altered column array.
		 */
		public static function post_sortable_columns( $columns ) {

			$columns['expire'] = 'expire';

			return $columns;
		}

		/**
		 * Adds meta boxes to the GD post types.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $post WordPress Post object.
		 */
		public static function add_meta_boxes() {
			global $post;

			$geodir_post_types = geodir_get_posttypes( 'array' );
			$geodir_posttypes  = array_keys( $geodir_post_types );

			if ( isset( $post->post_type ) && in_array( $post->post_type, $geodir_posttypes ) ):

				$geodir_posttype = $post->post_type;
				$post_typename   = __( $geodir_post_types[ $geodir_posttype ]['labels']['singular_name'], 'geodirectory' );
				$post_typename   = geodir_ucwords( $post_typename );

				add_meta_box( 'geodir_post_images', $post_typename . ' ' . __( 'Attachments', 'geodirectory' ), array(
					__CLASS__,
					'attachment_settings'
				), $geodir_posttype, 'side' );
				add_meta_box( 'geodir_post_info', $post_typename . ' ' . __( 'Information', 'geodirectory' ), array(
					__CLASS__,
					'listing_setting'
				), $geodir_posttype, 'normal', 'high' );
			endif;

		}

		/**
		 * Prints post information meta box content.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $post The post object.
		 * @global int $post_id The post ID.
		 */
		public static function listing_setting() {
			global $post, $post_id;

			$post_type = get_post_type();

			$package_info = array();

			$package_info = geodir_post_package_info( $package_info, $post, $post_type );
			wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_info_noncename' );
			echo '<div id="geodir_wrapper">';
			/**
			 * Called before the GD custom fields are output in the wp-admin area.
			 *
			 * @since 1.0.0
			 * @see 'geodir_after_default_field_in_meta_box'
			 */
			do_action( 'geodir_before_default_field_in_meta_box' );
			//geodir_get_custom_fields_html($package_info->pid,'default',$post_type);
			// to display all fields in one information box
			geodir_get_custom_fields_html( $package_info->pid, 'all', $post_type );
			/**
			 * Called after the GD custom fields are output in the wp-admin area.
			 *
			 * @since 1.0.0
			 * @see 'geodir_before_default_field_in_meta_box'
			 */
			do_action( 'geodir_after_default_field_in_meta_box' );
			echo '</div>';
		}

		/**
		 * Prints Attachments meta box content.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $post The post object.
		 * @global int $post_id The post ID.
		 */
		public static function attachment_settings() {
			global $post, $post_id;

			wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_attachments_noncename' );

			if ( geodir_get_featured_image( $post_id, 'thumbnail' ) ) {
				echo '<h4>' . __( 'Featured Image', 'geodirectory' ) . '</h4>';
				geodir_show_featured_image( $post_id, 'thumbnail' );
			}

			$image_limit = 0;

			?>


			<h5 class="form_title">
				<?php if ( $image_limit != 0 && $image_limit == 1 ) {
					echo '<br /><small>(' . __( 'You can upload', 'geodirectory' ) . ' ' . $image_limit . ' ' . __( 'image with this package', 'geodirectory' ) . ')</small>';
				} ?>
				<?php if ( $image_limit != 0 && $image_limit > 1 ) {
					echo '<br /><small>(' . __( 'You can upload', 'geodirectory' ) . ' ' . $image_limit . ' ' . __( 'images with this package', 'geodirectory' ) . ')</small>';
				} ?>
				<?php if ( $image_limit == 0 ) {
					echo '<br /><small>(' . __( 'You can upload unlimited images with this package', 'geodirectory' ) . ')</small>';
				} ?>
			</h5>


			<?php

			$curImages = geodir_get_images( $post_id );

			//print_r( $curImages );
			$place_img_array = array();

			if ( ! empty( $curImages ) ):
				foreach ( $curImages as $p_img ):
					$place_img_array[] = $p_img->src . "|" . $p_img->id . "|" . $p_img->title . "|" . $p_img->caption;
				endforeach;
			endif;

			if ( ! empty( $place_img_array ) ) {
				$curImages = implode( ',', $place_img_array );
			}


			$curImages = GeoDir_Media::get_post_images_edit_string( $post_id );


			// adjust values here
			$id = "post_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == �img1� then $_POST[�img1�] will have all the image urls

			$svalue = $curImages; // this will be initial value of the above form field. Image urls.

			$multiple = true; // allow multiple files upload

			$width = geodir_media_image_large_width(); // If you want to automatically resize all uploaded images then provide width here (in pixels)

			$height = geodir_media_image_large_height(); // If you want to automatically resize all uploaded images then provide height here (in pixels)

			?>

			<div class="gtd-form_row clearfix" id="<?php echo $id; ?>dropbox"
			     style="border:1px solid #999999;padding:5px;text-align:center;">
				<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo $svalue; ?>"/>

				<div
					class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ): ?>plupload-upload-uic-multiple<?php endif; ?>"
					id="<?php echo $id; ?>plupload-upload-ui">
					<h4><?php _e( 'Drop files to upload', 'geodirectory' ); ?></h4>
					<input id="<?php echo $id; ?>plupload-browse-button" type="button"
					       value="<?php _e( 'Select Files', 'geodirectory' ); ?>" class="button"/>
					<span class="ajaxnonceplu"
					      id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
					<?php if ( $width && $height ): ?>
						<span class="plupload-resize"></span>
						<span class="plupload-width" id="plupload-width<?php echo $width; ?>"></span>
						<span class="plupload-height" id="plupload-height<?php echo $height; ?>"></span>
					<?php endif; ?>
					<div class="filelist"></div>
				</div>
				<div class="plupload-thumbs <?php if ( $multiple ): ?>plupload-thumbs-multiple<?php endif; ?> clearfix"
				     id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
				</div>
        <span
	        id="upload-msg"><?php _e( 'Please drag &amp; drop the images to rearrange the order', 'geodirectory' ); ?></span>
				<span id="<?php echo $id; ?>upload-error" style="display:none"></span>

				<span style="display: none" id="gd-image-meta-input"></span>
			</div>

			<?php

		}

		/**
		 * Removes default thumbnail metabox on GD post types.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 * @global object $post WordPress Post object.
		 */
		public static function remove_wp_meta_box()
		{
			global $post;

			$geodir_posttypes = geodir_get_posttypes();

			if (isset($post) && in_array($post->post_type, $geodir_posttypes)):

				remove_meta_box('postimagediv', $post->post_type, 'side');
				remove_meta_box('revisionsdiv', $post->post_type, 'normal');

				//remove_meta_box($post->post_type.'category' . 'div', $post->post_type, 'normal');

			endif;

		}

		/**
		 * Removes taxonomy meta boxes.
		 *
		 * GeoDirectory hide categories post meta.
		 *
		 */
		public static function remove_cat_meta_box()
		{

			$geodir_post_types = geodir_get_option('post_types');

			if (!empty($geodir_post_types)) {
				foreach ($geodir_post_types as $geodir_post_type => $geodir_posttype_info) {

					$gd_taxonomy = geodir_get_taxonomies($geodir_post_type);

					if(!empty($gd_taxonomy)) {
						foreach ($gd_taxonomy as $tax) {

							remove_meta_box($tax . 'div', $geodir_post_type, 'normal');

						}
					}

				}
			}
		}
		
		public static function posts_footer() {
			$screen		= get_current_screen();
			$screen_id	= $screen ? $screen->id : '';
		
			if ( ! ( $screen_id && in_array( $screen_id, gd_get_screen_ids() ) ) ) {
				return;
			}
			
			$statuses = geodir_get_custom_statuses();
			$status_list = '';
			foreach ( $statuses as $status => $label ) {
			  $status_list .= '<option value="' . $status . '">' . $label . '</option>';
			}
			?>
			<script type="text/javascript">
			jQuery(function($) {
			   $('select[name="_status"]').append('<?php echo addslashes($status_list); ?>');
			});
			</script>
			<?php
		}
		
		public static function post_form_footer() {
			global $post;

			if ( !( ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) ) ) {
				return;
			}
			 
			$statuses = geodir_get_custom_statuses();
			$status_list = '';
			$current_label = '';
			foreach ( $statuses as $status => $label ) {
			  if ( $post->post_status == $status ) {
				  $current_label = $label;
			  }
			  $status_list .= '<option data-save-text="' . wp_sprintf( __( 'Save as %s', 'geodirectory' ), $label ) . '" value="' . $status . '" ' . selected( ( $post->post_status == $status ), true, false ) . '>' . $label . '</option>';
			}
			?>
			<script type="text/javascript">
			jQuery(function($) {
			   var $mbox = $("#submitdiv");
			   $("select#post_status", $mbox).append('<?php echo addslashes($status_list); ?>');
			   <?php if ( $current_label ) { ?>$(".misc-pub-section #post-status-display", $mbox).text('<?php echo $current_label; ?>');<?php } ?>
			   $('.save-post-status', $mbox).click(function(e) {
				   var txt = $("select#post_status option:selected", $mbox).data('save-text');
				   if (txt) {
					   $('#save-post', $mbox).show().val(txt);
				   }
			   });
			   $('.save-post-status', $mbox).trigger('click');
			});
			</script>
			<?php
		}
		
		public static function posts_column_status( $status, $post, $column, $mode ) {
			if ( $column == 'date' && ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) ) {
				$statuses = geodir_get_custom_statuses();
				if ( ! empty( $statuses[ $post->post_status ] ) ) {
					$status = $statuses[ $post->post_status ];
				}
			}
			return $status;
		}

	}

}