<?php

namespace Xbox\Includes;

class Metabox extends XboxCore {

    public function __construct( $args = array() ) {

        if ( ! is_array( $args ) || Functions::is_empty( $args ) || empty( $args['id'] ) ) {
            return;
        }

        $args['id'] = sanitize_title( $args['id'] );

        $this->args = wp_parse_args( $args, array(
            'id'         => '',
            'title'      => __( 'Xbox Metabox', 'xbox' ),
            'context'    => 'normal',
            'priority'   => 'high',
            'post_types' => 'post',
            'closed'     => false,
        ) );

        $this->object_type = 'metabox';
        $this->set_object_id();

        $this->args['post_types'] = (array) $this->args['post_types'];

        parent::__construct( $this->args );

        $this->hooks();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Acceso al id del objecto actual, post id o page id
    |---------------------------------------------------------------------------------------------------
     */
    public function set_object_id( $object_id = 0 ) {
        if ( $object_id ) {
            $this->object_id = $object_id;
        }
        if ( $this->object_id ) {
            return $this->object_id;
        }
        $object_id = get_the_ID();
        if ( ! $object_id ) {
            $object_id = isset( $GLOBALS['post']->ID ) ? $GLOBALS['post']->ID : 0;
        }
        if ( ! $object_id ) {
            $object_id = isset( $_REQUEST['post'] ) ? $_REQUEST['post'] : $object_id;
        }
        if ( ! $object_id ) {
            $object_id = isset( $_GET['post'] ) ? $_GET['post'] : $object_id;
        }
        $this->object_id = $object_id;
        return $this->object_id;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Register Hooks
    |---------------------------------------------------------------------------------------------------
     */
    private function hooks() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ), 10, 3 );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Add metaboxes
    |---------------------------------------------------------------------------------------------------
     */
    public function add_meta_boxes() {
        if ( ! $this->should_show() ) {
            return;
        }

        foreach ( $this->arg( 'post_types' ) as $post_type ) {
            add_meta_box(
                $this->id,
                $this->args['title'],
                array( $this, 'build_metabox' ),
                $post_type,
                $this->args['context'],
                $this->args['priority']
            );
            add_filter( "postbox_classes_{$post_type}_{$this->id}", array( $this, "add_metabox_classes" ) );
        }
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Build metabox
    |---------------------------------------------------------------------------------------------------
     */
    public function build_metabox() {
        echo $this->build_xbox();
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Add a class 'xbox-postbox' to a Metabox
    |---------------------------------------------------------------------------------------------------
     */
    public function add_metabox_classes( $classes = array() ) {
        array_push( $classes, 'xbox-postbox' );
        if ( $this->arg( 'closed' ) && empty( $this->args['header'] ) ) {
            array_push( $classes, 'closed' );
        }
        return $classes;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Comprueba si se debe mostrar el metabox
    |---------------------------------------------------------------------------------------------------
     */
    public function should_show() {
        $show          = true;
        $show_callback = $this->exists_callback( 'show_callback' );
        $show_in       = $this->arg( 'show_in' );
        $not_show_in   = $this->arg( 'not_show_in' );

        if ( $show_callback === false ) {
            return false;
        } elseif ( $show_callback ) {
            $show = (bool) call_user_func( $this->args['show_callback'], $this );
        }

        if ( ! Functions::is_empty( $show_in ) ) {
            if ( in_array( $this->object_id, $show_in ) ) {
                return true;
            } else {
                return false;
            }
        }

        if ( ! Functions::is_empty( $not_show_in ) ) {
            if ( in_array( $this->object_id, $not_show_in ) ) {
                return false;
            } else {
                return true;
            }
        }

        return $show;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Save metabox options
    |---------------------------------------------------------------------------------------------------
     */
    public function save_metabox( $post_id, $post, $update ) {
        if ( ! in_array( $post->post_type, $this->arg( 'post_types' ) ) ) {
            return $post_id;
        }
        if ( ! $this->can_save_metabox( $post ) ) {
            return $post_id;
        }
        $this->save_fields( $post_id, $_POST );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Guarda un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function set_field_value( $field_id, $value = '', $post_id = '' ) {
        $field_id = $this->get_field_id( $field_id );
        if ( empty( $post_id ) ) {
            $post_id = $this->get_object_id();
            if ( empty( $post_id ) ) {
                $post_id = get_the_ID();
            }
        }
        /*if( ( isset($_POST['duration_hh']) ) && ( isset($_POST['duration_mm']) ) && ( isset($_POST['duration_ss']) ) ){
        $duration_seconds = $_POST['duration_hh'] * 3600 + $_POST['duration_mm'] * 60 + $_POST['duration_ss'];
        return update_post_meta( $post_id, 'duration', $duration_seconds );
        }*/
        return update_post_meta( $post_id, $field_id, $value );
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene el valor de un campo
    |---------------------------------------------------------------------------------------------------
     */
    public function get_field_value( $field_id, $post_id = '', $default = '' ) {
        $field_id = $this->get_field_id( $field_id );
        if ( empty( $post_id ) ) {
            $post_id = $this->get_object_id();
            if ( empty( $post_id ) ) {
                $post_id = get_the_ID();
            }
        }
        $value = get_post_meta( $post_id, $field_id, true );
        if ( Functions::is_empty( $value ) ) {
            return $default;
        }
        return $value;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Verifica si el metabox puede ser guardado
    |---------------------------------------------------------------------------------------------------
     */
    public function can_save_metabox( $post ) {
        //Verify nonce
        if ( isset( $_POST[$this->get_nonce()] ) ) {
            if ( ! wp_verify_nonce( $_POST[$this->get_nonce()], $this->get_nonce() ) ) {
                return false;
            }
        } else {
            return false;
        }

        // Verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post->ID ) ) {
                return false;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post->ID ) ) {
                return false;
            }
        }
        return true;
    }
}
