<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class CVP_Product_Uploader {

    /**
     * The method to call (install/update)
     *
     * @var string
     */
    private $method;

    /**
     * The product SKU we want to install/update
     *
     * @var string;
     */
    private $product;

    /**
     * The type of product we want to install/update (theme/plugin)
     *
     * @var string
     */
    private $type;

    /**
     * Upload product to the server.
     *
     * @param string $type     The type of product to upload.
     * @param string $method   The method to call (install/update).
     * @param string $product  The product SKU.
     *
     * @return mixed The response of the called method or false if an issue occured.
     */
    public function upload_product( $type, $method, $product ) {
        $this->product = $product;
        $this->method  = $method;
        $this->type    = $type;
        $response      = false;

        switch ( $type ) {
            case 'plugin':
                if ( ! current_user_can( 'install_plugins' ) ) {
                    return __( 'You do not have sufficient permissions to install themes for this site.' );
                }
                $response = $this->install_plugin();
                break;
            default:
                break;
        }
        return $response;
    }

    /**
     * Install Plugin function.
     *
     * @return mixed Installation response if succes, bool false if not.
     */
    private function install_plugin() {
        require_once ABSPATH . 'wp-admin/includes/admin.php';
        $upgrader = new Plugin_Upgrader( new Plugin_Quiet_Skin() );
        switch ( $this->method ) {
            case 'install':
                ob_start();
                $results = $upgrader->install( $this->product['package'] );
                $data    = ob_get_contents();
                ob_clean();
                break;
            case 'upgrade':
                ob_start();
                $results = $upgrader->bulk_upgrade( array( $this->product['file_path'] ) );
                $data    = ob_get_contents();
                ob_clean();
                break;
            default:
                return false;
        }

        if ( ! $results ) {
            return $data;
        } else {
            return true;
        }
    }
}

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
if ( ! is_plugin_active( 'wp-script-core/wp-script-core.php' ) ) {
    class Plugin_Quiet_Skin extends Plugin_Installer_Skin {
        public function feedback( $string, ...$args ) {
            // just keep it quiet.
        }
    }
}
