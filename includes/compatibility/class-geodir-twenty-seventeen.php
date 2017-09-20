<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Twenty Seventeen suport.
 *
 * @class   GeoDir_Twenty_Seventeen
 */
class GeoDir_Twenty_Seventeen {

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
    public static function output_content_wrapper() { ?>
        <div class="wrap">
            <div id="primary" class="content-area twentyseventeen">
                <main id="main" class="site-main" role="main">
        <?php
    }

    /**
     * Close the wrapper.
     */
    public static function output_content_wrapper_end() { ?>
                </main>
            </div>
            <?php get_sidebar( 'geodirectory' ); ?>
        </div>
        <?php
    }
    
    public static function enqueue_scripts() {
        ob_start();
?>
@media screen and (min-width: 48em) {
    .has-sidebar.geodir-page:not(.error404) {
        #primary {
            width: 74%;
        }

        #secondary {
            width: 20%;
        }
    }

    body.page-two-column.geodir-page:not(.archive) #primary .entry-header {
        width: 16%;
    }

    body.page-two-column.geodir-page:not(.archive) #primary .entry-content {
        width: 78%;
    }
}
<?php
        $custom_style = ob_get_clean();
        
        wp_add_inline_style( 'geodir-core-scss', $custom_style );
    }
}

GeoDir_Twenty_Seventeen::init();
