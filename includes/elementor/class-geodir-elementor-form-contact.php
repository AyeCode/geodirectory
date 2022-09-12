<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add form action to email the listing email address.
 *
 * Class GeoDir_Elementor_Form_Contact
 */
class GeoDir_Elementor_Form_Contact extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	/**
	 * Run
	 *
	 * Runs the action after submit
	 *
	 * @access public
	 *
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		global $gd_post;
		$settings = $record->get( 'form_settings' );

		$email_to = is_email( $gd_post->email ) ? $gd_post->email : '';

		if ( ! $email_to ) {
			$ajax_handler->add_error_message( __( 'Listing email missing!', 'geodirectory' ) );

			return;
		}

		$send_html  = 'plain' !== $settings['geodir_email_content_type'];
		$line_break = $send_html ? '<br>' : "\n";


		$fields = [
			'email_to'        => $email_to,
			'email_subject'   => sprintf( __( 'New message from "%s"', 'geodirectory' ), get_bloginfo( 'name' ) ),
			'email_content'   => '[all-fields]',
			'email_from_name' => get_bloginfo( 'name' ),
			'email_from'      => get_bloginfo( 'admin_email' ),
			'email_reply_to'  => 'noreplay@' . $this->get_site_domain(),
			'email_to_cc'     => '',
			'email_to_bcc'    => '',
		];

		foreach ( $fields as $key => $default ) {
			$setting = isset( $settings[ "geodir_" . $key ] ) ? trim( $settings[ "geodir_" . $key ] ) : ( isset( $settings[ $key ] ) ? trim( $settings[ $key ] ) : '' );
			$setting = $record->replace_setting_shortcodes( $setting );
			if ( $key == 'email_to' ) {
				$fields[ $key ] = $default;
			} elseif ( ! empty( $setting ) ) {
				$key            = str_replace( "geodir_", "", $key ); // remove geodir_ from field key
				$fields[ $key ] = $setting;
			}
		}

		$reply_to_type = ! empty( $settings['geodir_email_reply_to'] ) ? esc_attr( $settings['geodir_email_reply_to'] ) : 'sender';
		switch ( $reply_to_type ) {
			case 'sender' :
				$email_reply_to = $this->get_reply_to( $record, $fields );
				break;
			case 'site' :
				$email_reply_to = get_bloginfo( 'admin_email' );
				break;
			case 'no-reply' :
				$email_reply_to = 'noreplay@' . $this->get_site_domain();
				break;
			default:
				$email_reply_to = 'noreplay@' . $this->get_site_domain();
		}


		$fields['email_content'] = $this->replace_content_shortcodes( $fields['email_content'], $record, $line_break );

		$email_meta = '';

		$form_metadata_settings = $settings['geodir_form_metadata'];

		if ( ! empty( $form_metadata_settings ) ) {
			foreach ( $form_metadata_settings as $field ) {

				switch ( $field ) {
					case 'date':
						$field_data = [
							'title' => __( 'Date', 'geodirectory' ),
							'value' => date_i18n( get_option( 'date_format' ) ),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;

					case 'time':
						$field_data = [
							'title' => __( 'Time', 'geodirectory' ),
							'value' => date_i18n( get_option( 'time_format' ) ),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;

					case 'page_url':
						$field_data = [
							'title' => __( 'Page URL', 'geodirectory' ),
							'value' => esc_url_raw( $_POST['referrer'] ),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;

					case 'user_agent':
						$field_data = [
							'title' => __( 'User Agent', 'geodirectory' ),
							'value' => wp_strip_all_tags( $_SERVER['HTTP_USER_AGENT'] ),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;

					case 'remote_ip':
						$field_data = [
							'title' => __( 'Remote IP', 'geodirectory' ),
							'value' => $this->get_client_ip(),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;
					case 'credit':
						$field_data = [
							'title' => __( 'Powered by', 'geodirectory' ),
							'value' => __( 'GeoDirectory', 'geodirectory' ),
						];
						$email_meta .= $this->field_formatted( $field_data ) . $line_break;
						break;
				}

			}
		}

		if ( ! empty( $email_meta ) ) {
			$fields['email_content'] .= $line_break . '---' . $line_break . $line_break . $email_meta;
		}

		$headers = sprintf( 'From: %s <%s>' . "\r\n", $fields['email_from_name'], $fields['email_from'] );
		if ( $email_reply_to ) {
			$headers .= sprintf( 'Reply-To: %s' . "\r\n", $email_reply_to );
		}

		if ( $send_html ) {
			$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
		}

		$cc_header = '';
		if ( ! empty( $fields['email_to_cc'] ) ) {
			$cc_header = 'Cc: ' . $fields['email_to_cc'] . "\r\n";
		}

		/**
		 * Email headers.
		 *
		 * Filters the additional headers sent when the form send an email.
		 *
		 * @since 1.0.0
		 *
		 * @param string|array $headers Additional headers.
		 */
		$headers = apply_filters( 'elementor_pro/forms/wp_mail_headers', $headers );

		/**
		 * Email content.
		 *
		 * Filters the content of the email sent by the form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $email_content Email content.
		 */
		$fields['email_content'] = apply_filters( 'elementor_pro/forms/wp_mail_message', $fields['email_content'] );

		$email_sent = wp_mail( $fields['email_to'], $fields['email_subject'], $fields['email_content'], $headers . $cc_header );

		if ( ! empty( $fields['email_to_bcc'] ) ) {
			$bcc_emails = explode( ',', $fields['email_to_bcc'] );
			foreach ( $bcc_emails as $bcc_email ) {
				wp_mail( trim( $bcc_email ), $fields['email_subject'], $fields['email_content'], $headers );
			}
		}

		/**
		 * Elementor form mail sent.
		 *
		 * Fires when an email was sent successfully.
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings Form settings.
		 * @param Form_Record $record An instance of the form record.
		 */
		do_action( 'elementor_pro/forms/mail_sent', $settings, $record );

		if ( ! $email_sent ) {
			$ajax_handler->add_error_message( ElementorPro\Modules\Forms\Classes\Ajax_Handler::get_default_message( ElementorPro\Modules\Forms\Classes\Ajax_Handler::SERVER_ERROR, $settings ) );
		}
	}

	/**
	 * Try to get the reply_to email.
	 *
	 * @param $record
	 * @param $fields
	 *
	 * @return string
	 */
	protected function get_reply_to( $record, $fields ) {
		$email_reply_to = '';

		if ( ! empty( $fields['email_reply_to'] ) ) {
			$sent_data = $record->get( 'sent_data' );
			foreach ( $record->get( 'fields' ) as $field_index => $field ) {
				if ( $field['id'] == 'email' && ! empty( $field['value'] ) && is_email( $field['value'] ) ) {
					$email_reply_to = $sent_data[ $field_index ];
					break;
				} elseif ( $field['type'] == 'email' && ! empty( $field['value'] ) && is_email( $field['value'] ) ) {
					$email_reply_to = $sent_data[ $field_index ];
				}
			}
		}

		return $email_reply_to;
	}

	/**
	 * @param string $email_content
	 * @param Form_Record $record
	 *
	 * @return string
	 */
	private function replace_content_shortcodes( $email_content, $record, $line_break ) {
		$email_content        = do_shortcode( $email_content );
		$all_fields_shortcode = '[all-fields]';

		if ( false !== strpos( $email_content, $all_fields_shortcode ) ) {
			$text = '';
			foreach ( $record->get( 'fields' ) as $field ) {
				$formatted = $this->field_formatted( $field );
				if ( ( 'textarea' === $field['type'] ) && ( '<br>' === $line_break ) ) {
					$formatted = str_replace( [ "\r\n", "\n", "\r" ], '<br />', $formatted );
				}
				$text .= $formatted . $line_break;
			}

			$email_content = str_replace( $all_fields_shortcode, $text, $email_content );

		}

		return $email_content;
	}

	private function field_formatted( $field ) {
		$formatted = '';
		if ( ! empty( $field['title'] ) ) {
			$formatted = sprintf( '%s: %s', $field['title'], $field['value'] );
		} elseif ( ! empty( $field['value'] ) ) {
			$formatted = sprintf( '%s', $field['value'] );
		}

		return $formatted;
	}

	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 *
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {

		$widget->start_controls_section(
			'section_geodir_contact',
			[
				'label'     => $this->get_label(),
				'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);


		$widget->add_control(
			'geodir_email_to_note',
			[
				'label' => __( 'To', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'This will email the listing defined email address.', 'geodirectory' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		/* translators: %s: Site title. */
		$default_message = sprintf( __( 'New contact form: "%s"', 'geodirectory' ), get_option( 'blogname' ) );

		$widget->add_control(
			'geodir_email_subject',
			[
				'label'       => __( 'Subject', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => $default_message,
				'placeholder' => $default_message,
				'label_block' => true,
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_content',
			[
				'label'       => __( 'Message', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'default'     => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => sprintf( __( 'By default, all form fields are sent via %s shortcode. To customize sent fields, copy the shortcode that appears inside each field and paste it above.', 'geodirectory' ), '<code>[all-fields]</code>' ),
				'render_type' => 'none',
			]
		);

		$site_domain = $this->get_site_domain();

		$widget->add_control(
			'geodir_email_from',
			[
				'label'       => __( 'From Email', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => 'email@' . $site_domain,
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_from_name',
			[
				'label'       => __( 'From Name', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => get_bloginfo( 'name' ) . " " . __( 'Contact Form', 'geodirectory' ),
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_reply_to',
			[
				'label'       => __( 'Reply-To', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => [
					'sender'   => __( 'Sender Email (field id must be set to: email)', 'geodirectory' ),
					'site'     => __( 'Site Email', 'geodirectory' ),
					'no-reply' => __( 'No-reply', 'geodirectory' ),
				],
				'default'     => 'sender',
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_to_cc',
			[
				'label'       => __( 'Cc', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'title'       => __( 'Separate emails with commas', 'geodirectory' ),
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_to_bcc',
			[
				'label'       => __( 'Bcc', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'title'       => __( 'Separate emails with commas', 'geodirectory' ),
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_form_metadata',
			[
				'label'       => __( 'Meta Data', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'separator'   => 'before',
				'default'     => [
					'date',
					'time',
					'page_url',
					'user_agent',
					'remote_ip',
					'credit',
				],
				'options'     => [
					'date'       => __( 'Date', 'geodirectory' ),
					'time'       => __( 'Time', 'geodirectory' ),
					'page_url'   => __( 'Page URL', 'geodirectory' ),
					'user_agent' => __( 'User Agent', 'geodirectory' ),
					'remote_ip'  => __( 'Remote IP', 'geodirectory' ),
					'credit'     => __( 'Credit', 'geodirectory' ),
				],
				'render_type' => 'none',
			]
		);

		$widget->add_control(
			'geodir_email_content_type',
			[
				'label'       => __( 'Send As', 'geodirectory' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'default'     => 'html',
				'render_type' => 'none',
				'options'     => [
					'html'  => __( 'HTML', 'geodirectory' ),
					'plain' => __( 'Plain', 'geodirectory' ),
				],
			]
		);

		$widget->end_controls_section();

	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return __( 'GD Email Listing', 'geodirectory' );
	}

	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'email-listing';
	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 *
	 * @param array $element
	 */
	public function on_export( $element ) {
		$controls_to_unset = [
			'geodir_email_to',
			'geodir_email_from',
			'geodir_email_from_name',
			'geodir_email_subject',
			'geodir_email_reply_to',
			'geodir_email_to_cc',
			'geodir_email_to_bcc',
		];

		foreach ( $controls_to_unset as $base_id ) {
			unset( $element[ $base_id ] );
		}

	}

	public function get_site_domain() {
		return str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	}

	public function get_client_ip() {
		$server_ip_keys = [
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $server_ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && filter_var( $_SERVER[ $key ], FILTER_VALIDATE_IP ) ) {
				return $_SERVER[ $key ];
			}
		}

		// Fallback local ip.
		return '127.0.0.1';
	}
}