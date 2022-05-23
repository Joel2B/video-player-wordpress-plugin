<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'cvp-options', 'cvp_options_page' );

function cvp_options_page( $options_table ) {
    $output = '<div id="cvp">
					<div class="content-tabs">';

    $output .= CVP()->display_tabs( false );

    $output .= '<div class="tab-content tab-options">';
    $output .= $options_table;
    $output .= '</div></div></div>';
    return $output;
}

add_action( 'xbox_init', 'cvp_options' );

function cvp_options() {
    $options = array(
        'id'         => 'cvp-options',
        'title'      => esc_html__( 'Plugin Options', 'cvp_lang' ),
        'menu_title' => esc_html__( 'Plugin Options', 'cvp_lang' ),
        'skin'       => 'pink',
        'layout'     => 'boxed',
        'header'     => array(
            'name' => 'Options',
        ),
        'capability' => 'edit_published_posts',
    );

    $xbox = xbox_new_admin_page( $options );

    $xbox->add_main_tab( array(
        'name'  => esc_html__( 'Main tab', 'cvp_lang' ),
        'id'    => 'main-tab',
        'items' => array(
            'player-general'     => '<i class="xbox-icon xbox-icon-gear"></i>' . __( 'General', 'cvp_lang' ),
            'player-transformer' => '<i class="xbox-icon xbox-icon-bolt"></i>' . __( 'Transformer', 'cvp_lang' ),
            'player-advertising' => '<i class="xbox-icon xbox-icon-money"></i>' . __( 'Advertising', 'cvp_lang' ),
        ),
    ) );

    // general
    $xbox->open_tab_item( 'player-general' );
    $xbox->add_field(
        array(
            'id'         => 'main-color',
            'name'       => __( 'Main color', 'cvp_lang' ),
            'type'       => 'colorpicker',
            'default'    => '#FF3565',
            'desc'       => __( 'Choose the color of the progress bar and close ad button.', 'cvp_lang' ),
            'grid'       => '2-of-8',
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'own-thumbnails',
            'name'       => __( 'Poster', 'cvp_lang' ),
            'type'       => 'switcher',
            'default'    => 'off',
            'desc'       => __( 'Use your own thumbnails for the video poster.', 'cvp_lang' ),
            'grid'       => '2-of-8',
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'autoplay',
            'name'    => __( 'Autoplay', 'cvp_lang' ),
            'type'    => 'switcher',
            'default' => 'off',
            'grid'    => '2-of-8',
            'desc'    => __( 'The video will play automatically..', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'loop',
            'name'    => __( 'Loop', 'cvp_lang' ),
            'type'    => 'switcher',
            'default' => 'off',
            'grid'    => '2-of-8',
            'desc'    => __( 'The video will loop automatically..', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'download',
            'name'    => __( 'Download video', 'cvp_lang' ),
            'type'    => 'switcher',
            'default' => 'off',
            'grid'    => '2-of-8',
            'desc'    => __( 'Display a button in the video player to download the video.', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'logo',
            'name'    => __( 'Logo', 'cvp_lang' ),
            'type'    => 'switcher',
            'default' => 'off',
            'grid'    => '2-of-8',
            'desc'    => __( 'Display your logo inside the video player.', 'cvp_lang' ),
        )
    );

    $xbox->open_mixed_field(
        array(
            'id'   => 'displayed-when:switch:logo:on:logo-settings',
            'name' => __( 'Logo settings', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'logo-url',
            'name'       => __( 'Image logo URL', 'cvp_lang' ),
            'type'       => 'file',
            'grid'       => '8-of-8',
            'desc'       => __( 'An image will be displayed in the video player.', 'cvp_lang' ),
            'options'    => array(
                'preview_size' => array(
                    'width'  => '100%',
                    'height' => 'auto',
                ),
            ),
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'logo-click-url',
            'name'       => __( 'Click URL', 'cvp_lang' ),
            'type'       => 'text',
            'grid'       => '6-of-6',
            'default'    => '',
            'desc'       => __( 'Put a link to logo.', 'cvp_lang' ),
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'logo-position',
            'name'    => _x( 'Position', 'Logo position', 'cvp_lang' ),
            'type'    => 'select',
            'default' => 'top-right',
            'items'   => array(
                'top-left'     => 'Top left',
                'top-right'    => 'Top right',
                'bottom-left'  => 'Bottom left',
                'bottom-right' => 'Bottom right',
            ),
            'grid'    => '2-of-8',
            'desc'    => __( 'Set the position of your logo inside the video player.', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'logo-width',
            'name'       => _x( 'Width', 'Logo width', 'cvp_lang' ),
            'type'       => 'number',
            'default'    => 25,
            'attributes' => array(
                'min'       => 1,
                'max'       => 100,
                'step'      => 1,
                'precision' => 0,
            ),
            'options'    => array(
                'unit' => '%',
            ),
            'desc'       => __( 'Set the logo width (1% - 100%).', 'cvp_lang' ),
            'grid'       => '1-of-6',
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'logo-margin',
            'name'       => _x( 'Margin', 'Logo margin', 'cvp_lang' ),
            'type'       => 'number',
            'default'    => 2,
            'attributes' => array(
                'min'       => 1,
                'max'       => 100,
                'step'      => 1,
                'precision' => 0,
            ),
            'options'    => array(
                'unit' => 'px',
            ),
            'desc'       => __( 'Set the logo margin.', 'cvp_lang' ),
            'grid'       => '2-of-8',
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'logo-opacity',
            'name'       => _x( 'Opacity', 'Logo opacity', 'cvp_lang' ),
            'type'       => 'number',
            'default'    => 50,
            'attributes' => array(
                'min'       => 1,
                'max'       => 100,
                'step'      => 1,
                'precision' => 0,
            ),
            'options'    => array(
                'unit' => '%',
            ),
            'desc'       => __( 'Set the logo opacity (1% - 100%).', 'cvp_lang' ),
            'grid'       => '2-of-8',
        )
    );
    $xbox->add_field(
        array(
            'id'      => 'logo-hide-with-controls',
            'name'    => _x( 'Hide with controls', 'Hide logo with controls', 'cvp_lang' ),
            'type'    => 'switcher',
            'default' => 'on',
            'desc'    => __( 'Hide logo with controls.', 'cvp_lang' ),
            'grid'    => '6-of-6',
        )
    );
    $xbox->close_mixed_field();
    $xbox->close_tab_item( 'player-general' );

    // transformer
    $xbox->open_tab_item( 'player-transformer' );
    $xbox->open_mixed_field( array( 'name' => __( 'HTML tags to transform', 'cvp_lang' ) ) );
    $xbox->add_field(
        array(
            'id'      => 'transform-iframe-video-player',
            'name'    => esc_html__( '<iframe> tags', 'cvp_lang' ),
            'type'    => 'switcher',
            'grid'    => '3-of-6 last',
            'default' => 'on',
            'desc'    => esc_html__( 'Video Player will play <iframe> tags', 'cvp_lang' ),
        )
    );
    $xbox->close_mixed_field();

    $xbox->open_mixed_field( array( 'name' => __( 'Transform adult tubes', 'cvp_lang' ) ) );
    $xbox->add_field(
        array(
            'name'    => 'Xvideos',
            'id'      => 'transform-xvideos-player',
            'type'    => 'switcher',
            'grid'    => '3-of-6 last',
            'default' => 'on',
            'desc'    => __( 'Video Player will try to transform Xvideos iframes.', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'name'    => 'Pornhub',
            'id'      => 'transform-pornhub-player',
            'type'    => 'switcher',
            'grid'    => '3-of-6',
            'default' => 'on',
            'desc'    => __( 'Video Player will try to transform Pornhub iframes.', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'name'    => 'Redtube',
            'id'      => 'transform-redtube-player',
            'type'    => 'switcher',
            'grid'    => '3-of-6',
            'default' => 'on',
            'desc'    => __( 'Video Player will try to transform Redtube iframes.', 'cvp_lang' ),
        )
    );
    $xbox->close_mixed_field();
    $xbox->close_tab_item( 'player-transformer' );

    // advertising
    $xbox->open_tab_item( 'player-advertising' );
    $xbox->add_field(
        array(
            'id'         => 'ad-pause-1',
            'name'       => __( 'On pause ad zone 1', 'cvp_lang' ),
            'type'       => 'textarea',
            'grid'       => '2-of-6',
            'default'    => '<a href="#!"><img src="' . CVP_URL . 'admin/assets/img/plugin-options/banner.jpg' . '"></a>',
            'desc'       => '<img src="' . CVP_URL . 'admin/assets/img/plugin-options/inside-player-happy-zone-1-desktop.jpg">',
            'attributes' => array(
                'rows'       => 6,
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'ad-pause-2',
            'name'       => __( 'On pause ad zone 2', 'cvp_lang' ),
            'type'       => 'textarea',
            'grid'       => '2-of-6',
            'default'    => '<a href="#!"><img src="' . CVP_URL . 'admin/assets/img/plugin-options/banner.jpg' . '"></a>',
            'desc'       => '<img src="' . CVP_URL . 'admin/assets/img/plugin-options/inside-player-happy-zone-2-desktop.jpg">',
            'attributes' => array(
                'rows'       => 6,
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->open_mixed_field(
        array(
            'id'   => 'pre-roll-settings',
            'name' => __( 'Pre-roll in-stream ad', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'pre-roll-url',
            'name'       => __( 'URL', 'cvp_lang' ),
            'type'       => 'text',
            'grid'       => '3-of-6',
            'default'    => '',
            'desc'       => __( 'Display an in-stream video advertising at the beginning of the video.', 'cvp_lang' ),
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->close_mixed_field();
    $xbox->open_mixed_field(
        array(
            'id'   => 'mid-roll-settings',
            'name' => __( 'Mid-roll in-stream ad', 'cvp_lang' ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'mid-roll-url',
            'name'       => __( 'URL', 'cvp_lang' ),
            'type'       => 'text',
            'grid'       => '3-of-6',
            'default'    => '',
            'desc'       => __( 'Display an in-stream video advertising at the middle of the video.', 'cvp_lang' ),
            'attributes' => array(
                'spellcheck' => 'false',
            ),
        )
    );
    $xbox->add_field(
        array(
            'id'         => 'mid-roll-timer',
            'name'       => _x( 'Timer', 'Mid-roll in-stream timer', 'cvp_lang' ),
            'type'       => 'number',
            'default'    => 50,
            'attributes' => array(
                'min'       => 1,
                'max'       => 99,
                'step'      => 1,
                'precision' => 0,
            ),
            'options'    => array(
                'unit' => '%',
            ),
            'desc'       => __( 'Choose when in the video you want to display the mid-roll ad (1% - 99%).', 'cvp_lang' ),
            'grid'       => '2-of-8 last',
        )
    );
    $xbox->close_mixed_field();
    $xbox->close_tab_item( 'player-advertising' );
    $xbox->close_tab( 'main-tab' );
}
