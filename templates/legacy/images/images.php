<?php
/**
 * Display post images single image/slider/gallery
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/images/images.php.
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
 */

global $gd_post;
?>
<div class="<?php echo $main_wrapper_class;?>" >
	<?php if($type=='slider'){ echo '<div class="geodir_flex-loader"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>';}?>
	<div id="<?php echo $slider_id; ?>" class="<?php echo $second_wrapper_class;?>" <?php
	if($controlnav==1){echo " data-controlnav='1' ";}
	if($animation =='fade'){echo " data-animation='fade' ";}
	if($slideshow){echo " data-slideshow='1' ";}
	if($limit_show){echo " data-limit_show='".absint($limit_show)."' ";}
	$image_total = count( (array) $post_images );
	?>>
		<ul class="<?php echo esc_attr($ul_class );?> geodir-images geodir-images-n-<?php echo $image_total; ?> clearfix"><?php
			$image_count = 0;
			foreach($post_images as $image){

				// reset temp tags
				$link_tag_open_ss = '';
				$link_tag_close_ss = '';

				$limit_show = !empty($limit_show) && $image_count >= $limit_show ? "style='display:none;'" : '';
				echo "<li $limit_show >";

				$img_tag = geodir_get_image_tag($image,$image_size );
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
						$link_tag_open_ss = "<a href='%s'>";
						$link_tag_close_ss = "</a>";
					}elseif($link_screenshot_to=='lightbox'){
						$link = geodir_get_image_src($image, 'large');
						$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-image' data-lity {$lightbox_attrs}>";
						$link_tag_close_ss = "<i class=\"fas fa-search-plus w-auto h-auto\" aria-hidden=\"true\"></i></a>";
					}elseif($link_screenshot_to=='lightbox_url'){
						$field_key = str_replace("_screenshot","",$image->type);
						$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
						$link_tag_open_ss = "<a href='%s' class='geodir-lightbox-image' data-lity {$lightbox_attrs}>";
						$link_tag_close_ss = "<i class=\"fas fa-search-plus w-auto h-auto\" aria-hidden=\"true\"></i></a>";
					}elseif($link_screenshot_to=='url' || $link_screenshot_to=='url_same'){
						$field_key = str_replace("_screenshot","",$image->type);
						$target = $link_screenshot_to=='url' ? "target='_blank'" : '';
						$link_icon = $link_screenshot_to=='url' ? "fas fa-external-link-alt w-auto h-auto" : 'fas fa-link w-auto h-auto';
						$link = isset($gd_post->{$field_key}) ? $gd_post->{$field_key} : '';
						$link_tag_open_ss = "<a href='%s' $target class='geodir-lightbox-image' rel='nofollow noopener noreferrer' {$lightbox_attrs}>";
						$link_tag_close_ss = "<i class=\"$link_icon\" aria-hidden=\"true\"></i></a>";
					}

				}

				// ajaxify images
				if($type=='slider' && $ajax_load && $image_count){
					$img_tag = geodir_image_tag_ajaxify($img_tag,$type!='slider');
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

				$flex_caption = '';
				if($type=='slider' && $show_title && !empty($image->title)){

					$flex_caption = esc_attr( stripslashes_deep( $image->title ) );
				}
				//Maybe add a caption to the title

				/**
				 * Filters whether or not the caption should be displayed.
				 *
				 * @since   2.0.0.63
				 * @package GeoDirectory
				 */
				$show_caption = apply_filters( 'geodir_post_images_show_caption', $show_caption );

				if( $show_caption && !empty( $image->caption ) ) {
					$flex_caption .= "<small>".esc_attr( stripslashes_deep( $image->caption ) )."</small>";
				}
				if( !empty( $flex_caption ) ){
					echo '<p class="flex-caption gd-flex-caption">'.$flex_caption.'</p>';
				}
				echo "</li>";
				$image_count++;
			}
			?></ul>
	</div>
	<?php if ($type=='slider' && $image_count > 1 && $controlnav == 2 ) { ?>
		<div id="<?php echo $slider_id; ?>_carousel" class="geodir_flexslider geodir_flexslider_carousel">
			<ul class="geodir-slides clearfix"><?php
				foreach($post_images as $image){
					echo "<li>";
					$img_tag = geodir_get_image_tag($image,'thumbnail');
					$meta = isset($image->metadata) ? maybe_unserialize($image->metadata) : '';
					//$img_tag =  wp_image_add_srcset_and_sizes( $img_tag, $meta , 0 );
					echo $img_tag;
					echo "</li>";
				}
				?></ul>
		</div>
	<?php } ?>
</div>
