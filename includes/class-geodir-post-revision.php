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
class GeoDir_Post_Revision {

	/**
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

		//add_filter('_wp_post_revision_fields', array( __CLASS__, 'revision_fields' ), 10, 2 );

		//add_filter('_wp_post_revision_fields', 'cfr_fields', 10, 1);
		//add_filter("_wp_post_revision_field_custom_fields", "cfr_field", 10, 3);



	}

	public static function revision_fields($fields,$post){

		if(isset($post['post_type']) && geodir_is_gd_post_type($post['post_type'])){


			$custom_fields = GeoDir_Settings_Cpt_Cf::get_cpt_custom_fields( $post['post_type'] );
			foreach ( $custom_fields as $cf ) {

				if ( isset( $cf->htmlvar_name ) ) {
					$fields[$cf->htmlvar_name] = $cf->admin_title;
					add_filter("_wp_post_revision_field_{$cf->htmlvar_name}", array( __CLASS__,"field_content"), 10, 4);
				}

			}







//			$fields["custom_fields"] = "Custom Fields";
//			add_filter("_wp_post_revision_field_custom_fields", array( __CLASS__,"field_content"), 10, 4);

//
//			$post_meta = geodir_get_post_meta($po)

		}
		return $fields;
	}

	public static function add_revision_field(){

	}


	/**
	 * Creates text format from custom fields
	 * @param $value
	 * @param $field
	 * @param $revision
	 * @return string
	 */
	public static function field_content($value, $field, $post,$context)
	{

		//echo $post->ID.'###'.$context." \n";
		//print_r($post);
//		$post_type = isset($post->post_parent) && !empty($post->post_parent) ? get_post_type( $post->post_parent ) : $post->post_type;
//		if(isset($post_type) && geodir_is_gd_post_type($post_type)){
//			//$gd_post = geodir_get_post_info( $post->ID );
//
//			$value = '###';
//		}

		if($context=='from'){
			$value  = "from".$post->ID;
		}else{
			$value  = "to".$post->ID;

		}
		return $value;
	}

}