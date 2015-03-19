<?php
/*
Plugin Name: Demo Tax meta class
Plugin URI: http://en.bainternet.info
Description: Tax meta class usage demo
Version: 1.2
Author: Bainternet, Ohad Raz
Author URI: http://en.bainternet.info
*/

//include the main class file
require_once("Tax-meta-class.php");
if (is_admin()) {
    /*
     * prefix of meta keys, optional
     * use underscore (_) at the beginning to make keys hidden, for example $prefix = '_ba_';
     *  you also can make prefix empty to disable it
     *
     */

    $prefix = 'ct_';
    /*
     * configure your meta box
     */

    $config = array(
        'id' => 'demo_meta_box',                    // meta box id, unique per meta box
        'title' => __('Demo Meta Box', GEODIRECTORY_TEXTDOMAIN),                    // meta box title
        'pages' => geodir_get_taxonomies(),            // taxonomy name, accept categories, post_tag and custom taxonomies
        'context' => 'normal',                        // where the meta box appear: normal (default), advanced, side; optional
        'fields' => array(),                        // list of meta fields (can be added by field arrays)
        'local_images' => false,                    // Use local or hosted images (meta box images for add/remove)
        'use_with_theme' => true                    //change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
    );


    /*
     * Initiate your meta box
     */
    $my_meta = new Tax_Meta_Class($config);
    $my_meta->addWysiwyg($prefix . 'cat_top_desc', array('name' => __('Category Top Description', GEODIRECTORY_TEXTDOMAIN), 'desc' => __('This will appear at the top of the category listing.', GEODIRECTORY_TEXTDOMAIN)));
    $my_meta->addImage($prefix . 'cat_default_img', array('name' => __('Default Listing Image', GEODIRECTORY_TEXTDOMAIN), 'desc' => __('Choose a default "no image"', GEODIRECTORY_TEXTDOMAIN)));
    $my_meta->addImage($prefix . 'cat_icon', array('name' => __('Category Icon', GEODIRECTORY_TEXTDOMAIN), 'desc' => __('Choose a category icon', GEODIRECTORY_TEXTDOMAIN), 'validate_func' => '!empty'));
    /*$my_meta->addCheckbox($prefix.'pointless',array('name'=> __('<b>Exclude</b> Rating sort option',GEODIRECTORY_TEXTDOMAIN),'style'=>'hidden'));*/// hidden setting to trick WPML

    /*$my_meta->addSelect($prefix.'cat_sort',array(''=>__('Default' , GEODIRECTORY_TEXTDOMAIN),
    'random'=>__('Random',GEODIRECTORY_TEXTDOMAIN),
    'az'=>__('Alphabetical' , GEODIRECTORY_TEXTDOMAIN),
    'newest'=>__('Newest',GEODIRECTORY_TEXTDOMAIN),
    'oldest'=>__('Oldest',GEODIRECTORY_TEXTDOMAIN),
    'high_rating'=>__('Highest Rating',GEODIRECTORY_TEXTDOMAIN),
    'low_rating'=>__('Lowest Rating',GEODIRECTORY_TEXTDOMAIN),
    'high_review'=>__('Highest Reviews',GEODIRECTORY_TEXTDOMAIN),
    'low_review'=>__('Lowest Reviews',GEODIRECTORY_TEXTDOMAIN)),
    array('name'=> __('Sort By',GEODIRECTORY_TEXTDOMAIN),'desc' => __('Select the default sort option.' ,GEODIRECTORY_TEXTDOMAIN), 'std'=> array('selectkey2')));*/

    // Show options for placecategories only
    /*	if(isset($_REQUEST['taxonomy']) && in_array($_REQUEST['taxonomy'],$config['pages']) ){
        // Exclude sort options
        $my_meta->addCheckbox($prefix.'cat_exclude_rating',array('name'=> __('<b>Exclude</b> Rating sort option',GEODIRECTORY_TEXTDOMAIN)));
        $my_meta->addCheckbox($prefix.'cat_exclude_reviews',array('name'=> __('<b>Exclude</b> Reviews sort option',GEODIRECTORY_TEXTDOMAIN)));

        // Include sort options
        $my_meta->addCheckbox($prefix.'cat_include_random',array('name'=> __('Include Random sort option',GEODIRECTORY_TEXTDOMAIN)));
        $my_meta->addCheckbox($prefix.'cat_include_newest',array('name'=> __('Include Newest/Oldest sort option',GEODIRECTORY_TEXTDOMAIN)));
        $my_meta->addCheckbox($prefix.'cat_include_az',array('name'=> __('Include Alphabetical sort option',GEODIRECTORY_TEXTDOMAIN)));

        }*/

    //Finish Meta Box Decleration
    $my_meta->Finish();
}


##############################################################
############## LETS ADD CUSTOM COLUMN HERE ###################
##############################################################
$gd_taxonomies = geodir_get_taxonomies();
if (!empty($gd_taxonomies)) {
    foreach ($gd_taxonomies as $gd_taxonomy) {

        add_filter('manage_edit-' . $gd_taxonomy . '_columns', 'addCat_column', 10, 2);
        add_action('manage_' . $gd_taxonomy . '_custom_column', 'manage_category_custom_fields', 10, 3);

    }
}

function addCat_column($columns)
{
    // only edit the columns on the current taxonomy
    /*if ( !isset($_GET['taxonomy']) && !in_array($_GET['taxonomy'],geodir_get_taxonomies()) )
    return $columns;
    */
    if ($posts = $columns['description']) {
        unset($columns['description']);
    }

    $columns['cat_icon'] = 'Icon';
    $columns['cat_default_img'] = __('Default Image', GEODIRECTORY_TEXTDOMAIN);
    $columns['cat_ID_num'] = __('Cat ID', GEODIRECTORY_TEXTDOMAIN);
    return $columns;
}

#############################################################
function manage_category_custom_fields($deprecated, $column_name, $term_id)
{
    if ($column_name == 'cat_ID_num')
        echo $term_id;

    if ($column_name == 'cat_icon') {
        $term_icon_url = get_tax_meta($term_id, 'ct_cat_icon');

        if ($term_icon_url != '') {

            $file_info = pathinfo($term_icon_url['src']);

            if ($file_info['dirname'] != '.' && $file_info['dirname'] != '..')
                $sub_dir = $file_info['dirname'];

            $uploads = wp_upload_dir(trim($sub_dir, '/')); // Array of key => value pairs
            $uploads_baseurl = $uploads['baseurl'];
            $uploads_path = $uploads['path'];

            $file_name = $file_info['basename'];

            $sub_dir = str_replace($uploads_baseurl, '', $sub_dir);

            $uploads_url = $uploads_baseurl . $sub_dir;

            $term_icon_url['src'] = $uploads_url . '/' . $file_name;
            echo '<img src="' . $term_icon_url['src'] . '" />';

        }
    }

    if ($column_name == 'cat_default_img') {
        $cat_default_img = get_tax_meta($term_id, 'ct_cat_default_img');
        if ($cat_default_img != '')
            echo '<img src="' . $cat_default_img['src'] . '" style="max-height:60px;max-width:60px;"/>';

    }
}

function geodir_get_default_catimage($term_id, $post_type = 'gd_place')
{

    if ($cat_default_img = get_tax_meta($term_id, 'ct_cat_default_img', '', $post_type))
        return $cat_default_img;
    else
        return false;
}

//Clear custom fields
add_action('in_admin_footer', 'geodir_tax_meta_clear_custom_field');
function geodir_tax_meta_clear_custom_field()
{
    if (isset($_REQUEST['taxonomy']) && !empty($_REQUEST['taxonomy'])):
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#addtag #submit').click(function () {
                    setTimeout(function () {
                        if (!jQuery('#addtag .form-invalid').length) {
                            jQuery('#addtag .rw-checkbox').prop('checked', false);
                            jQuery('#addtag .at-select option').removeAttr('selected');
                            jQuery("#addtag .mupload_img_holder").html('');
                            jQuery("#addtag iframe").contents().find("body").html('');
                            jQuery('#addtag [rel="ct_cat_default_img"]').removeClass('at-delete_image_button').addClass('at-upload_image_button');
                            jQuery('#addtag [rel="ct_cat_icon"]').removeClass('at-delete_image_button').addClass('at-upload_image_button');
                            jQuery('#addtag [rel="ct_cat_default_img"]').val('<?php _e('Upload Image',GEODIRECTORY_TEXTDOMAIN);?>');
                            jQuery('#addtag [rel="ct_cat_icon"]').val('<?php _e('Upload Image',GEODIRECTORY_TEXTDOMAIN);?>');
                        }
                    }, 1000);

                });
            });
        </script>
    <?php
    endif;
}
