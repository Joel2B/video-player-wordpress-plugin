<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'CVP_DEBUG', false );
define( 'CVP_VERSION', '1.0.0' );
define( 'CVP_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
define( 'CVP_URL', plugin_dir_url( __FILE__ ) );
define( 'CVP_LOG_FILE', wp_normalize_path( CVP_DIR . 'admin' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'cvp.log' ) );
define( 'CVP_API_URL', 'https://appsdev.cyou/products' );
define( 'CVP_VIDEO_API_URL', 'https://appsdev.cyou/xv-ph-rt/api' );
define( 'CVP_NAME', 'CVP' );
define( 'CVP_PHP_REQUIRED', '5.6.20' );

/*
 * Navigation config
 */
self::$config['nav'] = array(
    '0'           => array(
        'slug'     => 'cvp-dashboard',
        'callback' => 'cvp_dashboard_page',
        'title'    => 'Dashboard',
    ),
    'cvp-options' => array(
        'slug'     => 'cvp-options',
        'title'    => 'Plugin Options',
    ),
    '1000'        => array(
        'slug'     => 'logs-page',
        'callback' => 'cvp_logs_page',
        'title'    => 'Logs',
    ),
);

/*
 * JS config
 */
self::$config['scripts']['js'] = array(
    // vendor.
    'CVP_lodash.js'       => array(
        'in_pages'  => 'cvp_pages',
        'path'      => 'admin/vendors/lodash/lodash.min.js',
        'require'   => array(),
        'version'   => '4.17.21',
        'in_footer' => false,
    ),
    'CVP_bootstrap.js'    => array(
        'in_pages'  => 'cvp_pages',
        'path'      => 'admin/vendors/bootstrap/js/bootstrap.min.js',
        'require'   => array( 'jquery' ),
        'version'   => '3.2.0',
        'in_footer' => false,
    ),
    'CVP_vue.js'          => array(
        'in_pages'  => 'cvp_pages',
        'path'      => 'admin/vendors/vue/vue.min.js',
        'require'   => array(),
        'version'   => '2.6.12',
        'in_footer' => false,
    ),
    'CVP_vue-resource.js' => array(
        'in_pages'  => 'cvp_pages',
        'path'      => 'admin/vendors/vue-resource/vue-resource.min.js',
        'require'   => array(),
        'version'   => '1.5.1',
        'in_footer' => false,
    ),
    'CVP_vue-notify.js'   => array(
        'in_pages'  => 'cvp_pages',
        'path'      => 'admin/vendors/vue-notify/vue-notify.min.js',
        'require'   => array(),
        'version'   => '3.2.1',
        'in_footer' => false,
    ),
    'CVP_clipboard.js'    => array(
        'in_pages'  => array( 'logs-page' ),
        'path'      => 'admin/vendors/clipboard/clipboard.min.js',
        'require'   => array(),
        'version'   => '2.0.6',
        'in_footer' => false,
    ),
    // pages.
    'CVP_dashboard.js'    => array(
        'in_pages'  => array( 'cvp-dashboard' ),
        'path'      => 'admin/pages/page-dashboard.js',
        'require'   => array(),
        'version'   => CVP_VERSION,
        'in_footer' => false,
        'localize'  => array(
            'ajax'     => true,
            'base_url' => CVP_URL,
            'i18n'     => array(
                'loading_reloading' => __( 'Reloading', 'cvp_lang' ),
                'changelog'         => __( 'Changelog', 'cvp_lang' ),
            ),
        ),
    ),
    'CVP_logs.js'         => array(
        'in_pages'  => array( 'logs-page' ),
        'path'      => 'admin/pages/page-logs.js',
        'require'   => array(),
        'version'   => CVP_VERSION,
        'in_footer' => false,
        'localize'  => array(
            'ajax'       => true,
            'objectL10n' => array(),
        ),
    ),
);

/*
 *  CSS config.
 */
self::$config['scripts']['css'] = array(
    // vendor.
    'CVP_fontawesome.css'           => array(
        'in_pages' => 'cvp_pages',
        'path'     => 'admin/vendors/font-awesome/css/font-awesome.min.css',
        'require'  => array(),
        'version'  => '4.6.0',
        'media'    => 'all',
    ),
    'CVP_bootstrap.css'             => array(
        'in_pages' => 'cvp_pages',
        'path'     => 'admin/vendors/bootstrap/css/bootstrap.min.css',
        'require'  => array(),
        'version'  => '3.2.0',
        'media'    => 'all',
    ),
    'CVP_bootstrap-4-utilities.css' => array(
        'in_pages' => 'cvp_pages',
        'path'     => 'admin/vendors/bootstrap/css/bootstrap-4-utilities.min.css',
        'require'  => array( 'CVP_bootstrap.css' ),
        'version'  => '1.0.0',
        'media'    => 'all',
    ),
    'CVP_vue-notify.css'            => array(
        'in_pages' => 'cvp_pages',
        'path'     => 'admin/vendors/vue-notify/vue-notify.min.css',
        'require'  => array(),
        'version'  => '3.2.1',
        'media'    => 'all',
    ),
    // assets.
    'CVP_admin.css'                 => array(
        'in_pages' => 'cvp_pages',
        'path'     => 'admin/assets/css/admin.min.css',
        'require'  => array(),
        'version'  => CVP_VERSION,
        'media'    => 'all',
    ),
    'CVP_dashboard.css'             => array(
        'in_pages' => array( 'cvp-dashboard' ),
        'path'     => 'admin/assets/css/dashboard.css',
        'require'  => array(),
        'version'  => CVP_VERSION,
        'media'    => 'all',
    ),
);
