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
				<div class="membership-content">
				<!--
				<h2>With our GeoDirectory Membership you get access to all our products!</h2>
				<p><a class="button button-primary" href="https://wpgeodirectory.com/downloads/membership/">View Memberships</a></p> -->
				<?php if(defined('WP_EASY_UPDATES_ACTIVE')){?>


					<h2><?php echo _e( 'Have a membership key?', 'geodirectory' ); ?></h2>

					<p>
						<?php
						$wpeu_admin = new External_Updates_Admin('wpgeodirectory.com','2');
						echo $wpeu_admin->render_licence_actions('wpgeodirectory.com', 'membership',array(66235,111330,111327,3310305));
						?>
						<a href="https://wpgeodirectory.com/downloads/membership/"><?php echo _e( 'Don\'t have a membership key?', 'geodirectory' ); ?></a>
					</p>
				<?php }else{ ?>
				<p class="easy-update"><?php echo wp_sprintf( __( 'If you already have a membership key please install <a href="%s" target="_blank">WP Easy Updates</a>', 'geodirectory' ) , 'https://wpgeodirectory.com/wp-easy-updates/' ); ?></p>
			<?php	}?>

				<div class="membership-cta-contet">
					<div class="main-cta">
							<h2><?php echo __("Membership benefits Includes:","geodirectory"); ?></h2>
							<div class="feature-list">
								<ul>

									<?php
									$addon_obj = new GeoDir_Admin_Addons();
									if ($addons = $addon_obj->get_section_data( 'addons' ) ) {
										foreach ( $addons as $addon ) {
											echo '<li><i class="far fa-check-circle fa-sm"></i> '.esc_html( $addon->info->title ).'</li>';
										}
									}
									?>
								</ul>
							</div>
							<div class="feature-cta">
								<h3><?php echo __("Membership ","geodirectory"); ?> <br><?php echo __("Starts from","geodirectory"); ?></h3>
								<h4><?php echo __("$99","geodirectory"); ?></h4>
								<a href="https://wpgeodirectory.com/downloads/membership/" target="_blank"><?php echo __("Buy Membership","geodirectory"); ?></a>
							</div>

					</div>


					<div class="member-testimonials">
						<h3><?php echo __("Testimonials","geodirectory"); ?></h3>
						<div class="testimonial-content">
							<div class="t-image">
								<?php
									echo '<img src="' . plugins_url( 'images/t-image2.png', dirname(__FILE__) ) . '" > ';
								?>
							</div>
							<div class="t-content">
								<p><?php echo __("I'm becoming more impressed with  @wpGeoDirectory
as v2 evolves. It's a pretty awesome WordPress directory plugin.","geodirectory"); ?>

								</p>
								<p><strong><?php echo __("Vanessa Harris","geodirectory"); ?></strong> <?php echo __("Product  @Google, formerly at  @Microsoft","geodirectory"); ?></p>
							</div>
						</div>

						<div class="testimonial-content">
							<div class="t-image">
								<?php
									echo '<img src="' . plugins_url( 'images/t-image1.png', dirname(__FILE__) ) . '" > ';
								?>
							</div>
							<div class="t-content">
								<p><?php echo __("Switched from Joomla to WordPress and installed Geodirectory V2 to create a multi location directory and events site. Support has been absolutely brilliant with very quick response times, solving almost every issue I ran into (most of the time just me getting used to the new environment) in a matter of minutes but also a few other, more serious issues, in less than a day. I would definitely recommend Geodirectory to anyone who plans on creating a directory. It's easy to use as it uses the new Gutenberg blocks and custom fields to create and layout your pages and comes with lots of great add-ons for a very reasonable price. Keep up the good work! I'm hooked on WordPress and Geodirectory V2. That's a fact.","geodirectory"); ?>

								</p>
								<p><strong><?php echo __("gdweb (@gdweb)","geodirectory"); ?></strong> <?php echo __("Graphic Design and Web Design Studio in Phuket","geodirectory"); ?></p>
							</div>
						</div>
					</div>

					<div class="member-footer">
						<a class="footer-btn" href="https://wpgeodirectory.com/downloads/membership/" target="_blank"><?php echo __("Buy Membership","geodirectory"); ?></a>
						<a class="footer-link" href="post-new.php?post_type=gd_place"><?php echo __("Create your First Listing","geodirectory"); ?></a>
					</div>
				</div>

			</div>
			</div>

			<?php


		}else{
			$installed_plugins = get_plugins();
			if ($addons = GeoDir_Admin_Addons::get_section_data( $current_tab ) ) :

//				print_r($addons);

//			echo '###'.geodir_file_relative_url( 'http://localhost/wp-content/uploads/2018/12/restaurants19-2-150x150.jpg' );exit;
				?>
				<ul class="gd-products"><?php foreach ( $addons as $addon ) :
						if(388371==$addon->info->id || 65079==$addon->info->id){continue;}// don't show GD Dashboard
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
										echo '<a href="'.admin_url('/plugin-install.php?gd_wizard_recommend=true&amp;tab=plugin-information&amp;plugin='.$addon->info->slug).'" data-lity="">';
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
