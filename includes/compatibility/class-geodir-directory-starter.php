<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Twenty Seventeen suport.
 *
 * @class   GeoDir_Directory_Starter
 */
class GeoDir_Directory_Starter {

    /**
     * Theme init.
     */
    public static function init() {
        remove_action( 'geodir_before_main_content', 'geodir_output_content_wrapper', 10 );
        remove_action( 'geodir_after_main_content', 'geodir_output_content_wrapper_end', 10 );

        add_action( 'geodir_before_main_content', array( __CLASS__, 'output_content_wrapper' ), 10 );
        add_action( 'geodir_after_main_content', array( __CLASS__, 'output_content_wrapper_end' ), 10 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 10 );
    }

    /**
     * Open the wrapper.
     */
    public static function output_content_wrapper() {
        $dt_blog_sidebar_position = esc_attr(get_theme_mod('dt_blog_sidebar_position', DT_BLOG_SIDEBAR_POSITION)); ?>
        <div class="container">
            <div class="row">
                <?php if ($dt_blog_sidebar_position == 'left') { ?>
                    <div class="col-lg-4 col-md-3">
                        <div class="sidebar blog-sidebar page-sidebar">
                            <?php get_sidebar(); ?>
                        </div>
                    </div>
                <?php } ?>
                <div class="col-lg-8 col-md-9">
                    <div class="content-box content-archive">
                        <article class="page type-page status-publish hentry">
            <?php
    }

    /**
     * Close the wrapper.
     */
    public static function output_content_wrapper_end() {
        $dt_blog_sidebar_position = esc_attr(get_theme_mod('dt_blog_sidebar_position', DT_BLOG_SIDEBAR_POSITION));
        ?>              </article>
                    </div>
                 </div>
            <?php if ($dt_blog_sidebar_position == 'right') { ?>
                <div class="col-lg-4 col-md-3">
                    <div class="sidebar blog-sidebar page-sidebar">
                        <?php get_sidebar(); ?>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php
    }
    
    public static function enqueue_scripts() {
        ob_start();
?>
.container article .geodir_category_list_view article,
.container article .geodir_category_list_view article footer {
    clear: none;
}
.container .widget .geodir_list_heading, 
.container .widget .location_list_heading {
    margin-bottom: inherit;
    padding-bottom: inherit;
    border: none!important
}
.container .widget .geodir_location_listing .geodir_category_list_view {
    padding: 0;
}
<?php
        $custom_style = ob_get_clean();
        
        wp_add_inline_style( 'geodir-core-scss', $custom_style );
    }
}

GeoDir_Directory_Starter::init();
