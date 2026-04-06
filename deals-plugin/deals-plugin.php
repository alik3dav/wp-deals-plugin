<?php
/**
 * Plugin Name:       Deals Plugin
 * Plugin URI:        https://example.com/
 * Description:       Bootstrap architecture for a scalable deals, discounts, and coupons plugin.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Deals Plugin Team
 * Author URI:        https://example.com/
 * Text Domain:       deals-plugin
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package DealsPlugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEALS_PLUGIN_VERSION', '1.0.0' );
define( 'DEALS_PLUGIN_FILE', __FILE__ );
define( 'DEALS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DEALS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DEALS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once DEALS_PLUGIN_PATH . 'includes/class-activator.php';
require_once DEALS_PLUGIN_PATH . 'includes/class-deactivator.php';
require_once DEALS_PLUGIN_PATH . 'includes/class-plugin.php';

/**
 * Runs plugin activation tasks.
 *
 * @return void
 */
function deals_plugin_activate() {
	DealsPlugin\Activator::activate();
}
register_activation_hook( __FILE__, 'deals_plugin_activate' );

/**
 * Runs plugin deactivation tasks.
 *
 * @return void
 */
function deals_plugin_deactivate() {
	DealsPlugin\Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deals_plugin_deactivate' );

/**
 * Boots the plugin.
 *
 * @return void
 */
function deals_plugin_run() {
	$plugin = new DealsPlugin\Plugin();
	$plugin->init();
}
deals_plugin_run();
