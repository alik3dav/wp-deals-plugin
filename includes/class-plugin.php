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

		$deal_category_taxonomy = new Deal_Category_Taxonomy();
		$deal_category_taxonomy->init();

		$deals_shortcode = new Deals_Shortcode();
		$deals_shortcode->init();

		$deals_settings_page = new Deals_Settings_Page();
		$deals_settings_page->init();

		$deal_votes = new Deal_Votes();
		$deal_votes->init();

		$deal_votes_rest = new Deal_Votes_REST( $deal_votes );
		$deal_votes_rest->init();

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
