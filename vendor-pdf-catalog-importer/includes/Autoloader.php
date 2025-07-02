<?php
namespace VendorPDFCatalogImporter\Includes;

/**
 * Simple PSR-4 autoloader.
 */
class Autoloader {
    /**
     * Register autoloader for plugin classes.
     */
    public static function register() {
        spl_autoload_register( [ __CLASS__, 'autoload' ] );
    }

    /**
     * Autoload callback.
     */
    public static function autoload( $class ) {
        if ( 0 !== strpos( $class, 'VendorPDFCatalogImporter\\' ) ) {
            return;
        }

        $class   = str_replace( 'VendorPDFCatalogImporter\\', '', $class );
        $path    = plugin_dir_path( dirname( __DIR__ ) ) . 'includes/' . str_replace( '\\', '/', $class ) . '.php';

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
}
