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
        remove_action( 'geodir_before_main_content', 'geodir_output_content_wrapper_start', 10 );
        remove_action( 'geodir_after_main_content', 'geodir_output_content_wrapper_end', 10 );
        remove_action('geodir_wrapper_content_open', 'geodir_action_wrapper_content_open', 10);
        remove_action('geodir_wrapper_content_close', 'geodir_action_wrapper_content_close', 10);
        remove_action('geodir_sidebar_right_open', 'geodir_action_sidebar_right_open', 10);
        remove_action('geodir_sidebar_left_open', 'geodir_action_sidebar_left_open', 10);

        add_action( 'geodir_before_main_content', array( __CLASS__, 'output_content_wrapper_open' ), 10 );
        add_action( 'geodir_after_main_content', array( __CLASS__, 'output_content_wrapper_close' ), 10 );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ), 10 );
        add_action( 'geodir_breadcrumb_defaults', array( __CLASS__, 'breadcrumb_args' ), 10, 1 );
    }

    /**
     * Open the wrapper.
     */
    public static function output_content_wrapper_open() {
        $dt_blog_sidebar_position = esc_attr(get_theme_mod('dt_blog_sidebar_position', DT_BLOG_SIDEBAR_POSITION)); ?>
        <div class="container clearfix">
            <div class="row">
                <?php if ($dt_blog_sidebar_position == 'left') { ?>
                    <div class="col-lg-4 col-md-3">
                        <div class="sidebar blog-sidebar page-sidebar">
                            <?php get_sidebar( 'geodirectory' ); ?>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($dt_blog_sidebar_position == 'left' || $dt_blog_sidebar_position == 'right') { ?>
                <div class="col-lg-8 col-md-9">
                <?php } else { ?>
                <div class="col-lg-12">
                <?php } ?>
                    <div class="content-box content-archive clearfix">
                        <article class="hentry clearfix">
            <?php
    }

    /**
     * Close the wrapper.
     */
    public static function output_content_wrapper_close() {
        $dt_blog_sidebar_position = esc_attr(get_theme_mod('dt_blog_sidebar_position', DT_BLOG_SIDEBAR_POSITION));
        ?>              </article>
                    </div>
                 </div>
            <?php if ($dt_blog_sidebar_position == 'right') { ?>
                <div class="col-lg-4 col-md-3">
                    <div class="sidebar blog-sidebar page-sidebar">
                        <?php get_sidebar( 'geodirectory' ); ?>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php
    }
    
    public static function breadcrumb_args( $args = array() ) {
        $args['wrap_start'] = '<div class="container geodir-breadcrumb clearfix"><ul id="breadcrumbs">';
        $args['wrap_end'] = '</ul></div>';
        return $args;
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
