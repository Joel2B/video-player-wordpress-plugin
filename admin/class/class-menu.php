<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Menu Class
 *
 * @since 1.0.0
 */
class CVP_Admin_Menu {
    /**
     * Constructor method
     *
     * @return void
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menus' ) );
    }

    /**
     * Register the menu callback
     *
     * @return void
     */
    public function register_menus() {
        add_menu_page( CVP_NAME, CVP_NAME, 'manage_options', 'cvp-dashboard', 'cvp_dashboard_page', 'dashicons-video-alt3' );
        CVP()->generate_sub_menu();
    }
}

$cvp_menu = new CVP_Admin_Menu();
