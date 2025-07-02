<?php
namespace VendorPDFCatalogImporter\Includes;

/**
 * Shortcode for vendor upload form.
 */
class FrontendShortcode {
    public static function init() {
        add_shortcode( 'vendor_pdf_catalog_form', [ __CLASS__, 'render_form' ] );
        add_action( 'admin_post_nopriv_vpci_upload_front', [ __CLASS__, 'handle_upload' ] );
        add_action( 'admin_post_vpci_upload_front', [ __CLASS__, 'handle_upload' ] );
    }

    public static function render_form() {
        if ( ! is_user_logged_in() ) {
            return __( 'Please log in to upload catalog.', 'vendor-pdf-catalog-importer' );
        }
        ob_start();
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field( 'vpci_upload_front' ); ?>
            <input type="hidden" name="action" value="vpci_upload_front" />
            <input type="file" name="vpci_pdf" accept="application/pdf" required />
            <button type="submit"><?php _e( 'Upload', 'vendor-pdf-catalog-importer' ); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function handle_upload() {
        if ( ! is_user_logged_in() ) {
            wp_die( __( 'Access denied', 'vendor-pdf-catalog-importer' ) );
        }
        check_admin_referer( 'vpci_upload_front' );

        $file = $_FILES['vpci_pdf'];
        if ( empty( $file['name'] ) || 'application/pdf' !== $file['type'] || $file['size'] > 10 * 1024 * 1024 ) {
            wp_die( __( 'Invalid file', 'vendor-pdf-catalog-importer' ) );
        }
        $upload = wp_handle_upload( $file, [ 'test_form' => false ] );
        if ( isset( $upload['file'] ) ) {
            $vendor_id = get_current_user_id();
            $job_id = JobManager::create_job( $vendor_id, $upload['file'] );
            PDFProcessor::schedule_job( $job_id );
        }
        wp_safe_redirect( wp_get_referer() );
        exit;
    }
}
