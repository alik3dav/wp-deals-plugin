<?php
/**
 * Deals settings page registration and rendering.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Deals settings page in admin.
 */
class Deals_Settings_Page {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Register deals settings submenu page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'edit.php?post_type=deal',
			esc_html__( 'Deals Settings', 'deals-plugin' ),
			esc_html__( 'Settings', 'deals-plugin' ),
			'manage_options',
			'deals-settings',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render deals settings page.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Deals Settings', 'deals-plugin' ); ?></h1>
			<p><?php echo esc_html__( 'Use this page for quick shortcode references when displaying deals on the front end.', 'deals-plugin' ); ?></p>

			<h2><?php echo esc_html__( 'Shortcode Usage', 'deals-plugin' ); ?></h2>
			<p>
				<code>[deals]</code>
			</p>

			<p><?php echo esc_html__( 'Available attributes:', 'deals-plugin' ); ?></p>
			<ul>
				<li><code>limit</code> — <?php echo esc_html__( 'Number of deals to display. Default: 6', 'deals-plugin' ); ?></li>
				<li><code>order</code> — <?php echo esc_html__( 'Sort order: ASC or DESC. Default: DESC', 'deals-plugin' ); ?></li>
				<li><code>orderby</code> — <?php echo esc_html__( 'Sort field: date, title, modified, ID, name, or rand. Default: date', 'deals-plugin' ); ?></li>
			</ul>

			<h2><?php echo esc_html__( 'Examples', 'deals-plugin' ); ?></h2>
			<p><code>[deals limit="3"]</code></p>
			<p><code>[deals limit="10" order="ASC" orderby="title"]</code></p>
		</div>
		<?php
	}
}
