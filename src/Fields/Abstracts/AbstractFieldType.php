<?php
namespace AyeCode\GeoDirectory\Fields\Abstracts;

use AyeCode\GeoDirectory\Fields\Interfaces\FieldTypeInterface;

/**
 * Class AbstractFieldType
 *
 * Base class for all custom fields. Handles value retrieval and AUI arg preparation.
 *
 * @package AyeCode\GeoDirectory\Fields\Abstracts
 */
abstract class AbstractFieldType extends AbstractFieldOutput implements FieldTypeInterface {

	/**
	 * The raw field data from the database.
	 *
	 * @var array
	 */
	protected $field_data;

	/**
	 * The Post ID context.
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * The current value of the field.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Context of the render (frontend, admin).
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * AbstractFieldType constructor.
	 *
	 * @param array        $field_data Row from geodir_custom_fields table.
	 * @param int|object   $post       Post ID, or GeoDirectory post object with custom fields.
	 * @param string       $context    Context of usage.
	 */
	public function __construct( $field_data, $post = 0, $context = 'frontend' ) {
		$this->field_data = $field_data;

		// Handle different post formats
		if ( is_numeric( $post ) ) {
			// Just a post ID
			$this->post_id = absint( $post );
			$this->post    = $post;
		} elseif ( is_object( $post ) && isset( $post->ID ) ) {
			// Full $gd_post object with custom fields already loaded
			$this->post_id = $post->ID;
			$this->post    = $post;
		} elseif ( is_array( $post ) && isset( $post['ID'] ) ) {
			// Post data as array
			$this->post_id = $post['ID'];
			$this->post    = (object) $post;
		} else {
			$this->post_id = 0;
			$this->post    = null;
		}

		$this->context = $context;
		$this->value   = $this->get_value();
	}

	/**
	 * Get the value for this field.
	 *
	 * Refactored from geodir_get_cf_value().
	 *
	 * @return mixed
	 */
	public function get_value() {
		if ( ! is_null( $this->value ) ) {
			return $this->value;
		}

		$htmlvar_name = $this->field_data['htmlvar_name'];
		$field_type   = $this->field_data['field_type'];
		$default      = isset( $this->field_data['default_value'] ) ? $this->field_data['default_value'] : '';

		// files
		if ( $field_type === 'images' ) {
			return '';
		}

		// Special keys
		if ( $htmlvar_name === 'post_content' ) {
			return get_post_field( 'post_content', $this->post_id );
		} elseif ( $htmlvar_name === 'post_date' ) {
			return get_post_field( 'post_date', $this->post_id );
		} elseif ( $htmlvar_name === 'address' ) {
			return geodir_get_post_meta( $this->post_id, 'street', true );
		}

		// Standard Meta Retrieval
		// NOTE: Assuming geodir_get_post_meta is essentially get_post_meta wrapper
		$value = geodir_get_post_meta( $this->post_id, $htmlvar_name, true );

		// Fallback for auto-drafts
		if ( ( $value === '' || $this->field_data['field_type'] === 'checkbox' ) && $this->is_auto_draft() ) {
			return $default;
		}

		// Blank title for auto drafts
		if ( $htmlvar_name === 'post_title' && $value === __( "Auto Draft" ) ) {
			$value = "";
		}

		return apply_filters( 'geodir_field_get_value', $value, $this->field_data, $this->post_id );
	}

	/**
	 * Prepare standard arguments for AUI input functions.
	 *
	 * Replaces logic from geodir_cfi_input_output().
	 *
	 * @return array
	 */
	protected function get_aui_args() {
		$cf = $this->field_data;

		$args = [
			'id'          => $cf['htmlvar_name'],
			'name'        => $cf['htmlvar_name'],
			'label'       => __( $cf['frontend_title'], 'geodirectory' ),
			'value'       => $this->value,
			'required'    => ! empty( $cf['is_required'] ),
			'placeholder' => esc_html__( isset( $cf['placeholder_value'] ) ? $cf['placeholder_value'] : '', 'geodirectory' ),
			'help_text'   => __( isset( $cf['desc'] ) ? $cf['desc'] : '', 'geodirectory' ),
			'class'       => isset( $cf['css_class'] ) ? $cf['css_class'] : '',
			'label_type'  => 'horizontal', // Default
		];

		// Admin Only Icon - Use the helper function for consistency
		if ( function_exists( 'geodir_cfi_admin_only' ) ) {
			$args['label'] .= geodir_cfi_admin_only( $cf );
		}

		// Required Indicator
		if ( $args['required'] ) {
			$args['label'] .= ' <span class="text-danger">*</span>';
		}

		// Validation
		$extra_attributes = [];
		if ( ! empty( $cf['validation_pattern'] ) ) {
			$args['validation_pattern'] = $cf['validation_pattern'];
			$extra_attributes['pattern'] = $cf['validation_pattern'];
		}

		if ( ! empty( $cf['validation_msg'] ) ) {
			$args['validation_text'] = __( $cf['validation_msg'], 'geodirectory' );
			$args['title']           = $args['validation_text'];
		} elseif ( $args['required'] && ! empty( $cf['required_msg'] ) ) {
			$args['validation_text'] = __( $cf['required_msg'], 'geodirectory' );
		}

		// Field Type for JS validation
		$extra_attributes['field_type'] = $cf['field_type'];

		// Conditional Logic - Use the helper function
		if ( function_exists( 'geodir_conditional_field_attrs' ) ) {
			$args['wrap_attributes'] = geodir_conditional_field_attrs( $cf );
		}

		$args['extra_attributes'] = $extra_attributes;

		return apply_filters( 'geodir_field_aui_args', $args, $cf, $this->post_id );
	}

	/**
	 * Helper to check if current post is auto-draft.
	 */
	protected function is_auto_draft() {
		if ( ! $this->post_id ) return false;
		$post = get_post( $this->post_id );
		return $post && $post->post_status === 'auto-draft';
	}

	/**
	 * Default sanitization.
	 */
	public function sanitize( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Default validation.
	 */
	public function validate( $value ) {
		if ( ! empty( $this->field_data['is_required'] ) && empty( $value ) ) {
			return new \WP_Error( 'required', __( 'This field is required.', 'geodirectory' ) );
		}
		return true;
	}

	/**
	 * Default render_output implementation.
	 *
	 * Child classes should override this with their specific output logic.
	 * For now, returns empty string to prevent fatal errors.
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Default implementation - override in child classes
		// This prevents fatal errors for field types not yet migrated
		return '';
	}

	/**
	 * Helper to parse extra fields (serialized data).
	 *
	 * @return array
	 */
	protected function get_extra_fields() {
		if ( empty( $this->field_data['extra_fields'] ) ) {
			return [];
		}

		// If it's already an array (some DB layers might auto-unserialize), return it.
		if ( is_array( $this->field_data['extra_fields'] ) ) {
			return $this->field_data['extra_fields'];
		}

		return maybe_unserialize( $this->field_data['extra_fields'] );
	}
}
