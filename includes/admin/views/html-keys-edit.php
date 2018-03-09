<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="key-fields" class="settings-panel">
	<h2><?php _e( 'Key details', 'geodirectory' ); ?></h2>

	<input type="hidden" id="key_id" value="<?php echo esc_attr( $key_id ); ?>" />

	<table id="api-keys-options" class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="key_description"><?php _e( 'Description', 'geodirectory' ); ?></label>
					<?php echo geodir_help_tip( __( 'Friendly name for identifying this key.', 'geodirectory' ) ); ?>
				</th>
				<td class="forminp">
					<input id="key_description" type="text" class="input-text regular-text" value="<?php echo esc_attr( $key_data['description'] ); ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="key_user"><?php _e( 'User', 'geodirectory' ); ?></label>
					<?php echo geodir_help_tip( __( 'Owner of these keys.', 'geodirectory' ) ); ?>
				</th>
				<td class="forminp">
					<?php
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
					<?php geodir_dropdown_users( array(
                            'name' => 'key_user',
                            'selected' => $user_id,
                            'include_selected' => true,
                            'show' => 'name_id_email',
                            'orderby' => 'display_name',
                            'class' => 'gd-user-search geodir-select regular-text'
                        ) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="key_permissions"><?php _e( 'Permissions', 'geodirectory' ); ?></label>
					<?php echo geodir_help_tip( __( 'Select the access type of these keys.', 'geodirectory' ) ); ?>
				</th>
				<td class="forminp">
					<select id="key_permissions" class="geodir-select regular-text">
						<?php
							$permissions = array(
								'read'       => __( 'Read', 'geodirectory' ),
								'write'      => __( 'Write', 'geodirectory' ),
								'read_write' => __( 'Read/Write', 'geodirectory' ),
							);

							foreach ( $permissions as $permission_id => $permission_name ) : ?>
							<option value="<?php echo esc_attr( $permission_id ); ?>" <?php selected( $key_data['permissions'], $permission_id, true ); ?>><?php echo esc_html( $permission_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<?php if ( 0 !== $key_id ) : ?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<?php _e( 'Consumer key ending in', 'geodirectory' ); ?>
					</th>
					<td class="forminp">
						<code>&hellip;<?php echo esc_html( $key_data['truncated_key'] ); ?></code>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<?php _e( 'Last access', 'geodirectory' ); ?>
					</th>
					<td class="forminp">
						<span><?php
							if ( ! empty( $key_data['last_access'] ) ) {
								/* translators: 1: last access date 2: last access time */
								$date = sprintf( __( '%1$s at %2$s', 'geodirectory' ), date_i18n( geodir_date_format(), strtotime( $key_data['last_access'] ) ), date_i18n( geodir_time_format(), strtotime( $key_data['last_access'] ) ) );

								echo apply_filters( 'geodir_api_key_last_access_datetime', $date, $key_data['last_access'] );
							} else {
								_e( 'Unknown', 'geodirectory' );
							}
						?></span>
					</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>

	<?php do_action( 'geodir_admin_key_fields', $key_data ); ?>

	<?php
		if ( 0 == $key_id ) {
			submit_button( __( 'Generate API key', 'geodirectory' ), 'primary', 'update_api_key' );
		} else {
			?>
			<p class="submit">
				<?php submit_button( __( 'Save changes', 'geodirectory' ), 'primary', 'update_api_key', false ); ?>
				<a style="color: #a00; text-decoration: none; margin-left: 10px;" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'revoke-key' => $key_id ), admin_url( 'admin.php?page=gd-settings&tab=api&section=keys' ) ), 'revoke' ) ); ?>"><?php _e( 'Revoke key', 'geodirectory' ); ?></a>
			</p>
			<?php
		}
	?>
</div>

<script type="text/template" id="tmpl-api-keys-template">
	<p id="copy-error"></p>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Consumer key', 'geodirectory' ); ?>
				</th>
				<td class="forminp">
					<input id="key_consumer_key" type="text" value="{{ data.consumer_key }}" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-key" data-tip="<?php esc_attr_e( 'Copied!', 'geodirectory' ); ?>"><?php _e( 'Copy', 'geodirectory' ); ?></button>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'Consumer secret', 'geodirectory' ); ?>
				</th>
				<td class="forminp">
					<input id="key_consumer_secret" type="text" value="{{ data.consumer_secret }}" size="55" readonly="readonly"> <button type="button" class="button-secondary copy-secret" data-tip="<?php esc_attr_e( 'Copied!', 'geodirectory' ); ?>"><?php _e( 'Copy', 'geodirectory' ); ?></button>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php _e( 'QRCode', 'geodirectory' ); ?>
				</th>
				<td class="forminp">
					<div id="keys-qrcode"></div>
				</td>
			</tr>
		</tbody>
	</table>
</script>