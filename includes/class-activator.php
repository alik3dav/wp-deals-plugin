<?php
/**
 * Plugin activation routines.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin activation.
 */
class Activator {

	/**
	 * Run activation tasks.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( false === get_option( 'deals_plugin_version' ) ) {
			add_option( 'deals_plugin_version', DEALS_PLUGIN_VERSION );
		} else {
			update_option( 'deals_plugin_version', DEALS_PLUGIN_VERSION );
		}
	}
}
