<?php
/**
 * Display post images single image/slider/gallery
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/images/images.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory/Templates
 * @version    2.1.0.12
 */

defined( 'ABSPATH' ) || exit;

/**
 * Variables.
 *
 * @var string $main_wrapper_class
 * @var string $type
 * @var string $slider_id
 * @var string $second_wrapper_class
 * @var string $controlnav
 * @var string $animation
 * @var bool $slideshow
 * @var string $limit
 * @var string $limit_show
 * @var string $ul_class
 * @var object $post_images
 * @var string $link_to
 * @var string $link_screenshot_to
 * @var string $ajax_load
 * @var string $link
 * @var string $show_title
 * @var string $link_tag_open
 * @var string $link_tag_close
 * @var string $show_caption
 * @var string $image_size
 * @var string $cover
 * @var string $aspect
 * @var string $responsive_image_class
 */
global $gd_post;
?>
<div class="<?php echo $main_wrapper_class;?> " >
	<?php


	// slider stuff
	if($type=='slider'){
	//echo '<div class="geodir_flex-loader"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';?>
	<div id="<?php echo $slider_id; ?>" class="carousel <?php if($limit_show){echo 'carousel-multiple-items';} ?> slide <?php if($animation =='fade'){echo "carousel-fade ";} echo $second_wrapper_class;?>" <?php
	if($controlnav==1){echo " data-controlnav='1' ";}
	if($animation =='fade'){echo " data-animation='fade' ";}
	if($slideshow){echo " data-ride='carousel' ";}
	if($limit_show){echo " data-limit_show='".absint($limit_show)."' ";}
	?>>
	<?php
	}
	?>

		<?php
		$inner_wrapper_class = '';
		if($type=='slider' || $type=='image'){
			$limit_show_row = '';//$limit_show && $type=='slider' ? 'row' : '';
			$inner_wrapper_class = 'carousel-inner '.$limit_show_row;
		}elseif($type=='gallery'){
			$inner_wrapper_class = 'row row-cols-1 row-cols-md-3';
		}elseif($type=='masonry'){
			$inner_wrapper_class = 'card-columns';
		}

		$image_total = count( (array) $post_images );

		if ( $limit_show > 0 && $limit_show > $image_total ) {
			$limit_show = $image_total;
		}
		?>
		<div class="geodir-images geodir-images-n-<?php echo $image_total; ?> geodir-images-<?php echo $type . " " .$inner_wrapper_class ; ?> "><?php
			$image_count = 0;
			$max_width_percent = $limit_show ? 100 / absint( $limit_show ) : '';

			foreach($post_images as $image){
				// Reset temp tags
				$link_tag_open_ss = '';
				$link_tag_close_ss = '';

				$active = $image_count == 0 ? 'active' : '';
				$max_width = $active && $limit_show ? "style='width:$max_width_percent%'" : '';

				if($type=='slider' || $type=='image' ){
					echo "<div class='carousel-item  $active' $max_width>";
				}
				elseif($type=='gallery'){
					$limit_show_style = !empty($limit_show) && $image_count >= $limit_show ? "style='display:none;'" : '';
					echo '<div class="col mb-4" '.$limit_show_style.'><div class="card m-0 p-0 overflow-hidden">';
				}elseif($type=='masonry'){
					$limit_show_style = !empty($limit_show) && $image_count >= $limit_show ? "style='display:none;'" : '';
					echo '<div class="card m-0 mb-3 p-0 overflow-hidden">';
				}

				$image_class = 'embed-responsive-item';

				// image cover class
				if($cover =='x'){$image_class .= " embed-item-cover-x ";}
				elseif($cover =='y'){$image_class .= " embed-item-cover-y ";}
				elseif($cover=='n'){$image_class .= " embed-item-contain ";}
				else{$image_class .= " embed-item-cover-xy ";}

				if($type=='masonry'){
					$image_class = '';
				}

				$img_tag = geodir_get_image_tag($image,$image_size,'',$image_class );
				$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';

				// only set different sizes if not thumbnail
				if($image_size!='thumbnail'){
					$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
				}

				// image link
				if($link_to=='lightbox'){
					$link = geodir_get_image_src($image, 'large');
				}
								

				// check if screenshot link is different
				if($link_screenshot_to!='' && $link_screenshot_to!=$link_to && !$image->ID && stripos(strrev($image->type), "tohsneercs_") === 0){
					$lightbox_attrs = apply_filters( 'geodir_link_to_lightbox_attrs', '' );

					if($link_screenshot_to=='post'){
						$link = get_the_permalink($post->ID);
						$link_tag_open_ss = "<a href='%s' class='$responsive_image_class'>";
						$link_tag_close_ss = "</a>";
					}elseif($link_screenshot_to=='lightbox'){
						$link = geodir_get_image_src($image, 'large');
						$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-image $responsive_image_class' {$lightbox_attrs}>";
						$link_tag_close_ss = "<i class=\"fas fa-search-plus\" aria-hidden=\"true\"></i></a>";
					}elseif($link_screenshot_to=='lightbox_url'){
						$field_key = str_replace("_screenshot","",$image->type);
						$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
						$fa_icon = 'fas fa-link';
						// check if youtube
						$screenshot_base_url = 'https://www.youtube.com/embed/%s';
						// check if its a video URL
						if (!empty($link) && preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $link , $matches) ) {
							if(!empty($matches[1])){
								$link = esc_url( sprintf($screenshot_base_url, esc_attr($matches[1])) );
								$fa_icon = 'fas fa-video';
							}
						}

						$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-iframe $responsive_image_class' >";
						$link_tag_close_ss = "<i class=\"$fa_icon\" aria-hidden=\"true\"></i></a>";
					}elseif($link_screenshot_to=='url' || $link_screenshot_to=='url_same'){
						$field_key = str_replace("_screenshot","",$image->type);
						$target = $link_screenshot_to=='url' ? "target='_blank'" : '';
						$link_icon = $link_screenshot_to=='url' ? "fas fa-external-link-alt" : 'fas fa-link';
						$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
						$link_tag_open_ss = "<a href='%s' $target class=' $responsive_image_class' rel='nofollow noopener noreferrer'>";
						$link_tag_close_ss = "<i class=\"$link_icon\" aria-hidden=\"true\"></i></a>";
					}

				}

				// ajaxify images
				if($type=='slider' && $ajax_load ){
					if( !$image_count && geodir_is_page('single') ){
						// don't ajax the first image for a details page slider to improve the FCP pagespeed score.
					}else{
						$img_tag = geodir_image_tag_ajaxify($img_tag);
					}
				}elseif($ajax_load){
					$img_tag = geodir_image_tag_ajaxify($img_tag);
				}
				// output image
				if($link_tag_open_ss){
					echo $link_tag_open_ss ? sprintf($link_tag_open_ss,esc_url($link)) : '';
				}else{
					echo $link_tag_open ? sprintf($link_tag_open,esc_url($link)) : '';
				}
				echo $img_tag;
				if($link_tag_close_ss){
					echo $link_tag_close_ss;
				}else{
					echo $link_tag_close;
				}

				$title = '';
				$caption = '';
				if( $show_title && !empty($image->title)){
					$title = esc_attr( stripslashes_deep( $image->title ) );
				}

				/**
				 * Filters whether or not the caption should be displayed.
				 *
				 * @since   2.0.0.63
				 * @package GeoDirectory
				 */
				$show_caption = apply_filters( 'geodir_post_images_show_caption', $show_caption );

				if( $show_caption && !empty( $image->caption ) ) {
					$caption .= esc_attr( stripslashes_deep( $image->caption ) );
				}


				if($title || $caption){
					?>
					<div class="carousel-caption d-none d-md-block p-0 m-0 py-1 w-100 rounded-bottom <?php if($type=='gallery'){echo 'sr-only';}?>" style="bottom: 0;left:0;background: #00000060">
						<h5 class="m-0 p-0 h6 font-weight-bold text-white"><?php echo $title;?></h5>
						<p class="m-0 p-0 h6 text-white"><?php echo $caption;?></p>
					</div>
					<?php
				}

				if($type=='gallery'){ echo "</div></div>"; }
				else{echo "</div>";}

				$image_count++;
			}
			?>
		</div>


		<?php
		if($type=='slider') {
			?>
			<a class="carousel-control-prev" href="#<?php echo $slider_id; ?>" role="button" data-slide="prev">
				<span class="carousel-control-prev-icon" aria-hidden="true"></span>
				<span class="sr-only"><?php _e( 'Previous', 'geodirectory' ); ?></span>
			</a>
			<a class="carousel-control-next" href="#<?php echo $slider_id; ?>" role="button" data-slide="next">
				<span class="carousel-control-next-icon" aria-hidden="true"></span>
				<span class="sr-only"><?php _e( 'Next', 'geodirectory' ); ?></span>
			</a>
			<?php
		}

		if($type=='slider' && $controlnav==1 && $image_count > 1) {
			$image_count = 0;
			?>
			<ol class="carousel-indicators w-100 position-relative mx-0 my-1">
				<?php
				foreach($post_images as $image){
					$active = $image_count == 0 ? 'active' : '';
					$limit_show_break = !empty($limit_show) && $image_count >= $limit_show ? true : false;
					//if($limit_show_break){break;}
					echo '<li data-target="#'.$slider_id.'" data-slide-to="'.$image_count.'" class="my-1 mx-1 bg-dark '.$active.'"></li>';
					$image_count++;
				}
				?>
			</ol>
			<?php
		}elseif($type=='slider' && $controlnav==2 && $image_count > 1){
			$image_count = 0;
			?>
			<ul class="carousel-indicators w-100 position-relative mx-0 my-1 list-unstyled overflow-auto scrollbars-ios">
				<?php
				foreach($post_images as $image){
					$active = $image_count == 0 ? 'active' : '';
					$limit_show_break = !empty($limit_show) && $image_count >= $limit_show ? true : false;
					//if($limit_show_break){break;}
					echo '<li data-target="#'.$slider_id.'" data-slide-to="'.$image_count.'" class="my-1 mx-1 bg-dark list-unstyled border-0 '.$active.'"" style="text-indent:0;height:60px;width:60px;min-width:60px;">';
					$img_tag = geodir_get_image_tag($image,'thumbnail','','embed-item-cover-xy');
					$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
					echo $img_tag;
					echo '</li>';
					$image_count++;
				}
				?>
			</ul>
			<?php
		}
		?>

		<?php

		// slider close
		if($type=='slider'){
		?>
	</div>
<?php
}
	?>
</div>
