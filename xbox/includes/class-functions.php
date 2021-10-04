<?php namespace Xbox\Includes;

class Functions {
    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si un string empieza con un caracter específico
    |---------------------------------------------------------------------------------------------------
     */
    public static function starts_with( $needle, $haystack, $case_sensitive = false ) {
        if ( strlen( $needle ) == 0 || strlen( $haystack ) == 0 ) {
            return false;
        }
        return substr_compare( $haystack, $needle, 0, strlen( $needle ), ! $case_sensitive ) === 0;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si un string termina con un caracter específico
    |---------------------------------------------------------------------------------------------------
     */
    public static function ends_with( $needle, $haystack, $case_sensitive = false ) {
        $offset = strlen( $haystack ) - strlen( $needle );
        if ( strlen( $needle ) == 0 || strlen( $haystack ) == 0 || $offset >= strlen( $haystack ) ) {
            return false;
        }
        return substr_compare( $haystack, $needle, $offset, strlen( $needle ), ! $case_sensitive ) === 0;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Ordena un array
    |---------------------------------------------------------------------------------------------------
     */
    public static function sort( &$array = array(), $sort = 'asc' ) {
        if ( strtolower( $sort ) == 'asc' ) {
            ksort( $array );
        } elseif ( strtolower( $sort ) == 'desc' ) {
            krsort( $array );
        }
        return $array;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene un valor de un array desde una ruta
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_array_value_by_path( $path, $array ) {
        preg_match_all( "/\[['\"]*([a-z0-9_-]+)['\"]*\]/i", $path, $matches );

        if ( count( $matches[1] ) > 0 ) {
            foreach ( $matches[1] as $key ) {
                if ( isset( $array[$key] ) ) {
                    $array = $array[$key];
                } else {
                    return false;
                }
            }
            return $array;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si una variable está vacía
    |---------------------------------------------------------------------------------------------------
     */
    public static function is_empty( $value = '' ) {
        if ( is_array( $value ) ) {
            $value = array_filter( $value );
            if ( empty( $value ) ) {
                return true;
            }
            return false;
        } else if ( is_numeric( $value ) ) {
            return false;
        } else if ( empty( $value ) ) {
            return true;
        } else {
            return false;
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Filtra un array eliminando todo lo igual a false pero conserva el número 0
    |---------------------------------------------------------------------------------------------------
     */
    public static function array_filter( $array = array() ) {
        if ( ! is_array( $array ) ) {
            return array();
        }
        return array_filter( $array, function ( $val ) {
            return ( $val || is_numeric( $val ) );
        } );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Random string
    |---------------------------------------------------------------------------------------------------
     */
    public static function random_string( $length = 10, $numbers = true ) {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = $numbers ? $str . '0123456789' : $str;
        return substr( str_shuffle( $str ), 0, $length );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Une dos array, permite excluir keys y unir valores del mismo key
    |---------------------------------------------------------------------------------------------------
     */
    public static function nice_array_merge( $attrs = array(), $new_attrs = array(), $exclude_keys = array(), $join_keys = array() ) {
        $join_array_keys = isset( $join_keys[0] ) ? $join_keys : array_keys( $join_keys );

        foreach ( $new_attrs as $key => $val ) {
            if ( in_array( $key, $exclude_keys ) ) {
                continue;
            }
            if ( isset( $attrs[$key] ) && in_array( $key, $join_array_keys ) ) {
                $separator   = isset( $join_keys[0] ) ? ' ' : $join_keys[$key];
                $attrs[$key] = $attrs[$key] . $separator . $val;
            } else {
                $attrs[$key] = $val;
            }
        }
        return $attrs;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si estamos en una página opciones. Solo funciona con páginas dentro de admin.php?page=*
    |---------------------------------------------------------------------------------------------------
     */
    public static function is_admin_page() {
        global $pagenow;
        if ( ! is_admin() ) {
            return false;
        }
        return $pagenow == 'admin.php';
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si estamos en una página opciones
    |---------------------------------------------------------------------------------------------------
     */
    public static function is_post_page( $page = '' ) {
        global $pagenow;
        if ( ! is_admin() ) {
            return false;
        }
        if ( $page == 'edit' ) {
            return in_array( $pagenow, array( 'post.php' ) );
        } elseif ( $page == 'new' ) {
            return in_array( $pagenow, array( 'post-new.php' ) );
        }
        return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene id de un campo desde su name
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_id_attribute_by_name( $name = '' ) {
        if ( empty( $name ) ) {
            return '';
        }
        $id    = '';
        $array = explode( '[', $name );
        foreach ( $array as $key => $value ) {
            $new_value = str_replace( ']', '', $value );
            if ( $new_value != '' ) {
                if ( is_numeric( $new_value ) ) {
                    $id .= "__{$new_value}__";
                } else {
                    $id .= $new_value;
                }
            }
        }
        return $id;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Elimina espacios en blanco en un texto y lo convierte a minuscula
    |---------------------------------------------------------------------------------------------------
     */
    public static function str_trim_to_lower( $string, $replace = '-' ) {
        $string = strtolower( $string );
        $string = preg_replace( '/[_]+/', '_', $string );
        $string = preg_replace( '/[\s-]+/', $replace, $string );
        return $string;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene la extensión de un archivo
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_file_extension( $file_path = '' ) {
        $file_path = strtolower( $file_path );
        $file_path = parse_url( $file_path, PHP_URL_PATH );
        return pathinfo( $file_path, PATHINFO_EXTENSION );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el id de un archivo adjunto a través de una url
    |---------------------------------------------------------------------------------------------------
    | https://wpscholar.com/blog/get-attachment-id-from-wp-image-url/
     */
    public static function get_attachment_id_by_url( $url ) {
        $attachment_id = 0;
        $dir           = wp_upload_dir();
        if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
            $file       = basename( $url );
            $query_args = array(
                'post_type'   => 'attachment',
                'post_status' => 'inherit',
                'fields'      => 'ids',
                'meta_query'  => array(
                    array(
                        'value'   => $file,
                        'compare' => 'LIKE',
                        'key'     => '_wp_attachment_metadata',
                    ),
                ),
            );
            $query = new \WP_Query( $query_args );
            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $meta                = wp_get_attachment_metadata( $post_id );
                    $original_file       = basename( $meta['file'] );
                    $cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
                    if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
                        $attachment_id = $post_id;
                        break;
                    }
                }
            }
        }
        return $attachment_id;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el formato de un color. Devuelve 'hex' o 'rgb' o 'rgba' o false si $color es vacío
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_format_color( $color = '' ) {
        $color = str_replace( ' ', '', $color );
        if ( empty( $color ) ) {
            return false;
        }
        if ( preg_match( "/(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i", $color ) ) {
            return 'hex';
        }
        if ( preg_match( "/^rgb\((\d{1,3}),\s?(\d{1,3}),\s?(\d{1,3})\)$/i", $color ) ) {
            return 'rgb';
        }
        if ( preg_match( "/^rgba\((\d{1,3}),\s?(\d{1,3}),\s?(\d{1,3}),\s?(1|0|0?\.\d+)\)$/i", $color ) ) {
            return 'rgba';
        }
        //Si necesita encontrar (o no) uno (o varios) valores de color HEX / RGB (A) / HSL (A)
        //$colors = preg_match("/(#(?:[\da-f]{3}){1,2}|rgb\((?:\d{1,3},\s*){2}\d{1,3}\)|rgba\((?:\d{1,3},\s*){3}\d*\.?\d+\)|hsl\(\d{1,3}(?:,\s*\d{1,3}%){2}\)|hsla\(\d{1,3}(?:,\s*\d{1,3}%){2},\s*\d*\.?\d+\))/gi", "string");
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | RGB color to Hexadecimal, Input: 255,255,255,0.2 or rgb|rgba(255,255,255,0.2) Output: #FFFFFF
    |---------------------------------------------------------------------------------------------------
     */
    public static function rgb_to_hex( $rgb, $default = '' ) {
        if ( empty( $rgb ) ) {
            return $default;
        }

        $rgb = str_replace( array( ' ', 'rgba', 'rgb', '(', ')' ), '', $rgb );

        if ( preg_match( "/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $rgb ) ) {
            $rgb    = str_replace( array( ',', '.' ), ':', $rgb );
            $rgbarr = explode( ':', $rgb );
            $result = '#';
            $result .= str_pad( dechex( $rgbarr[0] ), 2, '0', STR_PAD_LEFT );
            $result .= str_pad( dechex( $rgbarr[1] ), 2, '0', STR_PAD_LEFT );
            $result .= str_pad( dechex( $rgbarr[2] ), 2, '0', STR_PAD_LEFT );
            $result = strtoupper( $result );
            return $result;
        } else {
            return $default;
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Hexadecimal color to RGB, Input: #FFFFFF  Output: rgb(255,255,255) or rgba(255,255,255, 0.5)
    |---------------------------------------------------------------------------------------------------
     */
    public static function hex_to_rgb( $color, $opacity = false, $default = '' ) {
        if ( empty( $color ) ) {
            return $default;
        }

        $color = str_replace( ' ', '', $color );
        $color = str_replace( '#', '', $color );

        if ( strlen( $color ) == 6 ) {
            $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
            $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
            return $default;
        }

        $rgb = array_map( 'hexdec', $hex );

        if ( $opacity !== false && is_numeric( $opacity ) ) {
            if ( abs( $opacity ) > 1 ) {
                $opacity = 1.0;
            } elseif ( $opacity < 0 ) {
                $opacity = 0;
            }
            return 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
        }
        return 'rgb(' . implode( ',', $rgb ) . ')';
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get oembed
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_oembed( $oembed_url = '', $preview_size = array(), $default_height = 260 ) {
        global $post, $wp_embed;
        $return             = array();
        $return['success']  = false;
        $return['oembed']   = '';
        $return['message']  = '';
        $return['provider'] = '';

        if ( self::is_empty( $preview_size ) ) {
            $return['HOLI'] = '100';
            $preview_size   = array( 'width' => '100%', 'height' => $default_height );
        }
        $oembed_url  = esc_url( $oembed_url );
        $width       = (int) $preview_size['width'];
        $height      = ( $preview_size['height'] == 'auto' ) ? $default_height : (int) $preview_size['height'];
        $oembed_args = "width='$width' height='$height'";
        $oembed_args = array( 'width' => $width, 'height' => $height );

        if ( ! empty( $oembed_url ) ) {
            $check_oembed = wp_oembed_get( $oembed_url, $preview_size );
            $maybe_link   = $wp_embed->maybe_make_link( $oembed_url );
            if ( $check_oembed && $check_oembed != $maybe_link ) {
                $return['success']  = true;
                $return['oembed']   = $check_oembed;
                $return['provider'] = strtolower( self::get_oembed_provider( $oembed_url ) );
            } else {
                $return['message'] = "<span class='xbox-preview-error'>" . sprintf( __( "No oEmbed results found for %s. See", 'xbox' ), $maybe_link ) . " <a href='http://codex.wordpress.org/Embeds' target='_blank'>Wordpress Embeds</a></span>";
            }
        }
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get oembed data
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_oembed_data( $oembed_url ) {
        require_once ABSPATH . WPINC . '/class-oembed.php';
        $oembed   = _wp_oembed_get_object();
        $provider = $oembed->discover( $oembed_url );
        $data     = $oembed->fetch( $provider, $oembed_url );

        if ( isset( $data ) && $data != false ) {
            return $data;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get oembed provider
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_oembed_provider( $oembed_url ) {
        $oembed_data = self::get_oembed_data( $oembed_url );
        if ( $oembed_data && isset( $oembed_data->provider_name ) ) {
            return $oembed_data->provider_name;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Get field value by name attribute
    |---------------------------------------------------------------------------------------------------
     */
    public static function get_field_value_by_name( $name_attr = '', $group_id = '', $post_id = '' ) {
        global $post;
        if ( empty( $name_attr ) || empty( $group_id ) || empty( $post_id ) ) {
            return '';
        }

        $group_value = get_metadata( 'post', $post_id, $group_id, true );

        $value = Functions::get_array_value_by_path( $name_attr, $group_value );

        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si un archivo rfemoto existe
    |---------------------------------------------------------------------------------------------------
     */
    public static function remote_file_exists( $url = '' ) {
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_NOBODY, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_exec( $ch );
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_close( $ch );
        if ( $http_code == 200 ) {
            return true;
        }
        return false;
    }
}
