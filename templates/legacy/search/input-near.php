<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var string $near The current near search term.
 * @var string $default_near_text The placeholder text.
 * @var string $near_class The near wrapper classes.
 * @var string $near_input_extra The near wrapper extras.
 */
echo "<div class='gd-search-input-wrapper gd-search-field-near $near_class' $near_input_extra>";
do_action( 'geodir_before_search_near_input' );
?>
	<input name="snear" class="snear" type="text" value="<?php echo $near; ?>"
	       onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);" <?php echo $near_input_extra; ?>
	       onClick="this.select();"
	       placeholder="<?php esc_html_e( $default_near_text, 'geodirectory' ) ?>"
	       aria-label="<?php esc_html_e( $default_near_text, 'geodirectory' ) ?>"
	       autocomplete="off"
	/>
<?php
do_action( 'geodir_after_search_near_input' );
echo "</div>";