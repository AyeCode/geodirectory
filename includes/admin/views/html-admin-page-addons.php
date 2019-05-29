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

		<?php
		
		if($current_tab == 'membership'){

			?>

			<div class="gd-membership-tab-conatiner">
				<h2>With our GeoDirectory Membership you get access to all our products!</h2>
				<p><a class="button button-primary" href="https://wpgeodirectory.com/downloads/membership/">View Memberships</a></p>
				<?php if(defined('WP_EASY_UPDATES_ACTIVE')){?>


					<h2>Have a membership key?</h2>

					<p>
						<?php
						$wpeu_admin = new External_Updates_Admin('wpgeodirectory.com','2');
						echo $wpeu_admin->render_licence_actions('wpgeodirectory.com', 'membership',array(66235,111330,111327));
						?>
					</p>
				<?php }?>
			</div>

			<?php


		}else{
			$installed_plugins = get_plugins();
			if ($addons = GeoDir_Admin_Addons::get_section_data( $current_tab ) ) : 
				
//				print_r($addons);

//			echo '###'.geodir_file_relative_url( 'http://localhost/wp-content/uploads/2018/12/restaurants19-2-150x150.jpg' );exit;
				?>
				<ul class="gd-products"><?php foreach ( $addons as $addon ) :
						if(388371==$addon->info->id || 65079==$addon->info->id){continue;}// don't show GD Dashbaord
						?><li class="gd-product">
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
											// if installed show activation link
											if(isset($installed_plugins['wp-easy-updates/external-updates.php'])){
												$url = '#gd-wpeu-required-activation';
											}else{
												$url = '#gd-wpeu-required-for-external';
											}
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


								<span class="gd-price"><?php //print_r($addon); //echo wp_kses_post( $addon->price ); ?></span></li><?php endforeach; ?></ul>
			<?php endif;
		}

	}
	?>


	<div class="clearfix" ></div>

	<?php if($current_tab =='addons'){?>
	<p><?php printf( __( 'All of our GeoDirectory Addons can be found on WPGeoDirectory.com here: <a href="%s">GeoDirectory Addons</a>', 'geodirectory' ), 'https://wpgeodirectory.com/downloads/category/addons/' ); ?></p>
	<?php }elseif($current_tab =='themes'){?>
		<p><?php printf( __( 'All of our GeoDirectory Themes can be found on WPGeoDirectory.com here: <a href="%s">GeoDirectory Themes</a>', 'geodirectory' ), 'https://wpgeodirectory.com/downloads/category/themes/' ); ?></p>
	<?php }?>

	<div id="gd-wpeu-required-activation" class="lity-hide "><span class="gd-notification "><?php printf( __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s'>activate</a> it now.","geodirectory"),wp_nonce_url(admin_url('plugins.php?action=activate&plugin=wp-easy-updates/external-updates.php'), 'activate-plugin_wp-easy-updates/external-updates.php'));?></span></div>
	<div id="gd-wpeu-required-for-external" class="lity-hide "><span class="gd-notification "><?php printf(  __("The plugin <a href='https://wpeasyupdates.com/' target='_blank'>WP Easy Updates</a> is required to check for and update some installed plugins/themes, please <a href='%s' onclick='window.open(\"https://wpeasyupdates.com/wp-easy-updates.zip\", \"_blank\");' >download</a> and install it now.","geodirectory"),admin_url("plugin-install.php?tab=upload&wpeu-install=true"));?></span></div>
	<div id="wpeu-licence-popup" class="lity-hide ">
		<span class="gd-notification noti-white">
			<h3 class="wpeu-licence-title"><?php _e("Licence key","geodirectory");?></h3>
			<input class="wpeu-licence-key" type="text" placeholder="<?php _e("Enter your licence key","geodirectory");?>"> <button class="button-primary wpeu-licence-popup-button" ><?php _e("Install","geodirectory");?></button>
			<br>
			<?php
			echo sprintf( __('%sFind your licence key here%s OR %sBuy one here%s', 'geodirectory'), '<a href="https://wpgeodirectory.com/your-account/" target="_blank">','</a>','<a class="wpeu-licence-link" href="https://wpgeodirectory.com/downloads/category/addons/" target="_blank">','</a>' );
			?>
		</span>
	</div>

</div>
