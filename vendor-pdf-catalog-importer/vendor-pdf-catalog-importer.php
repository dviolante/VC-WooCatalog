<?php
/**
 * Plugin Name: Vendor PDF Catalog Importer
 * Description: Upload PDF catalogs to create WooCommerce products automatically.
 * Version: 1.0.0
 * Author: Codex
 * License: GPL2
 * Text Domain: vendor-pdf-catalog-importer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'VPCI_PATH', plugin_dir_path( __FILE__ ) );

define( 'VPCI_URL', plugin_dir_url( __FILE__ ) );

require_once VPCI_PATH . 'includes/Autoloader.php';
VendorPDFCatalogImporter\Includes\Autoloader::register();

// Run the plugin.
VendorPDFCatalogImporter\Includes\Plugin::init();
