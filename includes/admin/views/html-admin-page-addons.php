<?php
/**
 * Admin View: Page - Addons
 *
 * @var string $view
 * @var object $addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap gd_addons_wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>

	<?php if ( $tabs ){ ?>
		<nav class="nav-tab-wrapper gd-nav-tab-wrapper">
			<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=gd-addons&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
			}
			do_action( 'geodir_addons_tabs' );
			?>
		</nav>

		<?php if ($addons = GeoDir_Admin_Addons::get_section_data( $current_tab ) ) : ?>
			<ul class="gd-products">
				<?php foreach ( $addons as $addon ) : ?>
					<li class="gd-product">
						<a href="<?php echo esc_attr( $addon->info->link ); ?>">
							<h3><?php echo esc_html( $addon->info->title ); ?></h3>

							<span class="gd-product-image">
								<?php if ( ! empty( $addon->info->thumbnail) ) : ?>
									<img src="<?php echo esc_attr( $addon->info->thumbnail ); ?>"/>
								<?php endif; ?>
							</span>

							<span class="gd-product-excerpt">
								<?php if ( ! empty( $addon->info->excerpt) ) : ?>
									<p><?php echo wp_kses_post( $addon->info->excerpt ); ?></p>
								<?php endif; ?>
							</span>

							<span class="gd-product-button">
								<?php
								echo GeoDir_Admin_Addons::output_button( $addon );
								?>
							</span>


							<span class="gd-price"><?php // echo wp_kses_post( $addon->price ); ?></span>

						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	<?php } ?>


	<div class="clearfix" ></div>

	<?php if($current_tab =='addons'){?>
	<p><?php printf( __( 'All of our GeoDirectory Addons can be found on GeoDirectory.com here: <a href="%s">GeoDirectory Addons</a>', 'geodirectory' ), 'https://wpgeodirectory.com/downloads/category/addons/' ); ?></p>
	<?php }elseif($current_tab =='themes'){?>
		<p><?php printf( __( 'All of our GeoDirectory Themes can be found on GeoDirectory.com here: <a href="%s">GeoDirectory Themes</a>', 'geodirectory' ), 'https://wpgeodirectory.com/downloads/category/themes/' ); ?></p>
	<?php }?>

</div>
