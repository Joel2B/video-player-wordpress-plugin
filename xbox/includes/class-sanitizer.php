<?php namespace Xbox\Includes;

class Sanitizer {
    public $field         = null;
    public $value         = null;
    public $default_value = '';

    public function __construct( $field, $value ) {
        $this->field = $field;
        $this->value = stripslashes_deep( $value );

        if ( $this->field->is_saved_field() ) {
            $this->default_value = $this->field->validate_value( '' );
        } else {
            $this->default_value = $this->field->arg( 'default' );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Acceso a cualquier método, evita errores al llamar a métodos inexistentes
    |---------------------------------------------------------------------------------------------------
     */
    public function __call( $name, $arguments ) {
        return $this->sanitize();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Función general de desinfección
    |---------------------------------------------------------------------------------------------------
     */
    public function sanitize() {
        $sanitized_value = '';
        switch ( $this->field->arg( 'type' ) ) {
            case 'wp_editor':
                $sanitized_value = $this->sanitize_value( $this->value, 'wp_kses_post' );
                break;

            case 'code_editor':
            case 'textarea':
                $sanitized_value = $this->sanitize_value( stripslashes( $this->value ), 'wp_specialchars_decode' );
                break;

            default:
                $sanitized_value = $this->sanitize_value( $this->value, 'sanitize_text_field' );
                break;
        }
        return $sanitized_value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor
    |---------------------------------------------------------------------------------------------------
     */
    public function sanitize_value( $value = null, $sanitize_function = 'sanitize_text_field' ) {
        if ( $value === null ) {
            $value = $this->value;
        }
        if ( Functions::is_empty( $value ) ) {
            return '';
        }

        if ( is_array( $value ) ) {
            return array_map( $sanitize_function, $value );
        }
        return call_user_func( $sanitize_function, $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: checkbox
    |---------------------------------------------------------------------------------------------------
     */
    public function checkbox() {
        $value = $this->validate_multiple_values( $this->value, array_keys( $this->field->arg( 'items' ) ), true );
        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: colorpicker
    |---------------------------------------------------------------------------------------------------
     */
    public function colorpicker() {
        $value        = trim( $this->value );
        $format_color = Functions::get_format_color( $value );
        if ( $format_color ) {
            switch ( $this->field->arg( 'options', 'format' ) ) {
                case 'hex':
                    if ( $format_color == 'hex' ) {
                        return $this->sanitize_value( $value );
                    }
                    break;

                case 'rgb':
                case 'rgba':
                    if ( $format_color == 'rgb' || $format_color == 'rgba' ) {
                        return $this->sanitize_value( $value );
                    }
                    break;
            }
        } else if ( Functions::get_format_color( $this->default_value ) ) {
            return $this->sanitize_value( $this->default_value );
        }

        return '';
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: file
    |---------------------------------------------------------------------------------------------------
     */
    public function file() {
        if ( $this->field->arg( 'options', 'multiple' ) ) {
            $files = (array) $this->value;
            $value = array();
            foreach ( $files as $file_url ) {
                if ( $val = $this->validate_file_value( $file_url ) ) {
                    $value[] = $val;
                }
            }
        } else {
            $value = $this->validate_file_value( $this->value );
        }

        if ( Functions::is_empty( $value ) ) {
            $value = $this->validate_file_value( $this->default_value );
        }
        //return $this->sanitize_value( $value );
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida la url del campo tipo file, verifica las extensiones permitidas
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_file_value( $value = '' ) {
        $value      = trim( $value );
        $value      = $this->validate_url_value( $value );
        $extension  = Functions::get_file_extension( $value );
        $mime_types = (array) $this->field->arg( 'options', 'mime_types' );

        if ( ! Functions::is_empty( $mime_types ) ) {
            if ( ! $extension || ! in_array( $extension, $mime_types ) ) {
                return '';
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: image_selector
    |---------------------------------------------------------------------------------------------------
     */
    public function image_selector() {
        if ( $this->field->is_checkbox_image_selector() ) {
            $value = $this->validate_multiple_values( $this->value, array_keys( $this->field->arg( 'items' ) ), true );
        } else {
            $value = $this->validate_single_value( $this->value, array_keys( $this->field->arg( 'items' ) ), true );
        }
        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: number
    |---------------------------------------------------------------------------------------------------
     */
    public function number() {
        $attributes   = $this->field->arg( 'attributes' );
        $value        = trim( $this->value );
        $valid_number = true;
        if ( is_numeric( $value ) ) {
            if ( is_numeric( $attributes['min'] ) && $value < $attributes['min'] ) {
                $valid_number = false;
            }
            if ( is_numeric( $attributes['max'] ) && $value > $attributes['max'] ) {
                $valid_number = false;
            }
            if ( $valid_number ) {
                return $this->sanitize_value( $value );
            }
        }
        return $this->sanitize_value( $this->default_value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: radio
    |---------------------------------------------------------------------------------------------------
     */
    public function radio() {
        $value = $this->validate_single_value( $this->value, array_keys( $this->field->arg( 'items' ) ), true );
        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: select
    |---------------------------------------------------------------------------------------------------
     */
    public function select() {
        $items        = $this->field->arg( 'items' );
        $valid_values = array();
        foreach ( $items as $key => $display ) {
            if ( is_array( $display ) && ! Functions::is_empty( $display ) ) {
                foreach ( $display as $i => $d ) {
                    $valid_values[] = $i;
                }
            } else {
                $valid_values[] = $key;
            }
        }
        if ( $this->field->arg( 'options', 'multiple' ) ) {
            $value = $this->value;
            if ( ! is_array( $value ) ) {
                $value = (array) $this->value;
            }
            $value = isset( $value[0] ) ? $value[0] : '';
            $value = explode( ',', $value );
            $value = $this->validate_multiple_values( $value, $valid_values, true );
        } else {
            $value = $this->validate_single_value( $this->value, $valid_values, true );
        }

        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: oembed
    |---------------------------------------------------------------------------------------------------
     */
    public function oembed() {
        $value = $this->validate_url_value( $this->value );
        if ( empty( $value ) ) {
            $value = $this->validate_url_value( $this->default_value );
        }
        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfectar valor de campo tipo: switcher
    |---------------------------------------------------------------------------------------------------
     */
    public function switcher() {
        $options = $this->field->arg( 'options' );
        $value   = $this->validate_single_value( $this->value, array( $options['on_value'], $options['off_value'] ), true );
        return $this->sanitize_value( $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida si el valor del campo es igual a uno de los items
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_single_value( $value = '', $valid_values = null, $set_default = true ) {
        $value = trim( $value );

        if ( $valid_values === null ) {
            $valid_values = $this->field->arg( 'items' );
        }

        if ( in_array( $value, $valid_values ) ) {
            return $value;
        }

        if ( $set_default ) {
            return $this->default_value;
        }

        return '';
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida si los valores del campo son iguales a los items
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_multiple_values( $value = array(), $valid_values = null, $set_default = true ) {
        $value = Functions::array_filter( $value );

        if ( $valid_values === null ) {
            $valid_values = $this->field->arg( 'items' );
        }

        $value = array_filter( $value, function ( $val ) use ( $valid_values ) {
            return in_array( $val, $valid_values );
        } );

        if ( Functions::is_empty( $value ) && $set_default ) {
            $value = (array) $this->default_value;
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida una url
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_url_value( $value = '' ) {
        $value     = trim( $value );
        $protocols = array_filter( (array) $this->field->arg( 'options', 'protocols' ) );
        if ( empty( $protocols ) ) {
            $protocols = null;
        }
        return esc_url_raw( $value, $protocols );
    }
}
