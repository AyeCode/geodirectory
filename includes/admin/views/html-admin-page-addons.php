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
				<?php foreach ( $addons as $addon ) :
					if(388371==$addon->info->id){continue;}// don't show GD Dashbaord
					?>
					<li class="gd-product">
							<div class="gd-product-title">
								<h3><?php
									if ( ! empty( $addon->info->excerpt) ){
										echo geodir_help_tip( $addon->info->excerpt );
									}
									echo esc_html( $addon->info->title ); ?></h3>
							</div>

							<span class="gd-product-image">
								<?php if ( ! empty( $addon->info->thumbnail) ) : ?>
									<img src="<?php echo esc_attr( $addon->info->thumbnail ); ?>"/>
								<?php endif;

								if(isset($addon->info->link) && substr( $addon->info->link, 0, 21 ) === "https://wordpress.org"){
									echo '<a href="'.admin_url('/plugin-install.php?gd_wizard_recommend=true&amp;tab=plugin-information&amp;plugin='.$addon->info->slug).')" data-lity="">';
									echo '<span class="gd-product-info">'.__('More info','geodirectory').'</span>';
									echo '</a>';
								}elseif(isset($addon->info->link) && substr( $addon->info->link, 0, 26 ) === "https://wpgeodirectory.com"){
									if(defined('WP_EASY_UPDATES_ACTIVE')){
										$url = admin_url('/plugin-install.php?gd_wizard_recommend=true&amp;tab=plugin-information&amp;plugin='.$addon->info->slug.'&item_id='.$addon->info->id.'&update_url=https://wpgeodirectory.com');
									}else{
										$url = '#gd-wpeu-required-for-external';
									}
									echo '<a href="'.$url.'" data-lity="">';
									echo '<span class="gd-product-info">'.__('More info','geodirectory').'</span>';
									echo '</a>';
								}

								?>

							</span>


							<span class="gd-product-button">
								<?php
								echo GeoDir_Admin_Addons::output_button( $addon );
								?>
							</span>


							<span class="gd-price"><?php //print_r($addon); //echo wp_kses_post( $addon->price ); ?></span>

<!--						<a href="--><?php //echo esc_attr( $addon->info->link ); ?><!--">-->
<!--						</a>-->
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

	<div id="gd-wpeu-required-for-external" class="lity-hide "><span class="gd-notification "><?php _e("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='https://wpeasyupdates.com/wp-easy-updates.zip'>download</a> and install it now.","geodirectory");?></span></div>

</div>
