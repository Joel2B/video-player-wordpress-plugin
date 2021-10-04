<?php
/**
 * Ajax Method to load logs data.
 *
 * @api
 * @package cvp\admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load the log page data.
 *
 * @return void
 */
function cvp_load_logs_data() {
    check_ajax_referer( 'ajax-nonce', 'nonce' );
    $data         = array();
    $data['logs'] = cvp_log()->get_logs();
    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_cvp_load_logs_data', 'cvp_load_logs_data' );
