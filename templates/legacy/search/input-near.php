<?php
/**
 * Near Search Input
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/search/input-near.php.
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
 * @var string $near The current near search term.
 * @var string $default_near_text The placeholder text.
 * @var string $near_class The near wrapper classes.
 * @var string $near_input_extra The near wrapper extras.
 */

defined( 'ABSPATH' ) || exit;

echo "<div class='gd-search-input-wrapper gd-search-field-near $near_class' $near_input_extra>";
do_action( 'geodir_before_search_near_input' );
?>
	<input name="snear" class="snear" type="text" value="<?php echo esc_attr( $near ); ?>" onkeydown="javascript: if(event.keyCode == 13) geodir_click_search(this);" <?php echo $near_input_extra; ?> onClick="this.select();" placeholder="<?php echo esc_attr( esc_html__( $default_near_text, 'geodirectory' ) ); ?>" aria-label="<?php echo esc_attr( esc_html__( $default_near_text, 'geodirectory' ) ); ?>" autocomplete="off" />
<?php
do_action( 'geodir_after_search_near_input' );
echo "</div>";