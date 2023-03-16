<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
global $aui_bs5;
$user_id        = ! empty( $key_data['user_id'] ) ? absint( $key_data['user_id'] ) : get_current_user_id();
$user           = get_user_by( 'id', $user_id );
/* translators: 1: user display name 2: user ID 3: user email */
$user_string    = sprintf(
	esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'geodirectory' ),
	$user->display_name,
	absint( $user->ID ),
	$user->user_email
);
?>
<div id="key-fields" class="settings-panel metabox-holder accordion">
	<div class="card p-0 mw-100 border-0 shadow-sm" style="overflow: initial;">
		<div class="card-header bg-white rounded-top">
			<h2	class="gd-settings-title h5 mb-0 "><?php echo __( 'Key details', 'geodirectory' ); ?></h2>
		</div>
		<div id="gd_ie_imreviews" class="gd-hndle-pbox card-body">
	<input type="hidden" id="key_id" value="<?php echo esc_attr( $key_id ); ?>" />

			<?php
			echo aui()->input(
				array(
					'label_col'        => '3',
					'type'      => 'text',
					'label_class'=> 'font-weight-bold fw-bold',
					'label_type'        => 'horizontal',
					'label'       => __( 'Description', 'geodirectory' ) . geodir_help_tip( __( 'Friendly name for identifying this key.', 'geodirectory' )),
					'class'     => 'mw-100',
					'id'         => 'key_description',
					'name'         => 'key_description',
					'value'    => esc_attr( $key_data['description'] ),
				)
			);
			?>
			<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row">
				<label for="key_user" class="font-weight-bold fw-bold  col-sm-3 col-form-label"><?php _e('User', 'geodirectory'); ?></label>
				<div class="col-sm-9">
					<?php

					geodir_dropdown_users( array(
						'name' => 'key_user',
						'selected' => $user_id,
						'include_selected' => true,
						'show' => 'name_id_email',
						'orderby' => 'display_name',
						'class' => 'gd-user-search aui-select2 mw-100 ' . ( $aui_bs5 ? 'form-select' : 'custom-select' )
					) );
					?>
				</div>
			</div>

			<?php
				echo aui()->select(
					array(
						'label_col'        => '3',
						'label_class'=> 'font-weight-bold fw-bold',
						'label_type'        => 'horizontal',
						'label'       => __( 'Permissions', 'geodirectory' ) . geodir_help_tip( __( 'Select the access type of these keys', 'geodirectory' )),
						'class'     => 'mw-100',
						'id'         => 'key_permissions',
						'name'         => 'key_permissions',
						'value'    => $key_data['permissions'],
						'options' => array(
							'read'       => __( 'Read', 'geodirectory' ),
							'write'      => __( 'Write', 'geodirectory' ),
							'read_write' => __( 'Read/Write', 'geodirectory' ),
						),
					)
				);
			?>
			<?php if ( 0 !== $key_id ) : ?>
				<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row">
					<label for="key_user" class="font-weight-bold fw-bold  col-sm-3 col-form-label"><?php _e('Consumer key ending in', 'geodirectory'); ?></label>
					<div class="col-sm-9">
						<div class="mt-2"><code>&hellip;<?php echo esc_html( $key_data['truncated_key'] ); ?></code></div>
					</div>
				</div>

				<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row">
					<label for="key_user" class="font-weight-bold fw-bold  col-sm-3 col-form-label"><?php _e('Last access', 'geodirectory'); ?></label>
					<div class="col-sm-9">
						<div class="mt-2"><?php
						if ( ! empty( $key_data['last_access'] ) ) {
							/* translators: 1: last access date 2: last access time */
							$date = sprintf( __( '%1$s at %2$s', 'geodirectory' ), date_i18n( geodir_date_format(), strtotime( $key_data['last_access'] ) ), date_i18n( geodir_time_format(), strtotime( $key_data['last_access'] ) ) );

							echo apply_filters( 'geodir_api_key_last_access_datetime', $date, $key_data['last_access'] );
						} else {
							_e( 'Unknown', 'geodirectory' );
						}
						?></div>
					</div>
				</div>
			<?php endif ?>


	<?php do_action( 'geodir_admin_key_fields', $key_data ); ?>

	<?php
		if ( 0 == $key_id ) {
			echo aui()->button(
				array(
					'type'      => 'button',
					'content'   => __('Generate API key', 'geodirectory'),
					'id'    => 'update_api_key'
					//'href'      => 'javascript:void(0)',
					//'onclick'   => "jQuery('#gd_im_catplupload-browse-button').trigger('click');"
				)
			);
		} else {
			echo aui()->button(
				array(
					'type'      => 'button',
					'content'   => __('Save changes', 'geodirectory'),
					'id'    => 'update_api_key'
				)
			);
			echo aui()->button(
				array(
					'type'      => 'a',
					'content'   => __('Revoke key', 'geodirectory'),
					'id'    => 'update_api_key',
					'class' => 'btn btn-danger ml-2 ms-2',
					'href'  => esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=gd-settings&tab=api&section=keys' ) ), 'revoke' ) ),
				)
			);

		}
	?>
		</div>
	</div>
</div>

<script type="text/template" id="tmpl-api-keys-template">
	<p id="copy-error" class="alert alert-info"></p>
	<?php
	echo aui()->input(
		array(
			'id'                => 'key_consumer_key',
			'name'              => 'key_consumer_key',
			'label_type'        => 'horizontal',
			'label_col'         => '3',
			'class'             => '',
			'label_class'       => 'font-weight-bold fw-bold',
			'wrap_class'        => '',
			'label'             => __( 'Consumer key', 'geodirectory' ),
			'type'              => 'text',
			'placeholder'       => '',
			'value'             => '{{ data.consumer_key }}',
			'extra_attributes'  => array( 'readonly' => 'readonly' ),
			'input_group_right' => '<button class="btn btn-primary copy-key"  data-tip="' . esc_attr__( 'Copied!', 'geodirectory' ) . '" type="button">' . esc_attr__( 'Copy', 'geodirectory' ) . '</button>',
			'element_require'   => '',
		)
	);

	echo aui()->input(
		array(
			'id'                => 'key_consumer_secret',
			'name'              => 'key_consumer_secret',
			'label_type'        => 'horizontal',
			'label_col'         => '3',
			'class'             => '',
			'label_class'       => 'font-weight-bold fw-bold',
			'wrap_class'        => '',
			'label'             => __( 'Consumer secret', 'geodirectory' ),
			'type'              => 'text',
			'placeholder'       => '',
			'value'             => '{{ data.consumer_secret }}',
			'extra_attributes'  => array( 'readonly' => 'readonly' ),
			'input_group_right' => '<button class="btn btn-primary copy-secret"  data-tip="' . esc_attr__( 'Copied!', 'geodirectory' ) . '" type="button">' . esc_attr__( 'Copy', 'geodirectory' ) . '</button>',
			'element_require'   => '',
		)
	);
	?>
	<div data-argument="keys-qrcode" class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row">
		<label for="keys-qrcode" class="font-weight-bold fw-bold  col-sm-3 col-form-label"><?php _e( 'QRCode', 'geodirectory' ); ?></label>
		<div class="col-sm-9"><div class="input-group"><div id="keys-qrcode"></div></div>
	</div>
</script>
