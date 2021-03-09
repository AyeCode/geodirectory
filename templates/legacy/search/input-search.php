<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variable explanations.
 *
 * @var string $search_term The current search term.
 * @var string $default_search_for_text The placeholder text.
 */
?>
<div class='gd-search-input-wrapper gd-search-field-search'>
	<?php 	do_action('geodir_before_search_for_input');?>
	<input class="search_text gd_search_text" name="s"
	       value="<?php echo $search_term;?>" type="text"
	       onkeydown="if(event.keyCode == 13) geodir_click_search(this);"
	       onClick="this.select();"
	       placeholder="<?php esc_html_e($default_search_for_text,'geodirectory') ?>"
	       aria-label="<?php esc_html_e($default_search_for_text,'geodirectory') ?>"
	       autocomplete="off"
	/>
	<?php 	do_action('geodir_after_search_for_input');?>
</div>
