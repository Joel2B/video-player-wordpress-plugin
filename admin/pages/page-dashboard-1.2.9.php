<?php
/**
 * Default page
 *
 * @package CORE\Admin\Pages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Default page callback function
 *
 * @return void
 */
function cvp_dashboard_page_1_2_9() {
	echo '<script>window.location.replace("admin.php?page=cvp-dashboard");</script>';
}
