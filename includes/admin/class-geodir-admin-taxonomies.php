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

        if ( $this->type == 'category' ) {
            // Add fields
            add_action( $this->taxonomy . '_add_form_fields', array( $this, 'add_category_fields' ), 10, 1 );
            add_action( $this->taxonomy . '_edit_form_fields', array( $this, 'edit_category_fields' ), 10, 2 );
            
            // Save fields
            add_action( 'create_term', array( $this, 'save_category_fields' ), 10, 3 );
            add_action( 'edit_term', array( $this, 'save_category_fields' ), 10, 3 );
            
            // Add columns
            add_filter( 'manage_edit-' . $this->taxonomy . '_columns', array( $this, 'get_columns' ) );
            add_filter( 'manage_edit-' . $this->taxonomy . '_sortable_columns', array( $this, 'get_sortable_columns' ), 10, 1 );
            add_filter( 'manage_' . $this->taxonomy . '_custom_column', array( $this, 'get_column' ), 10, 3 );
        }
    }

    /**
     * Add fields to add category form.
     *
     * @since 2.0.0
     *
     * @param string $taxonomy Current taxonomy slug.
     */
    public function add_category_fields( $taxonomy ) {
        ?>
        <?php do_action( 'geodir_add_category_top', $taxonomy ); ?>
        <div class="form-field term-ct_cat_top_desc-wrap gd-term-form-field">
            <label for="ct_cat_top_desc"><?php _e( 'Category Top Description', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_top_desc(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_top_desc', $taxonomy ); ?>
        <div class="form-field term-ct_cat_default_img-wrap gd-term-form-field">
            <label for="ct_cat_default_img"><?php _e( 'Default Listing Image', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_default_img(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_default_img', $taxonomy ); ?>
        <div class="form-field term-ct_cat_icon-wrap gd-term-form-field form-required">
            <label for="ct_cat_icon"><?php _e( 'Category Icon', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_icon(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_icon', $taxonomy ); ?>
        <div class="form-field term-ct_cat_schema-wrap gd-term-form-field ">
            <label for="ct_cat_schema"><?php _e( 'Schema Type', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_schema(); ?>
        </div>
        <?php do_action( 'geodir_add_category_bottom', $taxonomy ); ?>
        <?php
    }

    /**
     * Add fields to edit category form.
     *
     * @since 2.0.0
     *
     * @param object $term     Current taxonomy term object.
     * @param string $taxonomy Current taxonomy slug.
     */
    public function edit_category_fields( $term, $taxonomy ) {
        $cat_top_desc = get_term_meta( $term->term_id, 'ct_cat_top_desc', true );
        $cat_default_img = get_term_meta( $term->term_id, 'ct_cat_default_img', true );
        $cat_icon = get_term_meta( $term->term_id, 'ct_cat_icon', true );
        $cat_schema = get_term_meta( $term->term_id, 'ct_cat_schema', true );
        if ( !empty( $cat_default_img['id'] ) ) {
            $cat_default_img['full'] = geodir_get_cat_image( $term->term_id, true );
        }
        if ( !empty( $cat_icon['id'] ) ) {
            $cat_icon['full'] = geodir_get_cat_icon( $term->term_id, true );
        }
        ?>
        <?php do_action( 'geodir_edit_category_top', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_top_desc-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_top_desc"><?php _e( 'Category Top Description', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_top_desc( $cat_top_desc ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_top_desc', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_default_img-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_default_img"><?php _e( 'Default Listing Image', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_default_img( $cat_default_img ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_default_img', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_icon-wrap gd-term-form-field form-required">
            <th scope="row"><label for="ct_cat_icon"><?php _e( 'Category Icon', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_icon( $cat_icon ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_icon', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_schema-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_schema"><?php _e( 'Schema Type', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_schema( stripslashes( $cat_schema ) ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_bottom', $term, $taxonomy ); ?>
        <?php
    }
    
    public function render_cat_top_desc( $content = '', $id = 'ct_cat_top_desc', $name = '' ) {
        if ( empty( $name ) ) {
            $name = $id;
        }
        
        $settings = apply_filters( 'geodir_cat_top_desc_editor_settings', array( 'editor_height' => 150, 'textarea_rows' => 5, 'textarea_name' => $name ), $content, $id, $name );
        
        ob_start();
        wp_editor( $content, $id, $settings );
        ?><p class="description"><?php _e( 'This will appear at the top of the category listing.', 'geodirectory' ); ?></p><?php
        return ob_get_clean();
    }
    
    public function render_cat_default_img( $default_img = array(), $id = 'ct_cat_default_img', $name = '' ) {
        if ( empty( $name ) ) {
            $name = $id;
        }
        
        $img_id = !empty( $default_img['id'] ) ? $default_img['id'] : '';
        $img_src = !empty( $default_img['src'] ) ? $default_img['src'] : '';
        $show_img = !empty( $default_img['full'] ) ? $default_img['full'] : admin_url( 'images/media-button-image.gif' );
         
        ob_start();
        ?>
        <div class="gd-upload-img" data-field="<?php echo $name; ?>">
            <div class="gd-upload-display thumbnail"><div class="centered"><img src="<?php echo $show_img; ?>" /></div></div>
            <div class="gd-upload-fields">
                <input type="hidden" id="<?php echo $id; ?>[id]" name="<?php echo $name; ?>[id]" value="<?php echo $img_id; ?>" />
                <input type="hidden" id="<?php echo $id; ?>[src]" name="<?php echo $name; ?>[src]" value="<?php echo $img_src; ?>" />
                <button type="button" class="gd_upload_image_button button"><?php _e( 'Upload Image', 'geodirectory' ); ?></button>
                <button type="button" class="gd_remove_image_button button"><?php _e( 'Remove Image', 'geodirectory' ); ?></button>
            </div>
        </div>
        <p class="description clear"><?php _e( 'Choose a default image for the listing within this category.', 'geodirectory' ); ?></p>
        <?php
        return ob_get_clean();
    }
    
    public function render_cat_icon( $cat_icon = array(), $id = 'ct_cat_icon', $name = '' ) {
        if ( empty( $name ) ) {
            $name = $id;
        }
        
        $img_id = !empty( $cat_icon['id'] ) ? $cat_icon['id'] : '';
        $img_src = !empty( $cat_icon['src'] ) ? $cat_icon['src'] : '';
        $show_img = !empty( $cat_icon['full'] ) ? $cat_icon['full'] : admin_url( 'images/media-button-image.gif' );
         
        ob_start();
        ?>
        <div class="gd-upload-img" data-field="<?php echo $name; ?>">
            <div class="gd-upload-display thumbnail"><div class="centered"><img src="<?php echo $show_img; ?>" /></div></div>
            <div class="gd-upload-fields">
                <input type="hidden" id="<?php echo $id; ?>[id]" name="<?php echo $name; ?>[id]" value="<?php echo $img_id; ?>" />
                <input type="text" id="<?php echo $id; ?>[src]" name="<?php echo $name; ?>[src]" value="<?php echo $img_src; ?>" required style="position:absolute;left:-500px;width:50px;" />
                <button type="button" class="gd_upload_image_button button"><?php _e( 'Upload Icon', 'geodirectory' ); ?></button>
                <button type="button" class="gd_remove_image_button button"><?php _e( 'Remove Icon', 'geodirectory' ); ?></button>
            </div>
        </div>
        <p class="description clear"><?php _e( 'Choose a category icon', 'geodirectory' ); ?></p>
        <?php
        return ob_get_clean();
    }
    
    public function render_cat_schema( $cat_schema = '', $id = 'ct_cat_schema', $name = ''  ) {
        $schemas = geodir_get_cat_schemas();
        
        if ( empty( $name ) ) {
            $name = $id;
        }
        
        ob_start();
        ?>
        <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" class="postform">
            <?php foreach ( $schemas as $value => $label ) { ?>
            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $cat_schema == $value, true ); ?>><?php echo $label; ?></option>
            <?php } ?>
        </select>
        <p class="description"><?php _e( 'Select the schema to use for this category', 'geodirectory' ); ?></p>
        <?php
        return ob_get_clean();
    }

    /**
     * Save the category fields.
     *
     * @since 2.0.0
     *
     * @param int    $term_id  Term ID.
     * @param int    $tt_id    Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public function save_category_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
        // Category top description.
        if ( isset( $_POST['ct_cat_top_desc'] ) ) {
            update_term_meta( $term_id, 'ct_cat_top_desc', $_POST['ct_cat_top_desc'] );
        }
        
        // Categoty listing default image.
        if ( isset( $_POST['ct_cat_default_img'] ) ) {
            $cat_default_img = $_POST['ct_cat_default_img'];
            
            if ( !empty( $cat_default_img['src'] ) ) {
                $cat_default_img['src'] = geodir_clean_upload_baseurl( $cat_default_img['src'] );
            } else {
                $cat_default_img = array();
            }
            
            update_term_meta( $term_id, 'ct_cat_default_img', $cat_default_img );
        }
        
        // Categoty icon.
        if ( isset( $_POST['ct_cat_icon'] ) ) {
            $cat_icon = $_POST['ct_cat_icon'];
            
            if ( !empty( $cat_icon['src'] ) ) {
                $cat_icon['src'] = geodir_clean_upload_baseurl( $cat_icon['src'] );
            } else {
                $cat_icon = array();
            }
            
            update_term_meta( $term_id, 'ct_cat_icon', $cat_icon );
        }
        
        // Category schema.
        if ( isset( $_POST['ct_cat_schema'] ) ) {
            update_term_meta( $term_id, 'ct_cat_schema', sanitize_text_field( $_POST['ct_cat_schema'] ) );
        }
        
        do_action( 'geodir_term_save_category_fields', $term_id, $tt_id, $taxonomy );
    }

    /**
     * Custom columns added to category admin.
     *
     * @param mixed $columns
     * @return array
     */
    public function get_columns( $columns ) {
        if ( isset( $columns['description'] ) ) {
            unset( $columns['description'] );
        }

        $columns['cat_icon'] = 'Icon';
        $columns['cat_default_img'] = __('Default Image', 'geodirectory');
        $columns['cat_ID_num'] = __('Cat ID', 'geodirectory');

        return $columns;
    }
    
    /**
     * Get sortable columns.
     *
     * @param mixed $columns
     * @return array
     */
    public function get_sortable_columns( $columns ) {
        $columns['cat_ID_num'] = 'term_id';
        
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
    public function get_column( $columns, $column, $id ) {
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
