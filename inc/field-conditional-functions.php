<?php


/**
 * Get conditional field attributes.
 *
 * @since 2.1.1.0
 *
 * @param object $field Field object.
 * @param string $_key Field key. Default empty.
 * @param string $_type Field type. Default empty.
 * @return string Condition fields attributes.
 */
function geodir_conditional_field_attrs( $field, $_key = '', $_type = '' ) {
	$attrs = '';

	if ( ! geodir_design_style() ) {
		return $attrs;
	}

	if ( is_object( $field ) ) {
		$key = ! empty( $field->htmlvar_name ) ? $field->htmlvar_name : '';
		$type = ! empty( $field->field_type ) ? $field->field_type : '';
		$extra_fields = ! empty( $field->extra_fields ) ? $field->extra_fields : '';
	} else if ( is_array( $field ) ) {
		$key = ! empty( $field['htmlvar_name'] ) ? $field['htmlvar_name'] : '';
		$type = ! empty( $field['field_type'] ) ? $field['field_type'] : '';
		$extra_fields = ! empty( $field['extra_fields'] ) ? $field['extra_fields'] : '';
	} else {
		return $attrs;
	}

	if ( $_key ) {
		$key = $_key;
	}

	if ( $_type ) {
		$type = $_type;
	}

	$extra_fields = maybe_unserialize( $extra_fields );

	if ( is_array( $extra_fields ) && ! empty( $extra_fields['cat_display_type'] ) && $key != 'default_category' ) {
		$type = $extra_fields['cat_display_type'];
	}

	$conditions = geodir_parse_field_conditions( $extra_fields );

	return geodir_build_conditional_attrs( $conditions, $key, $type );
}

/**
 * Parse field conditions from extra fields.
 *
 * @since 2.1.1.0
 *
 * @param array|string $extra_fields Extra fields data.
 * @return array Field conditions.
 */
function geodir_parse_field_conditions( $extra_fields ) {
	if ( ! empty( $extra_fields ) && is_scalar( $extra_fields ) && is_serialized( $extra_fields ) ) {
		$extra_fields = maybe_unserialize( $extra_fields );
	}

	$_conditions = ! empty( $extra_fields ) && is_array( $extra_fields ) && ! empty( $extra_fields['conditions'] ) && is_array( $extra_fields['conditions'] ) ? $extra_fields['conditions'] : array();

	$conditions = array();

	if ( ! empty( $_conditions ) ) {
		foreach ( $_conditions as $k => $condition ) {
			if ( ! empty( $condition['action'] ) && ! empty( $condition['field'] ) && ! empty( $condition['condition'] ) ) {
				$conditions[ $k ] = array(
					'action' => stripslashes( $condition['action'] ),
					'field' => stripslashes( $condition['field'] ),
					'condition' => stripslashes( $condition['condition'] ),
					'value' => ( isset( $condition['value'] ) ? stripslashes( $condition['value'] ) : '' )
				);
			}
		}
	}

	return $conditions;
}

/**
 * Prepare conditional fields attributes.
 *
 * @since 2.1.1.0
 *
 * @param array  $conditions Field conditions.
 * @param string $key Field key.
 * @param string $type Field type.
 * @return string Conditional fields attributes.
 */
function geodir_build_conditional_attrs( $conditions, $key = '', $type = '' ) {
	$attrs = 'data-rule-key="' . esc_attr( $key ) . '"';

	if ( $key != '' ) {
		$attrs .= ' data-rule-type="' . esc_attr( $type ) . '"';
	}

	if ( ! empty( $conditions ) ) {
		$attrs .= ' data-has-rule="' . count( $conditions ) . '"';

		foreach ( $conditions as $k => $condition ) {
			$attrs .= ' data-rule-act-' . esc_attr( $k ) . '="' . esc_attr( $condition['action'] ) . '"';
			$attrs .= ' data-rule-fie-' . esc_attr( $k ) . '="' . esc_attr( $condition['field'] ) . '"';
			$attrs .= ' data-rule-con-' . esc_attr( $k ) . '="' . esc_attr( $condition['condition'] ) . '"';
			$attrs .= ' data-rule-val-' . esc_attr( $k ) . '="' . esc_attr( $condition['value'] ) . '"';
		}
	}

	/**
	 * Filter conditional fields attributes.
	 *
	 * @since 2.1.1.0
	 *
	 * @param string $attrs Conditional fields attributes.
	 * @param array  $conditions Field conditions.
	 * @param string $key  Field key.
	 * @param string $type Field type.
	 */
	return apply_filters( 'geodir_build_conditional_attrs', $attrs, $conditions, $key, $type );
}

/**
 * Get conditional field icon.
 *
 * @since 2.1.1.0
 *
 * @param string $attrs Conditional fields attributes.
 * @param array  $field Field array.
 * @return string Icon HTML.
 */
function geodir_conditional_field_icon( $attrs, $field = array() ) {
	if ( ! empty( $attrs ) && strpos( $attrs, 'data-has-rule=' ) !== false ) {
		$icon = '<i class="far fa-eye text-warning c-pointer" data-toggle="tooltip"  title="' . __( 'Has hide conditions', 'geodirectory' ) . '"></i>';
	} else {
		$icon = '';
	}

	/**
	 * Filter conditional field icon.
	 *
	 * @since 2.1.1.0
	 *
	 * @param string $icon Conditional field icon.
	 * @param string $attrs Conditional fields attributes.
	 * @param array  $field Field array.
	 * @return string Icon HTML.
	 */
	return apply_filters( 'geodir_conditional_field_icon', $icon, $attrs, $field );
}


function geodir_cfi_admin_only($cf){
	return !empty($cf['for_admin_use']) ? ' <i class="far fa-eye text-warning c-pointer" data-toggle="tooltip"  title="'.__("Admin only","geodirectory").'"></i>' : '';
}
