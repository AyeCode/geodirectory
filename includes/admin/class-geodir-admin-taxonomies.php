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


        // update term slug in details table when changed
        add_action('edited_term', array( $this, 'update_term_slug'), 1, 3);
        add_action('create_term', array( $this, 'update_term_slug'), 1, 3);

		// Update post data when term deleted
		add_action( 'delete_term', array( $this, 'on_delete_term' ), 0, 5 );

        // filter function to check if term slug exists
        add_filter( 'geodir_term_slug_is_exists', array( $this,'term_slug_is_exists'), 0, 3);
        add_filter( 'geodir_term_slug_is_exists', array( $this,'check_term_to_post_slug'), 10, 3 );

        // make sure post slug is unique
        add_filter( 'wp_unique_post_slug', array( $this,'check_post_to_term_slug'), 101, 6 );

        if ( empty( $_REQUEST['taxonomy'] ) ) {
            return;
        }

        $this->taxonomy = esc_attr( $_REQUEST['taxonomy'] );

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
     * Filters the unique post slug.
     *
     * @since 1.6.20
     *
     * @global object $wpdb WordPress Database object.
     *
     * @param string $slug          The post slug.
     * @param int    $post_ID       Post ID.
     * @param string $post_status   The post status.
     * @param string $post_type     Post type.
     * @param int    $post_parent   Post parent ID
     * @param string $original_slug The original post slug.
     */
    public function check_post_to_term_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
        global $wpdb;

        if ( $post_type && strpos( $post_type, 'gd_' ) === 0 ) {
            $posts_join = apply_filters( 'geodir_unique_post_slug_posts_join', "", $post_ID, $post_type );
            $posts_where = apply_filters( 'geodir_unique_post_slug_posts_where', "", $post_ID, $post_type );
            $terms_join = apply_filters( 'geodir_unique_post_slug_terms_join', "", $post_ID, $post_type );
            $terms_where = apply_filters( 'geodir_unique_post_slug_terms_where', "", $post_ID, $post_type );

            $term_slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT t.slug FROM $wpdb->terms AS t LEFT JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id {$terms_join} WHERE t.slug = '%s' AND ( tt.taxonomy = '" . $post_type . "category' OR tt.taxonomy = '" . $post_type . "_tags' ) {$terms_where} LIMIT 1", $slug ) );

            if ( $term_slug_check ) {
                $suffix = 1;

                do {
                    $alt_slug = _truncate_post_slug( $original_slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";

                    $term_check = $wpdb->get_var( $wpdb->prepare( "SELECT t.slug FROM $wpdb->terms AS t LEFT JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id {$terms_join} WHERE t.slug = '%s' AND ( tt.taxonomy = '" . $post_type . "category' OR tt.taxonomy = '" . $post_type . "_tags' ) {$terms_where} LIMIT 1", $alt_slug ) );

                    $post_check = !$term_check && $wpdb->get_var( $wpdb->prepare( "SELECT p.post_name FROM $wpdb->posts p {$posts_join} WHERE p.post_name = %s AND p.post_type = %s AND p.ID != %d {$posts_where} LIMIT 1", $alt_slug, $post_type, $post_ID ) );

                    $term_slug_check = $term_check || $post_check;

                    $suffix++;
                } while ( $term_slug_check );

                $slug = $alt_slug;
            }
        }

        return $slug;
    }

    /**
     * Check whether a post name with slug exists or not.
     *
     * @since 1.6.20
     *
     * @global object $wpdb WordPress Database object.
     * @global array $gd_term_post_type Cached array for term post type.
     * @global array $gd_term_taxonomy Cached array for term taxonomy.
     *
     * @param bool $slug_exists Default: false.
     * @param string $slug Term slug.
     * @param int $term_id The term ID.
     * @return bool true when exists. false when not exists.
     */
    public function check_term_to_post_slug( $slug_exists, $slug, $term_id ) {
        global $wpdb, $gd_term_post_type, $gd_term_taxonomy;

        if ( $slug_exists ) {
            return $slug_exists;
        }

        if ( !empty( $gd_term_taxonomy ) && isset($gd_term_taxonomy[$term_id]) ) {
            $taxonomy = $gd_term_taxonomy[$term_id];
        } else {
            $taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $term_id ) );
            $gd_term_taxonomy[$term_id] = $taxonomy;
        }

        if ( empty($taxonomy) ) {
            return $slug_exists;
        }

        if ( !empty( $gd_term_post_type ) && $gd_term_post_type[$term_id] ) {
            $post_type = $gd_term_post_type[$term_id];
        } else {
            $taxonomy_obj = get_taxonomy( $taxonomy );
            $post_type = !empty( $taxonomy_obj->object_type ) ? $taxonomy_obj->object_type[0] : NULL;
        }

        $posts_join = apply_filters( 'geodir_unique_term_slug_posts_join', "", $term_id, $taxonomy, $post_type );
        $posts_where = apply_filters( 'geodir_unique_term_slug_posts_where', "", $term_id, $taxonomy, $post_type );

        if ( $post_type && $wpdb->get_var( $wpdb->prepare( "SELECT p.post_name FROM $wpdb->posts p {$posts_join} WHERE p.post_name = %s AND p.post_type = %s {$posts_where} LIMIT 1", $slug, $post_type ) ) ) {
            $slug_exists = true;
        }

        return $slug_exists;
    }


    /**
     * Check whether a term slug exists or not.
     *
     * @since 1.0.0
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $table_prefix WordPress Database Table prefix.
     * @param bool $slug_exists Default: false.
     * @param string $slug Term slug.
     * @param int $term_id The term ID.
     * @return bool true when exists. false when not exists.
     */
    public function term_slug_is_exists( $slug_exists, $slug, $term_id ) {
        global $wpdb, $table_prefix, $geodirectory;

        if ( $slug_exists ) {
            return $slug_exists;
        }

        $default_location = $geodirectory->location->get_default_location();

        $country_slug = $default_location->country_slug;
        $region_slug = $default_location->region_slug;
        $city_slug = $default_location->city_slug;

        if ( $country_slug == $slug || $region_slug == $slug || $city_slug == $slug ) {
            return true;
        }

        // No longer required as we have category & tags slug now.
        //if ($wpdb->get_var($wpdb->prepare("SELECT slug FROM " . $table_prefix . "terms WHERE slug=%s AND term_id != %d", array($slug, $term_id))))
            //return $slug_exists = true;

        return $slug_exists;
    }

    /**
     * Update term slug.
     *
     * @since 1.0.0
     * @since 1.5.3 Modified to update tag in detail table when tag updated.
     * @package GeoDirectory
     * @global object $wpdb WordPress Database object.
     * @global string $plugin_prefix Geodirectory plugin table prefix.
     * @global string $table_prefix WordPress Database Table prefix.
     * @param int|string $term_id The term ID.
     * @param int $tt_id term Taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public function update_term_slug( $term_id, $tt_id, $taxonomy ) {
        global $wpdb, $plugin_prefix, $table_prefix;

        if ( ! geodir_is_gd_taxonomy( $taxonomy ) ) {
            return;
        }

        $tern_data = get_term_by( 'id', $term_id, $taxonomy );
        $slug = $tern_data->slug;

        /**
         * Filter if a term slug exists.
         *
         * @since 1.0.0
         * @package GeoDirectory
         * @param bool $bool Default: false.
         * @param string $slug The term slug.
         * @param int $term_id The term ID.
         */
        $slug_exists = apply_filters( 'geodir_term_slug_is_exists', false, $slug, $term_id );

        if ( $slug_exists ) {
            $suffix = 1;

            do {
                $new_slug = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-$suffix";

                /** This action is documented in geodirectory_hooks_actions.php */
                $term_slug_check = apply_filters( 'geodir_term_slug_is_exists', false, $new_slug, $term_id );

                $suffix++;
            } while ( $term_slug_check && $suffix < 100 );

            $slug = $new_slug;

            $wpdb->query( $wpdb->prepare( "UPDATE " . $wpdb->terms . " SET slug = %s WHERE term_id = %d", array( $slug, $term_id ) ) );
        }

        // Update tag in detail table.
        if ( geodir_taxonomy_type( $taxonomy ) == 'tag' && geodir_is_gd_taxonomy( $taxonomy ) ) {
            $posts = $wpdb->get_results( $wpdb->prepare( "SELECT object_id FROM " . $wpdb->term_relationships . " WHERE term_taxonomy_id = %d", array( $tt_id ) ) );

            if ( ! empty( $posts ) ) {
                $post_type = substr( $taxonomy, 0, strlen( $taxonomy ) - 5 );

                foreach ( $posts as $_post ) {
                    $post_id = $_post->object_id;

                    $object_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
                    $post_tags = ! empty( $object_terms ) ? implode( ',', $object_terms ) : '';

                    $table = geodir_db_cpt_table( $post_type );
                    $wpdb->query( $wpdb->prepare( "UPDATE `{$table}` SET post_tags = %s WHERE post_id = %d", array( $post_tags, $post_id ) ) );
                }
            }
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
        <div class="form-field term-ct_cat_bottom_desc-wrap gd-term-form-field">
            <label for="ct_cat_bottom_desc"><?php _e( 'Category Bottom Description', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_bottom_desc(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_bottom_desc', $taxonomy ); ?>
        <div class="form-field term-ct_cat_default_img-wrap gd-term-form-field">
            <label for="ct_cat_default_img"><?php _e( 'Default Listing Image', 'geodirectory' ); ?></label>
            <?php echo $this->render_cat_default_img(); ?>
        </div>
        <?php do_action( 'geodir_add_category_after_cat_default_img', $taxonomy ); ?>
        <div class="form-field term-ct_cat_icon-wrap gd-term-form-field">
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
        $cat_top_desc = get_term_meta( $term->term_id, 'ct_cat_top_desc', true );
        $cat_bottom_desc = get_term_meta( $term->term_id, 'ct_cat_bottom_desc', true );
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
        <tr class="form-field term-ct_cat_bottom_desc-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_bottom_desc"><?php _e( 'Category Bottom Description', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_bottom_desc( $cat_bottom_desc ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_bottom_desc', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_default_img-wrap gd-term-form-field">
            <th scope="row"><label for="ct_cat_default_img"><?php _e( 'Default Listing Image', 'geodirectory' ); ?></label></th>
            <td><?php echo $this->render_cat_default_img( $cat_default_img ); ?></td>
        </tr>
        <?php do_action( 'geodir_edit_category_after_cat_default_img', $term, $taxonomy ); ?>
        <tr class="form-field term-ct_cat_icon-wrap gd-term-form-field">
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

        $height = ! empty( $_REQUEST['tag_ID'] ) ? 150 : 100;

        $settings = apply_filters( 'geodir_cat_top_desc_editor_settings', array( 'editor_height' => $height, 'textarea_rows' => 5, 'textarea_name' => $name, 'wpautop' => false ), $content, $id, $name );

        ob_start();
        wp_editor( $content, $id, $settings );
        ?><p class="description"><?php _e( 'This will appear at the top of the category listings.', 'geodirectory' ); ?></p><?php
        return ob_get_clean();
    }

    /**
     * Render category bottom description.
     *
     * @since 2.2.19
     *
     * @param string $content Optional. Render cat content. Default null.
     * @param string $id Optional. Cat ID. Default ct_cat_bottom_desc.
     * @param string $name Optional. Cat name. Default null.
     * @return string Description.
     */
    public function render_cat_bottom_desc( $content = '', $id = 'ct_cat_bottom_desc', $name = '' ) {
        if ( empty( $name ) ) {
            $name = $id;
        }

        $height = ! empty( $_REQUEST['tag_ID'] ) ? 150 : 100;

        $settings = apply_filters( 'geodir_cat_bottom_desc_editor_settings', array( 'editor_height' => $height, 'textarea_rows' => 5, 'textarea_name' => $name, 'wpautop' => false ), $content, $id, $name );

        ob_start();
        wp_editor( $content, $id, $settings );
        ?><p class="description"><?php _e( 'This will appear at the bottom of the category listings.', 'geodirectory' ); ?></p><?php
        if ( ! empty( $_REQUEST['tag_ID'] ) ) { ?><div class="description wrap geodirectory" style="margin-bottom:0"><?php _e( 'Available Tags:.', 'geodirectory' ); ?> <?php echo GeoDir_SEO::helper_tags( 'location_tags' ); ?></div><?php }
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
        <div class="gd-upload-img" data-field="<?php echo esc_attr( $name ); ?>">
            <div class="gd-upload-display thumbnail"><div class="centered"><img src="<?php echo esc_url( $show_img ); ?>" /></div></div>
            <div class="gd-upload-fields">
                <input type="hidden" id="<?php echo esc_attr( $id ); ?>[id]" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $img_id ); ?>" />
                <input type="hidden" id="<?php echo esc_attr( $id ); ?>[src]" name="<?php echo esc_attr( $name ); ?>[src]" value="<?php echo esc_attr( $img_src ); ?>" />
                <button type="button" class="gd_upload_image_button button"><?php _e( 'Select Image', 'geodirectory' ); ?></button>
                <button type="button" class="gd_remove_image_button button"><?php _e( 'Remove Image', 'geodirectory' ); ?></button>
            </div>
        </div>
        <p class="description clear"><?php _e( 'Select a default image for the listing within this category.', 'geodirectory' ); ?></p>
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
        $show_img = !empty( $cat_icon['full'] ) ? $cat_icon['full'] : geodir_plugin_url() . '/assets/images/media-button-image.gif';

        ob_start();
        ?>
        <div class="gd-upload-img" data-field="<?php echo esc_attr( $name ); ?>">
            <div class="gd-upload-display thumbnail"><div class="centered"><img src="<?php echo esc_url( $show_img ); ?>" /></div></div>
            <div class="gd-upload-fields">
                <input type="hidden" id="<?php echo esc_attr( $id ); ?>[id]" name="<?php echo esc_attr( $name ); ?>[id]" value="<?php echo esc_attr( $img_id ); ?>" />
                <input type="text" id="<?php echo esc_attr( $id ); ?>[src]" name="<?php echo esc_attr( $name ); ?>[src]" value="<?php echo esc_attr( $img_src ); ?>" style="position:absolute;left:-500px;width:50px;" />
                <button type="button" class="gd_upload_image_button button"><?php _e( 'Select Icon', 'geodirectory' ); ?></button>
                <button type="button" class="gd_remove_image_button button"><?php _e( 'Remove Icon', 'geodirectory' ); ?></button>
            </div>
        </div>
        <p class="description clear"><?php _e( 'Select a map icon, or pick a FontAwesome icon and color below to have one auto-generated. (if this field is empty)', 'geodirectory' ); ?></p>
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

		if ( geodir_design_style() ) {
			echo aui()->input(
				array(
					'type'              =>  'iconpicker',
					'id'                => $id,
					'name'              => $name,
					'label_col'        => '3',
					'label_class'=> 'font-weight-bold fw-bold',
					'wrap_class'        =>  'bsui',
					'value'         => $cat_icon,
					'extra_attributes' => defined('FAS_PRO') && FAS_PRO ? array(
						'data-fa-icons'   => true,
						'data-bs-toggle'  => "tooltip",
						'data-bs-trigger' => "focus",
						'title'           => __('For pro icon variants (light, thin, duotone), paste the class here','geodirectory'),
					) : array(),
				)
			);
		}else{
        ?>
        <select
            name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>"
            class="regular-text geodir-select postform"
            data-fa-icons="1"  tabindex="-1" aria-hidden="true"
        >
            <?php
            if ( ! function_exists( 'geodir_font_awesome_array' ) ) {
                include_once( dirname( __FILE__ ) . '/settings/data_fontawesome.php' );
            }
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
		<?php } ?>
        <p class="description clear"><?php _e( 'Select a category icon', 'geodirectory' ); ?></p>
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
        <p class="description"><?php _e( 'Select the color to use for this category', 'geodirectory' ); ?></p>
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
        <select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" class="postform geodir-select">
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

        // Category bottom description.
        if ( isset( $_POST['ct_cat_bottom_desc'] ) ) {
            update_term_meta( $term_id, 'ct_cat_bottom_desc', $_POST['ct_cat_bottom_desc'] );
        }

        // Category listing default image.
        if ( isset( $_POST['ct_cat_default_img'] ) ) {
            $cat_default_img = $_POST['ct_cat_default_img'];

            if ( !empty( $cat_default_img['src'] ) ) {
                $cat_default_img['src'] = geodir_file_relative_url( sanitize_text_field( $cat_default_img['src'] ) );
            } else {
                $cat_default_img = array();
            }

            update_term_meta( $term_id, 'ct_cat_default_img', $cat_default_img );
        }

        // Category icon.
        if ( isset( $_POST['ct_cat_icon'] ) ) {
            $cat_icon = $_POST['ct_cat_icon'];

            if ( !empty( $cat_icon['src'] ) ) {
                $cat_icon['src'] = geodir_file_relative_url( sanitize_text_field( $cat_icon['src'] ) );
            } elseif(!empty($_POST['ct_cat_font_icon'])) {
                $cat_icon = $this->generate_cat_icon($_POST['ct_cat_font_icon'],$_POST['ct_cat_color']);
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
            update_term_meta( $term_id, 'ct_cat_color', sanitize_hex_color( $_POST['ct_cat_color'] ) );
        }

        // Category schema.
        if ( isset( $_POST['ct_cat_schema'] ) ) {
            update_term_meta( $term_id, 'ct_cat_schema', sanitize_text_field( $_POST['ct_cat_schema'] ) );
        }

        do_action( 'geodir_term_save_category_fields', $term_id, $tt_id, $taxonomy );
    }

    /**
     * @param $term_id
     *
     * @return bool
     */
    public function regenerate_term_icon( $term_id ){
        $icon = get_term_meta($term_id,'ct_cat_font_icon', true);
        $color = get_term_meta($term_id,'ct_cat_color', true);

        if ( $icon && $color ) {
            $this->generate_cat_icon($icon,$color);
            return true;
        }

        return false;
    }

	/**
	 * @param $icon
	 * @param $color
	 *
	 * @return array
	 */
	public function generate_cat_icon( $icon, $color ) {
		$cat_icon = array();

		if ( $icon && $color ) {
			$v6 = false;

			$api_url = "https://cdn.mapmarker.io/api/v1/font-awesome/v5/icon-stack?";

			if ( class_exists( 'WP_Font_Awesome_Settings' ) ) {
				$wp_font_awesome = WP_Font_Awesome_Settings::instance();
				$settings = $wp_font_awesome->get_settings();
				$version = $settings['version'];

				if ( ! $version || version_compare( $version, '5.999', '>' ) ) {
					$api_url = "https://mapmarker.io/api/v3/font-awesome/v6/icon-stack?";
					$v6 = true;
				}
			}

			$background = ! empty( $color ) ? ltrim( sanitize_hex_color( $color ), '#' ) : 'ef5646';
			$fa_icon_parts = explode( " ", $icon );
			$fa_icon = ! empty( $fa_icon_parts[1] ) ? sanitize_html_class( $fa_icon_parts[0] ) . " " . sanitize_html_class( $fa_icon_parts[1] ) : 'fas fa-star';

			if ( $v6 ) {
				$fa_icon = str_replace(
					array(
						'fas ',
						'far ',
						'fal ',
						'fad ',
						'fat ',
					),
					array(
						'fa-solid ',
						'fa-regular ',
						'fa-light ',
						'fa-duotone ',
						'fa-thin ',
					),
					$fa_icon
				);
			}

			$icon_url = $api_url;
			$icon_url .= "icon=" . $fa_icon;
			$icon_url .= $v6 ? "&size=40" : "&size=50";
			$icon_url .= "&color=fff";
			$icon_url .= $v6 ? '&on=fa-solid fa-location-pin' : "&on=fas fa-map-marker";
			$icon_url .= "&hoffset=0";
			$icon_url .= $v6 ? "&voffset=-5" : "&voffset=-4";
			$icon_url .= $v6 ? "&iconsize=16" : "";
			$icon_url .= "&oncolor=" . $background;

			$remove_svg = false;

			if ( $v6 ) {
				// Temp allow SVG
				$types = get_allowed_mime_types();

				if ( ! isset( $types['svg'] ) ) {
					$remove_svg = true;
				}

				add_filter( 'upload_mimes', function( $mimes ) {
					$mimes['svg'] = 'image/svg';
					return $mimes;
				} );

				$has_filter = has_filter( 'wp_check_filetype_and_ext', array( 'GeoDir_Admin_Import_Export', 'set_filetype_and_ext' ) );

				if ( ! $has_filter ) {
					add_filter( 'wp_check_filetype_and_ext', array( 'GeoDir_Admin_Import_Export', 'set_filetype_and_ext' ), 10, 4 );
				}

				$image = (array) GeoDir_Media::get_external_media( $icon_url, $fa_icon, array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp', 'image/avif', 'image/svg' ), array( 'ext' => 'svg', 'type' => 'image/svg' ) );

				if ( ! $has_filter ) {
					remove_filter( 'wp_check_filetype_and_ext', array( 'GeoDir_Admin_Import_Export', 'set_filetype_and_ext' ), 10, 4 );
				}
			} else {
				$image = (array) GeoDir_Media::get_external_media( $icon_url, $fa_icon, array( 'image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/webp', 'image/avif' ), array( 'ext' => 'png', 'type' => 'image/png' ) );
			}

			if ( ! empty($image['url'] ) ) {
				$attachment_id = GeoDir_Media::set_uploaded_image_as_attachment( $image );

				if( $attachment_id ){
					$cat_icon['id'] = $attachment_id;
					$cat_icon['src'] = geodir_file_relative_url( $image['url'] );
				}
			}

			// Maybe remove
			if ( $remove_svg ) {
				add_filter( 'upload_mimes', function( $mimes ) {
					unset( $mimes['svg'] );
					return $mimes;
				} );
			}
		}

		return $cat_icon;
	}

    public function get_fixed_icon_slug($slug){
        $fixed_slugs = array(

        );

        return $slug;
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

        if ( $column == 'cat_icon' && $icon = geodir_get_cat_icon( $id, true, true ) ) {
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
     * @since 2.0.0.66 Auto check/select option for a single category.
     *
     * @param string $cat_taxonomy Category taxonomy.
     * @param int $cat_parent Optional. Category parent ID. Default 0.
     * @param bool $hide_empty Optional. Taxonomy hide empty. Default false.
     * @param int $padding Optional. Padding value . Default 0.
     * @return string Taxonomy walker html.
     */
    public static function taxonomy_walker( $cat_taxonomy, $cat_parent = 0, $hide_empty = false, $padding = 0 ) {
        global $aui_bs5, $cat_display, $post_cat, $exclude_cats;

        $search_terms = trim( $post_cat, "," );
        $search_terms = explode( ",", $search_terms );

        $cat_terms = get_terms( array( 'taxonomy' => $cat_taxonomy, 'parent' => $cat_parent, 'hide_empty' => $hide_empty, 'exclude' => $exclude_cats ) );

        $display = '';
        $onchange = '';
        $term_check = '';
        $main_list_class = '';
        $out = '';

        //If there are terms, start displaying
        if (count($cat_terms) > 0) {
            //Displaying as a list
            $p = $padding * 20;
            $padding++;


            if ((!geodir_is_page('listing')) || (is_search() && $_REQUEST['search_taxonomy'] == '')) {
                if ($cat_parent == 0) {
                    $list_class = 'main_list gd-parent-cats-list gd-cats-display-' . $cat_display;
                    $main_list_class = 'main_list_selecter';
                } else {
                    //$display = 'display:none';
                    $list_class = 'sub_list gd-sub-cats-list';

                    if ( geodir_design_style() ) {
                        $list_class .= ' pl-3  ps-3'; // Left padding for sub-categories.
                    }
                }
            }

            if ($cat_display == 'checkbox' || $cat_display == 'radio') {
                $p = 0;
                $out = '<div class="' . $list_class . ' gd-cat-row-' . $cat_parent . '" style="margin-left:' . $p . 'px;' . $display . ';">';

                if ( geodir_design_style() ) {
                    $main_list_class .= ( $aui_bs5 ? ' me-1' : ' mr-1' );
                }
            }

            if ( $main_list_class ) {
                $main_list_class = 'class="' . $main_list_class . '"';
            }

            foreach ( $cat_terms as $cat_term ) {
                $checked = '';
				$sub_out = '';
				$no_child = false;

				if ( absint( $cat_parent ) == 0 && count( $cat_terms ) == 1 ) {
					// Call recursion to print sub cats
					$sub_out = self::taxonomy_walker( $cat_taxonomy, $cat_term->term_id, $hide_empty, $padding );

					if ( trim( $sub_out ) == '' ) {
						$no_child = true; // Set category selected when only one category.
					}
				}

				if ( in_array( $cat_term->term_id, $search_terms ) || $no_child ) {
                    if ( $cat_display == 'select' || $cat_display == 'multiselect' )
                        $checked = 'selected="selected"';
                    else
                        $checked = 'checked="checked"';
                }

                $child_dash = $p > 0 ? str_repeat( "-", $p / 20 ) . ' ' : '';

                if ($cat_display == 'radio')
                    $out .= '<span style="display:block" ><input type="radio" field_type="radio" name="tax_input['.$cat_term->taxonomy .'][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-cradio="default_category">' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '</span>';
                elseif ($cat_display == 'select' || $cat_display == 'multiselect')
                    $out .= '<option ' . $main_list_class . ' style="margin-left:' . $p . 'px;" alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' >' . $term_check . $child_dash . geodir_utf8_ucfirst($cat_term->name) . '</option>';

                else {
					$class = $checked ? 'class="gd-term-checked"' : '';
                    $out .= '<span style="display:block" ' . $class . '><input style="display:inline-block" type="checkbox" field_type="checkbox" name="tax_input['.$cat_term->taxonomy .'][]" ' . $main_list_class . ' alt="' . $cat_term->taxonomy . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '" value="' . $cat_term->term_id . '" ' . $checked . $onchange . ' id="gd-cat-' . $cat_term->term_id . '" data-ccheckbox="default_category">' . $term_check . geodir_utf8_ucfirst($cat_term->name) . '<span class="gd-make-default-term" style="display:none" title="' . esc_attr( wp_sprintf( __( 'Make %s default category', 'geodirectory' ), geodir_utf8_ucfirst($cat_term->name) ) ) . '">' . __( 'Make default', 'geodirectory' ). '</span><span class="gd-is-default-term" style="display:none">' . __( 'Default', 'geodirectory' ). '</span></span>';
                }

                if ( ! ( absint( $cat_parent ) == 0 && count( $cat_terms ) == 1 ) ) {
					// Call recursion to print sub cats
					$sub_out = self::taxonomy_walker( $cat_taxonomy, $cat_term->term_id, $hide_empty, $padding );
				}

				$out .= $sub_out;
            }

            if ( $cat_display == 'checkbox' || $cat_display == 'radio' )
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
        include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/settings/data_schemas.php' );
        $raw_schemas = geodir_data_schemas();
        $schemas = array_merge(array('' => __( 'Default (LocalBusiness)', 'geodirectory' )), $raw_schemas);

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

        if($top_description){
            // location variable
            $location_replace_vars = geodir_location_replace_vars();
            foreach($location_replace_vars as $lkey=>$lval){
                if ( strpos( $top_description, $lkey ) !== false ) {
                    $top_description = str_replace( $lkey, $lval, $top_description );
                }
            }
        }

        return apply_filters( 'geodir_get_cat_top_description', $top_description, $term_id );
    }

	/**
	 * Get the category description html.
	 *
	 * @since 2.2.19
	 *
	 * @param int    $term_id The term id.
	 * @param string $type Description type.
	 * @return mixed|void
	 */
	public static function get_category_description( $term_id, $type = 'top' ) {
		if ( $type && in_array( $type, array( 'bottom', 'main' ) ) ) {
			if ( $type == 'bottom' ) {
				$description = get_term_meta( $term_id, 'ct_cat_bottom_desc', true );
			} else {
				$description = term_description( $term_id );
			}

			if ( $description ) {
				// Location variables
				$replace_vars = geodir_location_replace_vars();

				foreach( $replace_vars as $key => $value ) {
					if ( strpos( $description, $key ) !== false ) {
						$description = str_replace( $key, $value, $description );
					}
				}
			}
		} else {
			$description = self::get_cat_top_description( $term_id );
		}

		if ( ! empty( $description ) && $type != 'main' ) {
			$description = geodir_filter_textarea_output( $description, 'category_description', array( 'type' => $type, 'term_id' => $term_id ) );
		}

		return apply_filters( 'geodir_get_category_description', $description, $term_id, $type );
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
            $cat_icon = GeoDir_Maps::default_marker_icon( $full_path );
        }

        if ( $cat_icon && $full_path ) {
            $cat_icon = geodir_file_relative_url( $cat_icon, true );
        }

        return apply_filters( 'geodir_get_cat_icon', $cat_icon, $term_id, $full_path, $default );
    }

	/**
	 * Get the category icon alt text.
	 *
	 * @since 2.3.76
	 *
	 * @param int $term_id Category ID.
	 * @param string|bool $default Default alt text. Default false.
	 * @return string Icon alt text.
	 */
	public static function get_cat_icon_alt( $term_id, $default = false ) {
		global $geodir_cat_icon_alt;

		if ( ! is_array( $geodir_cat_icon_alt ) ) {
			$geodir_cat_icon_alt = array();
		}

		if ( isset( $geodir_cat_icon_alt[ $term_id ] ) ) {
			return $geodir_cat_icon_alt[ $term_id ];
		}

		$alt = '';
		$attachment_id = 0;

		if ( ! empty( $term_id ) && $term_id != 'd' && $term_id > 0 ) {
			$term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );

			$attachment_id = is_array( $term_meta ) && ! empty( $term_meta['id'] ) ? absint( $term_meta['id'] ) : 0;
			$alt = $attachment_id > 0 ? get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';

			if ( $alt ) {
				$alt = trim( strip_tags( $alt ) );
			}
		}

		// Default alt text.
		if ( $alt == '' && $default != false && is_scalar( $default ) ) {
			$alt = $default;
		}

		$alt = apply_filters( 'geodir_get_cat_icon_alt', $alt, $term_id, $default, $attachment_id );

		$geodir_cat_icon_alt[ $term_id ] = $alt;

		return $alt;
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

	/**
	 * Update post data on term delete.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param int     $term         Term ID.
	 * @param int     $tt_id        Term taxonomy ID.
	 * @param string  $taxonomy     Taxonomy slug.
	 * @param mixed   $deleted_term Copy of the already-deleted term, in the form specified
	 *                              by the parent function. WP_Error otherwise.
	 * @param array   $object_ids   List of term object IDs.
	 */
	public function on_delete_term( $term, $tt_id, $taxonomy = '', $deleted_term = array(), $object_ids = array() ) {
		global $wpdb;

		if ( ! geodir_is_gd_taxonomy( $taxonomy ) ) {
            return;
        }

		if ( ! empty( $object_ids ) && geodir_taxonomy_type( $taxonomy ) == 'tag' && ( $taxonomy_obj = get_taxonomy( $taxonomy ) ) ) {
			$post_type = !empty( $taxonomy_obj ) ? $taxonomy_obj->object_type[0] : '';

			if ( $post_type ) {
				$table = geodir_db_cpt_table( $post_type );

				foreach ( $object_ids as $post_id ) {
					$post_tags = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'names' ) );
					$post_tags = ! empty( $post_tags ) && ! is_wp_error( $post_tags ) ? array_map( 'trim', $post_tags ) : '';
					$post_tags = ! empty( $post_tags ) ? implode( ',', array_filter( array_unique( $post_tags ) ) ) : '';

					$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET post_tags = %s WHERE post_id = %d", array( $post_tags, $post_id ) ) );
				}
			}
		}
	}

}

new GeoDir_Admin_Taxonomies();
