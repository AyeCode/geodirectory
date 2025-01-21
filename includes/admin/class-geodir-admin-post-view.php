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
			add_filter( 'post_row_actions', array( __CLASS__,'post_row_actions' ), 20, 2 );
			add_filter( 'sd_pagenow_exclude', array( __CLASS__, 'sd_pagenow_exclude' ), 10, 1 );
			add_filter( 'geodir_get_widgets', array( __CLASS__, 'sd_get_widgets' ), 99, 1 );

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
		 */
		public static function edit_post_columns( $columns ) {

			//print_r($columns);echo '###';

			$new_columns = array(
				'image' => __( 'Image', 'geodirectory' ),
				'location'  => __( 'Location', 'geodirectory' ),
				'gd_categories' => __( 'Categories', 'geodirectory' ),
				'gd_tags' => __( 'Tags', 'geodirectory' ),
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
			global $post, $wpdb, $gd_post;

			if ( ! ( ! empty( $gd_post ) && $gd_post->ID == $post_id ) ) {
				$gd_post = geodir_get_post_info( $post_id );
			}

			switch ( $column ):
				/* If displaying the 'city' column. */
				case 'location' :
					$location = $gd_post;
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

				/* If displaying the 'tags' column. */
				case 'gd_tags' :

					/* Get the categories for the post. */


					$terms = wp_get_object_terms( $post_id, get_object_taxonomies( $post ) );

					/* If terms were found. */
					if ( ! empty( $terms ) ) {
						$out = array();
						/* Loop through each term, linking to the 'edit posts' page for the specific term. */
						foreach ( $terms as $term ) {
							if ( strstr( $term->taxonomy, 'tag' ) ) {
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
						_e( 'No Tags', 'geodirectory' );
					}
					break;
				/* If displaying the 'city' column. */
				case 'image' :
					$upload_dir = wp_upload_dir();
					$image_raw = isset( $gd_post->featured_image ) && ! empty( $gd_post->featured_image ) ? $gd_post->featured_image : '';
					/* If no city is found, output a default message. */
					if ( empty( $image_raw ) ) {
						_e( 'N/A', 'geodirectory' );
					} else {
						echo do_shortcode( '[gd_post_images types="post_images" fallback_types="post_images" limit="1" limit_show="1" type="image" slideshow="0" controlnav="0" show_title="0" show_caption="0" image_size="thumbnail"]' );
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
				$post_type_object = get_post_type_object( $post->post_type );
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
				if ( post_type_supports( $geodir_posttype, 'author' ) && current_user_can( $post_type_object->cap->edit_others_posts ) ) {
					add_meta_box( 'geodir_mbox_owner', wp_sprintf( __( '%s Owner', 'geodirectory' ), $post_typename ), array(
						__CLASS__,
						'owner_meta_box'
					), $geodir_posttype, 'normal', 'core' );
				}
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
			$package_id = geodir_get_post_package_id( $post, $post_type );

			wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_info_noncename' );

			$wrapper_class = geodir_design_style() ? 'bsui' : '';

			echo '<div id="geodir_wrapper" class="'.$wrapper_class.'">';
			/**
			 * Called before the GD custom fields are output in the wp-admin area.
			 *
			 * @since 1.0.0
			 * @see 'geodir_after_default_field_in_meta_box'
			 */
			do_action( 'geodir_before_default_field_in_meta_box' );

			// to display all fields in one information box
			geodir_get_custom_fields_html( $package_id, 'all', $post_type );
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
         * Owner meta box.
         *
         * @since 2.0.0
         *
         * @global object $post WordPress post object.
         * @global object $user_ID WordPress user_ID object.
         *
         */
		public static function owner_meta_box() {
			global $post, $user_ID;
			$curent_user_id = empty($post->ID) ? $user_ID : $post->post_author;
			$user = get_user_by( 'id', $curent_user_id );
			/* translators: 1: user display name 2: user ID 3: user email */
			$curent_user_name	= sprintf(
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'geodirectory' ),
				preg_replace('/[^a-zA-Z0-9\s]/', '', $user->display_name ), // select2 can convert broken html tags into working ones which could result in XSS
				absint( $user->ID ),
				sanitize_email( $user->user_email )
			);
			?>
			<label class="screen-reader-text" for="post_author_override"><?php _e('User', 'geodirectory'); ?></label>
			<select class="geodir-user-search" name="post_author_override" id="post_author_override" data-placeholder="<?php esc_attr_e( 'Search for a user&hellip;', 'geodirectory' ); ?>" data-allow_clear="false"><option value="<?php echo esc_attr( $curent_user_id ); ?>" selected="selected"><?php echo esc_attr( $curent_user_name ); ?><option></select>
			<?php
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
			global $post, $post_id, $aui_bs5;

			wp_nonce_field( plugin_basename( __FILE__ ), 'geodir_post_attachments_noncename' );

			if ( $featured_image = get_the_post_thumbnail( $post_id, 'medium' ) ) {
				echo '<h4>' . __( 'Featured Image', 'geodirectory' ) . '</h4>';
				echo $featured_image;
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
			$curImages = GeoDir_Media::get_field_edit_string($post_id,'post_images');

			// adjust values here
			$id = "post_images"; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == �img1� then $_POST[�img1�] will have all the image urls

			$svalue = stripslashes_deep( $curImages ); // this will be initial value of the above form field. Image urls.

			$multiple = true; // allow multiple files upload
			?>
			<div class="gtd-form_row clearfix" id="<?php echo $id; ?>dropbox"
			     style="border:1px solid #999999;padding:5px;text-align:center;">
				<input type="hidden" name="<?php echo $id; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $svalue ); ?>"/>
				<div
					class="plupload-upload-uic hide-if-no-js <?php if ( $multiple ): ?>plupload-upload-uic-multiple<?php endif; ?>"
					id="<?php echo $id; ?>plupload-upload-ui">
					<h4><?php _e( 'Drop files to upload', 'geodirectory' ); ?></h4>
					<input id="<?php echo $id; ?>plupload-browse-button" type="button"
					       value="<?php _e( 'Select Files', 'geodirectory' ); ?>" class="button"/>
					<span class="ajaxnonceplu"
					      id="ajaxnonceplu<?php echo wp_create_nonce( $id . 'pluploadan' ); ?>"></span>
					<div class="filelist"></div>
				</div>
				<?php if ( geodir_design_style() ) { ?><div class="bsui"><span id="<?php echo $id; ?>upload-error" class="d-none alert alert-danger" role="alert"></span></div><?php } ?>
				<div class="plupload-thumbs <?php if ( $multiple ): ?>plupload-thumbs-multiple<?php endif; ?> clearfix"
				     id="<?php echo $id; ?>plupload-thumbs" style="border-top:1px solid #ccc; padding-top:10px;">
				</div>
				<span id="upload-msg"><?php _e( 'Please drag & drop the images to rearrange the order', 'geodirectory' ); ?></span>
				<span class="geodir-regenerate-thumbnails bsui" style="margin:25px 0 10px 0;display:block;"><button type="button" class="button-secondary" aria-label="<?php esc_attr_e( 'Regenerate Thumbnails', 'geodirectory' );?>" aria-expanded="false" data-action="geodir-regenerate-thumbnails" data-post-id="<?php echo $post_id; ?>"><?php _e( 'Regenerate Thumbnails', 'geodirectory' );?></button><span style="margin-top:5px;display:block;"><?php _e( 'Regenerate thumbnails & metadata.', 'geodirectory' ); ?></span></span>
				<?php if ( geodir_design_style() ) { ?>
				<div class="modal fade bsui" id="gd_image_meta_<?php echo esc_attr( $id ); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title mt-0"><?php _e('Set Image Texts','geodirectory'); ?></h5>
								<?php if( $aui_bs5 ){ ?>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
									</button>
								<?php }else{ ?>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								<?php } ?>
							</div>
							<div class="modal-body <?php echo $aui_bs5 ? 'text-start' : 'text-left';?>"></div>
							<div class="modal-footer"></div>
						</div>
					</div>
				</div>
				<?php } else { ?>
				<span id="<?php echo $id; ?>upload-error" style="display:none"></span>
				<span style="display: none" id="gd_image_meta_<?php echo esc_attr( $id ); ?>" class="lity-hide lity-show"></span>
				<?php } ?>
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
				remove_meta_box('authordiv', $post->post_type, 'normal');

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

        /**
         * Add scripts in Posts footer.
         *
         * @since 2.0.0
         *
         */
		public static function posts_footer() {
			$screen		= get_current_screen();
			$screen_id	= $screen ? $screen->id : '';

			if ( ! ( $screen_id && in_array( $screen_id, geodir_get_screen_ids() ) ) ) {
				return;
			}
			$post_type = isset( $screen->post_type ) ? $screen->post_type : '';

			$statuses = geodir_get_custom_statuses( $post_type );
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

        /**
         * Add scripts in post form footer.
         *
         * @since 2.0.0
         *
         */
		public static function post_form_footer() {
			global $post;

			if ( !( ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) ) ) {
				return;
			}

			$statuses = geodir_get_custom_statuses( $post->post_type );
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
			   $('.save-post-status', $mbox).on("click",function(e) {
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

        /**
         * Posts column status.
         *
         * @since 2.0.0
         *
         * @param string $status Post column status.
         * @param object $post Post object.
         * @param string $column Post column.
         * @param string $mode Post mode.
         * @return string $status.
         */
		public static function posts_column_status( $status, $post, $column, $mode ) {
			if ( $column == 'date' && ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) ) {
				$statuses = geodir_get_custom_statuses( $post->post_type );
				if ( ! empty( $statuses[ $post->post_status ] ) ) {
					$status = $statuses[ $post->post_status ];
				}
			}
			return $status;
		}

		/**
		 * Filters the array of row action links on the Posts list table.
		 *
		 * @since 2.1.0.10
		 *
		 * @param string[] $actions An array of row action links.
		 * @param WP_Post  $post    The post object.
		 * @return array An array of row action links.
		 */
		public static function post_row_actions( $actions, $post ) {
			if ( ! empty( $post->post_type ) && geodir_is_gd_post_type( $post->post_type ) && current_user_can( 'manage_options' ) ) {
				$actions['geodir-regenerate-thumbnails bsui'] = '<button type="button" class="button-link" aria-label="' . esc_attr__( 'Regenerate Thumbnails', 'geodirectory' ) . '" aria-expanded="false" data-action="geodir-regenerate-thumbnails" data-post-id="' . $post->ID . '">' . __( 'Regenerate Thumbnails', 'geodirectory' ) . '</button>';
			}

			return $actions;
		}

		/**
		 * Load GD widgets on CPT list page.
		 *
		 * @since 2.1.1.0
		 *
		 * @param array $pagenow_exclude Exclude pagenow list.
		 * @return array Filtered pagenow list.
		 */
		public static function sd_pagenow_exclude( $pagenow_exclude ) {
			global $pagenow;

			if ( $pagenow == 'edit.php' && ! empty( $pagenow_exclude ) && ( $key = array_search( 'edit.php', $pagenow_exclude ) ) !== false && ! empty( $_REQUEST['post_type'] ) && geodir_is_gd_post_type( sanitize_text_field( $_REQUEST['post_type'] ) ) ) {
				unset( $pagenow_exclude[ $key ] );
			}

			return $pagenow_exclude;
		}

		/**
		 * Load images widget on CPT list page.
		 *
		 * @since 2.1.1.0
		 *
		 * @param array $widgets Widget list.
		 * @return array GD widgets to load on edit.php page.
		 */
		public static function sd_get_widgets( $widgets ) {
			global $pagenow;

			if ( $pagenow == 'edit.php' && ! empty( $_REQUEST['post_type'] ) && geodir_is_gd_post_type( sanitize_text_field( $_REQUEST['post_type'] ) ) ) {
				$widgets = array( 'GeoDir_Widget_Post_Images' );
			}

			return $widgets;
		}

	}

}
