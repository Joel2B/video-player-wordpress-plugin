<?php

class XboxLoader109 {
    private $version;
    private $priority;

    public function __construct( $version = '1.0.0', $priority = 1000 ) {
        $this->version  = $version;
        $this->priority = $priority;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Init Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public function init() {
        add_action( 'init', array( $this, 'load_xbox' ), $this->priority );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Init Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public function load_xbox() {

        if ( class_exists( 'Xbox', false ) ) {
            return;
        }

        //Xbox constants
        $this->constants();

        //Class autoloader
        $this->class_autoloader();

        //Loacalization
        $this->localization();

        //Includes
        $this->includes();

        //Xbox hooks
        if ( is_admin() ) {
            do_action( 'xbox_admin_init' );
        }
        do_action( 'xbox_init' );

        Xbox::init( $this->version );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Constants
    |---------------------------------------------------------------------------------------------------
     */
    public function constants() {
        define( 'XBOX_VERSION', $this->version );
        define( 'XBOX_PRIORITY', $this->priority );
        define( 'XBOX_SLUG', 'xbox' );
        define( 'XBOX_DIR', trailingslashit( dirname( __FILE__ ) ) );
        define( 'XBOX_URL', trailingslashit( $this->get_url() ) );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | WP localization
    |---------------------------------------------------------------------------------------------------
     */
    public function localization() {
        $loaded = load_plugin_textdomain( 'xbox', false, trailingslashit( plugin_basename( XBOX_DIR ) ) . 'languages/' );

        if ( ! $loaded ) {
            load_textdomain( 'xbox', XBOX_DIR . 'languages/xbox-' . get_locale() . '.mo' );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Class autoloader
    |---------------------------------------------------------------------------------------------------
     */
    public function class_autoloader() {
        include dirname( __FILE__ ) . '/includes/class-autoloader.php';
        Xbox\Includes\Autoloader::run();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Xbox files
    |---------------------------------------------------------------------------------------------------
     */
    public function includes() {
        include dirname( __FILE__ ) . '/includes/class-xbox.php';
        include dirname( __FILE__ ) . '/includes/class-xbox-items.php';
        include dirname( __FILE__ ) . '/includes/global-functions.php';
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get Xbox Url
    |---------------------------------------------------------------------------------------------------
     */
    private function get_url() {
        $part_dir       = explode( 'wp-content', XBOX_DIR );
        $right_part_dir = end( $part_dir );
        if ( stripos( $right_part_dir, 'themes' ) !== false ) {
            $temp     = explode( 'themes', $right_part_dir );
            $xbox_url = content_url() . '/themes' . $temp[1];
        } else {
            $temp     = explode( 'plugins', $right_part_dir );
            $xbox_url = content_url() . '/plugins' . $temp[1];
        }
        $xbox_url = str_replace( "\\", "/", $xbox_url );
        return $xbox_url;
    }
}
