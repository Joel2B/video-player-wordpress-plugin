<?php namespace Xbox\Includes;

class Autoloader {
    private $_namespace_separator          = '\\';
    private $_file_extension               = '.php';
    private $_start_with                   = 'class-';
    private $_file                         = null;
    private static $_plugin_base_namespace = 'Xbox';

    /*
    |---------------------------------------------------------------------------------------------------
    | Run loader with SPL autoloader
    |---------------------------------------------------------------------------------------------------
     */
    public static function run() {
        spl_autoload_register( array( new self(), 'load_class' ) );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Load class file
    |---------------------------------------------------------------------------------------------------
     */
    private function load_class( $class ) {
        $full_path = plugin_dir_path( dirname( __FILE__ ) );
        $class     = trim( $class, $this->_namespace_separator );

        // If the requested class is not our plugin
        if ( false === strpos( $class, self::$_plugin_base_namespace ) ) {
            return;
        }

        $pos_end_slash = strripos( $class, $this->_namespace_separator );

        if ( false === $pos_end_slash ) {
            $class_name = $this->camelcase_to_underscore( $class );
        } else {
            $paths      = explode( $this->_namespace_separator, $class );
            $class_name = $this->camelcase_to_underscore( end( $paths ) );
            array_shift( $paths ); // Removing plugin base namespace
            array_pop( $paths ); //Removing the class name from paths

            foreach ( $paths as $path ) {
                $full_path .= $this->camelcase_to_underscore( $path ) . DIRECTORY_SEPARATOR;
            }
        }

        $this->file = $full_path . $this->_start_with . $class_name . $this->_file_extension;

        if ( file_exists( $this->file ) ) {
            require_once $this->file;
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Change CamelCase to camel-case
    |---------------------------------------------------------------------------------------------------
     */
    private function camelcase_to_underscore( $camelCase ) {
        preg_match_all( '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $camelCase, $matches );
        $ret = $matches[0];
        foreach ( $ret as &$match ) {
            $match = $match == strtoupper( $match ) ? strtolower( $match ) : lcfirst( $match );
        }
        return implode( '-', $ret );
    }
}
