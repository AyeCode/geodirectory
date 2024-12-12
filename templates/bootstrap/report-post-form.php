<?php
/**
 * Repost Post Form
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/report-post-form.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.1.1.12
 *
 * Variables.
 *
 * @var object $post The post object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $post ) ) {
	return;
}

$post_id = absint( $post->ID );

?>
<form method="post" class="geodir-report-post-form form-sm">
	<?php do_action( 'geodir_report_post_form_hidden_fields', $post_id ); ?>

	<div class="geodir-report-post-form-fields">
		<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
		<?php
		do_action( 'geodir_report_post_form_before_fields', $post_id );

		// Post Title
		echo aui()->input(
			array(
				'type' => 'text',
				'label' => $post_type_name,
				'label_type' => 'vertical',
				'value' => $post->post_title,
				'extra_attributes' => array( 'disabled' => 'disabled' )
			)
		);

		// Full Name
		echo aui()->input(
			array(
				'type' => 'text',
				'id' => 'geodir_name',
				'name' => 'geodir_name',
				'required' => true,
				'label' => __( 'Your Name', 'geodirectory' ) . ' <span class="text-danger">*</span>',
				'label_type' => 'vertical',
				'value' => $user_name,
				'extra_attributes' => $user_name ? array( 'readonly' => 'readonly' ) : array()
			)
		);

		// Email
		echo aui()->input(
			array(
				'type' => 'email',
				'id' => 'geodir_email',
				'name' => 'geodir_email',
				'required' => true,
				'label' => __( 'Your Email', 'geodirectory' ) . ' <span class="text-danger">*</span>',
				'label_type' => 'vertical',
				'value' => $user_email,
				'extra_attributes' => $user_email ? array( 'readonly' => 'readonly' ) : array()
			)
		);

		// Reason
		echo aui()->select(
			array(
				'id' => 'geodir_reason',
				'name' => 'geodir_reason',
				'required' => true,
				'label' => wp_sprintf( __( 'Reason for reporting this %s', 'geodirectory' ), geodir_strtolower( $post_type_name ) ) . ' <span class="text-danger">*</span>',
				'label_type' => 'vertical',
				'options' => array_merge( array( "" => __( 'Choose a reason', 'geodirectory' ) ), GeoDir_Report_Post::get_reasons() ),
				'select2' => true,
			)
		);

		// Message
		echo aui()->textarea(
			array(
				'id' => 'geodir_message',
				'name' => 'geodir_message',
				'required' => false,
				'label' => __( 'Additional Details', 'geodirectory' ),
				'label_type' => 'vertical',
				'no_wrap' => false,
				'rows' => 2,
				'wysiwyg' => false,
				'value' => ''
			)
		);

		do_action( 'geodir_report_post_form_after_fields', $post_id );

		?>
	</div>
	<div class="geodirectory-form-footer">
		<?php
		do_action( 'geodir_report_post_form_before_button', $post_id );

		$button = aui()->button(
			array(
				'type' => 'submit',
				'class' => 'btn btn-primary btn-sm w-100 geodir-report-post-button',
				'content' => __( 'Report', 'geodirectory' ),
				'no_wrap' => true
			)
		);

		echo AUI_Component_Input::wrap(
			array(
				'content' => $button,
				'class' => 'form-group text-center mb-0 pt-2'
			)
		);

		do_action( 'geodir_report_post_form_after_button', $post_id );
		?>
	</div>
</form>
