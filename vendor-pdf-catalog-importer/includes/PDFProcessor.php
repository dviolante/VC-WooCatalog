<?php
namespace VendorPDFCatalogImporter\Includes;

use Imagick;
use WP_Error;

/**
 * Convert PDF and create products.
 */
class PDFProcessor {
    public static function init() {
        add_action( 'vpci_process_job', [ __CLASS__, 'process_job' ] );
    }

    /**
     * Schedule async job using Action Scheduler if available.
     */
    public static function schedule_job( $job_id ) {
        if ( class_exists( 'ActionScheduler' ) ) {
            as_enqueue_async_action( 'vpci_process_job', [ 'job_id' => $job_id ], 'vpci' );
        } else {
            wp_schedule_single_event( time() + 60, 'vpci_process_job', [ 'job_id' => $job_id ] );
        }
    }

    /**
     * Process job: convert PDF, parse text, create products.
     */
    public static function process_job( $args ) {
        $job_id = $args['job_id'];
        JobManager::update_status( $job_id, 'processing' );
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_pdf_jobs';
        $job   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $job_id ) );
        if ( ! $job ) {
            return;
        }
        $upload_dir = wp_upload_dir();
        $dest_dir = trailingslashit( $upload_dir['basedir'] ) . 'vendor-catalogs/' . $job->vendor_id . '/' . $job_id . '/';
        wp_mkdir_p( $dest_dir );
        try {
            $imagick = new Imagick();
            $imagick->setResolution( 150, 150 );
            $imagick->readImage( $job->pdf_path );
            foreach ( $imagick as $i => $page ) {
                $page->setImageFormat( 'jpeg' );
                $page->setImageCompressionQuality( 85 );
                $page->scaleImage( 1280, 0 );
                $filename = $dest_dir . ( $i + 1 ) . '.jpg';
                $page->writeImage( $filename );
                $text = self::extract_text( $job->pdf_path, $i );
                ProductCreator::create_from_page( $job->vendor_id, $filename, $text );
            }
            JobManager::update_status( $job_id, 'completed' );
        } catch ( \Exception $e ) {
            JobManager::update_status( $job_id, 'error' );
            Logger::log( 'Job ' . $job_id . ' failed: ' . $e->getMessage() );
        }
    }

    /**
     * Extract text from PDF page.
     */
    protected static function extract_text( $pdf, $page = 0 ) {
        if ( class_exists( '\Smalot\PdfParser\Parser' ) ) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile( $pdf );
            $pages = $pdf->getPages();
            if ( isset( $pages[ $page ] ) ) {
                return $pages[ $page ]->getText();
            }
        }
        // Fallback empty text.
        return '';
    }
}
