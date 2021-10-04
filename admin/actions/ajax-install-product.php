<?php
/**
 * Ajax Method to install product (theme or plugin).
 *
 * @api
 * @package admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Install / Update product within cvp dashboard.
 *
 * @return void
 */
function cvp_install_product() {
    check_ajax_referer( 'ajax-nonce', 'nonce' );

    if ( ! isset( $_POST['method'], $_POST['product_type'], $_POST['product_zip'], $_POST['product_slug'], $_POST['product_folder_slug'], $_POST['new_version'] ) ) {
        wp_die( 'Some parameters are missing!' );
    }

    $method              = sanitize_text_field( wp_unslash( $_POST['method'] ) );
    $product_type        = sanitize_text_field( wp_unslash( $_POST['product_type'] ) );
    $product_zip         = sanitize_text_field( wp_unslash( $_POST['product_zip'] ) );
    $product_slug        = sanitize_text_field( wp_unslash( $_POST['product_slug'] ) );
    $product_folder_slug = sanitize_text_field( wp_unslash( $_POST['product_folder_slug'] ) );
    $new_version         = sanitize_text_field( wp_unslash( $_POST['new_version'] ) );

    $product = array(
        'file_path'   => $product_folder_slug . '/' . $product_folder_slug . '.php',
        'package'     => $product_zip,
        'new_version' => $new_version,
        'slug'        => $product_slug,
    );
    $installer = new CVP_Product_Uploader();
    $output    = $installer->upload_product( $product_type, $method, $product );

    wp_send_json( $output );
    wp_die();
}

add_action( 'wp_ajax_cvp_install_product', 'cvp_install_product' );
