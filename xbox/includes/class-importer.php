<?php namespace Xbox\Includes;

class Importer {
    private $xbox = null;
    private $data = array();

    /*
    |---------------------------------------------------------------------------------------------------
    | Constructor de la clase
    |---------------------------------------------------------------------------------------------------
     */
    public function __construct( $xbox = null, $data = array() ) {
        $this->xbox = $xbox;
        $this->data = $data;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Obtiene los datos a importar
    |---------------------------------------------------------------------------------------------------
     */
    public function get_import_xbox_data() {
        $import_xbox_data = false;
        $data             = $this->data;
        $prefix           = $this->xbox->arg( 'fields_prefix' );
        switch ( $data[$prefix . 'xbox-import-field'] ) {
            case 'from_file':
                if ( isset( $_FILES["xbox-import-file"] ) ) {
                    $file_name    = $_FILES['xbox-import-file']['name'];
                    $file_content = "";
                    if ( Functions::ends_with( '.json', $file_name ) ) {
                        $file_content = file_get_contents( $_FILES['xbox-import-file']['tmp_name'] );
                    }
                    if ( $file_content ) {
                        $import_xbox_data = json_decode( $file_content, true );
                    }
                }
                break;

            case 'from_url':
                $url_content = file_get_contents( $data['xbox-import-url'] );
                if ( $url_content !== false ) {
                    $import_xbox_data = json_decode( $url_content, true );
                }
                break;

            default:
                $import_source     = $data['xbox-import-field'];
                $import_xbox       = $import_source;
                $import_wp_content = '';
                $import_wp_widget  = '';
                $widget_cb         = '';
                if ( isset( $data['xbox-import-data'] ) ) {
                    $sources           = isset( $data['xbox-import-data'][$import_source] ) ? $data['xbox-import-data'][$import_source] : array();
                    $import_xbox       = isset( $sources['import_xbox'] ) ? $sources['import_xbox'] : '';
                    $import_wp_content = isset( $sources['import_wp_content'] ) ? $sources['import_wp_content'] : '';
                    $import_wp_widget  = isset( $sources['import_wp_widget'] ) ? $sources['import_wp_widget'] : '';
                    $widget_cb         = isset( $sources['import_wp_widget_callback'] ) ? $sources['import_wp_widget_callback'] : '';
                }

                //Import xbox data
                if ( file_exists( $import_xbox ) || Functions::remote_file_exists( $import_xbox ) ) {
                    $file_content = file_get_contents( $import_xbox );
                    if ( $file_content !== false ) {
                        $import_xbox_data = json_decode( $file_content, true );
                    }
                }

                //Import Wp Content
                if ( file_exists( $import_wp_content ) ) {
                    echo '<h2>Importing wordpress data from local file, please wait ...</h2>';
                    $this->set_wp_content_data( $import_wp_content );
                } else if ( Functions::remote_file_exists( $import_wp_content ) ) {
                    $file_content = file_get_contents( $import_wp_content );
                    if ( $file_content !== false ) {
                        if ( false !== file_put_contents( XBOX_DIR . 'wp-content-data.xml', $file_content ) ) {
                            echo '<h2>Importing wordpress data from remote file, please wait ...</h2>';
                            //echo '<div class="wp-import-messages">';
                            $this->set_wp_content_data( XBOX_DIR . 'wp-content-data.xml' );
                            unlink( XBOX_DIR . 'wp-content-data.xml' );
                            //echo '</div>';
                        }
                    }
                }

                //Import Wp Widget
                if ( file_exists( $import_wp_widget ) || Functions::remote_file_exists( $import_wp_widget ) ) {
                    if ( is_callable( $widget_cb ) ) {
                        call_user_func( $widget_cb, $import_wp_widget );
                    }
                }
                break;
        }
        return $import_xbox_data;
    }

    /*
    |---------------------------------------------------------------------------------------------------
    | Importa contenido de wordpres
    |---------------------------------------------------------------------------------------------------
     */
    public function set_wp_content_data( $file ) {
        if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
            define( 'WP_LOAD_IMPORTERS', true );
        }

        // Load Importer API
        require_once ABSPATH . 'wp-admin/includes/import.php';
        $importer_error = false;
        if ( ! class_exists( '\WP_Importer' ) ) {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            if ( file_exists( $class_wp_importer ) ) {
                require_once $class_wp_importer;
            } else {
                $importer_error = true;
            }
        }

        if ( ! class_exists( '\WP_Import' ) ) {
            $class_wp_import = XBOX_DIR . 'libs/wordpress-importer/wordpress-importer.php';
            if ( file_exists( $class_wp_import ) ) {
                require_once $class_wp_import;
            } else {
                $importer_error = true;
            }
        }

        if ( $importer_error ) {
            die( "Error on import" );
        } else {
            if ( is_file( $file ) ) {
                $wp_import                    = new \WP_Import();
                $wp_import->fetch_attachments = true;
                $wp_import->import( $file );
            } else {
                echo "The XML file containing the dummy content is not available or could not be read .. You might want to try to set the file permission to chmod 755.<br/>If this doesn't work please use the Wordpress importer and import the XML file (should be located in your download .zip: Sample Content folder) manually";
            }
        }
    }
}
