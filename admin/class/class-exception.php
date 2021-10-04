<?php


// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CVP_Exception' ) ) {
	class CVP_Exception extends Exception {
		/**
		 * Throw error.
		 * - write logs in all cases
		 * - display or not an admin notice depending on CVP_DEBUG bool const.
		 *
		 * @return void
		 */
		public function throw_exception() {
			$this->write_cvp_log();
			if ( true === CVP_DEBUG ) {
				$this->display_admin_notice();
			}
		}

		/**
		 * Write current exception into CVP logs.
		 *
		 * @return void
		 */
		public function write_cvp_log() {
			cvp_log()->write_log( 'error', $this->getMessage(), $this->getCode(), $this->getFile(), $this->getLine() );
		}

		/**
		 * Display admin notice.
		 *
		 * @return void
		 */
		public function display_admin_notice() {
			?>
				<div class="notice notice-error is-dismissible">
					<p><i><?php echo esc_html( $this->getMessage() ); ?></i> <small>( Error Code: <?php echo esc_html( $this->getCode() ); ?> )</small></p>
				</div>
			<?php
		}
	}
}
