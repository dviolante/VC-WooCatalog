<?php
namespace VendorPDFCatalogImporter\Includes;

use WP_Error;

/**
 * Manage import jobs.
 */
class JobManager {
    public static function init() {
        // Nothing for now.
    }

    /**
     * Create a job record.
     */
    public static function create_job( $vendor_id, $pdf_path ) {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_pdf_jobs';
        $wpdb->insert( $table, [
            'vendor_id' => $vendor_id,
            'pdf_path'  => $pdf_path,
            'status'    => 'pending',
            'created_at' => current_time( 'mysql' ),
            'updated_at' => current_time( 'mysql' ),
        ] );
        return $wpdb->insert_id;
    }

    /**
     * Update job status.
     */
    public static function update_status( $job_id, $status ) {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_pdf_jobs';
        $wpdb->update( $table, [
            'status'     => $status,
            'updated_at' => current_time( 'mysql' ),
        ], [ 'id' => $job_id ] );
    }

    /**
     * Get jobs by vendor.
     */
    public static function get_jobs( $vendor_id, $paged = 1, $per_page = 20 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_pdf_jobs';
        $offset = ( $paged - 1 ) * $per_page;
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE vendor_id = %d ORDER BY id DESC LIMIT %d OFFSET %d", $vendor_id, $per_page, $offset ) );
    }
}
