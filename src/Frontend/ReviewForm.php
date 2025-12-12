<?php
/**
 * Review Form Manager
 *
 * Handles all modifications and rendering for the comment/review form.
 *
 * @package GeoDirectory\Frontend
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Frontend\Renderers\RatingRenderer;

final class ReviewForm {
	private Settings $settings;
	private RatingRenderer $rating_renderer;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings utility.
	 * @param RatingRenderer $rating_renderer The rating renderer.
	 */
	public function __construct( Settings $settings, RatingRenderer $rating_renderer ) {
		$this->settings = $settings;
		$this->rating_renderer = $rating_renderer;
	}

	/**
	 * Modifies the WordPress comment form defaults to style it and add/change fields.
	 *
	 * @param array $defaults The default WordPress comment form arguments.
	 * @return array The modified arguments.
	 */
	public function modify_form_defaults( array $defaults ): array {
		global $post;

		// @todo Refactor the global aui() helper into an injectable service.
		if ( ! function_exists( 'aui' ) || ! $post || ! geodir_is_gd_post_type( $post->post_type ) ) {
			return $defaults;
		}

		$commenter = wp_get_current_commenter();

		$defaults['comment_field'] = aui()->textarea( [
			'name'        => 'comment',
			'id'          => 'comment',
			'placeholder' => esc_html__( 'Enter your review comments here (required)...', 'geodirectory' ),
			'required'    => true,
			'label'       => esc_html__( 'Review text', 'geodirectory' ),
			'rows'        => 8,
		] );

		$defaults['fields']['author'] = aui()->input( [
			'name'     => 'author',
			'required' => true,
			'label'    => esc_html__( 'Name', 'geodirectory' ),
			'value'    => $commenter['comment_author'] ?? '',
		] );

		$defaults['fields']['email'] = aui()->input( [
			'name'     => 'email',
			'required' => true,
			'label'    => esc_html__( 'Email', 'geodirectory' ),
			'type'     => 'email',
			'value'    => $commenter['comment_author_email'] ?? '',
		] );

		$defaults['fields']['url'] = aui()->input( [
			'name'  => 'url',
			'label' => esc_html__( 'Website', 'geodirectory' ),
			'type'  => 'url',
			'value' => $commenter['comment_author_url'] ?? '',
		] );

		$defaults['logged_in_as']         = str_replace( 'logged-in-as', 'logged-in-as mb-3', $defaults['logged_in_as'] );
		$defaults['comment_notes_before'] = aui()->alert( [
			'type'    => 'info',
			'content' => __( 'Your email address will not be published.', 'geodirectory' ),
		] );

		$defaults['class_submit'] = 'btn btn-primary';
		$defaults['label_submit'] = esc_html__( 'Post Review', 'geodirectory' );
		$defaults['title_reply']  = '<span class="gd-comment-review-title h4">Leave a Review</span>';

		return $defaults;
	}

	/**
	 * Renders the star rating input field.
	 *
	 * This is hooked into the comment form.
	 *
	 * @return void
	 */
	public function render_rating_input(): void {
		global $post;

		if ( ! $post || ! geodir_is_gd_post_type( $post->post_type ) || geodir_cpt_has_rating_disabled( $post->post_type ) ) {
			return;
		}

		echo '<div class="mb-3 form-control h-auto rounded px-3 pt-3 pb-3 gd-rating-input-group">';
		echo $this->rating_renderer->render_input();
		echo '</div>';
	}

	/**
	 * Gets the default, untranslated rating texts.
	 *
	 * This is a static helper for use in config files or other places
	 * where an object instance is not available.
	 *
	 * @return array The default rating texts.
	 */
	public static function get_default_rating_texts(): array {
		return [
			1 => 'Terrible',
			2 => 'Poor',
			3 => 'Average',
			4 => 'Very Good',
			5 => 'Excellent',
		];
	}
}
