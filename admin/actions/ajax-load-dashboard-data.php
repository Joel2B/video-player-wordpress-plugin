<?php
/**
 * Ajax Method to load dashboard data.
 *
 * @api
 * @package admin\actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load dashboard page data.
 *
 * @return void
 */
function cvp_load_dashboard_data() {
    check_ajax_referer( 'ajax-nonce', 'nonce' );
    $data = array(
        'changelog' => CVP()->get_changelog(),
        'core'      => CVP()->get_core_options(),
    );
    wp_send_json( $data );
    wp_die();
}

add_action( 'wp_ajax_cvp_load_dashboard_data', 'cvp_load_dashboard_data' );
