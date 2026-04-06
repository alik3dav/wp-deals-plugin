<?php
/**
 * Plugin deactivation routines.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin deactivation.
 */
class Deactivator {

	/**
	 * Run deactivation tasks.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Reserved for future deactivation cleanup.
	}
}
