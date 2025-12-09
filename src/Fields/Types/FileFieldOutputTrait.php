<?php
/**
 * File/Upload Field Output Rendering Trait
 *
 * Handles output rendering for file and upload fields.
 * Supports images, audio, video, and other file types.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

use GeoDir_Media;

/**
 * File field output methods.
 *
 * @since 3.0.0
 */
trait FileFieldOutputTrait {

	/**
	 * Render the output HTML for file field type.
	 *
	 * Replaces: geodir_cf_file()
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Use the $gd_post directly (no DB call needed - already has all custom fields!)
		if ( ! is_object( $gd_post ) ) {
			$gd_post = (object) $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return '';
		}

		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$html_var = $this->field_data['htmlvar_name'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post->{$html_var} = 'example@example.com'; // Demo placeholder
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;

		if ( empty( $post_id ) ) {
			return '';
		}

		$extra_fields = geodir_parse_cf_extra_fields( $this->field_data );
		$file_limit = ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
		$file_limit = apply_filters( 'geodir_custom_field_file_limit', $file_limit, $this->field_data, $gd_post );
		$revision_id = '';
		$file_status = '1';

		// Preview - show all statuses to admin & post author
		if ( is_preview() ) {
			if ( ! empty( $post_id ) ) {
				$revision_id = $post_id;
			}

			if ( geodir_listing_belong_to_current_user( $post_id ) ) {
				$file_status = '';
			}
		}

		$files = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $html_var, $file_limit, $revision_id, '', $file_status );

		if ( empty( $files ) ) {
			return '';
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			$value_raw = ( isset( $gd_post->{$html_var} ) ? stripslashes_deep( $gd_post->{$html_var} ) : '' );
			return apply_filters( 'geodir_cf_file_output_value_raw', $value_raw, $files, $location, $this->field_data, $output );
		}

		$allowed_file_types = ! empty( $extra_fields['gd_file_types'] ) && is_array( $extra_fields['gd_file_types'] ) && ! in_array( '*', $extra_fields['gd_file_types'] ) ? $extra_fields['gd_file_types'] : '';

		$upload_dir = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir'];
		$upload_baseurl = $upload_dir['baseurl'];
		$file_paths = '';
		$file_urls = [];

		foreach ( $files as $file ) {
			$file_path = isset( $file->file ) ? $file->file : '';
			$title = isset( $file->title ) && $file->title != '' ? strip_tags( stripslashes_deep( $file->title ) ) : '';
			$desc = isset( $file->caption ) ? stripslashes_deep( $file->caption ) : '';
			$url = $upload_baseurl . $file_path;
			$file_urls[] = $url;
			$output_item = '';

			if ( ! empty( $file ) ) {
				$image_name_arr = explode( '/', $url );
				$curr_img_dir = $image_name_arr[ count( $image_name_arr ) - 2 ];
				$filename = end( $image_name_arr );
				$img_name_arr = explode( '.', $filename );

				$arr_file_type = wp_check_filetype( $filename );
				if ( empty( $arr_file_type['ext'] ) || empty( $arr_file_type['type'] ) ) {
					continue;
				}

				$uploaded_file_type = $arr_file_type['type'];
				$file_ext = $arr_file_type['ext'];

				if ( ! empty( $allowed_file_types ) && ! in_array( $file_ext, $allowed_file_types ) ) {
					continue; // Invalid file type
				}

				$ext_path = '_' . $html_var . '_';
				if ( $title ) {
					$_filename = $title;
				} else {
					$_filename = explode( $ext_path, $filename );
					$_filename = $_filename[ count( $_filename ) - 1 ];
				}

				/**
				 * Filter the file display filename.
				 *
				 * @since 2.0.0.67
				 *
				 * @param string $filename  The display filename.
				 * @param object $gd_post   Post object.
				 * @param object $file      File object.
				 * @param array  $cf        Custom field data.
				 */
				if ( has_filter( 'geodir_cf_file_' . $html_var . '_filename' ) ) {
					$_filename = apply_filters( 'geodir_cf_file_' . $html_var . '_filename', $_filename, $gd_post, $file, $this->field_data );
				}

				$image_file_types = geodir_image_mime_types();
				$audio_file_types = [ 'audio/mpeg', 'audio/ogg', 'audio/mp4', 'audio/vnd.wav', 'audio/basic', 'audio/mid' ];

				$file_type = 'unknown';
				$wrap_class = '';
				$design_style = $this->get_design_style();

				if ( in_array( $uploaded_file_type, $image_file_types ) ) { // Image
					$file_type = 'image';
					$wrap_class = ' geodir-images';

					$image_wrap_class = '';
					$image_class = 'img-responsive';
					if ( $design_style ) {
						$image_wrap_class = 'embed-has-action';
						$image_class .= ' mw-100 embed-responsive-item embed-item-cover-xy';
					}
					$lightbox_attrs = apply_filters( 'geodir_link_to_lightbox_attrs', '' );

					$output_item .= '<span class="geodir-cf-file-name text-break clearfix mb-1"><i aria-hidden="true" class="fa fa-file-image"></i> ' . esc_html( $_filename ) . '</span>';
					$output_item .= '<a href="' . esc_url( $url ) . '" class="geodir-lightbox-image ' . $image_wrap_class . '" data-lity ' . $lightbox_attrs . '>';

					$image_params = [
						'size'  => 'medium',
						'align' => '',
						'class' => $image_class,
					];

					/**
					 * Filter image file output parameters.
					 *
					 * @since 2.1.0.17
					 *
					 * @param array  $image_params Image parameters.
					 * @param object $file         Image file object.
					 */
					$image_params = apply_filters( 'geodir_cf_file_output_image_params', $image_params, $file );

					$image_tag = geodir_get_image_tag( $file, $image_params['size'], $image_params['align'], $image_params['class'] );
					$metadata = ! empty( $file->metadata ) ? maybe_unserialize( $file->metadata ) : [];
					if ( $image_params['size'] != 'thumbnail' ) {
						$image_tag = wp_image_add_srcset_and_sizes( $image_tag, $metadata, 0 );
					}
					$output_item .= $image_tag;
					if ( $design_style ) {
						$output_item .= '<i class="fas fa-search-plus" aria-hidden="true"></i>';
					}
					$output_item .= '</a>';
				} elseif ( in_array( $uploaded_file_type, $audio_file_types ) || in_array( $file_ext, wp_get_audio_extensions() ) ) { // Audio
					$file_type = 'audio';
					$output_item .= '<span class="geodir-cf-file-name text-break clearfix"><i aria-hidden="true" class="fa fa-file-audio"></i> ' . esc_html( $_filename ) . '</span>';
					$output_item .= do_shortcode( '[audio src="' . esc_url( $url ) . '" ]' );
				} elseif ( in_array( $file_ext, wp_get_video_extensions() ) ) { // Video
					$file_type = 'video';
					$output_item .= '<span class="geodir-cf-file-name text-break clearfix"><i aria-hidden="true" class="fa fa-file-video"></i> ' . esc_html( $_filename ) . '</span>';
					$output_item .= do_shortcode( wp_embed_handler_video( [], [], $url, [] ) );
				} else { // Other file types
					$output_item .= '<a class="gd-meta-file" href="' . esc_url( $url ) . '" target="_blank" data-lity title="' . esc_attr( $title ) . '"><i aria-hidden="true" class="fa fa-file"></i> ' . esc_html( $_filename ) . '</a>';
				}

				$output_item = '<div class="geodir-custom-field-file clearfix geodir-cf-file-' . esc_attr( $file_ext ) . ' geodir-cf-type-' . esc_attr( $file_type ) . $wrap_class . '"> ' . $output_item . '</div>';

				/**
				 * Filter the file output html.
				 *
				 * @since 2.0.0.81
				 *
				 * @param string $output_item The file html output.
				 * @param object $file        The file object.
				 * @param string $location    The location to output the html.
				 * @param array  $cf          The custom field array.
				 * @param string $output      The output string that tells us what to output.
				 */
				$file_paths .= apply_filters( 'geodir_cf_file_output_item', $output_item, $file, $location, $this->field_data, $output );
			}
		}

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			$value_strip = ! empty( $file_urls ) ? $file_urls[0] : '';
			return apply_filters( 'geodir_cf_file_output_value_strip', $value_strip, $file_urls, $files, $location, $this->field_data, $output );
		}

		// Build full HTML output
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-file" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
		}

		// Label
		if ( $output == '' || isset( $output['label'] ) ) {
			$frontend_title = isset( $this->field_data['frontend_title'] ) ? trim( $this->field_data['frontend_title'] ) : '';
			if ( $frontend_title ) {
				$html .= '<span class="geodir_post_meta_title ' . esc_attr( $maybe_secondary_class ) . '">' . __( $frontend_title, 'geodirectory' ) . ': </span>';
			}
		}

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}

		// Value
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $file_paths;
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function process_icon();
	abstract protected function is_block_demo();
	abstract protected function get_design_style();
}
