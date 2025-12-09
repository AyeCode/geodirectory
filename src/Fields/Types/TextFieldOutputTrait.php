<?php
/**
 * TextField Output Rendering Trait
 *
 * Handles output rendering for text, email, phone, url, time, and datepicker fields.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * TextField output methods.
 *
 * @since 3.0.0
 */
trait TextFieldOutputTrait {

	/**
	 * Render the output HTML for text field types.
	 *
	 * Handles: text, email, phone, url, time, datepicker
	 * Replaces: geodir_cf_text(), geodir_cf_email(), geodir_cf_phone(), geodir_cf_url(), geodir_cf_time(), geodir_cf_datepicker()
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
		$field_type = $this->field_data['field_type'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post = $this->set_demo_content( $gd_post, $field_type, $html_var );
		}

		// Get field value directly from $gd_post (already loaded!)
		$value = isset( $gd_post->{$html_var} ) ? $gd_post->{$html_var} : '';

		// Empty value check
		if ( $value === '' || $value === null ) {
			return '';
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Render based on field type
		switch ( $field_type ) {
			case 'email':
				return $this->render_email_output( $gd_post, $value, $location, $output );

			case 'phone':
				return $this->render_phone_output( $gd_post, $value, $location, $output );

			case 'url':
				return $this->render_url_output( $gd_post, $value, $location, $output );

			case 'time':
				return $this->render_time_output( $gd_post, $value, $location, $output );

			case 'datepicker':
				return $this->render_datepicker_output( $gd_post, $value, $location, $output );

			default:
				return $this->render_text_output( $gd_post, $value, $location, $output );
		}
	}

	/**
	 * Render plain text output.
	 *
	 * @param object $gd_post  Post object.
	 * @param mixed  $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_text_output( $gd_post, $value, $location, $output ) {
		$value = stripslashes_deep( $value );

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		// Handle numeric fields with formatting
		if ( isset( $this->field_data['data_type'] ) && in_array( $this->field_data['data_type'], [ 'INT', 'FLOAT', 'DECIMAL' ] ) ) {
			$value = $this->format_numeric_value( $value );

			if ( $value === '' ) {
				return ''; // Don't output blank prices
			}
		}

		// Handle service_distance special case
		if ( $this->field_data['htmlvar_name'] === 'service_distance' && ! empty( $value ) ) {
			$value = geodir_show_distance( $value );
		}

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $value;
		}

		// Get default icon for text fields
		$default_icon = $this->field_data['htmlvar_name'] === 'geodir_timing' || $this->field_data['htmlvar_name'] === 'timing'
			? ( $this->get_design_style() ? '<i class="fas fa-clock fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-clock" aria-hidden="true"></i>' )
			: '';

		return $this->build_output_wrapper( $value, $output, $default_icon );
	}

	/**
	 * Render email output with obfuscation.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_email_output( $gd_post, $value, $location, $output ) {
		// Special handling for detail page check
		if ( $this->field_data['htmlvar_name'] === 'geodir_email' && ! geodir_is_page( 'detail' ) ) {
			return ''; // Remove Send Enquiry from listings page
		}

		$email = sanitize_email( $value );

		if ( empty( $email ) ) {
			return '';
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $email;
		}

		$is_elementor_preview = defined( 'ELEMENTOR_VERSION' ) && class_exists( 'GeoDir_Elementor' ) && \GeoDir_Elementor::is_elementor_view();
		$email_link = '';

		// Build email link with obfuscation for non-ajax/non-rest requests
		if ( $email && $email !== 'testing@example.com' && ( $e_split = explode( '@', $email ) ) && ! defined( 'REST_REQUEST' ) && ! $is_elementor_preview && ! wp_doing_ajax() && ! isset( $output['strip'] ) ) {
			$email_name = apply_filters( 'geodir_email_field_name_output', $email, $this->field_data );
			$email_link = '<a href="javascript:void(0)" onclick="javascript:window.open(\'mailto:\'+([\'' . $e_split[0] . '\',\'' . $e_split[1] . '\']).join(\'@\'),\'_blank\')">' . str_replace( "@", "<!---->@<!---->", $email_name ) . '</a>';
		} elseif ( $email && ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $is_elementor_preview || wp_doing_ajax() ) && ! isset( $output['strip'] ) ) {
			$email_name = apply_filters( 'geodir_email_field_name_output', $email, $this->field_data );
			$email_link = "<a href='mailto:$email' target='_blank'>$email_name</a>";
		} else {
			$email_link = $email;
		}

		$email_link = apply_filters( 'geodir_custom_field_output_email_value', $email_link, $gd_post, $location, $this->field_data, $output );

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $email_link;
		}

		$design_style = $this->get_design_style();
		$default_icon = $design_style ? '<i class="far fa-envelope fa-fw" aria-hidden="true"></i> ' : '<i class="far fa-envelope" aria-hidden="true"></i>';

		return $this->build_output_wrapper( stripslashes( $email_link ), $output, $default_icon );
	}

	/**
	 * Render phone output with tel: link.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_phone_output( $gd_post, $value, $location, $output ) {
		$raw_value = stripslashes( $value );

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $raw_value;
		}

		$phone_link = '<a href="tel:' . preg_replace( '/[^0-9+]/', '', $value ) . '">' . $raw_value . '</a>';

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $phone_link;
		}

		$design_style = $this->get_design_style();
		$default_icon = $design_style ? '<i class="fas fa-phone fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-phone" aria-hidden="true"></i>';

		return $this->build_output_wrapper( $phone_link, $output, $default_icon );
	}

	/**
	 * Render URL output with external link.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_url_output( $gd_post, $value, $location, $output ) {
		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		$design_style = $this->get_design_style();
		$a_url = geodir_parse_custom_field_url( $value );
		$website = ! empty( $a_url['url'] ) ? $a_url['url'] : '';
		$title = ! empty( $a_url['label'] ) ? $a_url['label'] : $this->field_data['frontend_title'];

		if ( ! empty( $this->field_data['default_value'] ) ) {
			$title = $this->field_data['default_value'];
		}

		$title = $title !== '' ? __( stripslashes( $title ), 'geodirectory' ) : '';
		$post_id = isset( $gd_post->ID ) ? $gd_post->ID : 0;

		// Nofollow for external links
		$rel = strpos( $website, get_site_url() ) !== false ? '' : 'rel="nofollow"';

		$link_html = '<a href="' . esc_url( $website ) . '" target="_blank" ' . $rel . '>' . apply_filters( 'geodir_custom_field_website_name', $title, $website, $post_id ) . '</a>';

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $website;
		}

		// Determine icon
		$default_icon = '';
		$field_name = isset( $this->field_data['name'] ) ? $this->field_data['name'] : '';

		if ( $field_name === 'facebook' ) {
			$default_icon = $design_style ? '<i class="fab fa-facebook-square fa-fw" aria-hidden="true"></i> ' : '<i class="fab fa-facebook-square" aria-hidden="true"></i>';
		} elseif ( $field_name === 'twitter' ) {
			$default_icon = $design_style ? '<i class="fab fa-twitter-square fa-fw" aria-hidden="true"></i> ' : '<i class="fab fa-twitter-square" aria-hidden="true"></i>';
		} else {
			$default_icon = $design_style ? '<i class="fas fa-link fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-link" aria-hidden="true"></i>';
		}

		return $this->build_output_wrapper( $link_html, $output, $default_icon );
	}

	/**
	 * Render time output with formatting.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_time_output( $gd_post, $value, $location, $output ) {
		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		$formatted_time = date_i18n( get_option( 'time_format' ), strtotime( $value ) );

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $formatted_time;
		}

		$design_style = $this->get_design_style();
		$default_icon = $design_style ? '<i class="fas fa-clock fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-clock" aria-hidden="true"></i>';

		return $this->build_output_wrapper( $formatted_time, $output, $default_icon );
	}

	/**
	 * Render datepicker output with formatting.
	 *
	 * @param object $gd_post  Post object.
	 * @param string $value    Field value.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string
	 */
	protected function render_datepicker_output( $gd_post, $value, $location, $output ) {
		// Skip invalid dates
		if ( empty( $value ) || $value === '0000-00-00' ) {
			return '';
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		// Get date format
		$date_format = geodir_date_format();

		if ( ! empty( $this->field_data['extra_fields'] ) ) {
			$extra_fields = stripslashes_deep( maybe_unserialize( $this->field_data['extra_fields'] ) );
			if ( ! empty( $extra_fields['date_format'] ) ) {
				$date_format = $extra_fields['date_format'];
			}
		}

		$formatted_date = date_i18n( $date_format, strtotime( $value ) );

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $formatted_date;
		}

		$design_style = $this->get_design_style();
		$default_icon = $design_style ? '<i class="fas fa-calendar fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-calendar" aria-hidden="true"></i>';

		return $this->build_output_wrapper( $formatted_date, $output, $default_icon );
	}

	/**
	 * Format numeric value with price or number formatting.
	 *
	 * @param mixed $value The numeric value.
	 * @return string Formatted value or empty string.
	 */
	protected function format_numeric_value( $value ) {
		if ( ! isset( $this->field_data['data_type'] ) || ! isset( $this->field_data['extra_fields'] ) || ! $this->field_data['extra_fields'] ) {
			return $value;
		}

		$extra_fields = stripslashes_deep( maybe_unserialize( $this->field_data['extra_fields'] ) );

		// Price formatting
		if ( ! empty( $extra_fields['is_price'] ) ) {
			if ( ! ceil( $value ) > 0 ) {
				return ''; // Don't output blank prices
			}
			return geodir_currency_format_number( $value, $this->field_data );
		}

		// Integer formatting
		if ( $this->field_data['data_type'] === 'INT' && ceil( $value ) > 0 ) {
			return geodir_cf_format_number( $value, $this->field_data );
		}

		// Float/Decimal formatting
		if ( in_array( $this->field_data['data_type'], [ 'FLOAT', 'DECIMAL' ] ) && ceil( $value ) > 0 ) {
			return geodir_cf_format_decimal( $value, $this->field_data );
		}

		return $value;
	}

	/**
	 * Set demo content for block editor.
	 *
	 * @param object $gd_post    Post object.
	 * @param string $field_type Field type.
	 * @param string $html_var   Field htmlvar_name.
	 * @return object Modified post object.
	 */
	protected function set_demo_content( $gd_post, $field_type, $html_var ) {
		switch ( $field_type ) {
			case 'email':
				$gd_post->{$html_var} = 'testing@example.com';
				break;
			case 'phone':
				$gd_post->{$html_var} = '0001010101010';
				break;
			case 'url':
				$gd_post->{$html_var} = 'https://example.com';
				break;
			case 'time':
				$gd_post->{$html_var} = '10:30';
				break;
			case 'datepicker':
				$gd_post->{$html_var} = '25/12/2020';
				break;
			default:
				// Text field demo
				if ( isset( $this->field_data['data_type'] ) ) {
					if ( $this->field_data['data_type'] === 'INT' ) {
						$gd_post->{$html_var} = 100;
					} elseif ( in_array( $this->field_data['data_type'], [ 'FLOAT', 'DECIMAL' ] ) ) {
						$gd_post->{$html_var} = 100.50;
					} else {
						$gd_post->{$html_var} = __( 'Some demo text.', 'geodirectory' );
					}
				} else {
					$gd_post->{$html_var} = __( 'Some demo text.', 'geodirectory' );
				}
				break;
		}

		return $gd_post;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function build_output_wrapper( $content, $output, $default_icon = '' );
	abstract protected function is_block_demo();
	abstract protected function get_design_style();
}
