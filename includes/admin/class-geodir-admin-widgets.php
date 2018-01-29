<?php
/**
 * Add widget settings.
 *
 * @author      GeoDirectory
 * @category    Admin
 * @package     GeoDirectory/Admin/Widgets
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Status Class.
 */
class GeoDir_Admin_Widgets {

	public static function init(){
		add_filter( 'widget_update_callback', array(__CLASS__, 'geodir_widget_update_callback'), 10, 4 );
		add_action( 'in_widget_form', array(__CLASS__,'geodir_widget_visibility_options'), 100, 3 );

	}


	public static function geodir_widget_visibility_options( $widget, $form, $instance ) {
		if ( empty( $widget->widget_options['geodirectory'] ) ) {
			return;
		}

		$gd_widget_pages = geodir_widget_pages_options();

		$showhide = !empty( $instance['gd_wgt_showhide'] ) ? $instance['gd_wgt_showhide'] : 'gd';
		$restrict = !empty( $instance['gd_wgt_restrict'] ) ? $instance['gd_wgt_restrict'] : array();

		$showhide = apply_filters( 'geodir_widget_showhide_value', $showhide, $widget, $form, $instance );
		$restrict = apply_filters( 'geodir_widget_restrict_value', $restrict, $widget, $form, $instance );
		if ( !is_array( $restrict ) ) {
			$restrict = array();
		}

		if ( geodir_is_detail_page_widget( $widget->id_base ) ) {
			?>
			<p class="gd-wgt-wrap gd-wgt-restrict-wrap">
				<input type="hidden" name="<?php echo esc_attr( $widget->get_field_name( 'gd_wgt_showhide' ) ); ?>" id="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_showhide' ) ); ?>" value="show_on" />
				<label for="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_restrict' ) ); ?>"><?php _e( 'Show widget on selected pages', 'geodirectory' ); ?></label>
				<select name="<?php echo esc_attr( $widget->get_field_name( 'gd_wgt_restrict' ) ); ?>[]" id="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_restrict' ) ); ?>" class="widefat" multiple="multiple" size="3" style="font:inherit">
					<option value="gd-detail" <?php selected( ( !empty( $restrict ) && in_array( 'gd-detail', $restrict ) ? true : false ), true ); ?>><?php _e( 'Listing Detail Page', 'geodirectory' ); ?></option>
					<option value="gd-preview" <?php selected( ( !empty( $restrict ) && in_array( 'gd-preview', $restrict ) ? true : false ), true ); ?>><?php _e( 'Listing Preview Page', 'geodirectory' ); ?></option>
				</select>
			</p>
			<p class="description"><?php _e( 'This widget is visible only on GeoDirectory Listing Detail & Preview page.', 'geodirectory' ); ?></p>
			<?php
		} else {
			?>
			<p class="gd-wgt-wrap gd-wgt-showhide-wrap">
				<label for="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_showhide' ) ); ?>"><?php _e( 'Show / Hide Widget:', 'geodirectory' ); ?></label>
				<select name="<?php echo esc_attr( $widget->get_field_name( 'gd_wgt_showhide' ) ); ?>" id="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_showhide' ) ); ?>" class="widefat">
					<option value="show" <?php selected( $showhide, 'show' ); ?>><?php _e( 'Show widget everywhere', 'geodirectory' ); ?></option>
					<option value="gd" <?php selected( $showhide, 'gd' ); ?>><?php _e( 'Show widget only on GD pages', 'geodirectory' ); ?></option>
					<option value="show_on" <?php selected( $showhide, 'show_on' ); ?>><?php _e( 'Show widget on selected pages', 'geodirectory' ); ?></option>
					<option value="hide_on" <?php selected( $showhide, 'hide_on' ); ?>><?php _e( 'Hide widget on selected pages', 'geodirectory' ); ?></option>
					<option value="hide" <?php selected( $showhide, 'hide' ); ?>><?php _e( 'Hide widget everywhere', 'geodirectory' ); ?></option>
				</select>
			</p>
			<p class="gd-wgt-wrap gd-wgt-restrict-wrap">
				<select name="<?php echo esc_attr( $widget->get_field_name( 'gd_wgt_restrict' ) ); ?>[]" id="<?php echo esc_attr( $widget->get_field_id( 'gd_wgt_restrict' ) ); ?>" class="widefat" multiple="multiple" size="6" style="font:inherit">
					<?php foreach ( $gd_widget_pages as $group => $options ) { ?>
						<?php if ( !empty( $group ) && !empty( $options['pages'] ) ) { $label = !empty( $options['label'] ) ? esc_attr( $options['label'] ) : ''; ?>
							<optgroup label="<?php echo $label; ?>">
								<?php foreach ( $options['pages'] as $value => $label ) { $selected = !empty( $restrict ) && in_array( $group . '-' . $value, $restrict ) ? true : false; ?>
									<option value="<?php echo esc_attr( $group . '-' . $value ); ?>" <?php selected( $selected, true ); ?>><?php echo $label; ?></option>
								<?php } ?>
							</optgroup>
						<?php } ?>
					<?php } ?>
				</select>
			</p>
			<?php
		}
	}

	public static function geodir_widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
		if ( !empty( $widget->widget_options['geodirectory'] ) ) {
			$instance['gd_wgt_showhide'] = !empty( $new_instance['gd_wgt_showhide'] ) ? $new_instance['gd_wgt_showhide'] : '';
			$instance['gd_wgt_restrict'] = !empty( $new_instance['gd_wgt_restrict'] ) && is_array( $new_instance['gd_wgt_restrict'] ) ? $new_instance['gd_wgt_restrict'] : array();
		}

		return $instance;
	}
}
