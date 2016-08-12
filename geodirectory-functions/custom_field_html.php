<?php
/**
 * Admin custom field form
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */
 
/**
 * Displays the custom field form content.
 *
 * @since 1.0.0
 *
 * @global string $post_type Post type.
 */

global $post_type;

if (!isset($field_info->post_type)) {
    $post_type = sanitize_text_field($_REQUEST['listing_type']);
} else
    $post_type = $field_info->post_type;

$field_info = stripslashes_deep($field_info); // strip slashes from labels

$nonce = wp_create_nonce('custom_fields_' . $result_str);

$field_admin_title = '';
if (isset($field_info->admin_title))
    $field_admin_title = $field_info->admin_title;

$default = isset($field_info->is_admin) ? $field_info->is_admin : '';

$display_on_listing = true;
// Remove Send Enquiry | Send To Friend from listings page
$htmlvar_name = isset($field_info->htmlvar_name) && $field_info->htmlvar_name != '' ? $field_info->htmlvar_name : '';
if ($htmlvar_name == 'geodir_email') {
	$field_info->show_on_listing = 0;
	$display_on_listing = false;
}
$field_data_type = isset($field_info->data_type) ? $field_info->data_type : '';

$field_display = $field_type == 'address' && $field_info->htmlvar_name == 'post' ? 'style="display:none"' : '';

$radio_id = (isset($field_info->htmlvar_name)) ? $field_info->htmlvar_name : rand(5, 500);
?>
<li class="text" id="licontainer_<?php echo $result_str; ?>">
    <div class="title title<?php echo $result_str; ?> gt-fieldset"
         title="<?php _e('Double Click to toggle and drag-drop to sort', 'geodirectory'); ?>"
         ondblclick="show_hide('field_frm<?php echo $result_str; ?>')">
        <?php

        $nonce = wp_create_nonce('custom_fields_' . $result_str);
        ?>

        <?php if ($default): ?>
            <div title="<?php _e('Drag and drop to sort', 'geodirectory'); ?>" class="handlediv move"></div>
        <?php else: ?>
            <div title="<?php _e('Click to remove field', 'geodirectory'); ?>"
                 onclick="delete_field('<?php echo $result_str; ?>', '<?php echo $nonce; ?>')"
                 class="handlediv close"></div>
        <?php endif;
        if ($field_type == 'fieldset') {
            ?>

            <b style="cursor:pointer;"
               onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo geodir_ucwords(__('Fieldset:', 'geodirectory') . ' ' . $field_admin_title);?></b>
        <?php
        } else {
            ?>
            <b style="cursor:pointer;"
               onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo geodir_ucwords(__('Field:', 'geodirectory') . ' ' . $field_admin_title . ' (' . $field_type . ')');?></b>
        <?php
        }
        ?>
    </div>

    <form><!-- we need to wrap in a fom so we can use radio buttons with same name -->
    <div id="field_frm<?php echo $result_str; ?>" class="field_frm"
         style="display:<?php if ($field_ins_upd == 'submit') {
             echo 'block;';
         } else {
             echo 'none;';
         } ?>">
        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>"/>
        <input type="hidden" name="listing_type" id="listing_type" value="<?php echo $post_type; ?>"/>
        <input type="hidden" name="field_type" id="field_type" value="<?php echo $field_type; ?>"/>
        <input type="hidden" name="field_id" id="field_id" value="<?php echo esc_attr($result_str); ?>"/>
        <input type="hidden" name="data_type" id="data_type" value="<?php if (isset($field_info->data_type)) {
            echo $field_info->data_type;
        } ?>"/>
        <input type="hidden" name="is_active" id="is_active" value="1"/>

        <input type="hidden" name="is_default" value="<?php echo $field_info->is_default;?>" /><?php // show in sidebar value?>
        <input type="hidden" name="show_on_listing" value="<?php echo $field_info->show_on_listing;?>" />
        <input type="hidden" name="show_on_detail" value="<?php echo $field_info->show_on_listing;?>" />
        <input type="hidden" name="show_as_tab" value="<?php echo $field_info->show_as_tab;?>" />

        <ul class="widefat post fixed" border="0" style="width:100%;">
            <?php if ($field_type != 'text' || $default) { ?>

                <input type="hidden" name="data_type" id="data_type" value="<?php if (isset($field_info->data_type)) {
                    echo esc_attr($field_info->data_type);
                } ?>"/>

            <?php } else { ?>

                <li>
                    <label for="data_type""><?php _e('Field Data Type ? :', 'geodirectory'); ?></label>
                    <div class="gd-cf-input-wrap">

                        <select name="data_type" id="data_type"
                                onchange="javascript:gd_data_type_changed(this, '<?php echo $result_str; ?>');">
                            <option
                                value="XVARCHAR" <?php if (isset($field_info->data_type) && $field_info->data_type == 'VARCHAR') {
                                echo 'selected="selected"';
                            } ?>><?php _e('CHARACTER', 'geodirectory'); ?></option>
                            <option
                                value="INT" <?php if (isset($field_info->data_type) && $field_info->data_type == 'INT') {
                                echo 'selected="selected"';
                            } ?>><?php _e('NUMBER', 'geodirectory'); ?></option>
                            <option
                                value="FLOAT" <?php if (isset($field_info->data_type) && $field_info->data_type == 'FLOAT') {
                                echo 'selected="selected"';
                            } ?>><?php _e('DECIMAL', 'geodirectory'); ?></option>
                        </select>
                        <br/> <span><?php _e('Select Custom Field type', 'geodirectory'); ?></span>

                    </div>
                </li>
                <li class="decimal-point-wrapper"
                    style="<?php echo (isset($field_info->data_type) && $field_info->data_type == 'FLOAT') ? '' : 'display:none' ?>">
                    <label for="decimal_point"><?php _e('Select decimal point :', 'geodirectory'); ?></label>
                    <div class="gd-cf-input-wrap">
                        <select name="decimal_point" id="decimal_point">
                            <option value=""><?php echo _e('Select', 'geodirectory'); ?></option>
                            <?php for ($i = 1; $i <= 10; $i++) {
                                $decimal_point = isset($field_info->decimal_point) ? $field_info->decimal_point : '';
                                $selected = $i == $decimal_point ? 'selected="selected"' : ''; ?>
                                <option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                        <br/> <span><?php _e('Decimal point to display after point', 'geodirectory'); ?></span>
                    </div>
                </li>

            <?php } ?>


            <li>
                <label for="admin_title" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Admin title :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('This is used as the field setting name here in the backend only.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="admin_title" id="admin_title"
                           value="<?php if (isset($field_info->admin_title)) {
                               echo esc_attr($field_info->admin_title);
                           } ?>"/>
                </div>
            </li>
            <li>
                <label for="site_title" class="gd-cf-tooltip-wrap"> <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Frontend title :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('This will be the title for the field on the frontend.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="site_title" id="site_title"
                           value="<?php if (isset($field_info->site_title)) {
                               echo esc_attr($field_info->site_title);
                           } ?>"/>
                </div>
            </li>
            <li>
                <label for="admin_desc" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Frontend description :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('This will be shown below the field on the add listing form.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="admin_desc" id="admin_desc"
                           value="<?php if (isset($field_info->admin_desc)) {
                               echo esc_attr($field_info->admin_desc);
                           } ?>"/>
                </div>
            </li>
            <?php if ($field_type != 'fieldset' && $field_type != 'taxonomy') {
                ?>

                <li>
                    <label for="htmlvar_name" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('HTML variable name :', 'geodirectory');?>
                        <div class="gdcf-tooltip">
                            <?php _e('This is a unique identifier used in the HTML, it MUST NOT contain spaces or special characters.', 'geodirectory'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">
                        <input type="text" name="htmlvar_name" id="htmlvar_name" pattern="[a-zA-Z0-9]+" title="<?php _e('Must not contain spaces or special characters', 'geodirectory');?>"
                               value="<?php if (isset($field_info->htmlvar_name)) {
                                   echo preg_replace('/geodir_/', '', $field_info->htmlvar_name, 1);
                               }?>" <?php if ($default) {
                            echo 'readonly="readonly"';
                        }?> />
                    </div>
                </li>
            <?php } ?>

            <?php // @todo this does not seem to be used for anything, it can be removed or replaced ?>
            <li style="display: none">
                <label for="clabels" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Admin label :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Section Title which will appear in backend', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap"><input type="text" name="clabels" id="clabels"
                                        value="<?php if (isset($field_info->clabels)) {
                                            echo esc_attr($field_info->clabels);
                                        } ?>"/>
                </div>
            </li>

            <li <?php echo $field_display; ?>>
                <label for="is_active" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is active :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('If no is selected then the field will not be displayed anywhere', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap gd-switch">

                    <input type="radio" id="is_active_yes<?php echo $radio_id;?>" name="is_active" class="gdri-enabled"  value="1"
                        <?php if (isset($field_info->is_active) && $field_info->is_active == '1') {
                            echo 'checked';
                        } ?>/>
                    <label for="is_active_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                    <input type="radio" id="is_active_no<?php echo $radio_id;?>" name="is_active" class="gdri-disabled" value="0"
                        <?php if ((isset($field_info->is_active) && $field_info->is_active == '0') || !isset($field_info->is_active)) {
                            echo 'checked';
                        } ?>/>
                    <label for="is_active_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                </div>
            </li>
            <?php if (!$default) { /* field for admin use only */
                $for_admin_use = isset($field_info->for_admin_use) && $field_info->for_admin_use == '1' ? true : false; ?>
                <li>
                    <label for="for_admin_use" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('For admin use only? :', 'geodirectory'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('If yes is selected then only site admin can see and edit this field.', 'geodirectory'); ?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap gd-switch">

                        <input type="radio" id="for_admin_use_yes<?php echo $radio_id;?>" name="for_admin_use" class="gdri-enabled"  value="1"
                            <?php if (isset($for_admin_use) && $for_admin_use == '1') {
                                echo 'checked';
                            } ?>/>
                        <label for="for_admin_use_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                        <input type="radio" id="for_admin_use_no<?php echo $radio_id;?>" name="for_admin_use" class="gdri-disabled" value="0"
                            <?php if ((isset($for_admin_use) && $for_admin_use == '0') || !isset($for_admin_use)) {
                                echo 'checked';
                            } ?>/>
                        <label for="for_admin_use_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                    </div>
                </li>
            <?php } ?>


            <?php 
			if ($field_type != 'textarea' && $field_type != 'html' && $field_type != 'file' && $field_type != 'fieldset' && $field_type != 'taxonomy' && $field_type != 'address') {
				$default_value = isset($field_info->default_value) ? $field_info->default_value : '';
			?>
			<li>
				<label for="default_value" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Default value :', 'geodirectory');?>
                    <div class="gdcf-tooltip">
                        <?php
                        if ($field_type == 'checkbox') {
                            _e('Should the checkbox be checked by default?', 'geodirectory');
                        } else if ($field_type == 'email') {
                            _e('A default value for the field, usually blank. Ex: info@mysite.com', 'geodirectory');
                        } else {
                            _e('A default value for the field, usually blank. (for "link" this will be used as the link text)', 'geodirectory');
                        }
                        ?>
                    </div>
                </label>
				<div class="gd-cf-input-wrap">
				<?php if ($field_type == 'checkbox') { ?>
				<select name="default_value" id="default_value">
					<option value=""><?php _e('Unchecked', 'geodirectory'); ?></option>
					<option value="1" <?php selected(true, (int)$default_value === 1);?>><?php _e('Checked', 'geodirectory'); ?></option>
				</select>
				<?php } else if ($field_type == 'email') { ?>
				<input type="email" name="default_value" placeholder="<?php _e('info@mysite.com', 'geodirectory') ;?>" id="default_value" value="<?php echo esc_attr($default_value);?>" /><br/>
				<?php } else { ?>
				<input type="text" name="default_value" id="default_value" value="<?php echo esc_attr($default_value);?>" /><br/>
				<?php } ?>
				</div>
			</li>
            <?php } ?>
            <li style="display: none">
                <label for="sort_order"><?php _e('Display order :', 'geodirectory'); ?></label>
                <div class="gd-cf-input-wrap"><input type="text" readonly="readonly" name="sort_order" id="sort_order"
                                        value="<?php if (isset($field_info->sort_order)) {
                                            echo esc_attr($field_info->sort_order);
                                        } ?>"/>
                    <br/>
                    <span><?php _e('Enter the display order of this field in backend. e.g. 5', 'geodirectory'); ?></span>
                </div>
            </li>

            <li>
                <label for="show_in" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show in what locations?:', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Select in what locations you want to display this field.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">

                    <?php

                    /*
                     * We wrap the key values in [] so we can search the DB easier with a LIKE query.
                     */
                    $show_in_locations = array(
                        "[detail]" => __("Details page sidebar", 'geodirectory'),
                        "[moreinfo]" => __("More info tab", 'geodirectory'),
                        "[listing]" => __("Listings page", 'geodirectory'),
                        "[owntab]" => __("Details page own tab", 'geodirectory'),
                        "[mapbubble]" => __("Map bubble", 'geodirectory'),
                    );

                    /**
                     * Filter the locations array for where to display custom fields.
                     *
                     * @since 1.6.6
                     * @param array $show_in_locations The array of locations and descriptions.
                     * @param object $field_info The field being displayed info.
                     */
                    $show_in_locations = apply_filters('geodir_show_in_locations',$show_in_locations,$field_info);


                    // remove some locations for some field types

                    // don't show new tab option for some types

                    if (in_array($field_type, array('text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox', 'file'))) {
                    }else{
                        unset($show_in_locations['[owntab]']);
                    }

                    if(!$display_on_listing){
                        unset($show_in_locations['[listings]']);
                    }

                    ?>

                    <select multiple="multiple" name="show_in[]"
                            id="show_in"
                            style="min-width:300px;"
                            class="chosen_select"
                            data-placeholder="<?php _e('Select locations', 'geodirectory'); ?>"
                            option-ajaxchosen="false">
                        <?php

                        $show_in_values = explode(',',$field_info->show_in);

                        foreach( $show_in_locations as $key => $val){
                            $selected = '';

                            if(is_array($show_in_values) && in_array($key,$show_in_values ) ){
                                $selected = 'selected';
                            }

                            ?>
                            <option  value="<?php echo $key;?>" <?php echo $selected;?>><?php echo $val;?></option>
                        <?php
                        }
                        ?>

                    </select>

                </div>
            </li>


            <?php if ($field_type == 'textarea' && isset($field_info->htmlvar_name) && $field_info->htmlvar_name == 'geodir_special_offers') { ?>

                <li>
                <label for="advanced_editor" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show advanced editor :', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Select if you want to show the advanced editor on add listing page.', 'geodirectory'); ?>
                    </div>
                </label>

                <div class="gd-cf-input-wrap">

                    <?php
                    $selected = '';
                    if (isset($field_info->extra_fields))
                        $advanced_editor = unserialize($field_info->extra_fields);

                    if (!empty($advanced_editor) && is_array($advanced_editor) && in_array('1', $advanced_editor))
                        $selected = 'checked="checked"';
                    ?>

                    <input type="checkbox" name="advanced_editor[]" id="advanced_editor"
                           value="1" <?php echo $selected; ?>/>
                </div>

                </li><?php
            } ?>

            <?php

            $pricearr = array();
            if (isset($field_info->packages) && $field_info->packages != '') {
                $pricearr = explode(',', trim($field_info->packages, ','));
            } else {
                $package_info = array();

                $package_info = geodir_post_package_info($package_info, '', $post_type);
                $pricearr[] = $package_info->pid;
            }

            ob_start()
            ?>

            <select style="display:none" name="show_on_pkg[]" id="show_on_pkg" multiple="multiple">
                <?php
                if (!empty($pricearr)) {
                    foreach ($pricearr as $val) {
                        ?>
                        <option selected="selected" value="<?php echo esc_attr($val); ?>" ><?php echo $val; ?></option><?php
                    }
                }
                ?>
            </select>

            <?php
            $html = ob_get_clean();

			/**
			 * Filter the price packages list.
			 *
			 * Filter the price packages list in custom field form in admin
             * custom fields settings.
			 *
			 * @since 1.0.0
			 *
			 * @param string $html The price packages content.
			 * @param object $field_info Current field object.
			 */
			echo $html = apply_filters('geodir_packages_list_on_custom_fields', $html, $field_info);

            ?>



            <?php if ($field_type != 'fieldset') { ?>
                <li>
                    <label for="is_required" class="gd-cf-tooltip-wrap"><i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Is required :', 'geodirectory'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Select yes to set field as required', 'geodirectory'); ?>
                        </div>
                    </label>

                    <div class="gd-cf-input-wrap gd-switch">

                        <input type="radio" id="is_required_yes<?php echo $radio_id;?>" name="is_required" class="gdri-enabled"  value="1"
                            <?php if (isset($field_info->is_required) && $field_info->is_required == '1') {
                                echo 'checked';
                            } ?>/>
                        <label onclick="show_hide_radio(this,'show','cf-is-required-msg');" for="is_required_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                        <input type="radio" id="is_required_no<?php echo $radio_id;?>" name="is_required" class="gdri-disabled" value="0"
                            <?php if ((isset($field_info->is_required) && $field_info->is_required == '0') || !isset($field_info->is_required)) {
                                echo 'checked';
                            } ?>/>
                        <label onclick="show_hide_radio(this,'hide','cf-is-required-msg');" for="is_required_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                    </div>

                </li>
            

            <li class="cf-is-required-msg"<?php if ((isset($field_info->is_required) && $field_info->is_required == '0') || !isset($field_info->is_required)) {echo "style='display:none;'";}?>>
                <label for="required_msg" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Required message:', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Enter text for the error message if the field is required and has not fulfilled the requirements.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="required_msg" id="required_msg"
                           value="<?php if (isset($field_info->required_msg)) {
                               echo esc_attr($field_info->required_msg);
                           } ?>"/>
                </div>
            </li>
            <?php } ?>

            <?php if ($field_type == 'text'){ ?>
            <li>
                <label for="validation_pattern" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Validation Pattern:', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Enter regex expression for HTML5 pattern validation.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="validation_pattern" id="validation_pattern"
                           value="<?php if (isset($field_info->validation_pattern)) {
                               echo esc_attr($field_info->validation_pattern);
                           } ?>"/>
                </div>
            </li>

            <li>
                <label for="validation_msg" class="gd-cf-tooltip-wrap">
                    <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Validation Message:', 'geodirectory'); ?>
                    <div class="gdcf-tooltip">
                        <?php _e('Enter a extra validation message to show to the user if validation fails.', 'geodirectory'); ?>
                    </div>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="text" name="validation_msg" id="validation_msg"
                           value="<?php if (isset($field_info->validation_msg)) {
                               echo esc_attr($field_info->validation_msg);
                           } ?>"/>
                </div>
            </li>
            <?php }



            switch ($field_type):
                case 'taxonomy': {
                    ?>
                    <li style="display: none;">
                        <label for="htmlvar_name" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Select taxonomy:', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Selected taxonomy name use as field name index. ex:-( post_category[gd_placecategory] )', 'geodirectory'); ?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">
                            <select name="htmlvar_name" id="htmlvar_name">
                                <?php
                                $gd_taxonomy = geodir_get_taxonomies($post_type);

                                foreach ($gd_taxonomy as $gd_tax) {
                                    ?>
                                    <option <?php if (isset($field_info->htmlvar_name) && $field_info->htmlvar_name == $gd_tax) {
                                        echo 'selected="selected"';
                                    }?> id="<?php echo $gd_tax;?>"><?php echo $gd_tax;?></option><?php
                                }
                                ?>
                            </select>
                        </div>
                    </li>

                    <li>
                        <label for="cat_display_type" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Category display type :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Show categories list as select, multiselect, checkbox or radio', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">

                            <select name="cat_display_type" id="cat_display_type">
                                <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'ajax_chained') {
                                    echo 'selected="selected"';
                                }?> value="ajax_chained"><?php _e('Ajax Chained', 'geodirectory');?></option>
                                <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'select') {
                                    echo 'selected="selected"';
                                }?> value="select"><?php _e('Select', 'geodirectory');?></option>
                                <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'multiselect') {
                                    echo 'selected="selected"';
                                }?> value="multiselect"><?php _e('Multiselect', 'geodirectory');?></option>
                                <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'checkbox') {
                                    echo 'selected="selected"';
                                }?> value="checkbox"><?php _e('Checkbox', 'geodirectory');?></option>
                                <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'radio') {
                                    echo 'selected="selected"';
                                }?> value="radio"><?php _e('Radio', 'geodirectory');?></option>
                            </select>
                        </div>
                    </li>
                <?php } // end of additional field for taxonomy field type
                    break;
                case 'address': {
                    if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
                        $address = unserialize($field_info->extra_fields);
                    }
                    ?>
                    <?php
                    /**
                     * Called on the add custom fields settings page before the address field is output.
                     *
                     * @since 1.0.0
                     * @param array $address The address settings array.
                     * @param object $field_info Extra fields info.
                     */
                    do_action('geodir_address_extra_admin_fields', $address, $field_info); ?>

                    <li>
                        <label for="show_zip" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Display zip/post code :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Select if you want to show zip/post code field in address section.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap gd-switch">

                            <input type="radio" id="show_zip_yes<?php echo $radio_id;?>" name="extra[show_zip]" class="gdri-enabled"  value="1"
                                <?php if (isset($address['show_zip']) && $address['show_zip'] == '1') {
                                    echo 'checked';
                                } ?>/>
                            <label onclick="show_hide_radio(this,'show','cf-zip-lable');" for="show_zip_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                            <input type="radio" id="show_zip_no<?php echo $radio_id;?>" name="extra[show_zip]" class="gdri-disabled" value="0"
                                <?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {
                                    echo 'checked';
                                } ?>/>
                            <label onclick="show_hide_radio(this,'hide','cf-zip-lable');" for="show_zip_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>


                        </div>
                    </li>

                    <li class="cf-zip-lable"  <?php if ((isset($address['show_zip']) && !$address['show_zip']) || !isset($address['show_zip'])) {echo "style='display:none;'";}?> >
                        <label for="zip_lable" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Zip/Post code label :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Enter zip/post code field label in address section.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">
                            <input type="text" name="extra[zip_lable]" id="zip_lable"
                                   value="<?php if (isset($address['zip_lable'])) {
                                       echo esc_attr($address['zip_lable']);
                                   }?>"/>
                        </div>
                    </li>

                    <input type="hidden" name="extra[show_map]" value="1" />


                    <li>
                        <label for="map_lable" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Map button label :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Enter text for `set address on map` button in address section.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">
                            <input type="text" name="extra[map_lable]" id="map_lable"
                                   value="<?php if (isset($address['map_lable'])) {
                                       echo esc_attr($address['map_lable']);
                                   }?>"/>
                        </div>
                    </li>

                    <li>
                        <label for="show_mapzoom" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Use user zoom level:', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Do you want to use the user defined map zoom level from the add listing page?', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap gd-switch">

                            <input type="radio" id="show_mapzoom_yes<?php echo $radio_id;?>" name="extra[show_mapzoom]" class="gdri-enabled"  value="1"
                                <?php if (isset($address['show_mapzoom']) && $address['show_mapzoom'] == '1') {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_mapzoom_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                            <input type="radio" id="show_mapzoom_no<?php echo $radio_id;?>" name="extra[show_mapzoom]" class="gdri-disabled" value="0"
                                <?php if ((isset($address['show_mapzoom']) && !$address['show_mapzoom']) || !isset($address['show_mapzoom'])) {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_mapzoom_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                        </div>
                    </li>

                    <li>
                        <label for="show_mapview" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Display map view:', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Select if you want to `set default map` options in address section. ( Satellite Map, Hybrid Map, Terrain Map)', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap gd-switch">

                            <input type="radio" id="show_mapview_yes<?php echo $radio_id;?>" name="extra[show_mapview]" class="gdri-enabled"  value="1"
                                <?php if (isset($address['show_mapview']) && $address['show_mapview'] == '1') {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_mapview_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                            <input type="radio" id="show_mapview_no<?php echo $radio_id;?>" name="extra[show_mapview]" class="gdri-disabled" value="0"
                                <?php if ((isset($address['show_mapview']) && !$address['show_mapview']) || !isset($address['show_mapview'])) {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_mapview_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                        </div>
                    </li>


                    <li>
                        <label for="mapview_lable" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Map view label:', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Enter mapview field label in address section.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">
                            <input type="text" name="extra[mapview_lable]" id="mapview_lable"
                                   value="<?php if (isset($address['mapview_lable'])) {
                                       echo esc_attr($address['mapview_lable']);
                                   }?>"/>
                        </div>
                    </li>
                    <li>
                        <label for="show_latlng" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Show latitude and longitude', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('This will show/hide the longitude fields in the address section add listing form.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap gd-switch">

                            <input type="radio" id="show_latlng_yes<?php echo $radio_id;?>" name="extra[show_latlng]" class="gdri-enabled"  value="1"
                                <?php if (isset($address['show_latlng']) && $address['show_latlng'] == '1') {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_latlng_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                            <input type="radio" id="show_latlng_no<?php echo $radio_id;?>" name="extra[show_latlng]" class="gdri-disabled" value="0"
                                <?php if ((isset($address['show_latlng']) && !$address['show_latlng']) || !isset($address['show_latlng'])) {
                                    echo 'checked';
                                } ?>/>
                            <label for="show_latlng_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                        </div>
                    </li>
                <?php } // end of extra fields for address field type
                    break;
                case 'select':
                case 'multiselect':
                case 'radio' : {
                    if ($field_type == 'multiselect') {

                        ?>
                        <li>
                            <label for="multi_display_type" class="gd-cf-tooltip-wrap">
                                <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Multiselect display type :', 'geodirectory'); ?>
                                <div class="gdcf-tooltip">
                                    <?php _e('Show multiselect list as multiselect,checkbox or radio.', 'geodirectory');?>
                                </div>
                            </label>
                            <div class="gd-cf-input-wrap">

                                <select name="multi_display_type" id="multi_display_type">
                                    <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'select') {
                                        echo 'selected="selected"';
                                    }?> value="select"><?php _e('Select', 'geodirectory');?></option>
                                    <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'checkbox') {
                                        echo 'selected="selected"';
                                    }?> value="checkbox"><?php _e('Checkbox', 'geodirectory');?></option>
                                    <option <?php if (isset($field_info->extra_fields) && unserialize($field_info->extra_fields) == 'radio') {
                                        echo 'selected="selected"';
                                    }?> value="radio"><?php _e('Radio', 'geodirectory');?></option>
                                </select>

                                <br/>
                            </div>
                        </li>
                    <?php
                    }
                    ?>
                    <li>
                        <label for="option_values" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Option Values :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <span><?php _e('Option Values should be separated by comma.', 'geodirectory');?></span>
                                <br/>
                                <small><span><?php _e('If using for a "tick filter" place a / and then either a 1 for true or 0 for false', 'geodirectory');?></span>
                                <br/>
                                <span><?php _e('eg: "No Dogs Allowed/0,Dogs Allowed/1" (Select only, not multiselect)', 'geodirectory');?></span>
                                <?php if ($field_type == 'multiselect' || $field_type == 'select') { ?>
                                    <br/>
                                    <span><?php _e('- If using OPTGROUP tag to grouping options, use "{optgroup}OPTGROUP-LABEL|OPTION-1,OPTION-2{/optgroup}"', 'geodirectory'); ?></span>
                                    <br/>
                                    <span><?php _e('eg: "{optgroup}Pets Allowed|No Dogs Allowed/0,Dogs Allowed/1{/optgroup},{optgroup}Sports|Cricket/Cricket,Football/Football,Hockey{/optgroup}"', 'geodirectory'); ?></span>
                                <?php } ?></small>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap">
                            <input type="text" name="option_values" id="option_values"
                                   value="<?php if (isset($field_info->option_values)) {
                                       echo esc_attr($field_info->option_values);
                                   }?>"/>
                            <br/>

                        </div>
                    </li>
                <?php
                } // end of extra fields for select , multiselect and radio type fields
                    break;
                case 'datepicker': {
                    if (isset($field_info->extra_fields) && $field_info->extra_fields != '') {
                        $extra = unserialize($field_info->extra_fields);
                    }
                    ?>
                    <li>
                        <label for="date_format" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Date Format :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Select the date format.', 'geodirectory');?>
                            </div>
                        </label>
                        <div class="gd-cf-input-wrap" style="overflow:inherit;">
                            <?php
                            $date_formats = array(
                                'm/d/Y',
                                'd/m/Y',
                                'Y/m/d',
                                'm-d-Y',
                                'd-m-Y',
                                'Y-m-d',
                                'F j, Y',
                            );
                            /**
                             * Filter the custom field date format options.
                             *
                             * @since 1.6.5
                             * @param array $date_formats The PHP date format array.
                             */
                            $date_formats = apply_filters('geodir_date_formats',$date_formats);
                            ?>
                            <select name="extra[date_format]" id="date_format">
                                <?php
                                foreach($date_formats as $format){
                                    $selected = '';
                                    if(esc_attr($extra['date_format'])==$format){
                                        $selected = "selected='selected'";
                                    }
                                    echo "<option $selected value='$format'>$format       (".date_i18n( $format, time()).")</option>";
                                }
                                ?>
                            </select>
                            
                        </div>
                    </li>
                <?php
                }
                    break;
				case 'file': {
					$allowed_file_types = geodir_allowed_mime_types();
					
					$extra_fields = isset($field_info->extra_fields) && $field_info->extra_fields != '' ? maybe_unserialize($field_info->extra_fields) : '';
					$gd_file_types = !empty($extra_fields) && !empty($extra_fields['gd_file_types']) ? $extra_fields['gd_file_types'] : array('*');
					?>
					<li>
                        <label for="gd_file_types" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Allowed file types :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Select file types to allowed for file uploading. (Select multiple file types by holding down "Ctrl" key.)', 'geodirectory');?>
                            </div>
                        </label>
						<div class="gd-cf-input-wrap">
							<select name="extra[gd_file_types][]" id="gd_file_types" multiple="multiple" style="height:100px;width:90%;">
								<option value="*" <?php selected(true, in_array('*', $gd_file_types));?>><?php _e('All types', 'geodirectory') ;?></option>
								<?php foreach ( $allowed_file_types as $format => $types ) { ?>
								<optgroup label="<?php echo esc_attr( wp_sprintf(__('%s formats', 'geodirectory'), __($format, 'geodirectory') ) ) ;?>">
									<?php foreach ( $types as $ext => $type ) { ?>
									<option value="<?php echo esc_attr($ext) ;?>" <?php selected(true, in_array($ext, $gd_file_types));?>><?php echo '.' . $ext ;?></option>
									<?php } ?>
								</optgroup>
								<?php } ?>
							</select>			
						</div>
					</li>
					<?php 
					}
					break;

            endswitch;
            if ($field_type != 'fieldset') {
                ?>
                <li>
                    <h3><?php echo __('Custom css', 'geodirectory'); ?></h3>


                <div class="gd-cf-input-wrap">
                    <label for="field_icon" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Upload icon :', 'geodirectory'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Upload icon using media and enter its url path, or enter <a href="http://fortawesome.github.io/Font-Awesome/icons/" target="_blank" >font awesome </a>class eg:"fa fa-home"', 'geodirectory');?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">
                        <input type="text" name="field_icon" id="field_icon"
                               value="<?php if (isset($field_info->field_icon)) {
                                   echo $field_info->field_icon;
                               }?>"/>
                    </div>



                    <label for="css_class" class="gd-cf-tooltip-wrap">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Css class :', 'geodirectory'); ?>
                        <div class="gdcf-tooltip">
                            <?php _e('Enter custom css class for field custom style.', 'geodirectory');?>
                        </div>
                    </label>
                    <div class="gd-cf-input-wrap">
                        <input type="text" name="css_class" id="css_class"
                               value="<?php if (isset($field_info->css_class)) {
                                   echo esc_attr($field_info->css_class);
                               }?>"/>
                    </div>
                </div>
                </li>
            <?php
            }

            switch ($field_type):
                case 'html':
                case 'file':
                case 'url':
                case 'fieldset':
                    break;
                default:
                    ?>
                    <li>
                        <h3><?php
								/**
								 * Filter the section title.
								 *
								 * Filter the section title in custom field form in admin
								 * custom fields settings.
								 *
								 * @since 1.0.0
								 *
								 * @param string $title Title of the section.
								 * @param string $field_type Current field type.
								 */
								echo apply_filters('geodir_advance_custom_fields_heading', __('Posts sort options', 'geodirectory'), $field_type);

                                ?></h3>


                    <?php if (!in_array($field_type, array('multiselect', 'textarea', 'taxonomy')) && $field_type != 'address') { ?>

                        <label for="cat_sort" class="gd-cf-tooltip-wrap">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> <?php _e('Include this field in sorting options :', 'geodirectory'); ?>
                            <div class="gdcf-tooltip">
                                <?php _e('Lets you use this filed as a sorting option, set from sorting options above.', 'geodirectory');?>
                            </div>
                        </label>

                        <div class="gd-cf-input-wrap gd-switch">

                            <input type="radio" id="cat_sort_yes<?php echo $radio_id;?>" name="cat_sort" class="gdri-enabled"  value="1"
                                <?php if (isset($field_info->cat_sort) && $field_info->cat_sort == '1') {
                                    echo 'checked';
                                } ?>/>
                            <label for="cat_sort_yes<?php echo $radio_id;?>" class="gdcb-enable"><span><?php _e('Yes', 'geodirectory'); ?></span></label>

                            <input type="radio" id="cat_sort_no<?php echo $radio_id;?>" name="cat_sort" class="gdri-disabled" value="0"
                                <?php if ((isset($field_info->cat_sort) && !$field_info->cat_sort) || !isset($field_info->cat_sort)) {
                                    echo 'checked';
                                } ?>/>
                            <label for="cat_sort_no<?php echo $radio_id;?>" class="gdcb-disable"><span><?php _e('No', 'geodirectory'); ?></span></label>

                        </div>
                    </li>

                <?php } ?>

                    <?php
                    /**
                     * Called at the end of the advanced custom fields settings page loop.
                     *
                     * Can be used to add or deal with different settings types.
                     *
                     * @since 1.0.0
                     * @param object $field_info The current fields info.
                     */
                    do_action('geodir_advance_custom_fields', $field_info);?>

                 
                <?php endswitch; ?>
            <li>

                <label for="save" class="gd-cf-tooltip-wrap">
                    <h3></h3>
                </label>
                <div class="gd-cf-input-wrap">
                    <input type="button" class="button button-primary" name="save" id="save" value="<?php echo esc_attr(__('Save','geodirectory'));?>"
                           onclick="save_field('<?php echo esc_attr($result_str); ?>')"/>
                    <?php if (!$default): ?>
                        <a href="javascript:void(0)"><input type="button" name="delete" value="<?php echo esc_attr(__('Delete','geodirectory'));?>"
                                                            onclick="delete_field('<?php echo esc_attr($result_str); ?>', '<?php echo $nonce; ?>')"
                                                            class="button"/></a>
                    <?php endif; ?>
                </div>
            </li>
        </ul>
    </div>
    </form>
</li>