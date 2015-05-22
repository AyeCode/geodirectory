<?php
/**
 * Creates custom fields
 *
 * @since 1.0.0
 * @package GeoDirectory
 */
$data_type = '';

if (isset($_REQUEST['data_type']))
    $data_type = trim($_REQUEST['data_type']);

$field_type = isset($_REQUEST['field_type']) ? trim($_REQUEST['field_type']) : '';

$field_id = isset($_REQUEST['field_id']) ? trim($_REQUEST['field_id'], '_') : '';

$field_action = isset($_REQUEST['field_ins_upd']) ? trim($_REQUEST['field_ins_upd']) : '';


/* ------- check nonce field ------- */

if (isset($_REQUEST['update']) && $_REQUEST['update'] == "update" && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    $field_ids = $_REQUEST['licontainer'];
    echo godir_set_field_order($field_ids);
}

if (isset($_REQUEST['update']) && $_REQUEST['update'] == "update" && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    $field_ids = $_REQUEST['licontainer'];
    echo godir_set_sort_field_order($field_ids);
}

/* ---- Show field form in admin ---- */

if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'custom_fields') {
    geodir_custom_field_adminhtml($field_type, $field_id, $field_action);
}


if ($field_type != '' && $field_id != '' && $field_action == 'new' && isset($_REQUEST['create_field']) && isset($_REQUEST['manage_field_type']) && $_REQUEST['manage_field_type'] == 'sorting_options') {
    geodir_custom_sort_field_adminhtml($field_type, $field_id, $field_action);
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

        if (is_array($_REQUEST[$pkey])) {
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
        geodir_custom_field_adminhtml($field_type, $lastid, 'submit');
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