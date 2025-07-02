<?php
namespace VendorPDFCatalogImporter\Includes;

/**
 * Simple logger to file.
 */
class Logger {
    public static function log( $message ) {
        $upload_dir = wp_upload_dir();
        $file = trailingslashit( $upload_dir['basedir'] ) . 'vpci.log';
        error_log( '[' . current_time( 'mysql' ) . '] ' . $message . "\n", 3, $file );
    }
}
