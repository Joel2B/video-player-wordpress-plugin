<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Video {

    public const VIDEO_TAG_ID = 'video-player';
    private $video_tag;
    private $post_id;
    private $link;
    public $data;
    private $dom;

    public function __construct( $iframe_tag, $post_id = 0 ) {
        libxml_use_internal_errors( true );
        $this->dom = new DOMDocument();
        $this->dom->loadHTML( $iframe_tag );
        $iframe_tag = $this->dom->getElementsByTagName( 'iframe' )->item( 0 );
        libxml_clear_errors();

        $this->post_id = intval( $post_id );
        $this->link    = $this->get_src_attr( $iframe_tag );

        $this->get_data_from_api();
        if ( is_null( $this->data ) || ! $this->is_video_available() ) {
            ob_get_clean();
            include CVP_DIR . 'public/views/error.php';
            die();
        }
    }

    public function get_data_from_api() {
        $url      = CVP_VIDEO_API_URL . "/?data={$this->link}";
        $response = wp_remote_get( $url );

        if ( ! is_array( $response ) && is_wp_error( $response ) ) {
            return;
        }

        $body       = wp_remote_retrieve_body( $response );
        $this->data = json_decode( $body );
    }

    /**
     * Get <iframe> tag string.
     *
     * @return string The iframe tag as a string.
     */
    public function get_video_tag() {
        return $this->dom->saveHTML( $this->video_tag );
    }

    public function get_src_attr( $elem ) {
        return $elem->getAttribute( 'src' );
    }

    public function set_video_sources() {
        $is_master_playlist = false;
        $use_hls            = false;

        foreach ( $this->data->hls as $key => $source ) {
            if ( empty( $source ) || $is_master_playlist ) {
                continue;
            }
            if ( $key == 'all' ) {
                $is_master_playlist = true;
            }
            $this->create_source( $source, 'application/x-mpegURL', $key );
            $use_hls = true;
        }

        if ( ! $use_hls ) {
            foreach ( $this->data->mp4 as $key => $source ) {
                if ( empty( $source ) ) {
                    continue;
                }
                $this->create_source( $source, 'video/mp4', $key );
            }
        }
    }

    public function is_video_available() {
        $available = false;
        foreach ( $this->data->hls as $source ) {
            if ( ! empty( $source ) ) {
                $available = true;
                break;
            }
        }
        foreach ( $this->data->mp4 as $source ) {
            if ( ! empty( $source ) ) {
                $available = true;
                break;
            }
        }
        return $available;
    }

    public function create_source( $source, $type, $title ) {
        $domSource = $this->dom->createElement( 'source' );
        $domSource->setAttribute( 'src', $source );
        $domSource->setAttribute( 'type', $type );
        $domSource->setAttribute( 'title', $title );
        $this->video_tag->appendChild( $domSource );
    }

    public function create_video() {
        $this->video_tag = $this->dom->createElement( 'video' );
        $this->video_tag->setAttribute( 'id', self::VIDEO_TAG_ID );
        $this->video_tag->setAttribute( 'poster', $this->get_poster() );

        $this->set_video_sources();

        return $this->get_video_tag();
    }

    public function get_poster() {
        $poster = $this->data->thumb;
        if ( 'on' === xbox_get_field_value( 'cvp-options', 'own-thumbnails' ) ) {
            $poster = Utils::get_post_thumbnail( $this->post_id );
        }
        return $poster;
    }
}
