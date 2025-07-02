<?php
namespace VendorPDFCatalogImporter\Includes;

/**
 * Admin page to upload PDFs.
 */
class AdminPage {
    /**
     * Init hooks.
     */
    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
        add_action( 'admin_post_vpci_upload', [ __CLASS__, 'handle_upload' ] );
    }

    /**
     * Register menu page for vendors.
     */
    public static function menu() {
        if ( ! current_user_can( 'read' ) ) {
            return;
        }
        add_menu_page( __( 'PDF Catalog Import', 'vendor-pdf-catalog-importer' ), __( 'PDF Catalog Import', 'vendor-pdf-catalog-importer' ), 'read', 'vpci-import', [ __CLASS__, 'page' ] );
    }

    /**
     * Render upload form.
     */
    public static function page() {
        if ( ! current_user_can( 'read' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php _e( 'Upload PDF Catalog', 'vendor-pdf-catalog-importer' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <?php wp_nonce_field( 'vpci_upload' ); ?>
                <input type="hidden" name="action" value="vpci_upload" />
                <input type="file" name="vpci_pdf" accept="application/pdf" required />
                <?php submit_button( __( 'Upload', 'vendor-pdf-catalog-importer' ) ); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Handle PDF upload from admin page.
     */
    public static function handle_upload() {
        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Access denied', 'vendor-pdf-catalog-importer' ) );
        }
        check_admin_referer( 'vpci_upload' );

        if ( empty( $_FILES['vpci_pdf']['name'] ) ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        $file = $_FILES['vpci_pdf'];
        if ( 'application/pdf' !== $file['type'] || $file['size'] > 10 * 1024 * 1024 ) {
            wp_die( __( 'Invalid file', 'vendor-pdf-catalog-importer' ) );
        }

        $upload = wp_handle_upload( $file, [ 'test_form' => false ] );
        if ( isset( $upload['file'] ) ) {
            $vendor_id = get_current_user_id();
            $job_id = JobManager::create_job( $vendor_id, $upload['file'] );
            PDFProcessor::schedule_job( $job_id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=vpci-import' ) );
        exit;
    }
}
