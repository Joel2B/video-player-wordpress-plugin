<?php
/**
 * CVP Hooks.
 *
 * @api
 * @package CORE\admin\hooks
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Display admin notice when there are some products updates.
 *
 * @return bool True if a notice is displayed, false if not.
 */
function cvp_admin_notice_updates() {
    $is_core_page      = 'toplevel_page_cvp-dashboard' === get_current_screen()->base ? true : false;
    $available_updates = CVP()->get_available_updates();
    if ( ! current_user_can( 'administrator' ) ) {
        return false;
    }
    if ( 0 === count( $available_updates ) ) {
        return false;
    }
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>Update available</p>';
    if ( $is_core_page ) {
        echo '<p><i class="fa fa-arrow-down" aria-hidden="true"></i> Just <strong>scroll down on this page and press green update button</strong> to update <i class="fa fa-arrow-down" aria-hidden="true"></i></p>';
    } else {
        $update_url = 'admin.php?page=cvp-dashboard';
        echo '<p>&#10149; ' . esc_html( $available_updates[0]['product_title'] ) . ' <strong>v' . esc_html( $available_updates[0]['product_latest_version'] ) . '</strong> &nbsp;&bull;&nbsp; <a href="' . esc_url( $update_url ) . '">Update</a></p>';
    }
    echo '</div>';
    return true;
}

add_action( 'admin_notices', 'cvp_admin_notice_updates' );
