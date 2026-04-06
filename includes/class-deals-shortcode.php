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
			echo '<div class="deals-wrapper"><p>' . esc_html__( 'No deals available at the moment.', 'deals-plugin' ) . '</p></div>';

			return (string) ob_get_clean();
		}

		echo '<div class="deals-wrapper">';
		echo '<ul class="deals-list">';

		while ( $query->have_posts() ) {
			$query->the_post();

			echo '<li class="deals-item">';
			echo '<h3 class="deals-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';

			$excerpt = get_the_excerpt();

			if ( '' !== $excerpt ) {
				echo '<div class="deals-excerpt">' . wp_kses_post( wpautop( $excerpt ) ) . '</div>';
			}

			echo '<p class="deals-link"><a href="' . esc_url( get_permalink() ) . '">' . esc_html__( 'View Deal', 'deals-plugin' ) . '</a></p>';
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
