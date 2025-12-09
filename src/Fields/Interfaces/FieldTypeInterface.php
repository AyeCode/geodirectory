<?php
namespace AyeCode\GeoDirectory\Fields\Interfaces;

/**
 * Interface FieldTypeInterface
 *
 * Contract for all GeoDirectory custom field types.
 *
 * @package AyeCode\GeoDirectory\Fields\Interfaces
 */
interface FieldTypeInterface {

	/**
	 * Render the input HTML for the field.
	 *
	 * @return string
	 */
	public function render_input();

	/**
	 * Render the output HTML for displaying the field value on frontend.
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display (icon, label, value, raw, strip, etc).
	 *                              - 'location' (string): Output location (detail, listing, etc).
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] );

	/**
	 * Sanitize the value before saving to DB.
	 *
	 * @param mixed $value The submitted value.
	 * @return mixed
	 */
	public function sanitize( $value );

	/**
	 * Validate the value.
	 *
	 * @param mixed $value The submitted value.
	 * @return bool|\WP_Error True if valid, WP_Error on failure.
	 */
	public function validate( $value );
}
