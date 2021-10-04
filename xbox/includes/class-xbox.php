<?php

use Xbox\Includes\Ajax as Ajax;
use Xbox\Includes\AssetsLoader as AssetsLoader;
use Xbox\Includes\Functions as Functions;
use Xbox\Includes\XboxCore as XboxCore;

class Xbox {
    public $version;
    private static $instance = null;
    private static $xboxs    = array();

    private function __construct( $version = '1.0.0' ) {
        $this->version = $version;
        add_action( 'current_screen', array( $this, 'load_assets' ) );
        $this->ajax();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Singleton
    |---------------------------------------------------------------------------------------------------
     */
    //Stopping Clonning of Object
    private function __clone() {}

    //Stopping unserialize of object
    public function __wakeup() {}

    public static function init( $version = '1.0.0' ) {
        if ( null === self::$instance ) {
            self::$instance = new self( $version );
        }
        return self::$instance;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Carga de scripts y estilos.
    |---------------------------------------------------------------------------------------------------
     */
    public function load_assets() {
        $load_scripts = false;
        $screen       = get_current_screen();

        foreach ( self::$xboxs as $xbox ) {
            if ( is_a( $xbox, 'Xbox\Includes\Metabox' ) ) {
                if ( in_array( $screen->post_type, (array) $xbox->arg( 'post_types' ) ) ) {
                    $load_scripts = true;
                }
            } else {
                if ( false !== stripos( $screen->id, $xbox->id ) ) {
                    $load_scripts = true;
                }
            }
        }
        //Los scripts también se incluyen en la lista de cada post_type, para futuras características

        if ( $load_scripts ) {
            new AssetsLoader( $this->version );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Ajax
    |---------------------------------------------------------------------------------------------------
     */
    public function ajax() {
        new Ajax();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Crear un Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public static function new_xbox( $options = array() ) {
        if ( empty( $options['id'] ) ) {
            return false;
        }

        $xbox = self::get( $options['id'] );
        if ( $xbox ) {
            return $xbox;
        }
        return new XboxCore( $options );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene una instancia de Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public static function get( $xbox_id ) {
        $xbox_id = trim( $xbox_id );
        if ( empty( $xbox_id ) ) {
            return false;
        }

        if ( Functions::is_empty( self::$xboxs ) || ! isset( self::$xboxs[$xbox_id] ) ) {
            return false;
        }

        return self::$xboxs[$xbox_id];
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene todos los xbox creados
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_all_xboxs() {
        return self::$xboxs;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega una instancia de Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public static function add( $xbox ) {
        if ( is_a( $xbox, 'Xbox\Includes\XboxCore' ) ) {
            self::$xboxs[$xbox->get_id()] = $xbox;
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Elimina una instancia de Xbox
    |---------------------------------------------------------------------------------------------------
     */
    public static function remove_xbox( $id ) {
        if ( isset( self::$xboxs[$id] ) ) {
            unset( self::$xboxs[$id] );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Retorna el valor de una opción
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_field_value( $xbox_id, $field_id = '', $default = '', $post_id = '' ) {
        $value = '';
        $xbox  = self::get( $xbox_id );
        if ( ! $xbox ) {
            return false;
        }
        switch ( $xbox->get_object_type() ) {
            case 'metabox':
                $value = $xbox->get_field_value( $field_id, $post_id, $default );
                break;

            case 'admin-page':
                $value = $xbox->get_field_value( $field_id, $default );
                break;
        }
        if ( Functions::is_empty( $value ) ) {
            return $default;
        }
        return $value;
    }
}
