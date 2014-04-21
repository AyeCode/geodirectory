<?php 
get_header();
?>
<div id="geodir_wrapper"> 
<div class="clearfix">
<div id="geodir_content"> 
		<?php
			global $information ;
			echo '<h5 class="geodir_information">';
			echo $information;
			echo '</h5>';

		  ?> 
</div><!-- geodir_content ends here-->
<div id="gd-sidebar-wrapper">
    <div class="geodir-sidebar-main" >
        <div class="geodir-gd-sidebar">
            <?php do_action('geodir_sidebar'); ?>
        </div>
    </div>
</div>  <!-- gd-sidebar-wrapper ends here-->
</div> <!-- clearfix ends here-->
</div><!-- geodir_wrapper ends here-->
<?php get_footer(); ?>