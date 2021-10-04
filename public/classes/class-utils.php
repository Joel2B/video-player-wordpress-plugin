<?php

// Exit if accessed directly.
if (count(get_included_files()) == 1) {
    die();
}

if ( ! class_exists( 'Utils' ) ) {
    class Utils {
        /**
         * Try to get wp-load.php file uri.
         *
         * @return string The wp-load.php file uri, empty string if not found.
         */
        public static function get_wpload_uri() {
            if ( isset( $_SERVER['SCRIPT_FILENAME'] ) ) {
                $parse_uri = explode( '/plugins', str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ) );
                if ( file_exists( dirname( $parse_uri[0] ) . '/wp-load.php' ) ) {
                    return dirname( $parse_uri[0] ) . '/wp-load.php';
                }
            }

            if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
                $root_paths = array( $_SERVER['DOCUMENT_ROOT'], '/wordpress', '/wp' );
                foreach ( $root_paths as $root_path ) {
                    $it = new RecursiveDirectoryIterator( $root_path );
                    foreach ( new RecursiveIteratorIterator( $it ) as $file_path ) {
                        if ( false !== strpos( $file_path, 'wp-load.php' ) ) {
                            return $file_path;
                        }
                    }
                }
            }
            return '';
        }

        public static function match( $regex, $content ) {
            preg_match( '#' . $regex . '#', $content, $match );
            return $match[1] ?? null;
        }

        public static function get_post_thumbnail( $post_id ) {
            if ( has_post_thumbnail( $post_id ) ) {
                return get_the_post_thumbnail_url( $post_id );
            }
            if ( get_post_meta( $post_id, 'thumb', true ) ) {
                return get_post_meta( $post_id, 'thumb', true );
            }
            return CVP_URL . 'public/assets/images/no-img.png';
        }
    }
}
