<?php
/**
 * Main Search Input
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/search/input-search.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.2.6
 *
 * Variables.
 *
 * @var string $search_term The current search term.
 * @var string $default_search_for_text The placeholder text.
 * @var string $input_wrap_class Input wrap CSS class.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class='gd-search-input-wrapper gd-search-field-search<?php echo $input_wrap_class; ?>'>
	<?php do_action( 'geodir_before_search_for_input' ); ?>
	<input class="search_text gd_search_text" name="s" value="<?php echo esc_attr( $search_term ); ?>" type="text" onkeydown="if(event.keyCode == 13) geodir_click_search(this);" onClick="this.select();" placeholder="<?php echo esc_attr( esc_html__( $default_search_for_text, 'geodirectory' ) ); ?>" aria-label="<?php echo esc_attr( esc_html__( $default_search_for_text, 'geodirectory' ) ); ?>" autocomplete="off" />
	<?php do_action( 'geodir_after_search_for_input' ); ?>
</div>