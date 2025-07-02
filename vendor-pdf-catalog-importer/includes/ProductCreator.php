<?php
namespace VendorPDFCatalogImporter\Includes;

use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Attribute;
use WC_Product_Variation;

/**
 * Create products from images and text.
 */
class ProductCreator {
    /**
     * Create product from page image and text.
     */
    public static function create_from_page( $vendor_id, $image, $text ) {
        $title = self::parse_field( 'title', $text );
        $sku   = self::parse_field( 'sku', $text );
        $short = self::parse_field( 'short', $text );
        $long  = self::parse_field( 'long', $text );

        $product = new WC_Product_Simple();
        $product->set_name( $title ? $title : __( 'Imported Product', 'vendor-pdf-catalog-importer' ) );
        if ( $sku ) {
            $product->set_sku( $sku );
        }
        $product->set_description( $long );
        $product->set_short_description( $short );
        $product->set_catalog_visibility( 'visible' );
        $product->set_status( 'draft' );
        $product->set_reviews_allowed( true );
        $product->set_menu_order( 0 );
        $product->set_manage_stock( false );
        $product_id = $product->save();

        // Attach image as featured image.
        $attachment_id = self::attach_image( $product_id, $image );
        if ( $attachment_id ) {
            set_post_thumbnail( $product_id, $attachment_id );
        }

        // Assign author (vendor).
        wp_update_post( [ 'ID' => $product_id, 'post_author' => $vendor_id ] );
    }

    /**
     * Parse text to get field placeholder (very basic parsing for demo).
     */
    protected static function parse_field( $field, $text ) {
        $pattern = '/' . $field . ':\s*(.+)/i';
        if ( preg_match( $pattern, $text, $m ) ) {
            return sanitize_text_field( $m[1] );
        }
        return '';
    }

    /**
     * Attach image to product.
     */
    protected static function attach_image( $post_id, $file ) {
        $upload_dir = wp_upload_dir();
        $relative   = str_replace( trailingslashit( $upload_dir['basedir'] ), '', $file );
        $attachment = [
            'post_mime_type' => 'image/jpeg',
            'post_title'     => get_the_title( $post_id ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];
        $attach_id = wp_insert_attachment( $attachment, $relative, $post_id );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        return $attach_id;
    }
}
