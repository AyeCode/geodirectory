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

            // update term icons on cat update/create
            add_action( 'created_term', array( $this, 'update_term_icons'), 10, 3 );
            add_action( 'edited_term', array( $this, 'update_term_icons'), 10, 3 );
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
            <label for="ct_cat_icon"><?php _e( 'Map Icon', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_icon(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_icon', $taxonomy ); ?>
        
        <div class="form-field term-ct_cat_font_icon-wrap gd-term-form-field">
            <label for="ct_cat_font_icon"><?php _e( 'Category Icon', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_font_icon(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_font_icon', $taxonomy ); ?>

        <div class="form-field term-ct_cat_color-wrap gd-term-form-field">
            <label for="ct_cat_color"><?php _e( 'Category Color', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_color(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_color', $taxonomy ); ?>
        
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
        //print_r(get_term_meta( $term->term_id));
        $cat_top_desc = get_term_meta( $term->term_id, 'ct_cat_top_desc', true );
        $cat_default_img = get_term_meta( $term->term_id, 'ct_cat_default_img', true );
        $cat_icon = get_term_meta( $term->term_id, 'ct_cat_icon', true );
        $cat_font_icon = get_term_meta( $term->term_id, 'ct_cat_font_icon', true );
        $cat_color = get_term_meta( $term->term_id, 'ct_cat_color', true );
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
            <th scope="row"><label for="ct_cat_icon"><?php _e( 'Map Icon', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_icon( $cat_icon ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_icon', $term, $taxonomy ); ?>

        <tr class="form-field term-ct_cat_font_icon-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_font_icon"><?php _e( 'Category Icon', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_font_icon( $cat_font_icon ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_font_icon', $term, $taxonomy ); ?>

        <tr class="form-field term-ct_cat_color-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_color"><?php _e( 'Category Color', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_color( $cat_color ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_color', $term, $taxonomy ); ?>


        <tr class="form-field term-ct_cat_schema-wrap gd-term-form-field ">
            <th scope="row"><label for="ct_cat_schema"><?php _e( 'Schema Type', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_schema( stripslashes( $cat_schema ) ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_bottom', $term, $taxonomy ); ?>
        <?php
    }

    /**
     * Render cat top description.
     *
     * @since 2.0.0
     *
     * @param string $content Optional. Render cat content. Default null.
     * @param string $id Optional. Cat ID. Default ct_cat_top_desc.
     * @param string $name Optional. Cat name. Default null.
     * @return string Description.
     */
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

    /**
     * Get Render category default image html.
     *
     * @since 2.0.0
     *
     * @param array $default_img Optional. Render cat image. Default array().
     * @param string $id Optional. Cat ID. Default ct_cat_default_img.
     * @param string $name Optional. Cat name. Default null.
     * @return string Render default image html.
     */
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

    /**
     * Get render category icon html.
     *
     * @since 2.0.0
     *
     * @param array $cat_icon Optional. Default array.
     * @param string $id Optional. Cat ID. Default ct_cat_icon.
     * @param string $name Optional. Cat name. Default null.
     * @return string Category icon html
     */
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

    /**
     * Get render cat font icon html.
     *
     * @since 2.0.0
     *
     * @param string $cat_icon Optional. Font cat icon. Default null.
     * @param string $id Optional. Category id. Default ct_cat_font_icon.
     * @param string $name Optional. Category name. Default null.
     * @return string Font icon html.
     */
    public function render_cat_font_icon( $cat_icon = '', $id = 'ct_cat_font_icon', $name = '' ) {
        if ( empty( $name ) ) {
            $name = $id;
        }
        ob_start();
        ?>
        <select
            name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
            class="regular-text geodir-select postform"
            data-fa-icons="1"  tabindex="-1" aria-hidden="true"
        >
            <?php
            include_once( dirname( __FILE__ ) . '/settings/data_fontawesome.php' );
            echo "<option value=''>".__('None','geodirectory')."</option>";
            foreach ( geodir_font_awesome_array() as $key => $val ) {
                ?>
                <option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php
                selected( $cat_icon, $key );
                ?>><?php echo $key ?></option>
                <?php
            }
            ?>
        </select>
        <p class="description clear"><?php _e( 'Choose a category icon', 'geodirectory' ); ?></p>
        <?php
        return ob_get_clean();
    }

    /**
     * Get render cat color html.
     *
     * @since 2.0.0
     *
     * @param string $cat_color Optional. Category color. Default null.
     * @param string $id Optional. Category id. Default ct_cat_color.
     * @param string $name Optional. Category name. Default null.
     * @return string cat color html.
     */
    public function render_cat_color( $cat_color = '', $id = 'ct_cat_color', $name = ''  ) {
        if ( empty( $name ) ) {
            $name = $id;
        }

        ob_start();
        ?>
        <input
            name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
            type="text"
            dir="ltr"
            value="<?php echo esc_attr( $cat_color ); ?>"
            class="gd-color-picker"
            placeholder="<?php  ?>"
            data-default-color=""/>
        <p class="description"><?php _e( 'Select the schema to use for this category', 'geodirectory' ); ?></p>
        <?php
        return ob_get_clean();
    }

    /**
     * Get render schema html.
     *
     * @since 2.0.0
     *
     * @param string $cat_schema Optional. Cat schema value. Default null.
     * @param string $id Optional. Cat id. Default ct_cat_schema.
     * @param string $name Optional. Cat name. Default null.
     * @return string Category schema html.
     */
    public function render_cat_schema( $cat_schema = '', $id = 'ct_cat_schema', $name = ''  ) {
        $schemas = self::get_schemas();
        
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
                $cat_default_img['src'] = geodir_file_relative_url( $cat_default_img['src'] );
            } else {
                $cat_default_img = array();
            }
            
            update_term_meta( $term_id, 'ct_cat_default_img', $cat_default_img );
        }
        
        // Categoty icon.
        if ( isset( $_POST['ct_cat_icon'] ) ) {
            $cat_icon = $_POST['ct_cat_icon'];
            
            if ( !empty( $cat_icon['src'] ) ) {
                $cat_icon['src'] = geodir_file_relative_url( $cat_icon['src'] );
            } else {
                $cat_icon = array();
            }
            
            update_term_meta( $term_id, 'ct_cat_icon', $cat_icon );
        }

        // Category font icon.
        if ( isset( $_POST['ct_cat_font_icon'] ) ) {
            update_term_meta( $term_id, 'ct_cat_font_icon', sanitize_text_field( $_POST['ct_cat_font_icon'] ) );
        }

        // Category color.
        if ( isset( $_POST['ct_cat_color'] ) ) {
            update_term_meta( $term_id, 'ct_cat_color', sanitize_text_field( $_POST['ct_cat_color'] ) );
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

    /**
     * Taxonomy walker.
     *
     * @since 2.0.0
     *
     * @param string $cat_taxonomy Category taxonomy.
     * @param int $cat_parent Optional. Category parent ID. Default 0.
     * @param bool $hide_empty Optional. Taxonomy hide empty. Default false.
     * @param int $pading Optional. Pading value . Default 0.
     * @return string Taxonomy walker html.
     */
    public static function taxonomy_walker($cat_taxonomy, $cat_parent = 0, $hide_empty = false, $pading = 0)
    {
        global $cat_display, $post_cat, $exclude_cats;

        $search_terms = trim($post_cat, ",");

        $search_terms = explode(",", $search_terms);

        $cat_terms = get_terms($cat_taxonomy, array('parent' => $cat_parent, 'hide_empty' => $hide_empty, 'exclude' => $exclude_cats));

        $display = '';
        $onchange = '';
        $term_check = '';
        $main_list_class = '';
        $out = '';
        //If there are terms, start displaying
        if (count($cat_terms) > 0) {
            //Displaying as a list
            $p = $pading * 20;
            $pading++;


            if ((!geodir_is_page('listing')) || (is_search() && $_REQUEST['search_taxonomy'] == '')) {
                if ($cat_parent == 0) {
                    $list_class = 'main_list gd-parent-cats-list gd-cats-display-' . $cat_display;
                    $main_list_class = 'class="main_list_selecter"';
                } else {
                    //$display = 'display:none';
                    $list_class = 'sub_list gd-sub-cats-list';
                }
            }

            if ($cat_display == 'checkbox' || $cat_display == 'radio') {
                $p = 0;
                $out = '<div class="' . $list_class . ' gd-cat-row-' . $cat_parent . '" style="margin-left:' . $p . 'px;' . $display . ';">';
            }

            foreach ($cat_terms as $cat_term) {

                $checked = '';

                if (in_array($cat_term->term_id, $search_terms)) {
                    if ($cat_display == 'select' || $cat_display == 'multiselect')
                        $checked = 'selected="selected"';
                    else
                        $checked = 'checked="checked"';
                }

                $child_dash = $p > 0 ? str_repeat("-", $p/20).' ' : '';

                if ($cat_display == 'radio')
                    $out .= '<span style="display:block" ><input type="radio" field_type="radio" name="post_category[]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-cradio="default_category">' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '</span>';
                elseif ($cat_display == 'select' || $cat_display == 'multiselect')
                    $out .= '<option ' . $main_list_class . ' style="margin-left:' . $p . 'px;" alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' >' . $term_check . $child_dash . geodir_utf8_ucfirst($cat_term->name) . '</option>';

                else {
					$class = $checked ? 'class="gd-term-checked"' : '';
                    $out .= '<span style="display:block" ' . $class . '><input style="display:inline-block" type="checkbox" field_type="checkbox" name="post_category[]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-ccheckbox="default_category">' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '<span class="gd-make-default-term" style="display:none" title="' . esc_attr( wp_sprintf( __( 'Make %s default category', 'geodirectory' ), geodir_utf8_ucfirst($cat_term->name) ) ) . '">' . __( 'Make default', 'geodirectory' ). '</span><span class="gd-is-default-term" style="display:none">' . __( 'Default', 'geodirectory' ). '</span></span>';
                }

                // Call recurson to print sub cats
                $out .= self::taxonomy_walker($cat_taxonomy, $cat_term->term_id, $hide_empty, $pading);

            }

            if ($cat_display == 'checkbox' || $cat_display == 'radio')
                $out .= '</div>';

            return $out;
        }
        return '';
    }

    /**
     * Get CPT taxonomy select.
     *
     * Check if $echo is true then select category html echo
     * else return select category html.
     *
     * @since 2.0.0
     *
     * @param string $post_type Optional. Post type. Default null.
     * @param string $selected Optional. Category selected. Default null.
     * @param bool $is_tag Optional. Is tag. Default false.
     * @param bool $echo Optional. Html echo. Default true.
     * @return string
     */
    public static function get_category_select($post_type = '', $selected = '', $is_tag = false, $echo = true){
        $html = '';
        $taxonomies = geodir_get_taxonomies($post_type, $is_tag);

        $categories = get_terms($taxonomies);

        $html .= '<option value="0">' . __('All', 'geodirectory') . '</option>';

        foreach ($categories as $category_obj) {
            $select_opt = '';
            if ($selected == $category_obj->term_id) {
                $select_opt = 'selected="selected"';
            }
            $html .= '<option ' . $select_opt . ' value="' . $category_obj->term_id . '">'
                     . geodir_utf8_ucfirst($category_obj->name) . '</option>';
        }

        if ($echo)
            echo $html;
        else
            return $html;
    }

    /**
     * Return the schemas options as an array.
     * 
     * @return mixed|void
     */
    public static function get_schemas(){
        $schemas = array(
            '' => __( 'Default (LocalBusiness)', 'geodirectory' ),
            'AccountingService' => 'AccountingService',
            'Attorney' => 'Attorney',
            'AutoBodyShop' => 'AutoBodyShop',
            'AutoDealer' => 'AutoDealer',
            'AutoPartsStore' => 'AutoPartsStore',
            'AutoRental' => 'AutoRental',
            'AutoRepair' => 'AutoRepair',
            'AutoWash' => 'AutoWash',
            'Bakery' => 'Bakery',
            'BarOrPub' => 'BarOrPub',
            'BeautySalon' => 'BeautySalon',
            'BedAndBreakfast' => 'BedAndBreakfast',
            'BikeStore' => 'BikeStore',
            'BookStore' => 'BookStore',
            'CafeOrCoffeeShop' => 'CafeOrCoffeeShop',
            'Campground' => 'Campground',
            'ChildCare' => 'ChildCare',
            'ClothingStore' => 'ClothingStore',
            'ComputerStore' => 'ComputerStore',
            'DaySpa' => 'DaySpa',
            'Dentist' => 'Dentist',
            'DryCleaningOrLaundry' => 'DryCleaningOrLaundry',
            'Electrician' => 'Electrician',
            'ElectronicsStore' => 'ElectronicsStore',
            'EmergencyService' => 'EmergencyService',
            'EntertainmentBusiness' => 'EntertainmentBusiness',
            'Event' => 'Event',
            'EventVenue' => 'EventVenue',
            'ExerciseGym' => 'ExerciseGym',
            'FinancialService' => 'FinancialService',
            'Florist' => 'Florist',
            'FoodEstablishment' => 'FoodEstablishment',
            'FurnitureStore' => 'FurnitureStore',
            'GardenStore' => 'GardenStore',
            'GeneralContractor' => 'GeneralContractor',
            'GolfCourse' => 'GolfCourse',
            'HairSalon' => 'HairSalon',
            'HardwareStore' => 'HardwareStore',
            'HealthAndBeautyBusiness' => 'HealthAndBeautyBusiness',
            'HobbyShop' => 'HobbyShop',
            'HomeAndConstructionBusiness' => 'HomeAndConstructionBusiness',
            'HomeGoodsStore' => 'HomeGoodsStore',
            'Hospital' => 'Hospital',
            'Hostel' => 'Hostel',
            'Hotel' => 'Hotel',
            'HousePainter' => 'HousePainter',
            'HVACBusiness' => 'HVACBusiness',
            'InsuranceAgency' => 'InsuranceAgency',
            'JewelryStore' => 'JewelryStore',
            'LiquorStore' => 'LiquorStore',
            'Locksmith' => 'Locksmith',
            'LodgingBusiness' => 'LodgingBusiness',
            'MedicalClinic' => 'MedicalClinic',
            'MensClothingStore' => 'MensClothingStore',
            'MobilePhoneStore' => 'MobilePhoneStore',
            'Motel' => 'Motel',
            'MotorcycleDealer' => 'MotorcycleDealer',
            'MotorcycleRepair' => 'MotorcycleRepair',
            'MovingCompany' => 'MovingCompany',
            'MusicStore' => 'MusicStore',
            'NailSalon' => 'NailSalon',
            'NightClub' => 'NightClub',
            'Notary' => 'Notary',
            'OfficeEquipmentStore' => 'OfficeEquipmentStore',
            'Optician' => 'Optician',
            'PetStore' => 'PetStore',
            'Physician' => 'Physician',
            'Plumber' => 'Plumber',
            'ProfessionalService' => 'ProfessionalService',
            'RealEstateAgent' => 'RealEstateAgent',
            'Residence' => 'Residence',
            'Restaurant' => 'Restaurant',
            'RoofingContractor' => 'RoofingContractor',
            'RVPark' => 'RVPark',
            'School' => 'School',
            'SelfStorage' => 'SelfStorage',
            'ShoeStore' => 'ShoeStore',
            'SkiResort' => 'SkiResort',
            'SportingGoodsStore' => 'SportingGoodsStore',
            'SportsClub' => 'SportsClub',
            'Store' => 'Store',
            'TattooParlor' => 'TattooParlor',
            'Taxi' => 'Taxi',
            'TennisComplex' => 'TennisComplex',
            'TireShop' => 'TireShop',
            'TouristAttraction' => 'TouristAttraction',
            'ToyStore' => 'ToyStore',
            'TravelAgency' => 'TravelAgency',
            //'VacationRentals' => 'VacationRentals', // Not recognised by google yet
            'VeterinaryCare' => 'VeterinaryCare',
            'WholesaleStore' => 'WholesaleStore',
            'Winery' => 'Winery'
        );

        /*
		 * Allows you to add/filter the cat schema types.
		 *
		 * @since 1.5.7
		 */
        return apply_filters( 'geodir_cat_schemas', $schemas );
    }

    /**
     * Get the category top description html.
     *
     * @param int $term_id The term id.
     *
     * @return mixed|void
     */
    public static function get_cat_top_description( $term_id ) {
        $top_description = get_term_meta( $term_id, 'ct_cat_top_desc', true );

        return apply_filters( 'geodir_get_cat_top_description', $top_description, $term_id );
    }

    /**
     * Get the category default image.
     *
     * @param $term_id
     * @param bool $full_path
     *
     * @return mixed|void
     */
    public static function get_cat_image( $term_id, $full_path = false ) {
        $term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );

        $cat_image = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';

        if ( $cat_image && $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
            $cat_image = geodir_file_relative_url( $cat_image, true );
        }

        return apply_filters( 'geodir_get_cat_image', $cat_image, $term_id, $full_path );
    }

    /**
     * Get the category icon url.
     *
     * @param $term_id
     * @param bool $full_path
     * @param bool $default
     *
     * @return mixed|void
     */
    public static function get_cat_icon( $term_id, $full_path = false, $default = false ) {
        $term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );

        $cat_icon = is_array( $term_meta ) && !empty( $term_meta['src'] ) ? $term_meta['src'] : '';

        if ( !$cat_icon && $default ) {
            $cat_icon = geodir_default_marker_icon( $full_path );
        }

        if ( $cat_icon && $full_path ) {
            $cat_icon = geodir_file_relative_url( $cat_icon, true );
        }

        return apply_filters( 'geodir_get_cat_icon', $cat_icon, $term_id, $full_path, $default );
    }


    /**
     * Fires after a new term is created or term updated.
     *
     * @since 2.0.0
     *
     * @param int    $term_id  Term ID.
     * @param int    $tt_id    Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public static function update_term_icons( $term_id, $tt_id, $taxonomy ) {
        if ( geodir_is_gd_taxonomy( $taxonomy ) ) {
            geodir_update_option( 'gd_term_icons', '' ); // Rebuild term icons.
        }
    }

}

new GeoDir_Admin_Taxonomies();
