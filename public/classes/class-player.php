<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Player {

    private $player;
    private $video;

    public function __construct( $dom_tag, $post_id ) {
        $this->video = new Video( $dom_tag, $post_id );

        $this->player = [
            'id'      => $this->video::VIDEO_TAG_ID,
            'options' => [
                'primaryColor'  => esc_html( xbox_get_field_value( 'cvp-options', 'main-color' ) ),
                'autoPlay'      => 'on' === esc_html( xbox_get_field_value( 'cvp-options', 'autoplay' ) ),
                'loop'          => 'on' === esc_html( xbox_get_field_value( 'cvp-options', 'loop' ) ),
                'allowDownload' => 'on' === esc_html( xbox_get_field_value( 'cvp-options', 'download' ) ),
                'vastTimeout'   => 10000,
                'thumbnails'    => $this->video->data->thumbnails,
            ],
        ];

        $this->get_ad_list();
        $this->get_html_on_pause();
        $this->get_logo();
    }

    public function get_ad_list() {
        $adList  = &$this->player['options']['adList'];
        $preRoll = trim( xbox_get_field_value( 'cvp-options', 'pre-roll-url' ) );
        if ( $preRoll ) {
            $adList[] = [
                'roll'    => 'preRoll',
                'vastTag' => $preRoll,
            ];
        }

        $midRoll = trim( xbox_get_field_value( 'cvp-options', 'mid-roll-url' ) );
        if ( $midRoll ) {
            $adList[] = [
                'roll'    => 'midRoll',
                'vastTag' => $midRoll,
                'timer'   => esc_html( xbox_get_field_value( 'cvp-options', 'mid-roll-timer' ) ) . '%',
            ];
        }
    }

    public function get_html_on_pause() {
        $htmlOnPauseBlock = &$this->player['options']['htmlOnPauseBlock'];
        $htmlOnPauseBlock = xbox_get_field_value( 'cvp-options', 'ad-pause-1' );
        $htmlOnPauseBlock .= xbox_get_field_value( 'cvp-options', 'ad-pause-2' );
        $htmlOnPauseBlock = str_replace( '"', '\"', $htmlOnPauseBlock );
    }

    public function get_logo() {
        if ( 'on' === xbox_get_field_value( 'cvp-options', 'logo' ) ) {
            $this->player['options']['logo'] = [
                'imageUrl'         => xbox_get_field_value( 'cvp-options', 'logo-url' ),
                'position'         => str_replace( '-', ' ', xbox_get_field_value( 'cvp-options', 'logo-position' ) ),
                'clickUrl'         => xbox_get_field_value( 'cvp-options', 'logo-click-url' ),
                'opacity'          => intval( xbox_get_field_value( 'cvp-options', 'logo-opacity' ) ) / 100,
                'imageMargin'      => xbox_get_field_value( 'cvp-options', 'logo-margin' ) . 'px',
                'width'            => xbox_get_field_value( 'cvp-options', 'logo-width' ) . '%',
                'hideWithControls' => 'on' === xbox_get_field_value( 'cvp-options', 'logo-hide-with-controls' ),
            ];
        }
    }

    public function render_player() {
        echo $this->video->create_video();
    }

    public function get_config() {
        return json_encode( $this->player );
    }
}
