<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Dom manipulation class.
 */
class Dom {
    /**
     * Prepend loader block add transform-iframe class to iframes.
     *
     * @param string $content The page content passed to the function.
     *
     * @return string The HTML result
     */
    public function prepare_iframes( $content ) {
        if ( 'off' === xbox_get_field_value( 'cvp-options', 'transform-iframe-video-player' ) ) {
            return $content;
        }

        global $post;
        $post_id = is_single() ? intval( $post->ID ) : 0;
        preg_match_all( '/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/', $content, $iframes, PREG_SET_ORDER );
        foreach ( $iframes as $iframe ) {
            if ( empty( $iframe ) ) {
                continue;
            }
            $iframe         = $iframe[0];
            $iframe_classes = trim( Utils::match( 'class="(.*)"', $iframe ) );
            $iframe_src     = trim( Utils::match( 'src="(.*)"', $iframe ) );
            // Bypass <video> tags in themes
            if ( false !== strpos( $iframe_classes, 'wp' . 'st-trailer' ) ) {
                continue;
            }
            // Bypass iframes that must not be rendered
            $site_id = $this->is_compatible_source_url( $iframe_src );
            if ( ! $site_id ) {
                continue;
            }
            if ( ! $this->is_transformable( $site_id ) ) {
                continue;
            }

            $iframe_tag = rawurlencode( $iframe );
            $new_iframe = $this->render_iframe( $iframe_tag, $post_id );
            $content    = str_replace( $iframe, $new_iframe, $content );
        }
        return $content;
    }

    /**
     * Check if a given url is included in the compatible sources array.
     *
     * @param string $url The url to test.
     *
     * @return string|bool string source name if found, bool false if not
     */
    private function is_compatible_source_url( $url ) {
        foreach ( $this->get_transformable_sources() as $source ) {
            if ( false !== strpos( $url, $source ) ) {
                return $source;
            }
        }
        return false;
    }

    /**
     * Get all transformable iframe sources
     *
     * @return array List of transformable iframe sources
     */
    public function get_transformable_sources() {
        return array(
            'xvideos',
            'pornhub',
            'redtube',
        );
    }

    /**
     * Find if the given tag comes from a tube and is transformable.
     *
     * @return bool True if it is transformable, false if not.
     */
    public function is_transformable( $site_id ) {
        return 'on' === xbox_get_field_value( 'cvp-options', "transform-$site_id-player", 'off' );
    }

    /**
     * Render Iframe.
     *
     * @param string $tag  The html tag of the media.
     * @param int    $post_id The post id where the media has been found. 0 if no post.
     *
     * @return string The iframe tag to render in the page.
     */
    public function render_iframe( $tag, $post_id ) {
        $data = [
            'tag'     => $tag,
            'post_id' => $post_id,
        ];
        $query  = ( new Encryption() )->encrypt( http_build_query( $data ) );
        $url    = home_url( "/?cvp_data=$query" );
        $iframe = '<iframe src="' . $url . '" frameborder="0" scrolling="no" allow="clipboard-write" allowfullscreen style="width: 100%; height: 100%;"></iframe>';
        return $iframe;
    }
}
