<?php global $post,$preview,$post_images; 
$package_info = array();

$package_info = geodir_post_package_info($package_info , $post);
if(isset($package_info->google_analytics))
	$package_info->google_analytics = false;
$html = '';  
?>
<div id="gd-sidebar-wrapper">
<div class="geodir-sidebar-main" >
 <?php ob_start(); ?>
   <div class="geodir-gd-sidebar">
  	    <?php ob_start(); ?>
      	<?php do_action('geodir_detail_page_sidebar') ; 
		$html = ob_get_clean();
		echo apply_filters('geodir_post_sidebar_html',$html);
		?>
	</div>  <!-- geodir-gd-sidebar ends here-->
<?php
    $html = ob_get_clean();  
	echo apply_filters('geodir_detail_page_sidebar_html',$html);
?>
</div>  <!-- geodir-sidebar-main ends here-->

<div class="geodir-sidebar-main" >
   <div class="geodir-gd-sidebar">		
		<?php dynamic_sidebar('geodir_detail_sidebar'); 
		
	 	do_action('geodir_sidebar'); 
   		?>
	</div>  <!-- geodir-gd-sidebar ends here-->
</div>  <!-- geodir-sidebar-main ends here-->
</div>  <!-- gd-sidebar-wrapper ends here-->
