<?php
/**
 * Email Message Value Object
 *
 * Represents a complete email message with all its properties.
 * This immutable value object ensures type safety and consistency.
 *
 * @package GeoDirectory\Core\Data
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Data;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EmailMessage class.
 *
 * Value object representing an email message with all required and optional properties.
 *
 * @since 3.0.0
 */
final class EmailMessage {
	/**
	 * Recipient email address(es).
	 *
	 * @var string|array
	 */
	private $to;

	/**
	 * Email subject.
	 *
	 * @var string
	 */
	private string $subject;

	/**
	 * Email message body.
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * Email headers.
	 *
	 * @var string
	 */
	private string $headers;

	/**
	 * Email attachments.
	 *
	 * @var array
	 */
	private array $attachments;

	/**
	 * Email name/type identifier.
	 *
	 * @var string
	 */
	private string $email_name;

	/**
	 * Email template variables.
	 *
	 * @var array
	 */
	private array $email_vars;

	/**
	 * Constructor.
	 *
	 * @param string|array $to          Recipient email address(es).
	 * @param string       $subject     Email subject.
	 * @param string       $message     Email message body.
	 * @param string       $headers     Email headers.
	 * @param array        $attachments Email attachments.
	 * @param string       $email_name  Email name/type identifier.
	 * @param array        $email_vars  Email template variables.
	 */
	public function __construct(
		$to,
		string $subject,
		string $message,
		string $headers = '',
		array $attachments = [],
		string $email_name = '',
		array $email_vars = []
	) {
		$this->to          = $to;
		$this->subject     = $subject;
		$this->message     = $message;
		$this->headers     = $headers;
		$this->attachments = $attachments;
		$this->email_name  = $email_name;
		$this->email_vars  = $email_vars;
	}

	/**
	 * Get recipient email address(es).
	 *
	 * @return string|array
	 */
	public function get_to() {
		return $this->to;
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_subject(): string {
		return $this->subject;
	}

	/**
	 * Get email message body.
	 *
	 * @return string
	 */
	public function get_message(): string {
		return $this->message;
	}

	/**
	 * Get email headers.
	 *
	 * @return string
	 */
	public function get_headers(): string {
		return $this->headers;
	}

	/**
	 * Get email attachments.
	 *
	 * @return array
	 */
	public function get_attachments(): array {
		return $this->attachments;
	}

	/**
	 * Get email name/type.
	 *
	 * @return string
	 */
	public function get_email_name(): string {
		return $this->email_name;
	}

	/**
	 * Get email template variables.
	 *
	 * @return array
	 */
	public function get_email_vars(): array {
		return $this->email_vars;
	}

	/**
	 * Create a new instance with modified message.
	 *
	 * @param string $message New message body.
	 * @return self New instance with updated message.
	 */
	public function with_message( string $message ): self {
		return new self(
			$this->to,
			$this->subject,
			$message,
			$this->headers,
			$this->attachments,
			$this->email_name,
			$this->email_vars
		);
	}

	/**
	 * Create a new instance with modified headers.
	 *
	 * @param string $headers New headers.
	 * @return self New instance with updated headers.
	 */
	public function with_headers( string $headers ): self {
		return new self(
			$this->to,
			$this->subject,
			$this->message,
			$headers,
			$this->attachments,
			$this->email_name,
			$this->email_vars
		);
	}
}
