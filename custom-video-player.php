<?php

/**
 * Plugin Name: Custom Video Player
 * Description: Custom Video Player for Xvideos, PornHub and RedTube
 * Author: Joel2B
 * Author URI: https://github.com/Joel2B
 * Version: 1.0.0
 * Text Domain: custom-video-player
 * Domain Path: /languages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Singleton Class
 */
final class CVP {

    /**
     * The instance of the CORE plugin
     *
     * @var      instanceof CVP $instance
     * @static
     */
    private static $instance;

    /**
     * The config of the CORE plugin
     *
     * @var      array $config
     * @static
     */
    private static $config;

    /**
     * __clone method
     *
     * @return   void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'cvp_lang' ), '1.0' );
    }

    /**
     * __wakeup method
     *
     * @return   void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'cvp_lang' ), '1.0' );
    }

    /**
     * Instance method
     *
     * @return   self::$instance
     */
    public static function instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CVP ) ) {
            self::$instance = new CVP();

            // Load textdomain.
            self::$instance->load_textdomain();

            // Load config file.
            require_once plugin_dir_path( __FILE__ ) . 'config.php';

            // Load log system.
            require_once plugin_dir_path( __FILE__ ) . 'admin/logs/class-log.php';

            // Load options system.
            if ( CVP()->php_version_ok() ) {
                if ( strpos( self::$instance->get_current_page_slug(), 'cvp' ) !== false ) {
                    require_once plugin_dir_path( __FILE__ ) . 'xbox/xbox.php';
                }

                if ( ! function_exists( 'is_plugin_active' ) ) {
                    require_once ABSPATH . 'wp-admin/includes/plugin.php';
                }

                if ( ! is_plugin_active( 'wp-script-core/wp-script-core.php' ) ) {
                    require_once plugin_dir_path( __FILE__ ) . 'xbox/xbox.php';
                }
            }

            if ( is_admin() ) {
                self::$instance->load_filters();
                self::$instance->load_hooks();
                self::$instance->auto_load_php_files( 'admin' );
                self::$instance->init();
            } else {
                self::$instance->load_public_filters();
                self::$instance->load_public_hooks();
                self::$instance->auto_load_php_files( 'public/classes' );
            }
            require_once plugin_dir_path( __FILE__ ) . 'admin/pages/options.php';
        }
        return self::$instance;
    }

    public function load_filters() {
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_details' ), 25, 4 );
        add_filter( 'plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
    }

    public function load_hooks() {
        add_action( 'admin_init', array( $this, 'save_default_options' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'auto_load_scripts' ), 100 );
        add_action( 'admin_init', array( $this, 'reorder_submenu' ) );
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
    }

    public function load_public_filters() {
        add_filter( 'query_vars', array( $this, 'player_query_var' ) );
        add_filter( 'template_include', array( $this, 'player_page_template' ), 99 );
    }

    private function load_public_hooks() {
        add_action( 'wp_head', array( $this, 'converter' ), 9999 );
    }

    public function converter() {
        $dom = new Dom();
        ob_start( array( $dom, 'prepare_iframes' ) );
    }

    public function player_query_var( $vars ) {
        $vars[] = 'cvp_data';
        return $vars;
    }

    public function player_page_template( $template ) {
        if ( get_query_var( 'cvp_data' ) ) {
            $template = CVP_DIR . 'public/index.php';
        }
        return $template;
    }

    public function init() {
        $plugin = $this->get_core_options();
        if ( $plugin['is_latest_version'] ) {
            return;
        }
        $repo_updates_plugins = get_site_transient( 'update_plugins' );
        if ( ! is_object( $repo_updates_plugins ) ) {
            $repo_updates_plugins = new stdClass();
        }

        $file_path = $plugin['slug'] . '/' . $plugin['slug'] . '.php';
        if ( empty( $repo_updates_plugins->response[$file_path] ) ) {
            $repo_updates_plugins->response[$file_path] = new stdClass();
        }
        $repo_updates_plugins->response[$file_path]->slug        = $plugin['slug'];
        $repo_updates_plugins->response[$file_path]->new_version = $plugin['latest_version'];
        $repo_updates_plugins->response[$file_path]->package     = $plugin['zip_file'];
        set_site_transient( 'update_plugins', $repo_updates_plugins );
    }

    public static function activation() {
        CVP()->init();
    }

    public static function deactivation() {
        CVP()->init();
    }

    public function get_api_url( $action ) {
        return CVP_API_URL . '/' . CVP_NAME . '/' . $action;
    }

    public function plugin_info( $res, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return false;
        }

        $plugin_slug = plugin_basename( __DIR__ );
        if ( $plugin_slug !== $args->slug ) {
            return false;
        }

        $response = wp_remote_get( $this->get_api_url( 'details' ) );
        if ( is_wp_error( $response ) ) {
            return false;
        }

        $res           = json_decode( wp_remote_retrieve_body( $response ) );
        $res->sections = (array) $res->sections;
        $res->banners  = (array) $res->banners;
        return $res;
    }

    public function plugin_details( $links, $plugin_file, $plugin_data ) {
        if (
            isset( $plugin_data['new_version'] ) ||
            strpos( $plugin_file, basename( __FILE__ ) ) === false
        ) {
            return $links;
        }

        $links[] = sprintf(
            '<a href="%s" class="thickbox open-plugin-details-modal">%s</a>',
            add_query_arg(
                array(
                    'tab'       => 'plugin-information',
                    'plugin'    => plugin_basename( __DIR__ ),
                    'TB_iframe' => true,
                    'width'     => 772,
                    'height'    => 788,
                ),
                admin_url( 'plugin-install.php' )
            ),
            __( 'View details' )
        );
        return $links;
    }

    public static function add_settings_link( $links, $plugin_file ) {
        if (
            strpos( $plugin_file, basename( __FILE__ ) ) === false ||
            ! current_user_can( 'manage_options' )
        ) {
            return $links;
        }

        if ( current_filter() === 'plugin_action_links' ) {
            $url = admin_url( 'admin.php?page=cvp-dashboard' );
        }

        // Prevent warnings in PHP 7.0+ when a plugin uses this filter incorrectly.
        $links   = (array) $links;
        $links[] = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'classic-editor' ) );
        return $links;
    }

    /**
     * Get the current page slug.
     *
     * @return string The current page slug.
     */
    private function get_current_page_slug() {
        if ( ! isset( $_GET['page'] ) ) {
            return '';
        }
        return sanitize_title( wp_unslash( $_GET['page'] ) );
    }

    /**
     * Method to save default Xbox options on admin_init hook action
     *
     * @return void
     */
    public function save_default_options() {
        check_ajax_referer( 'ajax-nonce', 'nonce', false );
        if ( ! CVP()->php_version_ok() || 'cvp-dashboard' !== $this->get_current_page_slug() ) {
            return;
        }
        $all_options = xbox_get_all();
        foreach ( (array) $all_options as $xbox_id => $xbox_options ) {
            if ( get_option( $xbox_id ) === false ) {
                $xbox = xbox_get( strtolower( $xbox_id ) );
                if ( $xbox ) {
                    $xbox->save_fields( 0, array( 'display_message_on_save' => false ) );
                }
            }
        }
    }

    /**
     * Method to load js and css files
     *
     * @return void
     */
    public function auto_load_scripts() {
        // phpcs:disable
        $scripts = apply_filters( 'CVP-scripts', self::$config['scripts']['js'] + self::$config['scripts']['css'] );
        // phpcs:enable
        $cvp_pages         = $this->get_pages_slugs();
        $current_page_slug = $this->get_current_page_slug();

        if ( in_array( $current_page_slug, $cvp_pages, true ) && strpos( $current_page_slug, '-options' ) === false ) {
            global $wp_scripts, $wp_styles;
            // Removing Bootstrap scripts on pages.
            foreach ( (array) $wp_scripts->registered as $script_key => $script_config ) {
                if ( strpos( $script_config->src, 'bootstrap' ) !== false ) {
                    wp_deregister_script( $script_key );
                }
            }
            // Removing Bootstrap styles on pages.
            foreach ( (array) $wp_styles->registered as $script_key => $script_config ) {
                if ( strpos( $script_config->src, 'bootstrap' ) !== false ) {
                    wp_deregister_script( $script_key );
                }
            }
        }

        // add scripts and css on pages.
        foreach ( (array) $scripts as $k => $v ) {
            if ( ! isset( $v['in_pages'] ) || in_array( $current_page_slug, ( 'cvp_pages' === $v['in_pages'] ? $cvp_pages : $v['in_pages'] ), true ) ) {
                $type    = explode( '.', $k );
                $type    = end( $type );
                $sku     = explode( '_', $k );
                $sku     = current( $sku );
                $path    = str_replace( array( 'http:', 'https:' ), array( '', '' ), constant( $sku . '_URL' ) . $v['path'] );
                $uri     = str_replace( array( 'http:', 'https:' ), array( '', '' ), constant( $sku . '_DIR' ) . $v['path'] );
                $version = $v['version'] . '.' . filemtime( $uri );
                switch ( $type ) {
                    case 'js':
                        // exclude script if option pages and script is bootstrap to avoid dropdown conflicts.
                        if ( strpos( $current_page_slug, '-options' ) !== false && 'CVP_bootstrap.js' === $k ) {
                            break;
                        }
                        // exclude script if option pages and script is lodash to avoid gutenberg and underscore conflicts.
                        if ( strpos( $current_page_slug, '-options' ) !== false && 'CVP_lodash.js' === $k ) {
                            break;
                        }
                        $v['in_footer'] = true; // Force to load scripts in footer to prevent JS loading issues.
                        wp_enqueue_script( $k, $path, $v['require'], $version, $v['in_footer'] );
                        if ( isset( $v['localize'] ) && ! empty( $v['localize'] ) ) {
                            if ( isset( $v['localize']['ajax'] ) && true === $v['localize']['ajax'] ) {
                                $v['localize']['ajax'] = array(
                                    'url'   => str_replace( array( 'http:', 'https:' ), array( '', '' ), admin_url( 'admin-ajax.php' ) ),
                                    'nonce' => wp_create_nonce( 'ajax-nonce' ),
                                );
                            }
                            wp_localize_script( $k, str_replace( array( '-', '.js' ), array( '_', '' ), $k ), $v['localize'] );
                        }
                        break;
                    case 'css':
                        wp_enqueue_style( $k, $path, $v['require'], $version, $v['media'] );
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Auto-loader for PHP files
     *
     * @since 1.0.0
     *
     * @param string{'admin','public'} $dir Directory where to find PHP files to load.
     * @static
     * @return void
     */
    public function auto_load_php_files( $dir ) {
        $dirs = (array) ( plugin_dir_path( __FILE__ ) . $dir . '/' );
        foreach ( (array) $dirs as $dir ) {
            $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ) );
            if ( ! empty( $files ) ) {
                foreach ( $files as $file ) {
                    // exclude dir.
                    if ( $file->isDir() ) {
                        continue;
                    }
                    // exclude index.php.
                    if ( $file->getPathname() === 'index.php' ) {
                        continue;
                    }
                    // exclude files != .php.
                    if ( substr( $file->getPathname(), -4 ) !== '.php' ) {
                        continue;
                    }
                    // exclude files from -x suffixed directories.
                    if ( substr( $file->getPath(), -2 ) === '-x' ) {
                        continue;
                    }
                    // exclude -x suffixed files.
                    if ( substr( $file->getPathname(), -6 ) === '-x.php' ) {
                        continue;
                    }
                    // else require file.
                    require $file->getPathname();
                }
            }
        }
    }

    /**
     * Get CVP options
     *
     * @return mixed array|bool CVP options if found, false if not.
     */
    public function get_core_options() {
        $response = wp_remote_get( $this->get_api_url( 'info' ) );
        if ( is_wp_error( $response ) ) {
            CVP()->write_log( 'error', 'Connection to API (changelog) failed', __FILE__, __LINE__ );
            return false;
        }
        $products                      = json_decode( wp_remote_retrieve_body( $response ), true );
        $products['installed_version'] = CVP_VERSION;
        $products['is_latest_version'] = version_compare( $products['installed_version'], $products['latest_version'], '>=' );
        return $products;
    }

    public function get_changelog() {
        $response = wp_remote_get( $this->get_api_url( 'changelog' ) );
        if ( is_wp_error( $response ) ) {
            CVP()->write_log( 'error', 'Connection to API (changelog) failed', __FILE__, __LINE__ );
            return;
        }
        $changelog = json_decode( wp_remote_retrieve_body( $response ), true );
        return $changelog;
    }

    /**
     * Is PHP required version ok?
     * - >= 5.3.0 since v1.0.0
     * - >= 5.6.20 since v1.3.9
     *
     * @return bool True if PHP version is ok, false if not.
     */
    public function php_version_ok() {
        return version_compare( PHP_VERSION, CVP_PHP_REQUIRED ) >= 0;
    }

    /**
     * Is cUrl installed?
     *
     * @return bool True if cUrl is installed, false if not.
     */
    public function curl_ok() {
        return function_exists( 'curl_version' );
    }

    /**
     * Get installed cUrl version.
     *
     * @return string The installed cUrl version.
     */
    public function get_curl_version() {
        if ( ! CVP()->curl_ok() ) {
            return '';
        }
        $curl_infos = curl_version();
        return $curl_infos['version'];
    }

    /**
     * Is cUrl required version installed?
     *
     * @return bool True if the cUrl installed version is ok, false if not.
     */
    public function curl_version_ok() {
        if ( ! CVP()->curl_ok() ) {
            return false;
        }
        return version_compare( CVP()->get_curl_version(), '7.34.0' ) >= 0;
    }

    /**
     * Write a new line of log in the log file.
     *
     * @deprecated 1.3.9 Use CVP_Log class instead.
     *
     * @param string $type      Log type.
     * @param string $message   Log message.
     * @param string $file_uri  Log file uri.
     * @param int    $file_line Log file line.
     * @return void
     */
    public function write_log( $type, $message, $file_uri = '', $file_line = '' ) {
        cvp_log()->write_log( $type, $message, 0, $file_uri, $file_line );
    }

    /**
     * Get pages & tabs slugs
     *
     * @return array with all pages & tabs slugs
     */
    public function get_pages_slugs() {
        // phpcs:disable
        $pages = apply_filters( 'CVP-pages', self::$config['nav'] );
        // phpcs:enable
        foreach ( (array) $pages as $k => $v ) {
            $output[] = $v['slug'];
        }
        // add plugin options page.
        $output[] = 'cvp-options';
        return $output;
    }

    /**
     * Generate sub-menus.
     *
     * @return void
     */
    public function generate_sub_menu() {
        // phpcs:disable
        $nav_elts = apply_filters( 'CVP-pages', self::$config['nav'] );
        // phpcs:enable
        // filter and sort menus.
        $final_nav_elts = array();
        foreach ( (array) $nav_elts as $key => $nav_elt ) {
            // add plugin options
            if ( ! is_int( $key ) && 'cvp-options' !== $key ) {
                continue;
            }
            // exclude [0] dashboard && [1000] logs pages keys.
            if ( 0 === $key || 1000 === $key ) {
                continue;
            }
            $final_nav_elts[] = $nav_elt;
        };

        usort( $final_nav_elts, array( $this, 'sort_sub_menu' ) );

        // add v < 1.2.9 Dashboard hidden submenu to redirect CVP-dashboard to cvp-dashboard and prevent forbidden access to not existing page.
        add_submenu_page( null, null, null, 'manage_options', 'CVP-dashboard', 'cvp_dashboard_page_1_2_9' );
        // add Dashboard submenu.
        add_submenu_page( 'cvp-dashboard', $nav_elts[0]['title'], $nav_elts[0]['title'], 'manage_options', $nav_elts[0]['slug'], $nav_elts[0]['callback'] );
        if ( strpos( $this->get_current_page_slug(), 'cvp' ) === false &&
            is_plugin_active( 'wp-script-core/wp-script-core.php' ) ) {
            add_submenu_page( 'cvp-dashboard', $nav_elts['cvp-options']['title'], $nav_elts['cvp-options']['title'], 'manage_options', $nav_elts['cvp-options']['slug'], 'build_admin_page' );
        }
        // add submenus.
        foreach ( (array) $final_nav_elts as $final_nav_elt ) {
            if ( isset( $final_nav_elt['slug'], $final_nav_elt['callback'], $final_nav_elt['title'] ) ) {
                $final_nav_elt['title'] = $final_nav_elt['title'];
                add_submenu_page( 'cvp-dashboard', $final_nav_elt['title'], $final_nav_elt['title'], 'manage_options', $final_nav_elt['slug'], $final_nav_elt['callback'] );
            }
        }
        // add Logs submenu.
        add_submenu_page( 'cvp-dashboard', $nav_elts[1000]['title'], $nav_elts[1000]['title'], 'manage_options', $nav_elts[1000]['slug'], $nav_elts[1000]['callback'] );
        // add Help submenu.
        add_submenu_page( 'cvp-dashboard', __( 'Help', 'cvp_lang' ), __( 'Help', 'cvp_lang' ), 'manage_options', 'http://google.com' );
    }

    /**
     * Sort sub menu.
     *
     * @param array $nav_elt_1 First element for sort process.
     * @param array $nav_elt_2 Second element for sort process.
     *
     * @return array the new array sorted.
     */
    private function sort_sub_menu( $nav_elt_1, $nav_elt_2 ) {
        return $nav_elt_1['title'] > $nav_elt_2['title'];
    }

    /**
     * Reorder plugins sub menu.
     * Update $submenu WordPress Global variable.
     *
     * @return void
     */
    public function reorder_submenu() {
        global $submenu;
        if ( isset( $submenu['cvp-dashboard'] ) && is_array( $submenu['cvp-dashboard'] ) ) {
            $plugin_submenu = end( $submenu['cvp-dashboard'] );
            if ( 'Plugin Options' === $plugin_submenu[0] ) {
                // insert plugin option submenu at index 1, just after Dashboard indexed 0 submenu.
                array_splice( $submenu['cvp-dashboard'], 1, 0, array( $plugin_submenu ) );
                // Remove plugin option submenu at latest index.
                array_pop( $submenu['cvp-dashboard'] );
            }
        }
    }

    /**
     * Display tabs.
     *
     * @param boolean $echo Echo or not the tabs.
     *
     * @return mixed void|string Echoes the tabs if $echo === true or return tabs as array if not.
     */
    public function display_tabs( $echo = true ) {
        $current_page_slug = $this->get_current_page_slug();

        // phpcs:disable
        $data = apply_filters( 'CVP-tabs', self::$config['nav'] );
        // phpcs:enable
        ksort( $data );

        $static_tabs_slugs = array( 'cvp-dashboard', 'cvp-options', 'logs-page' );

        $output_tabs = '<ul class="nav nav-tabs">';
        // Output loop.
        foreach ( (array) $data as $index => $tab ) {
            if ( isset( $tab['slug'], $tab['title'] ) ) {
                $active = $tab['slug'] === $current_page_slug ? 'active' : null;
                if ( in_array( $tab['slug'], $static_tabs_slugs, true ) ) {
                    $output_tabs .= '<li class="' . $active . '"><a href="admin.php?page=' . $tab['slug'] . '"> ' . $tab['title'] . '</a></li>';
                }
            }
        }

        $output_tabs .= '<li><a href="https://www.wp-script.com/help/?utm_source=core&utm_medium=dashboard&utm_campaign=help&utm_content=tab" target="_blank"> ' . __( 'Help', 'cvp_lang' ) . '</a></li>';
        $output_tabs .= '</ul>';

        if ( ! $echo ) {
            return $output_tabs;
        }
        echo wp_kses( $output_tabs, wp_kses_allowed_html( 'post' ) );
    }

    /**
     * Get available updates
     *
     * @return array Array of available updates.
     */
    public function get_available_updates() {
        $core_data         = CVP()->get_core_options();
        $available_updates = array();

        if ( ! $core_data['is_latest_version'] ) {
            $available_updates[] = array(
                'product_key'            => CVP_NAME,
                'product_title'          => $core_data['title'],
                'product_latest_version' => $core_data['latest_version'],
            );
        }
        return $available_updates;
    }

    /**
     * Load textdomain method.
     *
     * @return bool True when textdomain is successfully loaded, false if not.
     */
    public function load_textdomain() {
        $lang       = ( current( explode( '_', get_locale() ) ) );
        $textdomain = 'cvp_lang';
        $mofile     = dirname( __FILE__ ) . "/languages/{$textdomain}_{$lang}.mo";
        return load_textdomain( $textdomain, $mofile );
    }
}

/**
 * Create the CVP instance in a function and call it.
 *
 * @return CVP::instance();
 */
// phpcs:disable
function CVP() {
    return CVP::instance();
}

CVP();
