<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Autoloader.
 *
 * @class       GeoDir_Autoloader
 * @version     2.0.0
 * @package     GeoDirectory/Classes
 * @category    Class
 * @author      WooThemes
 */
class GeoDir_Autoloader {

    /**
     * Path to the includes directory.
     *
     * @var string
     */
    private $include_path = '';

    /**
     * GeoDir_Autoloader constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {
        if ( function_exists( "__autoload" ) ) {
            spl_autoload_register( "__autoload" );
        }

        spl_autoload_register( array( $this, 'autoload' ) );

        $this->include_path = untrailingslashit( plugin_dir_path( GEODIRECTORY_PLUGIN_FILE ) ) . '/includes/';
    }

    /**
     * Take a class name and turn it into a file name.
     *
     * @since 2.0.0
     *
     * @param  string $class
     * @return string
     */
    private function get_file_name_from_class( $class ) {
        return 'class-' . str_replace( '_', '-', $class ) . '.php';
    }

    /**
     * Include a class file.
     *
     * @since 2.0.0
     *
     * @param  string $path
     * @return bool successful or not
     */
    private function load_file( $path ) {
        if ( $path && is_readable( $path ) ) {
            include_once( $path );
            return true;
        }
        return false;
    }

    /**
     * Auto-load GeoDir classes on demand to reduce memory consumption.
     *
     * @since 2.0.0
     *
     * @param string $class
     */
    public function autoload( $class ) {

        $class = strtolower( $class );

        if ( 0 !== strpos( $class, 'geodir_' ) ) {
            return;
        }

        $file  = $this->get_file_name_from_class( $class );
        $path  = '';

        if ( strpos( $class, 'geodir_admin' ) === 0 ) {
            $path = $this->include_path . 'admin/';
        }elseif ( strpos( $class, 'geodir_widget' ) === 0 ) {
            $path = $this->include_path . 'widgets/';
        }elseif ( strpos( $class, 'geodir_settings' ) === 0 ) {
            $path = $this->include_path . 'admin/settings/';
        }elseif ( strpos( $class, 'geodir_elementor' ) === 0 ) {
            $path = $this->include_path . 'elementor/';
        }
        
        if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
            $this->load_file( $this->include_path . $file );
        }
    }
}

new GeoDir_Autoloader();
