<?php
/**
 * Deals shortcode registration and rendering.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles deals shortcode output.
 */
class Deals_Shortcode {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Register shortcode tag.
	 *
	 * @return void
	 */
	public function register_shortcode() {
		add_shortcode( 'deals', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'deals-plugin-styles',
			DEALS_PLUGIN_URL . 'assets/css/deals.css',
			array(),
			DEALS_PLUGIN_VERSION
		);
	}

	/**
	 * Render the deals shortcode output.
	 *
	 * @param array<string, mixed> $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render_shortcode( $atts ) {
		$attributes = $this->sanitize_attributes( $atts );
		$query      = new \WP_Query( $this->get_query_args( $attributes ) );

		ob_start();

		if ( ! $query->have_posts() ) {
			echo '<div class="dp-deals"><p>' . esc_html__( 'No deals available at the moment.', 'deals-plugin' ) . '</p></div>';

			return (string) ob_get_clean();
		}

		echo '<div class="dp-deals">';
		echo '<ul class="dp-deals-list">';

		while ( $query->have_posts() ) {
			$query->the_post();

			echo '<li class="dp-deal-card">';
			echo '<div class="dp-deal-main">';

			echo '<div class="dp-deal-image">';
			if ( has_post_thumbnail() ) {
				echo get_the_post_thumbnail( get_the_ID(), 'medium' );
			} else {
				echo '<span class="dp-deal-image-placeholder">' . esc_html__( 'No Image', 'deals-plugin' ) . '</span>';
			}
			echo '</div>';

			echo '<div class="dp-deal-content">';
			echo '<h3 class="dp-deal-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
			echo '<p class="dp-deal-store">' . esc_html__( 'Store Name', 'deals-plugin' ) . '</p>';
			echo '<div class="dp-deal-price">';
			echo '<span class="dp-deal-price-current">$49</span>';
			echo '<span class="dp-deal-price-old">$79</span>';
			echo '<span class="dp-deal-price-discount">-38%</span>';
			echo '</div>';
			echo '</div>';

			echo '<div class="dp-deal-actions">';
			echo '<p class="dp-deal-time">5h</p>';
			echo '<a class="dp-deal-button" href="' . esc_url( get_permalink() ) . '">' . esc_html__( 'See offer', 'deals-plugin' ) . '</a>';
			echo '</div>';

			echo '</div>';

			echo '<div class="dp-deal-footer">';
			echo '<span class="dp-deal-user">' . esc_html__( 'By Deals Team', 'deals-plugin' ) . '</span>';
			echo '<span class="dp-deal-comments">' . esc_html__( '12 comments', 'deals-plugin' ) . '</span>';
			echo '</div>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Sanitize and validate shortcode attributes.
	 *
	 * @param array<string, mixed> $atts Raw shortcode attributes.
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_attributes( $atts ) {
		$defaults = array(
			'limit'   => 6,
			'order'   => 'DESC',
			'orderby' => 'date',
		);

		$attributes = shortcode_atts( $defaults, $atts, 'deals' );

		$limit = absint( $attributes['limit'] );
		if ( 0 === $limit ) {
			$limit = (int) $defaults['limit'];
		}

		$order = strtoupper( sanitize_text_field( (string) $attributes['order'] ) );
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = $defaults['order'];
		}

		$allowed_orderby = array( 'date', 'title', 'modified', 'ID', 'name', 'rand' );
		$orderby         = sanitize_key( (string) $attributes['orderby'] );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = $defaults['orderby'];
		}

		return array(
			'limit'   => $limit,
			'order'   => $order,
			'orderby' => $orderby,
		);
	}

	/**
	 * Build query args from sanitized shortcode attributes.
	 *
	 * @param array<string, mixed> $attributes Sanitized attributes.
	 *
	 * @return array<string, mixed>
	 */
	private function get_query_args( $attributes ) {
		return array(
			'post_type'           => 'deal',
			'post_status'         => 'publish',
			'posts_per_page'      => (int) $attributes['limit'],
			'orderby'             => (string) $attributes['orderby'],
			'order'               => (string) $attributes['order'],
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);
	}
}
