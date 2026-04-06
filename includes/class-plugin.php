<?php
/**
 * Core plugin bootstrap class.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initializes plugin hooks.
 */
class Plugin {

	/**
	 * Register startup hooks.
	 *
	 * @return void
	 */
	public function init() {
		$deal_post_type = new Deal_Post_Type();
		$deal_post_type->init();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin translations.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'deals-plugin', false, dirname( DEALS_PLUGIN_BASENAME ) . '/languages' );
	}
}
