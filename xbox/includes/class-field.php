<?php namespace Xbox\Includes;

class Field {
    public $id             = 0;
    public $xbox           = null;
    public $in_mixed       = false;
    public $value          = null;
    public $index          = 0;
    public $parents        = array();
    public $args           = array();
    public $fields         = array();
    public $fields_objects = array();
    public $parent_type    = null;

    public function __construct( $field_args, $xbox, $parent_object ) {
        $this->id   = $field_args['id'];
        $this->xbox = $xbox;
        $this->set_args( $field_args );

        if ( is_a( $parent_object, 'Xbox\Includes\Field' ) ) {
            $this->add_parent( $parent_object );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Acceso a cualquier método, evita errores al llamar a métodos inexistentes
    |---------------------------------------------------------------------------------------------------
     */
    public function __call( $name, $arguments ) {
        if ( Functions::starts_with( 'set_', $name ) && strlen( $name ) > 4 ) {
            $property = substr( $name, 4 );
            if ( property_exists( $this, $property ) && isset( $arguments[0] ) ) {
                $this->$property = $arguments[0];
                return $this->$property;
            }
            return null;
        } else if ( Functions::starts_with( 'get_', $name ) && strlen( $name ) > 4 ) {
            $property = substr( $name, 4 );
            if ( property_exists( $this, $property ) ) {
                return $this->$property;
            }
            return null;
        } else if ( property_exists( $this, $name ) ) {
            return $this->$name;
        } else {
            $arg_sub_key = isset( $arguments[0] ) ? $arguments[0] : false;
            return $this->arg( $name, $arg_sub_key );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Acceso a cualquier argumento
    |---------------------------------------------------------------------------------------------------
     */
    public function arg( $name = '', $key = '' ) {
        if ( isset( $this->args[$name] ) ) {
            if ( $key ) {
                if ( isset( $this->args[$name][$key] ) ) {
                    return $this->args[$name][$key];
                } else {
                    return null;
                }
            }
            return $this->args[$name];
        }
        return null;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Establece los argumentos por defecto
    |---------------------------------------------------------------------------------------------------
     */
    public function set_args( $field_args = array() ) {
        $field_type   = $field_args['type'];
        $default_args = array(
            'id'                       => '',
            'name'                     => '',
            'type'                     => '',
            'desc'                     => '',
            'desc_title'               => '',
            'default'                  => '',
            'attributes'               => array(),
            'options'                  => array(),
            'controls'                 => array(),
            'items'                    => array(),
            'items_desc'               => array(),
            'row_class'                => '',
            'field_class'              => '',
            'action'                   => 'default',
            'grid'                     => '',
            'insert_before_row'        => '',
            'insert_after_row'         => '',
            'insert_before_name'       => '',
            'insert_after_name'        => '',
            'insert_before_field'      => '',
            'insert_after_field'       => '',
            'insert_before_repeatable' => '',
            'insert_after_repeatable'  => '',
            'repeatable'               => false,
            'import_data'              => array(),
        );
        $this->args = wp_parse_args( $field_args, $default_args );

        //Validate arguments
        $this->args['name']                     = $this->validate_arg( 'name', 'string' );
        $this->args['attributes']               = $this->validate_arg( 'attributes', 'array' );
        $this->args['options']                  = $this->validate_arg( 'options', 'array' );
        $this->args['items']                    = $this->validate_arg( 'items', 'array' );
        $this->args['items_desc']               = $this->validate_arg( 'items_desc', 'array' );
        $this->args['row_class']                = $this->validate_arg( 'row_class', 'string' );
        $this->args['field_class']              = $this->validate_arg( 'field_class', 'string' );
        $this->args['grid']                     = $this->validate_arg( 'grid', 'string' );
        $this->args['insert_before_row']        = $this->validate_arg( 'insert_before_row', 'html' );
        $this->args['insert_after_row']         = $this->validate_arg( 'insert_after_row', 'html' );
        $this->args['insert_before_name']       = $this->validate_arg( 'insert_before_name', 'html' );
        $this->args['insert_after_name']        = $this->validate_arg( 'insert_after_name', 'html' );
        $this->args['insert_before_field']      = $this->validate_arg( 'insert_before_field', 'html' );
        $this->args['insert_after_field']       = $this->validate_arg( 'insert_after_field', 'html' );
        $this->args['insert_before_repeatable'] = $this->validate_arg( 'insert_before_repeatable', 'html' );
        $this->args['insert_after_repeatable']  = $this->validate_arg( 'insert_after_repeatable', 'html' );
        $this->args['import_data']              = $this->validate_arg( 'import_data', 'array' );

        //Validamos campos que no pueden ser repetibles
        $this->args['repeatable'] = $this->validate_repeatable( $field_type );

        //Los campos repetibles no puedes ser múltiples
        if ( $this->args['repeatable'] && isset( $this->args['options']['multiple'] ) ) {
            $this->args['options']['multiple'] = false;
        }

        //Establecer valores de opciones por defecto
        $this->args['options'] = wp_parse_args( $this->args['options'], array(
            'show_name'    => true,
            'desc_tooltip' => false,
        ) );

        //Establecer opciones por defecto para cada tipo de campo
        switch ( $field_type ) {
            case 'colorpicker':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'format'  => 'hex',
                    'opacity' => 1,
                ) );
                $this->args['default'] = $this->validate_colorpicker( $this->validate_arg( 'default', 'string' ) );
                break;

            case 'code_editor':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'language' => 'javascript', //css, php, javascript, html, xml. Default: javascript
                    'theme' => 'tomorrow_night', // ambiance, chrome, cobalt, dreamweaver, monokai, solarized_light
                    'height' => '240px',
                ) );
                break;

            case 'file':
                $this->args['options'] = array_replace_recursive( array(
                    'multiple'             => false,
                    'mime_types'           => array(),
                    'protocols'            => array(),
                    'preview_size'         => array( 'width' => '120px', 'height' => '75px' ),
                    'synchronize_selector' => '',
                    'upload_file_text'     => __( 'Upload file', 'xbox' ),
                    'upload_file_class'    => '',
                ), $this->args['options'] );

                if ( array_intersect( array( 'jpg', 'jpeg', 'png', 'gif', 'ico', 'icon' ),
                    $this->args['options']['mime_types'] ) ) {
                    $this->args['options']['upload_file_text'] = __( 'Upload image', 'xbox' );
                }
                break;

            case 'group':
                $this->args['options'] = array_replace_recursive( array(
                    'add_item_text'    => _x( 'Add new', 'New item for groups', 'xbox' ),
                    'remove_item_text' => _x( 'Remove', 'Remove item for groups', 'xbox' ),
                    'sortable'         => true,
                ), $this->args['options'] );

                $this->args['controls'] = array_replace_recursive( array(
                    'name'           => 'Group Item #',
                    'readonly_name'  => true, //Determines whether the control input will be editable. Default: true
                    'images' => false,
                    'default_image'  => XBOX_URL . '/img/transparent.png',
                    'image_field_id' => '',
                    'position'       => 'top', //top, left
                    'width'  => '',
                    'height'         => '',
                    'left_actions'   => array(
                        'xbox-info-order-item' => '#',
                    ),
                    'right_actions'  => array(
                        'xbox-duplicate-group-item' => '<i class="dashicons dashicons-admin-page"></i>',
                        'xbox-remove-group-item'    => '<i class="xbox-icon xbox-icon-trash"></i>',
                    ),
                ), $this->args['controls'] );
                break;

            case 'image':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'hide_input'  => false,
                    'image_class' => '',
                ) );
                break;

            case 'image_selector':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'width'         => '100px',
                    'height'        => 'auto',
                    'active_class'  => 'xbox-active',
                    'active_color'  => '#379FE7',
                    'in_line'       => true,
                    'like_checkbox' => false,
                ) );
                break;

            case 'import':
                $this->args['default'] = empty( $this->args['default'] ) ? 'from_file' : $this->args['default'];
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'import_from_file'   => true,
                    'import_from_url'    => true,
                    'width'              => '100px',
                    'height'             => 'auto',
                    'active_class'       => 'xbox-active',
                    'active_color'       => '#379FE7',
                    'in_line'            => true,
                    'like_checkbox'      => false,
                    'import_button_text' => __( 'Import', 'xbox' ),
                ) );
                //$this->args['import_data'] = wp_parse_args( $this->args['import_data'],  array() );

                $this->args['items'] = wp_parse_args( array(
                    'from_file' => _x( 'From file', 'Import/Export field', 'xbox' ),
                    'from_url'  => _x( 'From url', 'Import/Export field', 'xbox' ),
                ), $this->args['items'] );
                if ( ! $this->args['options']['import_from_file'] ) {
                    unset( $this->args['items']['from_file'] );
                }
                if ( ! $this->args['options']['import_from_url'] ) {
                    unset( $this->args['items']['from_url'] );
                }
                break;

            case 'export':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'export_button_text' => __( 'Download', 'xbox' ),
                ) );
                break;

            case 'number':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'unit'            => 'px',
                    'show_unit'       => true,
                    'show_spinner'    => false,
                    'disable_spinner' => false,
                ) );
                $this->args['attributes'] = wp_parse_args( $this->args['attributes'], array(
                    'min'       => 'null',
                    'max'       => 'null',
                    'step'      => 1,
                    'precision' => 0,
                ) );
                break;

            case 'oembed':
                $this->args['options'] = array_replace_recursive( array(
                    'oembed_class'     => '',
                    'preview_onload'   => false,
                    'preview_size'     => array( 'width' => '100%', 'height' => '260px' ),
                    'get_preview_text' => __( 'Get preview', 'xbox' ),
                ), $this->args['options'] );
                break;

            case 'radio':
            case 'checkbox':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'in_line' => true,
                ) );
                //Nota: El argumento 'default' de los checkboxs puede aceptar el comodín '$all$' o array('$all$') para activar todos sus items por defecto.
                break;

            case 'section':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'toggle'            => false,
                    'toggle_default'    => 'open', //open,close
                    'toggle_effect' => 'slide',
                    'toggle_target'     => 'header', //header, icon
                    'toggle_speed'  => 400,
                    'toggle_open_icon'  => 'xbox-icon-chevron-up',
                    'toggle_close_icon' => 'xbox-icon-chevron-down',
                ) );
                break;

            case 'select':
                $max_selections = 1;
                if ( ! empty( $this->args['options']['multiple'] ) ) {
                    $max_selections = 3;
                }
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'sort'           => false,
                    'multiple'       => false,
                    'search'         => false,
                    'max_selections' => $max_selections,
                ) );
                $this->args['options']['sort'] = $this->args['options']['sort'] === true ? 'asc' : $this->args['options']['sort'];
                $this->args['attributes']      = wp_parse_args( $this->args['attributes'], array(
                    'placeholder' => 'Select option',
                ) );
                break;

            case 'switcher':
                $this->args['default'] = empty( $this->args['default'] ) ? 'off' : $this->args['default'];
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'on_text'   => '',
                    'off_text'  => '',
                    'on_value'  => 'on',
                    'off_value' => 'off',
                ) );
                break;

            case 'tab':
                if ( isset( $this->args['options']['main_tab'] ) && $this->args['options']['main_tab'] == true ) {
                    $this->args['options']['skin']    = 'dark'; //Tab principal siempre es dark
                    $this->args['options']['on_left'] = true; //Tab principal siempre es a la izquierda
                } else {
                    $this->args['options'] = wp_parse_args( $this->args['options'], array(
                        'on_left'  => false,
                        'skin'     => 'light',
                        'main_tab' => false,
                    ) );
                    $this->args['options']['on_left'] = false; //Los Tabs generales no pueden ir a la izquierda
                    if ( ! in_array( $this->args['options']['skin'], array( 'light', 'dark' ) ) ) {
                        $this->args['options']['skin'] = 'light';
                    }
                }
                break;

            case 'title':
                $this->args['options']['show_name'] = false;
                break;

            case 'wp_editor':
                $this->args['options'] = wp_parse_args( $this->args['options'], array(
                    'editor_height' => 150,
                    'media_buttons' => true,
                ) );
                $this->args['options']['tinymce']['setup'] = 'function(editor){ window.XBOX.on_change_wp_editor(editor); }';
                $this->args['options']['default_editor']   = 'tinymce';
                //Opciones disponibles en:
                //https://codex.wordpress.org/Function_Reference/wp_editor
                break;
        }

        //Validamos el valor por defecto al final porque no es del mismo tipo para cada campo
        if ( $field_type == 'group' ) {
            $this->args['default'] = array();
        } else if (
            $field_type == 'checkbox' || ( $field_type == 'select' && $this->args['options']['multiple'] ) || ( $this->is_checkbox_image_selector() ) ) {
            $this->args['default'] = $this->validate_arg( 'default', 'array' );
        } else {
            $this->args['default'] = $this->validate_arg( 'default', 'string', ',' );
        }

        //Opciones adicionales para grupos y campos repetibles
        if ( $field_type == 'group' || $this->args['repeatable'] ) {
            $this->args['options'] = wp_parse_args( $this->args['options'], array(
                'sortable'              => true,
                'add_item_text'         => _x( 'Add new', 'New item for repeatable fields', 'xbox' ),
                'remove_item_text'      => _x( 'Remove', 'Remove item for repeatable fields', 'xbox' ),
                'add_item_class'        => '', //'xbox-custom-add' para agregar como nombre de cada control el texto del botón
                'remove_item_class' => '',
                'duplicate_item_text'   => __( 'Duplicate', 'xbox' ),
                'duplicate_item_class'  => '',
                'sort_item_text'        => __( 'Sort', 'xbox' ),
                'sort_item_class'       => '',
                'visibility_item_text'  => __( 'Visibility', 'xbox' ),
                'visibility_item_class' => '',
            ) );
        }

        //Validamos algunos campos de las opciones
        foreach ( $this->args['options'] as $option => $value ) {
            if ( false !== stripos( $option, 'class' ) ) { //Class debe ser escaparse con esc_attr
                $this->args['options'][$option] = esc_attr( $value );
            }
        }

        //Validamos los atributos
        foreach ( $this->args['attributes'] as $attr => $value ) {
            if ( $attr != 'disabled' ) { //Porque los checkboxs pueden tener un array de valores disabled.
                $this->args['attributes'][$attr] = esc_attr( $value );
            }
        }

        return $this->args;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida un argumento
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_arg( $arg, $data_type = 'array', $implode = ' ', $escaping_function = 'esc_attr' ) {
        $value   = $this->get_result_callback( $arg );
        $type    = $this->args['type'];
        $options = $this->args['options'];

        if ( $data_type == 'array' ) {
            if ( ! is_array( $value ) ) {
                $value = (array) $value;
            }
            if ( $arg == 'default' && $value == array( '$all$' ) ) {
                if ( $type == 'checkbox' || $this->is_checkbox_image_selector() ) {
                    $value = array_keys( $this->args['items'] );
                }
            }
            return $value;
        } else if ( $data_type == 'string' ) {
            if ( is_array( $value ) ) {
                $value = implode( $implode, $value );
            }
            if ( in_array( $this->args['type'], array( 'textarea', 'wp_editor', 'code_editor' ) ) ) {
                $value = call_user_func( 'stripslashes', $value );
            } else {
                $value = call_user_func( $escaping_function, $value );
            }
        } else if ( $data_type == 'html' ) {
            if ( is_array( $value ) ) {
                $value = implode( $implode, $value );
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida los campos que no pueden ser repetibles
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_repeatable( $field_type = '' ) {
        if ( empty( $field_type ) ) {
            return false;
        }
        $non_repeatable_fields = array(
            'title',
            'tab',
            'tab_item',
            'group',
            'checkbox',
            'radio',
            'image_selector',
            'html',
            'section',
            'import',
            'export',
            'switcher',
        );
        return $this->args['repeatable'] == true && ! in_array( $field_type, $non_repeatable_fields );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida el valor del campo colorpicker
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_colorpicker( $color = '' ) {
        $format_color = Functions::get_format_color( $color );
        $format       = $this->arg( 'options', 'format' );
        $opacity      = $this->arg( 'options', 'opacity' );
        if ( $format_color ) {
            if ( $format == 'hex' && ( $format_color == 'rgb' || $format_color == 'rgba' ) ) {
                $color = Functions::rgb_to_hex( $color );
            }
            if (  ( $format == 'rgb' || $format == 'rgba' ) && ( $format_color == 'hex' ) ) {
                $color = Functions::hex_to_rgb( $color, $opacity );
            }
        } else {
            $color = '';
        }
        return $color;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function add_field( $field_args = array() ) {
        return $this->xbox->add_field( $field_args, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_field( $field_id = '' ) {
        return $this->xbox->get_field( $field_id, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo grupo
    |---------------------------------------------------------------------------------------------------
     */
    public function add_group( $field_args = array() ) {
        return $this->xbox->add_group( $field_args, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene un campo de tipo grupo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_group( $field_id = '' ) {
        return $this->xbox->get_group( $field_id, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo mixto con acción abrir
    |---------------------------------------------------------------------------------------------------
     */
    public function open_mixed_field( $field_args = array() ) {
        return $this->xbox->open_mixed_field( $field_args, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo mixto con acción cerrar
    |---------------------------------------------------------------------------------------------------
     */
    public function close_mixed_field( $field_args = array() ) {
        return $this->xbox->close_mixed_field( $field_args, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega tab
    |---------------------------------------------------------------------------------------------------
     */
    public function add_tab( $field_args = array() ) {
        return $this->xbox->add_tab( $field_args, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Finaliza un tab
    |---------------------------------------------------------------------------------------------------
     */
    public function close_tab( $field_id = '' ) {
        return $this->xbox->close_tab( $field_id, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo tab_item con acción abrir
    |---------------------------------------------------------------------------------------------------
     */
    public function open_tab_item( $item_name = '' ) {
        return $this->xbox->open_tab_item( $item_name, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo tab_item con acción cerrar
    |---------------------------------------------------------------------------------------------------
     */
    public function close_tab_item( $item_name = '' ) {
        return $this->xbox->close_tab_item( $item_name, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si existe el argumento width por defecto
    |---------------------------------------------------------------------------------------------------
     */
    public function is_valid_grid_value( $grid ) {
        return strpos( $grid, 'last' ) !== false || strpos( $grid, 'new-row' ) !== false || in_array( $grid, array( '1-of-6', '2-of-6', '3-of-6', '4-of-6', '5-of-6', '6-of-6', '1-of-8', '2-of-8', '3-of-8', '4-of-8', '5-of-8', '6-of-8', '7-of-8', '8-of-8' ) );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si existe el argumento width por defecto
    |---------------------------------------------------------------------------------------------------
     */
    public function is_checkbox_image_selector() {
        return $this->arg( 'type' ) == 'image_selector' && $this->arg( 'options', 'like_checkbox' ) === true;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el nivel del campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_row_level( $real_level = false ) {
        $parents = $this->get_parents();
        $level   = count( $parents ) + 1;
        if ( $real_level ) {
            return $level;
        }
        foreach ( $parents as $parent ) {
            if ( $parent['type'] != 'group' ) {
                $level--;
            }
        }
        return $level;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega el id del campo padre
    |---------------------------------------------------------------------------------------------------
     */
    private function add_parent( $parent_object ) {
        $parents                    = $parent_object->get_parents();
        $parent_type                = $parent_object->arg( 'type' );
        $levels                     = count( $parents ) + 1;
        $parents["level-{$levels}"] = array(
            'id'   => $parent_object->id(),
            'type' => $parent_type,
        );
        $this->parents     = $parents;
        $this->parent_type = $parent_type;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el campo padre de un campo. Se le puede pasar el nivel de campo a obtener
    |---------------------------------------------------------------------------------------------------
     */
    public function get_parent( $parent_id = '', $level_number = false ) {
        $parent    = null;
        $parents   = $this->get_parents();
        $row_level = $this->get_row_level( true );

        if ( $row_level == 1 ) {
            return $this->xbox;
        }

        $parent = $this->xbox;
        foreach ( $parents as $level => $level_value ) {
            $parent = $parent->get_field( $level_value['id'] );
            if ( is_numeric( $level_number ) ) {
                if ( $level == 'level-' . $level_number ) {
                    return $parent;
                }
            }
            if ( $level_number == true && $parent->get_id() == $parent_id ) {
                return $parent;
            }
        }
        if ( $parent_id != '' && $parent->get_id() != $parent_id ) {
            return null;
        }
        return $parent;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si el campo ya ha sido guardado previamente en wordpress
    |---------------------------------------------------------------------------------------------------
     */
    public function is_saved_field() {
        $saved     = false;
        $row_level = $this->get_row_level();

        if ( $row_level == 1 ) {
            switch ( $this->xbox->get_object_type() ) {
                case 'metabox':
                    $saved = metadata_exists( 'post', $this->xbox->get_object_id(), $this->id );
                    break;

                case 'admin-page':
                    $options = get_option( $this->xbox->id );
                    if ( isset( $options[$this->id] ) ) {
                        $saved = true;
                    }
                    break;
            }
        } else {
            $group = $this->get_parent();
            $value = $group->get_group_value();
            if ( isset( $value[$group->index][$this->id] ) ) {
                $saved = true;
            }
        }
        return $saved;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el valor de un campo de primer nivel. Para los campos dentro de grupos usar get_value()
    |---------------------------------------------------------------------------------------------------
     */
    public function get_saved_value() {
        $row_level = $this->get_row_level();
        $value     = null;

        if ( $row_level > 1 ) {
            return null;
        }

        switch ( $this->xbox->get_object_type() ) {
            case 'metabox':
                $value = $this->xbox->get_field_value( $this->id, $this->xbox->get_object_id(), '' );
                break;

            case 'admin-page':
                $value = $this->xbox->get_field_value( $this->id, '' );
                break;
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el valor de un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_value( $default = true, $escaping_function = 'esc_attr', $index = null, $all = false ) {
        if ( $this->arg( 'type' ) == 'group' ) {
            return ''; //para grupos usar get_group_value()
        }
        $value     = '';
        $row_level = $this->get_row_level();

        //Para los campos de primer nivel
        if ( $row_level == 1 ) {
            if ( Functions::is_empty( $this->value ) ) {
                $this->value = $this->get_saved_value();
            }
            $value = $this->value;
        }
        //Para los campos dentro de grupos
        else {
            /*
            SOPORTE PARA SECCIONES DENTRO DE GRUPOS
            $group = $this->get_parent();
            //Si el padre del campo es una sección, entonces el padre de la seccion sería el grupo
            if( $group->arg( 'type' ) == 'section' ){
            $group = $group->get_parent();
            }
            $value = $group->get_group_value();
             */
            $group = $this->get_parent();
            $value = $group->get_group_value();

            if ( isset( $value[$group->index][$this->id] ) ) {
                $value = $value[$group->index][$this->id];
            } else {
                $value = '';
            }
        }

        if ( Functions::is_empty( $value ) && $default && ! $this->is_saved_field() ) {
            $value = $this->arg( 'default' );
        }

        if ( $escaping_function ) {
            $value = $this->escape_value( $value, $escaping_function );
        }

        $value = $this->validate_value( $value, $index, $all );

        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el valor de un grupo de cualquier nivel
    |---------------------------------------------------------------------------------------------------
     */
    public function get_group_value() {
        if ( $this->arg( 'type' ) != 'group' ) {
            return '';
        }

        $parents   = $this->get_parents();
        $row_level = $this->get_row_level();

        //Para los grupos de primer nivel
        if ( $row_level == 1 ) {
            if ( Functions::is_empty( $this->value ) ) {
                $this->value = $this->get_saved_value();
            }
            return $this->value;
        }

        //Para los grupos de nivel 2 primero tenemos que obtener el valor de grupo de nivel 1

        $group = $this->xbox->get_field( $parents['level-1']['id'] );
        //Si el campo de nivel 1 es es una sección, entonces el grupo de primer nivel está dentro de esa sección
        if ( $group->arg( 'type' ) == 'section' ) {
            $group = $group->get_field( $parents['level-2']['id'] );
        }
        $value = $group->get_saved_value();

        if ( is_array( $value ) && ! Functions::is_empty( $value ) ) {
            switch ( $row_level ) {
                case 2: //Para los grupos dentro de grupos
                    if ( isset( $value[$group->index][$this->id] ) ) {
                        $value = $value[$group->index][$this->id];
                    } else {
                        $value = array();
                    }
                    break;

                case 3: //Para los grupos de tercer nivel
                    //Nada por ahora
                    $value = array();
                    break;
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el atributo name de un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_name( $index = null ) {
        $field_name = $this->id;
        $parents    = $this->get_parents();
        $row_level  = $this->get_row_level();

        //Para los campos de primer nivel
        if ( $row_level == 1 ) {
            $field_name = $this->id;
        }
        //Para los campos dentro de grupos
        else {
            //Primero tenemos que posicionarnos en el grupo de nivel 1
            $group_1 = $this->xbox->get_field( $parents['level-1']['id'] );
            //Si el campo de nivel 1 es es una sección, entonces el grupo de primer nivel está dentro de esa sección
            if ( $group_1->arg( 'type' ) == 'section' ) {
                $group_1 = $group_1->get_field( $parents['level-2']['id'] );
            }

            switch ( $row_level ) {
                case 2: //Para los campos dentro de grupos de primer nivel
                    $field_name = "{$group_1->id}[$group_1->index][$this->id]";
                    break;

                case 3: //Para los campos dentro de grupos de segundo nivel
                    $group_2 = $group_1->get_group( $parents['level-2']['id'] );
                    if ( ! $group_2 ) {
                        $group_2 = $group_1->get_group( $parents['level-3']['id'] );
                    }
                    $field_name = "{$group_1->id}[$group_1->index][$group_2->id][$group_2->index][$this->id]";
                    break;

                case 4: //Para los campos dentro de grupos de tercer nivel
                    //Nada por ahora
                    break;
            }
        }
        if ( $this->arg( 'repeatable' ) ) {
            $index      = $index == null ? $this->index : $index;
            $field_name = "{$field_name}[$index]";
        }
        if ( $this->arg( 'type' ) == 'checkbox' || $this->arg( 'options', 'multiple' ) == true || $this->is_checkbox_image_selector() ) {
            $field_name = "{$field_name}[]";
        }
        return $field_name;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el valor de un campo por su atributo name. Función no utilizada pero sí funciona
    |---------------------------------------------------------------------------------------------------
     */
    public function get_value_by_name( $name_attr = '' ) {
        $value     = '';
        $parents   = $this->get_parents();
        $row_level = $this->get_row_level();

        if ( $row_level == 1 ) {
            return $this->get_saved_value();
        }

        //Para los campos dentro de grupos
        $field_name = ! empty( $name_attr ) ? $name_attr : $this->get_name();

        $group = $this->xbox->get_field( $parents['level-1']['id'] );
        if ( $group->arg( 'type' ) == 'section' ) {
            $group = $group->get_field( $parents['level-2']['id'] );
        }
        $value = $group->get_saved_value();
        $value = Functions::get_array_value_by_path( $field_name, $value );
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Valida el valor de un campo ya que puede ser repetible o no
    |---------------------------------------------------------------------------------------------------
     */
    public function validate_value( $value = '', $index = null, $all = false ) {
        $type       = $this->arg( 'type' );
        $repeatable = $this->arg( 'repeatable' );
        $multiple   = $this->arg( 'options', 'multiple' );
        $index      = $index == null ? $this->index : $index;

        if ( $repeatable || $type == 'checkbox' || $multiple == true || $this->is_checkbox_image_selector() ) {
            if ( ! Functions::is_empty( $value ) ) {
                if ( ! is_array( $value ) ) {
                    $value = str_split( $value, strlen( $value ) );
                }
                $value = Functions::array_filter( $value );
            }
            if ( ! $all ) {
                if ( $repeatable ) {
                    $value = isset( $value[$index] ) ? $value[$index] : '';
                }
            }
        } else {
            if ( is_array( $value ) ) {
                $value = Functions::array_filter( $value );
                $value = isset( $value[0] ) ? $value[0] : '';
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Escapar atributos HTML del valor de un campo
    |---------------------------------------------------------------------------------------------------
     */
    private function escape_value( $value = '', $escaping_function = 'esc_attr' ) {
        if ( Functions::is_empty( $value ) ) {
            return '';
        }
        $escape = $this->xbox->exists_callback( 'escape_callback', $this );

        //No escapar valor
        if ( $escape === false ) {
            return $value;
        }
        //Escapar valor con una función del usuario
        elseif ( $escape ) {
            return call_user_func( 'escape_callback', $value, $this->args, $this );
        }
        //Escapar valor con la función por defecto de wordpress
        if ( ! is_array( $value ) ) {
            $value = call_user_func( $escaping_function, $value );
        } else {
            foreach ( $value as $i => $val ) {
                $value[$i] = call_user_func( $escaping_function, $val );
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Guarda un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function save( $value = '' ) {
        $old_value = $this->get_saved_value();
        $new_value = $this->sanitize_value( $value );

        //No hacer nada si el nuevo valor es igual al guardado en la base datos
        if ( $new_value == $old_value ) {
            return false;
        }

        switch ( $this->xbox->get_object_type() ) {
            case 'metabox':
                return $this->xbox->set_field_value( $this->id, $new_value, $this->xbox->get_object_id() );
                break;

            case 'admin-page':
                return $this->xbox->set_field_value( $this->id, $new_value );
                break;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfecta el valor de un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function sanitize_value( $value = '' ) {
        if ( $this->arg( 'type' ) == 'group' ) {
            return (array) $value;
        }
        if ( is_array( $value ) && $this->arg( 'repeatable' ) ) {
            return $this->sanitize_repeatable( $value );
        }

        $sanitize = $this->xbox->exists_callback( 'sanitize_callback', $this );

        //No desinfectar
        if ( $sanitize === false ) {
            return $value;
        }
        //Desinfección por el usuario
        else if ( $sanitize ) {
            return call_user_func( $this->args['sanitize_callback'], $value, $this->args, $this );
        }

        $sanitizer = new Sanitizer( $this, $value );

        //Desinfección por defecto
        $sanitized_value = $sanitizer->{$this->args['type']}();

        //Filtro para desinfección por el usuario según tipo de campo
        $filter_tag = "xbox_sanitize_{$this->arg( 'type' )}";
        if ( has_filter( $filter_tag ) ) {
            $filter_value = apply_filters( $filter_tag, $sanitizer->value, $this->xbox->get_object_id(), $this->args, $sanitizer );
            if ( $filter_value === false ) {
                return $value;
            }
            return $filter_value;
        }

        return $sanitized_value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfecta un campo de tipo grupo
    |---------------------------------------------------------------------------------------------------
     */
    public function sanitize_group( $value = array() ) {
        if ( Functions::is_empty( $value ) ) {
            return (array) $value;
        }
        foreach ( $value as $index => $array_values ) {
            if ( ! Functions::is_empty( $array_values ) ) {
                foreach ( $array_values as $field_id => $val ) {
                    $field = $this->get_field( $field_id );
                    if ( $field ) {
                        if ( $field->arg( 'type' ) == 'group' ) {
                            $value[$index][$field_id] = $field->sanitize_group( $val );
                        } else {
                            $value[$index][$field_id] = $field->sanitize_value( $val );
                        }
                    }
                }
            }
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Desinfecta un campo repetible
    |---------------------------------------------------------------------------------------------------
     */
    public function sanitize_repeatable( $value = '' ) {
        if ( ! is_array( $value ) || ! $this->arg( 'repeatable' ) ) {
            return $value;
        }
        $value = array_filter( $value );
        $value = array_values( $value ); //Remove empty keys
        foreach ( $value as $index => $val ) {
            $value[$index] = $this->sanitize_value( $val, $index );
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el contenido de una función callback
    |---------------------------------------------------------------------------------------------------
     */
    public function get_result_callback( $callback = '' ) {
        if ( isset( $this->args[$callback] ) && $this->args[$callback] !== 'disabled' && $this->xbox->exists_callback( $callback, $this ) ) {
            return call_user_func( $this->args[$callback], $this->args, $this );
        }
        return isset( $this->args[$callback] ) ? $this->args[$callback] : '';
    }
}
