<?php namespace Xbox\Includes;

class Ajax {

    public function __construct() {
        //Ajax oembed
        add_action( 'wp_ajax_xbox_get_oembed', array( $this, 'get_oembed_ajax' ) );
        add_action( 'wp_ajax_nopriv_xbox_get_oembed', array( $this, 'get_oembed_ajax' ) );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get oembed ajax
    |---------------------------------------------------------------------------------------------------
     */
    public function get_oembed_ajax() {
        if ( ! isset( $_POST['ajax_nonce'] ) || ! isset( $_POST['oembed_url'] ) ) {
            die();
        }
        if ( ! wp_verify_nonce( $_POST['ajax_nonce'], 'xbox_ajax_nonce' ) ) {
            die();
        }

        $oembed_url   = $_POST['oembed_url'];
        $preview_size = isset( $_POST['preview_size'] ) ? json_decode( json_encode( $_POST['preview_size'] ), true ) : array();
        $oembed       = Functions::get_oembed( $oembed_url, $preview_size );
        wp_send_json( $oembed );
    }
}
