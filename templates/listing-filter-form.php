<?php
/**
 * GD Search Form
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/listing-filter-form.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.2.6
 *
 * @global object $geodirectory GeoDirectory object.
 *
 * @vars
 * @var string $wrap_class Main wrapper CSS class.
 * @var string $form_class Form CSS class.
 * @var array  $instance Widget instance.
 * @var array  $keep_args Keep args.
 */

defined( 'ABSPATH' ) || exit;

global $geodirectory;
?>
<form class="<?php echo esc_attr( $form_class ); ?>" data-show="<?php echo esc_attr( $show ); ?>" name="geodir-listing-search" action="<?php echo geodir_search_page_base_url(); ?>" method="get">
	<input type="hidden" name="geodir_search" value="1" />
	<div class="geodir-loc-bar">
		<?php
		/**
		 * Called inside the search form but before any of the fields.
		 *
		 * @since 1.0.0
		 */
		do_action( 'geodir_before_search_form', $instance );
		?>
		<div class="clearfix geodir-loc-bar-in">
			<div class="geodir-search">
				<?php
				/**
				 * Adds the input fields to the search form.
				 *
				 * @since 1.6.9
				 */
				do_action( 'geodir_search_form_inputs', $instance );

				/**
				 * Called on the GD search form just before the search button.
				 *
				 * @since 1.0.0
				 */
				do_action( 'geodir_before_search_button', $instance );

				/**
				 * Called on the GD search form just after the search button.
				 *
				 * @since 1.0.0
				 */
				do_action( 'geodir_after_search_button', $instance );
				?>
			</div>
		</div>
		<?php
		/**
		 * Called inside the search form but after all the input fields.
		 *
		 * @since 1.0.0
		 */
		do_action( 'geodir_after_search_form', $instance ); ?>
	</div>
	<?php
	$latlon = $geodirectory->location->get_latlon();
	$slat = ! empty( $latlon['lat'] ) ? $latlon['lat'] : '';
	$slon = ! empty( $latlon['lon'] ) ? $latlon['lon'] : '';
	?>
	<input name="sgeo_lat" class="sgeo_lat" type="hidden" value="<?php echo esc_attr( $slat ); ?>"/>
	<input name="sgeo_lon" class="sgeo_lon" type="hidden" value="<?php echo esc_attr( $slon ); ?>"/>
	<?php do_action( 'geodir_search_hidden_fields', $instance );?>
</form>
