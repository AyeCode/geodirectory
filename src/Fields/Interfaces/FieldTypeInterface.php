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
