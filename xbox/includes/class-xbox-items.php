<?php
use Xbox\Includes\Functions as Functions;

class XboxItems {
    private static $instance = null;

    /*
    |---------------------------------------------------------------------------------------------------
    | Lista de términos de taxonomias
    |---------------------------------------------------------------------------------------------------
     */
    public static function terms( $taxonomy = '', $args = array(), $more_items = array() ) {
        $args = wp_parse_args( $args, array(
            'hide_empty' => false,
        ) );
        $terms = get_terms( $taxonomy, $args );
        if ( is_wp_error( $terms ) ) {
            return array();
        }
        $items = array();
        foreach ( $terms as $term ) {
            $items[$term->slug] = $term->name;
        }
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Lista de tipos de post
    |---------------------------------------------------------------------------------------------------
     */
    public static function post_types( $args = array(), $operator = 'and', $more_items = array() ) {
        $post_types = get_post_types( $args, 'objects', $operator );
        $items      = array();
        foreach ( $post_types as $post_type ) {
            $items[$post_type->name] = $post_type->label;
        }
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Lista de posts de un tipos de post
    |---------------------------------------------------------------------------------------------------
     */
    public static function posts_by_post_type( $post_type = 'post', $args = array(), $more_items = array() ) {
        $args = wp_parse_args( $args, array(
            'post_type'      => $post_type,
            'posts_per_page' => 5,
        ) );
        $posts = get_posts( $args );
        $items = array();
        foreach ( $posts as $post ) {
            $items[$post->ID] = $post->post_title;
        }
        return Functions::nice_array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Google fonts
    |---------------------------------------------------------------------------------------------------
     */
    public static function google_fonts( $more_items = array() ) {
        $google_fonts = include XBOX_DIR . 'includes/data/google-fonts.php';
        $items        = array();
        foreach ( $google_fonts as $font ) {
            $items[$font['family']] = $font['family'];
        }
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Web safe fonts
    |---------------------------------------------------------------------------------------------------
     */
    public static function web_safe_fonts( $more_items = array() ) {
        $web_safe_fonts = include XBOX_DIR . 'includes/data/web-safe-fonts.php';
        $items          = array();
        foreach ( $web_safe_fonts as $font ) {
            $items[$font] = $font;
        }
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Border style
    |---------------------------------------------------------------------------------------------------
     */
    public static function border_style( $more_items = array() ) {
        $items = array(
            'solid'  => 'Solid',
            'none'   => 'None',
            'dotted' => 'Dotted',
            'dashed' => 'Dashed',
            'double' => 'Double',
            'groove' => 'Groove',
            'ridge'  => 'Ridge',
            'inset'  => 'Inset',
            'outset' => 'Outset',
            'hidden' => 'Hidden',
        );
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Opacity
    |---------------------------------------------------------------------------------------------------
     */
    public static function opacity( $more_items = array() ) {
        $items = array(
            '1.0' => '1',
            '0.9' => '0.9',
            '0.8' => '0.8',
            '0.7' => '0.7',
            '0.6' => '0.6',
            '0.5' => '0.5',
            '0.4' => '0.4',
            '0.3' => '0.3',
            '0.2' => '0.2',
            '0.1' => '0.1',
            '0'   => '0',
        );
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Text align
    |---------------------------------------------------------------------------------------------------
     */
    public static function text_align( $more_items = array() ) {
        $items = array(
            'left'    => 'Left',
            'right'   => 'Right',
            'center'  => 'Center',
            'justify' => 'Justify',
            'initial' => 'Initial',
            'inherit' => 'Inherit',
        );
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Countries
    |---------------------------------------------------------------------------------------------------
     */
    public static function countries( $more_items = array() ) {
        $countries = include XBOX_DIR . 'includes/data/countries.php';
        $items     = array();
        foreach ( $countries as $country ) {
            $value  = $country['value'];
            $option = $country['option'];
            if ( isset( $country['icon'] ) ) {
                $icon   = $country['icon'];
                $option = "<i class='{$icon}'></i>" . $option;
            }
            $items[$value] = $option;
        }
        return array_merge( $more_items, $items );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Font Awesome Icons
    |---------------------------------------------------------------------------------------------------
     */
    public static function icons( $more_items = array() ) {
        $icons = include XBOX_DIR . 'includes/data/icons.php';
        $items = array();
        foreach ( $icons as $icon ) {
            $items[$icon] = "<i class='xbox-icon xbox-icon-{$icon}'></i>$icon";
        }
        return array_merge( $more_items, $items );
    }
}
