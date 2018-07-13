<?php
/**
 * Frontend Taxonomies
 *
 * @class     GeoDir_Taxonomies
 * @since     2.0.0
 * @package   GeoDirectory
 * @category  Class
 * @author    AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Permalinks Class.
 */
class GeoDir_Taxonomies {

	public function __construct() {
		

	}


	/**
	 * Get all custom taxonomies.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @param string $post_type The post type.
	 * @param bool $tages_taxonomies Is this a tag taxonomy?. Default: false.
	 * @return array|bool Taxonomies on success. false on failure.
	 */
	public static function get_taxonomies($post_type = '', $tages_taxonomies = false)
	{

		$taxonomies = array();
		$gd_taxonomies = array();

		if ($taxonomies = geodir_get_option('taxonomies')) {


			$gd_taxonomies = array_keys($taxonomies);


			if ($post_type != '')
				$gd_taxonomies = array();

			$i = 0;
			foreach ($taxonomies as $taxonomy => $args) {

				if ($post_type != '' && $args['object_type'] == $post_type)
					$gd_taxonomies[] = $taxonomy;

				if ($tages_taxonomies === false && strpos($taxonomy, '_tag') !== false) {
					if (array_search($taxonomy, $gd_taxonomies) !== false)
						unset($gd_taxonomies[array_search($taxonomy, $gd_taxonomies)]);
				}

			}

			$gd_taxonomies = array_values($gd_taxonomies);
		}

		/**
		 * Filter the taxonomies.
		 *
		 * @since 1.0.0
		 * @param array $gd_taxonomies The taxonomy array.
		 */
		$taxonomies = apply_filters('geodir_taxonomy', $gd_taxonomies);

		if (!empty($taxonomies)) {
			return $taxonomies;
		} else {
			return false;
		}
	}

}


