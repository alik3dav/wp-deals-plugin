<?php
/**
 * Template tags for deal voting UI.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register vote shortcode for Gutenberg shortcode block compatibility.
 *
 * @return void
 */
function deals_plugin_register_vote_shortcode() {
	add_shortcode( 'deal_voting', __NAMESPACE__ . '\\deals_plugin_render_vote_shortcode' );
}
add_action( 'init', __NAMESPACE__ . '\\deals_plugin_register_vote_shortcode' );

/**
 * Render vote shortcode.
 *
 * @param array<string,mixed> $atts Shortcode attributes.
 *
 * @return string
 */
function deals_plugin_render_vote_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'deal_id' => 0,
		),
		$atts,
		'deal_voting'
	);

	$deal_id = absint( $atts['deal_id'] );
	if ( $deal_id <= 0 ) {
		$deal_id = get_the_ID();
	}

	if ( ! $deal_id || 'deal' !== get_post_type( $deal_id ) ) {
		return '';
	}

	return deals_plugin_get_vote_markup( $deal_id );
}

/**
 * Echo deal vote component.
 *
 * @param int $deal_id Deal ID.
 *
 * @return void
 */
function deals_plugin_the_vote_component( $deal_id = 0 ) {
	echo deals_plugin_get_vote_markup( $deal_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Get deal vote component markup.
 *
 * @param int $deal_id Deal ID.
 *
 * @return string
 */
function deals_plugin_get_vote_markup( $deal_id = 0 ) {
	$deal_id = absint( $deal_id );

	if ( $deal_id <= 0 ) {
		$deal_id = get_the_ID();
	}

	if ( ! $deal_id || 'deal' !== get_post_type( $deal_id ) ) {
		return '';
	}

	$votes         = new Deal_Votes();
	$current_vote  = is_user_logged_in() ? $votes->get_user_vote( $deal_id, get_current_user_id() ) : 0;
	$upvotes       = (int) get_post_meta( $deal_id, Deal_Votes::META_UPVOTES, true );
	$downvotes     = (int) get_post_meta( $deal_id, Deal_Votes::META_DOWNVOTES, true );
	$score         = (int) get_post_meta( $deal_id, Deal_Votes::META_SCORE, true );
	$is_logged_in  = is_user_logged_in();
	$disabled_attr = $is_logged_in ? '' : ' disabled aria-disabled="true"';

	ob_start();
	?>
	<div
		class="deal-votes"
		data-deal-vote-component
		data-deal-id="<?php echo esc_attr( $deal_id ); ?>"
		data-current-vote="<?php echo esc_attr( $current_vote ); ?>"
	>
		<button class="deal-votes__button deal-votes__button--up<?php echo 1 === $current_vote ? ' is-active' : ''; ?>" data-vote-type="1" type="button"<?php echo $disabled_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php esc_html_e( 'Upvote', 'deals-plugin' ); ?>
		</button>
		<span class="deal-votes__score" data-vote-score><?php echo esc_html( (string) $score ); ?></span>
		<button class="deal-votes__button deal-votes__button--down<?php echo -1 === $current_vote ? ' is-active' : ''; ?>" data-vote-type="-1" type="button"<?php echo $disabled_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
			<?php esc_html_e( 'Downvote', 'deals-plugin' ); ?>
		</button>
		<span class="deal-votes__counts" data-vote-counts data-upvotes="<?php echo esc_attr( $upvotes ); ?>" data-downvotes="<?php echo esc_attr( $downvotes ); ?>">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: upvotes, 2: downvotes */
					__( '%1$d up / %2$d down', 'deals-plugin' ),
					$upvotes,
					$downvotes
				)
			);
			?>
		</span>
		<?php if ( ! $is_logged_in ) : ?>
			<p class="deal-votes__login-message"><?php esc_html_e( 'Please log in to vote.', 'deals-plugin' ); ?></p>
		<?php endif; ?>
		<p class="deal-votes__error" data-vote-error hidden></p>
	</div>
	<?php

	return (string) ob_get_clean();
}
