<?php
namespace VendorPDFCatalogImporter\Includes;

use VendorPDFCatalogImporter\Includes\AdminPage;
use VendorPDFCatalogImporter\Includes\FrontendShortcode;
use VendorPDFCatalogImporter\Includes\JobManager;
use VendorPDFCatalogImporter\Includes\PDFProcessor;

/**
 * Main plugin bootstrap.
 */
class Plugin {
    /**
     * Initialize plugin hooks.
     */
    public static function init() {
        register_activation_hook( VPCI_PATH . 'vendor-pdf-catalog-importer.php', [ __CLASS__, 'activate' ] );
        add_action( 'plugins_loaded', [ __CLASS__, 'load' ] );
    }

    /**
     * Activation hook to create tables.
     */
    public static function activate() {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_pdf_jobs';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            vendor_id bigint(20) unsigned NOT NULL,
            pdf_path text NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Load plugin components.
     */
    public static function load() {
        AdminPage::init();
        FrontendShortcode::init();
        JobManager::init();
        PDFProcessor::init();
    }
}
