<?php
namespace AyeCode\GeoDirectory\Fields;

use AyeCode\GeoDirectory\Fields\Types;

class FieldRegistry {

	protected $fields = [];

	public function __construct() {
		// Core Field Registrations
		$this->register( 'text', Types\TextField::class );
		$this->register( 'email', Types\TextField::class ); // TextField handles email
		$this->register( 'url', Types\TextField::class );   // TextField handles url
		$this->register( 'phone', Types\TextField::class ); // TextField handles phone

		$this->register( 'datepicker', Types\DatepickerField::class );


		$this->register( 'textarea', Types\TextareaField::class );
		$this->register( 'html', Types\TextareaField::class );

		$this->register( 'select', Types\SelectField::class );
		$this->register( 'multiselect', Types\MultiselectField::class );
		$this->register( 'checkbox', Types\CheckboxField::class );
		$this->register( 'radio', Types\RadioField::class );

		$this->register( 'address', Types\AddressField::class );
		$this->register( 'business_hours', Types\BusinessHoursField::class );

		// File uploads (images/files)
		$this->register( 'file', Types\UploadField::class );
		$this->register( 'images', Types\UploadField::class );

		// Fieldset
		$this->register( 'fieldset', Types\FieldsetField::class );

		// Categories
		$this->register( 'categories', Types\TaxonomyField::class );

		// Tags
		$this->register( 'tags', Types\TagsField::class );

		// Allow addons to register their own fields
		do_action( 'geodirectory/fields/register', $this );
	}

	public function register( $type, $class ) {
		$this->fields[ $type ] = $class;
	}

	public function get( $type ) {
		return isset( $this->fields[ $type ] ) ? $this->fields[ $type ] : null;
	}
}
