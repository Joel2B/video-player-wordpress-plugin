<?php namespace Xbox\Includes;

class XboxCore {
    public $id             = 0;
    public $fields_prefix  = '';
    public $args           = array();
    public $fields         = array();
    public $fields_objects = array();
    protected $object_id   = 0;
    protected $object_type = 'metabox'; //'metabox' & 'admin-page'
    protected $reset       = false;
    protected $import      = false;
    private $nonce         = '';
    private $main_tab      = false;
    public $update_message = '';
    public $update_error   = false;

    public function __construct( $args = array() ) {
        if ( empty( $args['id'] ) ) {
            return;
        }

        $this->id            = $args['id'];
        $this->fields_prefix = isset( $args['fields_prefix'] ) ? $args['fields_prefix'] : '';
        $this->set_args( $args );
        \Xbox::add( $this );
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
            return $this->arg( $name );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Acceso a cualquier argumento
    |---------------------------------------------------------------------------------------------------
     */
    public function arg( $arg = '', $default_value = null ) {
        if ( isset( $this->args[$arg] ) ) {
            return $this->args[$arg];
        } else if ( $default_value ) {
            $this->args[$arg] = $default_value;
            return $this->args[$arg];
        }
        return null;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Establece las opciones por defecto
    |---------------------------------------------------------------------------------------------------
     */
    public function set_args( $args = array() ) {
        $default_args = array(
            'id'            => '',
            'title'         => '',
            'class'         => '',
            'fields_prefix' => '',
            'show_callback' => null, // Función callback para comprobar si se debe mostrar
            'show_in' => array(), // Post/Page IDs donde se debe mostrar Xbox
            'not_show_in' => array(), // Post/Page IDs donde no se debe mostrar Xbox
            'skin' => 'pink', // Skins: blue, lightblue, green, teal, pink, purple, bluepurple, yellow, orange'
            'layout' => 'wide', // boxed & wide
            'header' => null,
            'footer'        => null,
        );

        $this->args = wp_parse_args( $args, $default_args );

        $this->args['show_in']     = (array) $this->args['show_in'];
        $this->args['not_show_in'] = (array) $this->args['not_show_in'];

        if ( is_array( $this->args['header'] ) && ! empty( $this->args['header'] ) || $this->args['header'] === true ) {
            $header_defaults = array(
                'icon'  => '<i class="xbox-icon xbox-icon-cog"></i>',
                'desc'  => '',
                'class' => '',
            );
            if ( $this->args['header'] === true ) {
                $this->args['header'] = $header_defaults;
            } else {
                $this->args['header'] = wp_parse_args( $this->args['header'], $header_defaults );
            }
        }

        return $this->args;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo grupo
    |---------------------------------------------------------------------------------------------------
     */
    public function add_group( $field_args = array(), &$parent_object = null ) {
        $field_args['type'] = 'group';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene un campo de tipo grupo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_group( $field_id = '', $parent_object = null ) {
        return $this->get_field( $field_id, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega tab
    |---------------------------------------------------------------------------------------------------
     */
    public function add_tab( $field_args = array(), &$parent_object = null, $main_tab = false ) {
        $object = $this->get_object( $parent_object );
        if ( empty( $field_args['id'] ) ||
            $this->exists_field( $this->prefix_open_field( 'tab' ) . $field_args['id'], $object->fields ) ) {
            return;
        }

        $field_args['id']                  = $this->prefix_open_field( 'tab' ) . $field_args['id'];
        $field_args['type']                = 'tab';
        $field_args['action']              = 'open';
        $field_args['options']['main_tab'] = $main_tab;
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega tab principal
    |---------------------------------------------------------------------------------------------------
     */
    public function add_main_tab( $field_args = array(), &$parent_object = null ) {
        $object = $this->get_object( $parent_object );
        if ( empty( $field_args['id'] ) ||
            $this->exists_field( $this->prefix_open_field( 'tab' ) . $field_args['id'], $object->fields ) ) {
            return;
        }
        if ( ! $this->main_tab ) {
            $this->main_tab = true;
            return $this->add_tab( $field_args, $parent_object, true );
        }
        return $this->add_tab( $field_args, $parent_object, false );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Finaliza un tab
    |---------------------------------------------------------------------------------------------------
     */
    public function close_tab( $field_id = '', &$parent_object = null ) {
        $object = $this->get_object( $parent_object );
        if ( empty( $field_id ) ||
            $this->exists_field( $this->prefix_close_field( 'tab' ) . $field_id, $object->fields ) ) {
            return;
        }
        if ( ! $this->exists_field( $this->prefix_open_field( 'tab' ) . $field_id, $object->fields ) ) {
            return;
        }
        $field_args['id']     = $this->prefix_close_field( 'tab' ) . $field_id;
        $field_args['type']   = 'tab';
        $field_args['action'] = 'close';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo tab_item con acción abrir
    |---------------------------------------------------------------------------------------------------
     */
    public function open_tab_item( $item_name = '', &$parent_object = null ) {
        $object = $this->get_object( $parent_object );
        if ( empty( $item_name ) ||
            $this->exists_field( $this->prefix_open_field( 'tab_item' ) . $item_name, $object->fields ) ) {
            return;
        }
        $field_args['id']     = $this->prefix_open_field( 'tab_item' ) . $item_name;
        $field_args['type']   = 'tab_item';
        $field_args['action'] = 'open';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo tab_item con acción cerrar
    |---------------------------------------------------------------------------------------------------
     */
    public function close_tab_item( $item_name = '', &$parent_object = null ) {
        $object = $this->get_object( $parent_object );
        if ( empty( $item_name ) ||
            $this->exists_field( $this->prefix_close_field( 'tab_item' ) . $item_name, $object->fields ) ) {
            return;
        }
        if ( ! $this->exists_field( $this->prefix_open_field( 'tab_item' ) . $item_name, $object->fields ) ) {
            return;
        }
        $field_args['id']     = $this->prefix_close_field( 'tab_item' ) . $item_name;
        $field_args['type']   = 'tab_item';
        $field_args['action'] = 'close';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo mixto con acción abrir
    |---------------------------------------------------------------------------------------------------
     */
    public function open_mixed_field( $field_args = array(), &$parent_object = null ) {
        $object   = $this->get_object( $parent_object );
        $field_id = ! empty( $field_args['id'] ) ? $field_args['id'] : Functions::random_string( 15 );
        if ( $this->exists_field( $this->prefix_open_field( 'mixed' ) . $field_id, $object->fields ) ) {
            $field_id = Functions::random_string( 15 );
        }
        $field_args['id']     = $this->prefix_open_field( 'mixed' ) . $field_id;
        $field_args['type']   = 'mixed';
        $field_args['action'] = 'open';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo mixto con acción cerrar
    |---------------------------------------------------------------------------------------------------
     */
    public function close_mixed_field( $field_args = array(), &$parent_object = null ) {
        $object = $this->get_object( $parent_object );
        if ( ! $id = $this->get_id_last_open_field( 'mixed', $object->fields ) ) {
            return;
        }
        $open_field               = $object->get_field( $id );
        $field_args['id']         = str_replace( $this->prefix_open_field( 'mixed' ), $this->prefix_close_field( 'mixed' ), $id );
        $field_args['type']       = 'mixed';
        $field_args['action']     = 'close';
        $field_args['desc']       = $open_field->arg( 'desc' );
        $field_args['desc_title'] = $open_field->arg( 'desc_title' );
        $field_args['options']    = $open_field->arg( 'options' );
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de tipo section
    |---------------------------------------------------------------------------------------------------
     */
    public function add_section( $field_args = array(), &$parent_object = null ) {
        $field_args['type'] = 'section';
        $field_args['id']   = ! empty( $field_args['id'] ) ? $field_args['id'] : Functions::random_string( 15 );
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de importación de datos
    |---------------------------------------------------------------------------------------------------
     */
    public function add_import_field( $field_args = array(), &$parent_object = null ) {
        $field_args['type'] = 'import';
        $field_args['id']   = 'xbox-import-field';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo de importación de datos
    |---------------------------------------------------------------------------------------------------
     */
    public function add_export_field( $field_args = array(), &$parent_object = null ) {
        $field_args['type'] = 'export';
        $field_args['id']   = 'xbox-export-field';
        return $this->add_field( $field_args, $parent_object );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function add_field( $field_args = array(), &$parent_object = null ) {
        $object = $this->get_object( $parent_object );

        if ( ! $this->is_valid_field( $field_args, $object->fields ) ) {
            return;
        }

        $field_id         = $this->get_field_id( $field_args['id'] );
        $field_args['id'] = $field_id;

        //Agregamos el nuevo array field al array de fields
        $object->fields[$field_id] = $field_args;

        //Agregamos el nuevo objecto field al array de objetos fields
        $object->fields_objects[$field_id] = new Field( $field_args, $this, $object );

        //Agregamos como campo mixto si es necesario
        if ( $this->in_mixed_field( $field_id, $object->fields ) ) {
            $object->get_field( $field_id )->set_in_mixed( true );
        }

        //Estructura jerárquica de los campos creados. Por ahora no se requiere.
        //$this->set_fields_structure( $object, $field_id, $field_args );

        //Campos privados para trabajo interno
        $this->add_private_field( $object, $field_args );

        return $object->fields_objects[$field_id];
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Establece la estructura jerárquica de los campos creados. Por ahora no se requiere.
    |---------------------------------------------------------------------------------------------------
     */
    private function set_fields_structure( $object, $field_id, $field_args = array() ) {
        //Si el object actual es una instancia de Field
        if ( is_a( $object, 'Xbox\Includes\Field' ) ) {
            switch ( $object->get_row_level( true ) ) {
                case 1:
                    if ( isset( $this->fields[$object->id] ) ) {
                        $this->fields[$object->id]['fields'][$field_id] = $field_args;
                    }
                    break;

                case 2:
                    $parent = $object->get_parent( '', false );
                    if ( $parent ) {
                        $id = $parent->id;
                        if ( isset( $this->fields[$id]['fields'][$object->id] ) ) {
                            $this->fields[$id]['fields'][$object->id]['fields'][$field_id] = $field_args;
                        }
                    }
                    break;

                case 3:
                    $parent_1 = $object->get_parent( '', 1 );
                    $parent_2 = $object->get_parent( '', 2 );

                    if ( $parent_1 && $parent_2 ) {
                        $id_1 = $parent_1->id;
                        $id_2 = $parent_2->id;
                        if ( isset( $this->fields[$id_1]['fields'][$id_2]['fields'][$object->id] ) ) {
                            $this->fields[$id_1]['fields'][$id_2]['fields'][$object->id]['fields'][$field_id] = $field_args;
                        }
                    }
                    break;
            }
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Agrega un campo privado
    |---------------------------------------------------------------------------------------------------
     */
    private function add_private_field( $object, $field_args = array() ) {
        if ( $field_args['type'] == 'file' ) {
            $field = $object->get_field( $field_args['id'] );

            //Agregamos campo privado para guardar el id de cada archivo
            $object->add_field( array(
                'id'         => $field_args['id'] . '_id',
                'type'       => 'private',
                'options'    => array(
                    'multiple' => $field->arg( 'options', 'multiple' ),
                ),
                'repeatable' => $field->arg( 'repeatable' ),
            ) );
        }

        if ( $field_args['type'] == 'group' ) {
            $group_object = $object->get_field( $field_args['id'] );

            //Agregamos un campo privado para guardar el nombre de cada item de grupo
            $group_object->add_field( array(
                'id'   => $field_args['id'] . '_name',
                'type' => 'private',
            ) );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el objecto al que se le está agregando campos (Xbox o Field)
    |---------------------------------------------------------------------------------------------------
     */
    public function get_object( $parent_object = null ) {
        $object = $this;
        if ( $parent_object != null ) {
            $object = $parent_object;
        }
        return $object;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene un campo si existe y si no devuelve el objeto padre
    |---------------------------------------------------------------------------------------------------
     */
    public function get_field( $field_id = '', $parent_object = null ) {
        $field_id = $this->get_field_id( $field_id );
        $object   = $this->get_object( $parent_object );
        if ( isset( $object->fields_objects[$field_id] ) ) {
            return $object->fields_objects[$field_id];
        }
        return null;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si un campo es válido
    |---------------------------------------------------------------------------------------------------
     */
    public function is_valid_field( $field_args, $fields = array() ) {
        if ( ! is_array( $field_args ) || empty( $field_args ) || ! isset( $field_args['id'] ) ) {
            return false;
        }
        $field_id = str_replace( $this->fields_prefix, '', $field_args['id'] );
        if ( empty( $field_id ) || empty( $field_args['type'] ) ) {
            return false;
        }

        $field_id = $this->get_field_id( $field_args['id'] );

        if ( $this->exists_field( $field_id, $fields ) ) {
            return false;
        }
        return true;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si un campo existe
    |---------------------------------------------------------------------------------------------------
     */
    public function exists_field( $field_id, $fields = array() ) {
        $field_id = $this->get_field_id( $field_id );
        if ( isset( $fields[$field_id] ) ) {
            return true;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el id real del campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_field_id( $field_id ) {
        $field_id = Functions::str_trim_to_lower( $field_id, '-' );
        if ( ! Functions::starts_with( $this->fields_prefix, $field_id ) ) {
            return $this->fields_prefix . $field_id;
        }
        return $field_id;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Compueba si el campo se debe agregar dentro de un grupo mixto
    |---------------------------------------------------------------------------------------------------
     */
    public function in_mixed_field( $field_id, $fields = array() ) {
        $in_mixed = false;
        if ( Functions::starts_with( $this->prefix_open_field( 'mixed' ), $field_id ) || Functions::starts_with( $this->prefix_close_field( 'mixed' ), $field_id ) ) {
            return false;
        }
        foreach ( $fields as $field ) {
            if ( Functions::starts_with( $this->prefix_open_field( 'mixed' ), $field['id'] ) ) {
                $in_mixed = true;
            } elseif ( Functions::starts_with( $this->prefix_close_field( 'mixed' ), $field['id'] ) ) {
                $in_mixed = false;
            }
        }
        return $in_mixed;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Prefijos de un tipo de campo
    |---------------------------------------------------------------------------------------------------
     */
    public function prefix_open_field( $type ) {
        return $this->fields_prefix . "open-{$type}-";
    }

    public function prefix_close_field( $type ) {
        return $this->fields_prefix . "close-{$type}-";
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el id del último campo abierto
    |---------------------------------------------------------------------------------------------------
     */
    private function get_id_last_open_field( $type, $fields = array() ) {
        $id = '';
        foreach ( $fields as $field ) {
            if ( Functions::starts_with( $this->prefix_open_field( $type ), $field['id'] ) ) {
                $id = $field['id'];
            } elseif ( Functions::starts_with( $this->prefix_close_field( $type ), $field['id'] ) ) {
                $id = '';
            }
        }
        return $id;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Función principal para crear todos los campos
    |---------------------------------------------------------------------------------------------------
     */
    public function build_xbox( $object_id = 0, $echo = false ) {
        global $post;
        $return = "";

        if ( $object_id ) {
            $this->object_id = $object_id;
        } else {
            $this->set_object_id();
        }

        $skin = 'xbox-skin-' . $this->arg( 'skin' );

        $xbox_class = "xbox xbox-{$this->object_type} xbox-clearfix xbox-radius xbox-{$this->arg( 'layout' )} {$this->arg( 'class' )} $skin";

        if ( $this->main_tab ) {
            $xbox_class .= ' xbox-has-main-tab';
        }

        $return .= "<div id='xbox-{$this->id}' class='$xbox_class' data-skin='$skin' data-object-id='$this->object_id' data-object-type='$this->object_type'>";
        $return .= $this->build_header();
        $return .= $this->build_fields();
        $return .= $this->build_footer();
        $return .= "</div>";

        $return .= $this->create_nonce();

        if ( ! $echo ) {
            return $return;
        }
        echo $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Construye los campos
    |---------------------------------------------------------------------------------------------------
     */
    public function build_fields() {
        $return = '';
        foreach ( $this->fields_objects as $field ) {
            $field_builder = new FieldBuilder( $field );
            $return .= $field_builder->build();
        }
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Nonce html para la seguridad
    |---------------------------------------------------------------------------------------------------
     */
    public function create_nonce() {
        $nonce = $this->get_nonce();
        return wp_nonce_field( $nonce, $nonce, false, false );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Generate nonce
    |---------------------------------------------------------------------------------------------------
     */
    public function get_nonce() {
        if ( empty( $this->nonce ) ) {
            $this->nonce = sanitize_text_field( 'xbox_nonce_' . $this->id );
        }
        return $this->nonce;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Construye header
    |---------------------------------------------------------------------------------------------------
     */
    public function build_header() {
        $return = '';
        $header = $this->arg( 'header' );
        if ( empty( $header ) ) {
            return '';
        }

        $style = "<style>";
        $style .= "
			.xbox-postbox#{$this->id} > .hndle,
			.xbox-postbox#{$this->id} > .handlediv {
				display: none !important;
			}
			.xbox-postbox#{$this->id} > button {
				display: none !important;
			}
		";
        $style .= "</style>";

        $icon = ! empty( $header['icon'] ) ? trim( $header['icon'] ) : '';

        $header_class = 'xbox-header xbox-clearfix ' . $header['class'];
        if ( Functions::starts_with( '<img', $icon ) ) {
            $header_class .= ' xbox-has-logo';
        }

        $return .= "<div class='$header_class'>";
        if ( $this->args['title'] == 'Plugin Options' ) {
            $return .= "<div class='xbox-header-theme-name'>";
            $return .= $this->args['header']['name'];
            $return .= "</div>";
        }
        $return .= "<div class='xbox-header-actions'>";
        if ( $this->object_type == 'admin-page' ) {
            $return .= $this->get_form_buttons();
        }
        $return .= "</div>";

        $return .= "</div>";
        return $style . $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Construye footer
    |---------------------------------------------------------------------------------------------------
     */
    public function build_footer() {
        $return = '';
        $footer = $this->arg( 'footer' );
        if ( empty( $footer ) ) {
            return '';
        }
        $return .= "<div class='xbox-footer'>";
        $return .= "<div class='xbox-footer-title'>";
        $return .= "</div>";
        $return .= "<div class='xbox-footer-content'>";
        $return .= "<span>Xbox Framework v" . XBOX_VERSION . "</span>";
        $return .= "</div>";
        $return .= "</div>";
        return $return;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Guarda los campos
    |---------------------------------------------------------------------------------------------------
     */
    public function save_fields( $post_id = 0, $data = array() ) {
        $data = ! empty( $data ) ? $data : $_POST;

        //Importante para indicar donde guardar los datos
        $this->set_object_id();
        $updated_fields = array();

        //Comprobamos si debemos importar datos
        if ( isset( $data['xbox-import'] ) ) {
            $this->import = true;
            $importer     = new Importer( $this, $data );
            $import_data  = $importer->get_import_xbox_data();
            if ( $import_data !== false ) {
                $data = wp_parse_args( $import_data, $data );
            } else {
                $this->update_error = true;
            }
        }
        do_action( "xbox_before_save_fields", $this->object_id, $this );
        do_action( "xbox_before_save_fields_{$this->object_type}", $this->object_id, $this );
        foreach ( $this->fields_objects as $field ) {
            if ( $field->arg( 'type' ) == 'section' ) {
                foreach ( $field->fields_objects as $_field ) {
                    $saved = $this->save_field( $_field, $data );
                    if ( $saved ) {
                        $updated_fields[] = $saved;
                    }
                }
            } else {
                $saved = $this->save_field( $field, $data );
                if ( $saved ) {
                    $updated_fields[] = $saved;
                }
            }
        }

        do_action( "xbox_after_save_fields", $this->object_id, $updated_fields, $this );
        do_action( "xbox_after_save_fields_{$this->object_type}", $this->object_id, $updated_fields, $this );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Guarda cada campo
    |---------------------------------------------------------------------------------------------------
     */
    public function save_field( $field, $data = array() ) {

        $value = isset( $data[$field->id] ) ? $data[$field->id] : '';
        if ( in_array( $field->arg( 'type' ), $this->exclude_field_type_for_save() ) ) {
            return false;
        }

        //Para resetear las opciones, debe ir antes de todo sino no funciona
        if ( isset( $data['xbox-reset'] ) ) {
            $value       = $field->arg( 'default' );
            $this->reset = true;
        }

        //sanitize_group falla in import. Se tiene que recargar la página para ver los valores importados.
        if ( $field->arg( 'type' ) == 'group' ) {
            $value        = (array) $field->sanitize_group( $value );
            $field->value = null;
        }

        do_action( "xbox_before_save_field", $field->id, $value, $field );
        do_action( "xbox_before_save_field_{$field->id}", $value, $field );

        $updated = $field->save( $value );

        do_action( "xbox_after_save_field", $field->id, $updated, $field );
        do_action( "xbox_after_save_field_{$field->id}", $updated, $field );

        if ( $updated ) {
            return $field->id;
        }
        return false;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene todos los fields con sus valores en formato json o array
    |---------------------------------------------------------------------------------------------------
     */
    public function get_fields_data( $format = 'json' ) {
        $fields_data = array();
        foreach ( $this->fields_objects as $field ) {
            if ( $field->arg( 'type' ) == 'section' ) {
                foreach ( $field->fields_objects as $_field ) {
                    $data = $this->get_field_data( $_field );
                    if ( $data !== false ) {
                        $fields_data[$_field->id] = $data;
                    }
                }
            } else {
                $data = $this->get_field_data( $field );
                if ( $data !== false ) {
                    $fields_data[$field->id] = $data;
                }
            }
        }
        if ( $format == 'json' ) {
            return json_encode( $fields_data );
        }
        return $fields_data;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si existe una función callback
    |---------------------------------------------------------------------------------------------------
     */
    public function get_field_data( $field ) {
        $value = '';
        if ( in_array( $field->arg( 'type' ), $this->exclude_field_type_for_save() ) ) {
            return false;
        }

        $value = $field->get_saved_value();

        if ( $field->arg( 'type' ) == 'group' ) {
            $value = (array) $field->sanitize_group( $value );
        } else {
            $value = $field->sanitize_value( $value );
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si existe una función callback
    |---------------------------------------------------------------------------------------------------
     */
    public function exists_callback( $callback = '', $object = null ) {
        if ( $object == null ) {
            $object = $this;
        }
        if ( ! isset( $object->args[$callback] ) ) {
            return '';
        }
        if ( $object->args[$callback] == false ) {
            return false;
        }
        if ( is_callable( $object->args[$callback] ) ) {
            return true;
        }
        return null;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Excluye los campos que no se deben guardar
    |---------------------------------------------------------------------------------------------------
     */
    public function exclude_field_type_for_save() {
        return array( 'title', 'tab', 'tab_item', 'mixed', 'section', 'import', 'export' );
    }
}
