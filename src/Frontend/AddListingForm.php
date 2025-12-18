<?php
/**
 * Add Listing Form
 *
 * Handles the rendering and logic for the GeoDirectory add/edit listing form.
 * This replaces the legacy GeoDir_Post_Data::add_listing_form() method with
 * a modern, dependency-injected approach.
 *
 * @package GeoDirectory\Frontend
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Core\Services\PostDrafts;
use AyeCode\GeoDirectory\Core\Services\PostPermissions;
use AyeCode\GeoDirectory\Core\Services\Posts;
use AyeCode\GeoDirectory\Core\Services\PostTypes;
use AyeCode\GeoDirectory\Fields\FieldsService;

/**
 * Add listing form renderer.
 *
 * @since 3.0.0
 */
final class AddListingForm {

	private PostDrafts $drafts;
	private PostPermissions $permissions;
	private Posts $posts;
	private PostTypes $post_types;
	private FieldsService $fields;

	/**
	 * Constructor.
	 *
	 * @param PostDrafts      $drafts      Draft/revision service.
	 * @param PostPermissions $permissions Permission checking service.
	 * @param Posts           $posts       Post operations service.
	 * @param PostTypes       $post_types  Post types service.
	 * @param FieldsService   $fields      Fields service.
	 */
	public function __construct(
		PostDrafts $drafts,
		PostPermissions $permissions,
		Posts $posts,
		PostTypes $post_types,
		FieldsService $fields
	) {
		$this->drafts      = $drafts;
		$this->permissions = $permissions;
		$this->posts       = $posts;
		$this->post_types  = $post_types;
		$this->fields      = $fields;
	}

	/**
	 * Render the add listing form.
	 *
	 * Main entry point that outputs the complete add/edit listing form HTML.
	 *
	 * @param array $params Widget/shortcode parameters.
	 * @return void Outputs HTML directly.
	 */
	public function render( array $params = [] ): void {
		global $aui_bs5, $gd_post, $geodir_label_type;

		$page_id = get_the_ID();
		$user_id = get_current_user_id();

		// Determine the post to edit or create.
		$post_id      = 0;
		$listing_type = '';

		if ( $user_id && isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] !== '' ) {
			$post_id      = absint( $_REQUEST['pid'] );
			$maybe_parent = wp_get_post_parent_id( $post_id );
			$parent_id    = $maybe_parent ? absint( $maybe_parent ) : 0;

			// Check permissions.
			if ( ! $this->permissions->can_edit( $post_id, $user_id, $parent_id ) ) {
				echo $this->output_user_notes( [ 'gd-error' => __( 'You do not have permission to edit this post.', 'geodirectory' ) ] );
				return;
			}

			$temp_post    = geodir_get_post_info( $post_id );
			$listing_type = $temp_post->post_type ?? '';
		} elseif ( isset( $_REQUEST['listing_type'] ) && $_REQUEST['listing_type'] !== '' ) {
			$listing_type = sanitize_text_field( $_REQUEST['listing_type'] );
		}

		if ( ! $listing_type ) {
			echo '### A post type could not be determined.';
			return;
		}

		// Get or create the draft/revision.
		$draft_data  = $this->drafts->get_or_create_for_form( $post_id, $listing_type, $user_id );
//		print_r( $draft_data );
		$post        = $draft_data['post'];
		$post_id     = $draft_data['post_id'];
		$post_parent = $draft_data['post_parent'];
		$user_notes  = $draft_data['user_notes'];

		if ( ! $post || ! $post_id ) {
			echo $this->output_user_notes( [ 'gd-error' => __( 'Unable to create or load the post.', 'geodirectory' ) ] );
			return;
		}

		// Set global $gd_post.
		$gd_post = $post;

		// Get post type info.
		$post_type_info = geodir_get_posttype_info( $listing_type );
		$cpt_singular_name = isset( $post_type_info['labels']['singular_name'] ) && $post_type_info['labels']['singular_name']
			? __( $post_type_info['labels']['singular_name'], 'geodirectory' )
			: __( 'Listing', 'geodirectory' );

		// Get package info.
		$package = geodir_get_post_package( $post, $listing_type );

		// Output user notes.
		if ( ! empty( $user_notes ) ) {//echo '###';exit;
			echo $this->output_user_notes( $user_notes );
		}

		// Create security nonce.
		$security_nonce = wp_create_nonce( 'geodir-save-post' );

		$design_style = geodir_design_style();
		$horizontal   = false;

		if ( $design_style ) {
			$horizontal = $geodir_label_type === 'horizontal';
		}

		// Build wrap class.
		$wrap_class = sd_build_aui_class( $params );

		/**
		 * Action before add listing form.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to AddListingForm class.
		 *
		 * @param string $listing_type The post type.
		 * @param object $post         The post object.
		 * @param object $package      The package object.
		 */
		do_action( 'geodir_before_add_listing_form', $listing_type, $post, $package );

		?>
		<form name="geodirectory-add-post" id="geodirectory-add-post" class="<?php echo esc_attr( $wrap_class ); ?>"
			  action="<?php echo esc_url( get_page_link( $post->ID ) ); ?>" method="post"
			  enctype="multipart/form-data">
			<input type="hidden" name="action" value="geodir_save_post"/>
			<input type="hidden" name="preview" value="<?php echo esc_attr( $listing_type ); ?>"/>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $listing_type ); ?>"/>
			<input type="hidden" name="post_parent" value="<?php echo esc_attr( $post_parent ); ?>"/>
			<input type="hidden" name="ID" value="<?php echo esc_attr( $post_id ); ?>"/>
			<input type="hidden" name="security" value="<?php echo esc_attr( $security_nonce ); ?>"/>

			<?php if ( $page_id ) { ?>
				<input type="hidden" name="add_listing_page_id" value="<?php echo absint( $page_id ); ?>"/>
			<?php } ?>

			<?php if ( ! empty( $params['container'] ) ) { ?>
				<input type="hidden" id="gd-add-listing-replace-container" value="<?php echo esc_attr( $params['container'] ); ?>"/>
			<?php } ?>

			<?php
			/**
			 * Action at start of add listing form.
			 *
			 * @since 2.0.0
			 * @since 3.0.0 Moved to AddListingForm class.
			 *
			 * @param string $listing_type The post type.
			 * @param object $post         The post object.
			 * @param object $package      The package object.
			 */
			do_action( 'geodir_add_listing_form_start', $listing_type, $post, $package );

			// Add registration fields for logged-out users.
			if ( ! $user_id && geodir_get_option( 'post_logged_out' ) && get_option( 'users_can_register' ) ) {
				$this->render_registration_fields( $design_style, $aui_bs5, $geodir_label_type );
			}

			// Add main listing detail fields header.
			$details_header = apply_filters(
				'geodir_add_listing_details_header',
				__( 'Enter Listing Details', 'geodirectory' ),
				$listing_type,
				$post,
				$package
			);

			if ( $details_header !== '' ) {
				if ( $design_style ) {
					$conditional_attrs = geodir_conditional_field_attrs( [ 'type' => 'fieldset' ], 'details', 'fieldset' );

					echo '<fieldset class="' . esc_attr( $aui_bs5 ? 'mb-3' : 'form-group' ) . '" id="geodir_fieldset_details"' . $conditional_attrs . '>';
					echo '<h3 class="h3">' . esc_html( $details_header ) . '</h3>';
					echo '</fieldset>';
				} else {
					?>
					<h5 id="geodir_fieldset_details" class="geodir-fieldset-row" gd-fieldset="details"><?php echo esc_html( $details_header ); ?></h5>
					<?php
				}
			}

			/**
			 * Action before main form fields.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_before_main_form_fields' );

			// Output custom fields.
			$this->fields->render_fields( $post_id, $listing_type, 'listing_form', $package->id );

			/**
			 * Action after main form fields.
			 *
			 * @since 1.0.0
			 */
			do_action( 'geodir_after_main_form_fields' );

			// Add spam blocker.
			if ( ! $this->skip_spamblocker() ) {
				$this->render_spamblocker( $design_style );
			}

			// Render submit buttons.
			$this->render_submit_buttons( $design_style, $horizontal, $aui_bs5, $post );

			/**
			 * Action at end of add listing form.
			 *
			 * @since 2.0.0
			 * @since 3.0.0 Moved to AddListingForm class.
			 *
			 * @param string $listing_type The post type.
			 * @param object $post         The post object.
			 * @param object $package      The package object.
			 */
			do_action( 'geodir_add_listing_form_end', $listing_type, $post, $package );
			?>
		</form>
		<?php

		/**
		 * Action after add listing form.
		 *
		 * @since 2.0.0
		 * @since 3.0.0 Moved to AddListingForm class.
		 *
		 * @param string $listing_type The post type.
		 * @param object $post         The post object.
		 * @param object $package      The package object.
		 */
		do_action( 'geodir_after_add_listing_form', $listing_type, $post, $package );
	}

	/**
	 * Render user registration fields for logged-out users.
	 *
	 * @param string $design_style      Design style (e.g., 'bootstrap' or empty).
	 * @param bool   $aui_bs5           Whether using Bootstrap 5.
	 * @param string $geodir_label_type Label type (horizontal, top, floating, hidden).
	 * @return void Outputs HTML directly.
	 */
	private function render_registration_fields( string $design_style, bool $aui_bs5, string $geodir_label_type ): void {
		if ( $design_style ) {
			echo '<fieldset class="' . esc_attr( $aui_bs5 ? 'mb-3' : 'form-group' ) . '" id="geodir_fieldset_your_details">';
			echo '<h3 class="h3">' . esc_html__( 'Your Details', 'geodirectory' ) . '</h3>';
			echo '</fieldset>';

			echo aui()->input( [
				'id'         => 'user_login',
				'name'       => 'user_login',
				'required'   => true,
				'label'      => __( 'Name', 'geodirectory' ) . ' <span class="text-danger">*</span>',
				'label_type' => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'type'       => 'text',
				'placeholder' => esc_html__( 'Enter your name', 'geodirectory' ),
				'class'      => '',
				'help_text'  => __( 'Enter your name.', 'geodirectory' ),
			] );

			echo aui()->input( [
				'id'         => 'user_email',
				'name'       => 'user_email',
				'required'   => true,
				'label'      => __( 'Email', 'geodirectory' ) . ' <span class="text-danger">*</span>',
				'label_type' => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'type'       => 'email',
				'class'      => '',
				'help_text'  => __( 'Enter your email address.', 'geodirectory' ),
			] );
		} else {
			?>
			<h5 id="geodir_fieldset_details" class="geodir-fieldset-row" gd-fieldset="user_details"><?php esc_html_e( 'Your Details', 'geodirectory' ); ?></h5>

			<div id="user_login_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
				<label><?php esc_html_e( 'Name', 'geodirectory' ); ?> <span>*</span></label>
				<input field_type="text" name="user_login" id="user_login" value="" type="text" class="geodir_textfield">
				<span class="geodir_message_note"><?php esc_html_e( 'Enter your name.', 'geodirectory' ); ?></span>
				<span class="geodir_message_error"></span>
			</div>
			<div id="user_email_row" class="required_field geodir_form_row clearfix gd-fieldset-details">
				<label><?php esc_html_e( 'Email', 'geodirectory' ); ?> <span>*</span></label>
				<input field_type="text" name="user_email" id="user_email" value="" type="text" class="geodir_textfield">
				<span class="geodir_message_note"><?php esc_html_e( 'Enter your email address.', 'geodirectory' ); ?></span>
				<span class="geodir_message_error"></span>
			</div>
			<?php
		}

		/**
		 * Action before detail fields.
		 *
		 * @since 1.0.0
		 */
		do_action( 'geodir_before_detail_fields' );
	}

	/**
	 * Render spam blocker fields.
	 *
	 * @param string $design_style Design style (e.g., 'bootstrap' or empty).
	 * @return void Outputs HTML directly.
	 */
	private function render_spamblocker( string $design_style ): void {
		?>
		<!-- add captcha code -->
		<script>
			/*<!--<script>-->*/
			document.write('<inp' + 'ut type="hidden" id="geodir_sp' + 'amblocker_top_form" name="geodir_sp' + 'amblocker" value="64"/>');
		</script>
		<noscript aria-hidden="true">
			<div>
				<label><?php esc_html_e( 'Type 64 into this box', 'geodirectory' ); ?></label>
				<input type="text" id="geodir_spamblocker_top_form" <?php echo $design_style ? 'class="d-none"' : ''; ?> name="geodir_spamblocker" value="" maxlength="10"/>
			</div>
		</noscript>
		<input type="text" id="geodir_filled_by_spam_bot_top_form" <?php echo $design_style ? 'class="d-none"' : ''; ?> name="geodir_filled_by_spam_bot" value="" aria-label="<?php esc_attr_e( 'Type 64 into this box', 'geodirectory' ); ?>"/>
		<?php
	}

	/**
	 * Output user notes/messages.
	 *
	 * @param array $user_notes Array of notes to display.
	 * @return string HTML output.
	 */
	private function output_user_notes( array $user_notes ): string {
		$design_style = geodir_design_style();

		/**
		 * Filter user notes.
		 *
		 * @since 2.0.0.59
		 * @since 3.0.0 Moved to AddListingForm class.
		 *
		 * @param array $user_notes An array of user notes.
		 */
		$user_notes = apply_filters( 'geodir_post_output_user_notes', $user_notes );

		$notes = '';
		if ( ! empty( $user_notes ) ) {
			foreach ( $user_notes as $key => $user_note ) {
				if ( $design_style ) {
					$notes .= "<div class='gd-notification alert alert-info {$key}' role='alert'>";
					$notes .= $user_note;
					$notes .= '</div>';
				} else {
					$notes .= "<div class='gd-notification {$key}'>";
					$notes .= $user_note;
					$notes .= '</div>';
				}
			}
		}

		return $notes;
	}

	/**
	 * Check if spam blocker should be skipped.
	 *
	 * @return bool True to skip spam blocker, false otherwise.
	 */
	private function skip_spamblocker(): bool {
		// Skip spam blocker in Beaver Builder page edit mode.
		if ( class_exists( 'FLBuilder' ) && isset( $_REQUEST['fl_builder'] ) ) {
			return true;
		}

		/**
		 * Filter whether to skip spam blocker.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $skip Whether to skip spam blocker.
		 */
		return apply_filters( 'geodir_skip_spamblocker', false );
	}

	/**
	 * Render submit and preview buttons.
	 *
	 * @param string $design_style Design style (e.g., 'bootstrap' or empty).
	 * @param bool   $horizontal   Whether using horizontal label layout.
	 * @param bool   $aui_bs5      Whether using Bootstrap 5.
	 * @param object $post         The post object.
	 * @return void Outputs HTML directly.
	 */
	private function render_submit_buttons( string $design_style, bool $horizontal, bool $aui_bs5, object $post ): void {
		?>
		<div id="geodir-add-listing-submit" class="geodir_form_row clear_both <?php echo $design_style && $horizontal ? ( $aui_bs5 ? 'mb-3' : 'form-group' ) . ' row' : ''; ?>"
			 style="<?php echo $design_style ? '' : 'padding:2px;text-align:center;'; ?>">

			<?php echo $design_style && $horizontal ? '<label class="col-sm-2 col-form-label"></label>' : ''; ?>

			<?php echo $design_style && $horizontal ? '<div class="col-sm-10">' : ''; ?>

			<button type="submit" class="geodir_button <?php echo $design_style ? 'btn btn-primary' : ''; ?>">
				<?php
				/**
				 * Filter submit button text.
				 *
				 * @since 1.0.0
				 *
				 * @param string $text Submit button text.
				 */
				echo esc_html( apply_filters( 'geodir_add_listing_btn_text', __( 'Submit Listing', 'geodirectory' ) ) );
				?>
			</button>

			<?php
			// Show preview button if enabled.
			if ( geodir_get_option( 'post_preview' ) ) {
				$preview_link  = $this->get_preview_link( $post );
				$preview_id    = ! empty( $post->post_parent ) ? $post->post_parent : $post->ID;
				$preview_class = $design_style ? 'btn btn-outline-primary' : '';

				/**
				 * Filter preview button text.
				 *
				 * @since 2.1.1.12
				 *
				 * @param string $preview_text Preview button text.
				 * @param int    $preview_id   Preview ID.
				 */
				$preview_text = apply_filters( 'geodir_add_listing_preview_btn_text', __( 'Preview Listing', 'geodirectory' ), $preview_id );

				$preview_action = "<a href='" . esc_url( $preview_link ) . "' target='wp-preview-" . absint( $preview_id ) . "' class='geodir_button geodir_preview_button " . esc_attr( $preview_class ) . "'>" . esc_html( $preview_text ) . " <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\"></i></a>";

				/**
				 * Filter preview action HTML.
				 *
				 * @since 2.1.1.12
				 *
				 * @param string $preview_action Preview action HTML.
				 * @param int    $preview_id     Preview ID.
				 * @param string $preview_link   Preview link URL.
				 */
				echo apply_filters( 'geodir_add_listing_preview_action', $preview_action, $preview_id, $preview_link );
			}
			?>
			<span class="geodir_message_note" style="padding-left:0px;"></span>

			<?php echo $design_style && $horizontal ? '</div>' : ''; ?>
		</div>
		<?php
	}

	/**
	 * Get preview link for a post.
	 *
	 * @param object $post The post object.
	 * @return string Preview link URL.
	 */
	private function get_preview_link( object $post ): string {
		$query_args = [];

		if ( isset( $post->post_parent ) && $post->post_parent ) {
			$query_args['preview_id']    = $post->post_parent;
			$query_args['preview_nonce'] = wp_create_nonce( 'post_preview_' . $post->post_parent );
			$post_id                     = $post->post_parent;
		} else {
			$post_id = $post->ID;
		}

		// Logged out user check.
		if ( empty( $post->post_author ) && ! get_current_user_id() ) {
			$query_args['preview'] = true;
		}

		return get_preview_post_link( $post_id, $query_args );
	}
}
