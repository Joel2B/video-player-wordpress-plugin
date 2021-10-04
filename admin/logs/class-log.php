<?php
/**
 * CVP_Log Singleton Class
 *
 * @package \admin\class\CVP_Log
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CVP_Log' ) ) {
    /**
     * CVP_Log Singleton Class
     *
     * @since 1.3.9
     *
     * @return self::$instance
     */
    final class CVP_Log {
        /**
         * The instance of the CORE plugin
         *
         * @var instanceof CVP_Log $instance
         * @static
         */
        private static $instance;

        /**
         * The log file path
         *
         * @var string $log_file_path
         */
        private $log_file_path;

        /**
         * Singleton constructor
         *
         * @param string $log_file_path The log file path.
         *
         * @return void
         */
        private function __construct( $log_file_path ) {
            $this->log_file_path = $log_file_path;
        }

        /**
         * __clone method
         *
         * @return void
         */
        public function __clone() {
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'CVP_Log' ), '1.0' );}

        /**
         * __wakeup method
         *
         * @return void
         */
        public function __wakeup() {
            _doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'CVP_Log' ), '1.0' );
        }

        /**
         * Instance method
         *
         * @param string $log_file_path The log file path.
         *
         * @return self::$instance
         */
        public static function instance( $log_file_path ) {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof CVP_Log ) ) {
                self::$instance = new CVP_Log( $log_file_path );
            }
            return self::$instance;
        }

        /**
         * Get all logs as a string.
         *
         * @throws CVP_Exception If error while loading the log file.
         *
         * @return string Log file as an array. 1 log line = 1 array row.
         */
        private function get_raw_logs() {
            $raw_logs = array();
            if ( file_exists( $this->log_file_path ) ) {
                $raw_logs = file( $this->log_file_path );
            }
            return $raw_logs;
        }

        /**
         * Get all logs as an array.
         *
         * @return array Log file as an array. 1 log line = 1 array row.
         */
        public function get_logs() {
            $output_logs = array();
            $lines       = $this->get_raw_logs();
            foreach ( (array) $lines as $line ) {
                if ( ! empty( $line ) ) {
                    $output_logs[] = $this->prepare_log_line_to_array( $line );
                }
            }
            return $output_logs;
        }

        /**
         * Prepare an array with all data from a given log line string.
         *
         * @param string $log_line The line to prepare.
         * @return array The array with the line data prepared.
         */
        private function prepare_log_line_to_array( $log_line ) {
            preg_match_all( '/\[([^\]]*)\]/', $log_line, $line_data );
            $message = explode( ']', $log_line );
            return array(
                'date'      => isset( $line_data[1][0] ) ? (string) $line_data[1][0] : 'undefined',
                'type'      => isset( $line_data[1][1] ) ? (string) $line_data[1][1] : 'undefined',
                'file_uri'  => isset( $line_data[1][2] ) ? (string) $line_data[1][2] : 'undefined',
                'file_line' => isset( $line_data[1][3] ) ? (int) $line_data[1][3] : 0,
                'code'      => isset( $line_data[1][4] ) ? (int) $line_data[1][4] : 0,
                'message'   => end( $message ),
            );
        }

        /**
         * Write a new line of log in the log file.
         *
         * @param string $type       Log type.
         * @param string $message    Log message.
         * @param int    $code       Log code.
         * @param string $file_uri   Log file uri.
         * @param int    $file_line  Log file line.
         *
         * @return void
         */
        public function write_log( $type, $message, $code = 0, $file_uri = null, $file_line = null ) {
            $raw_logs = $this->get_raw_logs();
            // set $file_uri and / or $file_line if null.
            // phpcs:disable
            $backtrace = debug_backtrace();
            // phpcs:enable
            $file_uri  = null === $file_uri ? $backtrace[0]['file'] : $file_uri;
            $file_line = null === $file_line ? $backtrace[0]['line'] : $file_uri;

            $file_uri = $this->shorten_file_uri( $file_uri );

            // prepare new line to write.
            $new_log_line = $this->prepare_log_line( current_time( 'Y-m-d H:i:s' ), $type, $message, $file_uri, $file_line, $code );
            $raw_logs[]   = $new_log_line . "\n";

            // write the logs with the new line.
            // phpcs:disable
            file_put_contents( $this->log_file_path, $raw_logs );
            // phpcs:enable
        }

        /**
         * Create a wp-content relative path of a given $file_uri path.
         *
         * @param string $file_uri The file uri to shorten.
         * @return string The wp-content relative path of the given $file_uri.
         */
        private function shorten_file_uri( $file_uri ) {
            $wp_content_index = strpos( $file_uri, 'wp-content' );
            if ( false !== $wp_content_index ) {
                $shorten_file_uri = '..' . substr( $file_uri, $wp_content_index - 1 );
            }
            return $shorten_file_uri;
        }

        /**
         * Prepare a log line with all data from given log params.
         *
         * @param string $date       Log date.
         * @param string $type       Log type.
         * @param string $message    Log message.
         * @param string $file_uri   Log file uri.
         * @param int    $file_line  Log file line.
         * @param int    $code       Log code.
         *
         * @return string The log line as a string.
         */
        private function prepare_log_line( $date, $type, $message, $file_uri, $file_line, $code ) {
            $log_data = array( $date, $type, $file_uri, $file_line, $code );
            $log_line = '[' . implode( '][', $log_data ) . ']' . $message;
            return $log_line;
        }

        /**
         * Delete all logs in log file.
         *
         * @return void
         */
        public function delete_logs() {
            if ( file_exists( $this->log_file_path ) ) {
                file_put_contents( $this->log_file_path, '' );
            }
        }
    }
    /**
     * Create the CVP_Log instance in a function and call it.
     *
     * @return CVP_Log::instance();
     */
    function cvp_log() {
        return CVP_Log::instance( CVP_LOG_FILE );
    }

    cvp_log();
}
