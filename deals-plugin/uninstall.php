<?php
/**
 * Handles plugin uninstallation.
 *
 * @package DealsPlugin
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'deals_plugin_version' );
