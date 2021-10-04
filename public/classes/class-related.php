<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Related {

    public $id;
    public $title;
    public $extra_info;
    public $posts;

    public function __construct( $extra_info, $id = '', $title = '' ) {
        $this->id         = $id;
        $this->title      = $title;
        $this->extra_info = $extra_info;
        if ( empty( $id ) ) {
            $this->id = 'related-videos';
        }
        if ( empty( $title ) ) {
            $this->title = __( 'Related videos', 'cvp_lang' );
        }
        $this->get_posts();
    }

    public function get_posts() {
        // TODO: don't save the current post
        query_posts( 'showposts=12&orderby=rand' );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $this->posts[] = [
                    'thumbnail' => Utils::get_post_thumbnail( get_the_ID() ),
                    'url'       => get_permalink(),
                    'title'     => get_the_title(),
                ];
            }
        }
        wp_reset_query();
    }

    public function add_extra_info( $post ) {
        $copy_btn = __( 'Copy', 'cvp_lang' );

        $whatsapp_url = 'https://wa.me/?text=' . urlencode( "{$post['title']} - {$post['url']}" );

        $view = "
            <div class=\"url-content\">
                <div class=\"url\">
                    <span>{$post['url']}</span>
                </div>
                <button class=\"copy\">$copy_btn</button>
            </div>
            <div class=\"social-network\">
                <span onclick=\"window.open('$whatsapp_url')\" class=\"whatsapp\"></span>
            </div>
        ";
        return $view;
    }

    public function render_view() {
        if ( ! empty( $this->id ) ) {
            $this->id = "id=\"{$this->id}\"";
        }
        $view = "<div {$this->id} class=\"related\">";
        $view .= "<h1 class=\"title\">{$this->title}</h1>";
        foreach ( $this->posts as $post ) {
            $view .= "
                <div class=\"thumbnail-content\">
                    <a target=\"_blank\" rel=\"nofollow noopener noreferrer\" href=\"{$post['url']}\">
                        <div class=\"thumbnail-hover\">
                            <span>{$post['title']}</span>
                        </div>
                        <img src=\"{$post['thumbnail']}\" alt=\"{$post['title']}\">
                    </a>
                </div>
            ";
        }
        if ( $this->extra_info ) {
            $view .= $this->add_extra_info( $post );
        }
        $view .= '</div>';
        echo $view;
    }
}
