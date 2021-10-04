<?php

try {
    include CVP_DIR . 'public/classes/class-utils.php';

    $wpload_uri = Utils::get_wpload_uri();
    if ( ! file_exists( $wpload_uri ) ) {
        throw new Exception( 'wp-load.php not found' );
    }
    require_once $wpload_uri;

    $data = sanitize_text_field( wp_unslash( $_GET['cvp_data'] ) );
    $data = ( new Encryption() )->decrypt( $data );
    parse_str( $data, $params );

    if ( ! isset( $params['tag'] ) ) {
        die();
    }
    if ( ! isset( $params['post_id'] ) ) {
        $params['post_id'] = 0;
    }

    $dom_tag = urldecode( $params['tag'] );
    $post_id = intval( $params['post_id'] );

    $player  = new Player( $dom_tag, $post_id );
    $related = new Related( true );

    $player->render_player();
    $related->render_view();
} catch ( Exception $exception ) {
    CVP()->write_log( 'error', $exception->getMessage() . ' <code>' . $exception->getCode() . '</code>', $exception->getFile(), $exception->getLine() );
    die();
}
