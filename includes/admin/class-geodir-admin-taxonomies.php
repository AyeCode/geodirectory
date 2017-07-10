<?php
/**
 * Handles taxonomies in admin
 *
 * @class    GeoDir_Admin_Taxonomies
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Admin_Taxonomies class.
 */
class GeoDir_Admin_Taxonomies {
    /**
     * Taxonomy.
     *
     * @var string
     * @access private
     * @since 2.0.0
     */
    private $taxonomy = '';
    
    /**
     * Taxonomy type.
     *
     * @var string
     * @access private
     * @since 2.0.0
     */
    private $type = '';

    /**
     * Constructor.
     */
    public function __construct() {
        if ( empty( $_REQUEST['taxonomy'] ) ) {
            return;
        }
        
        $this->taxonomy = $_REQUEST['taxonomy'];
        
        if ( !geodir_is_gd_taxonomy( $this->taxonomy ) ) {
            return;
        }
        
        $this->type = geodir_taxonomy_type( $this->taxonomy );

        // Add columns
        if ( $this->type == 'category' ) {
            add_filter( 'manage_edit-' . $this->taxonomy . '_columns', array( $this, 'taxonomy_columns' ) );
            add_filter( 'manage_' . $this->taxonomy . '_custom_column', array( $this, 'custom_column' ), 10, 3 );
        }
    }

    /**
     * Category thumbnail fields.
     */
    public function add_category_fields() {
    }

    /**
     * Edit category thumbnail field.
     *
     * @param mixed $term Term (category) being edited
     */
    public function edit_category_fields( $term ) {
    }

    /**
     * save_category_fields function.
     *
     * @param mixed $term_id Term ID being saved
     * @param mixed $tt_id
     * @param string $taxonomy
     */
    public function save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
    }

    /**
     * Custom columns added to category admin.
     *
     * @param mixed $columns
     * @return array
     */
    public function taxonomy_columns( $columns ) {
        if ( isset( $columns['description'] ) ) {
            unset( $columns['description'] );
        }

        $columns['cat_icon'] = 'Icon';
        $columns['cat_default_img'] = __('Default Image', 'geodirectory');
        $columns['cat_ID_num'] = __('Cat ID', 'geodirectory');

        return $columns;
    }

    /**
     * Custom column value added to category admin.
     *
     * @param string $columns
     * @param string $column
     * @param int $id
     * @return array
     */
    public function custom_column( $columns, $column, $id ) {
        if ( $column == 'cat_ID_num' ) {
            $columns .= $id;
        }
        
        if ( $column == 'cat_icon' && $icon = geodir_get_cat_icon( $id, true ) ) {
            $columns .= '<img src="' . esc_url( $icon ) . '" />';
        }

        if ( $column == 'cat_default_img' && $image = geodir_get_cat_image( $id, true ) ) {
            $columns .= '<img src="' . esc_url( $image ) . '" style="max-height:60px;max-width:60px;" />';
        }

        return $columns;
    }
}

new GeoDir_Admin_Taxonomies();
