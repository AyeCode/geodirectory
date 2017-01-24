<?php
/**
 * Creates custom fields
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

$data_type = isset($_REQUEST['data_type']) ? sanitize_text_field($_REQUEST['data_type']) : '';
$field_type = isset($_REQUEST['field_type']) ? sanitize_text_field($_REQUEST['field_type']) : '';
$field_type_key = isset($_REQUEST['field_type_key']) ? sanitize_text_field($_REQUEST['field_type_key']) : '';
$field_action = isset($_REQUEST['field_ins_upd']) ? sanitize_text_field($_REQUEST['field_ins_upd']) : '';
$field_id = isset($_REQUEST['field_id']) ? sanitize_text_field($_REQUEST['field_id']) : '';

$field_id = $field_id != '' ? trim($field_id, '_') : $field_id;

$field_ids = array();
if (!empty($_REQUEST['licontainer']) && is_array($_REQUEST['licontainer'])) {
    foreach ($_REQUEST['licontainer'] as $lic_id) {
        $field_ids[] = sanitize_text_field($lic_id);
    }
}

/* ------- check nonce field ------- */
if (isset($_REQUEST['update']) && $_REQUEST['update'] == "update" && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    echo godir_set_field_order($field_ids);
}

if (isset($_REQUEST['update']) && $_REQUEST['update'] == "update" && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    $response = godir_set_sort_field_order($field_ids);
    if (is_array($response)) {
        wp_send_json($response);
    } else {
        echo $response;
    }
}

/* ---- Show field form in admin ---- */
if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    geodir_custom_field_adminhtml($field_type, $field_id, $field_action,$field_type_key);
}

if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    geodir_custom_sort_field_adminhtml($field_type, $field_id, $field_action,$field_type_key);
}

/* ---- Delete field ---- */
if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
        return;
    
    echo geodir_custom_field_delete($field_id);
}

if ($field_id != '' && $field_action == 'delete' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
        return;
    
    echo geodir_custom_sort_field_delete($field_id);
}

/* ---- Save field  ---- */
if ($field_id != '' && $field_action == 'submit' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
        return;

    foreach ($_REQUEST as $pkey => $pval) {
        if (is_array($_REQUEST[$pkey]) || $pkey=='default_value') {
            $tags = 'skip_field';
        } else {
            $tags = '';
        }

        if ($tags != 'skip_field') {
            $_REQUEST[$pkey] = strip_tags($_REQUEST[$pkey], $tags);
        }
    }

    $return = geodir_custom_field_save($_REQUEST);

    if (is_int($return)) {
        $lastid = $return;
        geodir_custom_field_adminhtml($field_type, $lastid, 'submit',$field_type_key);
    } else {
        echo $return;
    }
}

/* ---- Save sort field  ---- */
if ($field_id != '' && $field_action == 'submit' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'custom_fields_' . $field_id))
        return;

    foreach ($_REQUEST as $pkey => $pval) {
        if (is_array($_REQUEST[$pkey])) {
            $tags = 'skip_field';
        } else {
            $tags = '';
        }

        if ($tags != 'skip_field') {
            $_REQUEST[$pkey] = strip_tags($_REQUEST[$pkey], $tags);
        }
    }

    $return = geodir_custom_sort_field_save($_REQUEST);

    if (is_int($return)) {
        $lastid = $return;
        $default = false;
        geodir_custom_sort_field_adminhtml($field_type, $lastid, 'submit', $default);
    } else {
        echo $return;
    }
}