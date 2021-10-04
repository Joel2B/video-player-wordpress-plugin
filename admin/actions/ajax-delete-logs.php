<?php
/**
 * Ajax Method to delete logs data.
 *
 * @api
 * @package admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Delete logs.
 *
 * @return void
 */
function cvp_delete_logs() {
    check_ajax_referer( 'ajax-nonce', 'nonce' );
    cvp_log()->delete_logs();
    wp_die();
}

add_action( 'wp_ajax_cvp_delete_logs', 'cvp_delete_logs' );
