<?php
/**
 * Deal votes data and business logic.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles deal vote persistence and aggregate counters.
 */
class Deal_Votes {

	/**
	 * Vote table name without prefix.
	 *
	 * @var string
	 */
	const TABLE_SLUG = 'deal_votes';

	/**
	 * Upvotes meta key.
	 *
	 * @var string
	 */
	const META_UPVOTES = '_deal_upvotes';

	/**
	 * Downvotes meta key.
	 *
	 * @var string
	 */
	const META_DOWNVOTES = '_deal_downvotes';

	/**
	 * Score meta key.
	 *
	 * @var string
	 */
	const META_SCORE = '_deal_score';

	/**
	 * Hot score placeholder meta key.
	 *
	 * @var string
	 */
	const META_HOT_SCORE = '_deal_hot_score';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'save_post_deal', array( $this, 'ensure_meta_defaults' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Create or update the custom votes table.
	 *
	 * @return void
	 */
	public static function install_table() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			deal_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			vote_type TINYINT(4) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY deal_user_unique (deal_id, user_id),
			KEY deal_idx (deal_id),
			KEY user_idx (user_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Register deal vote meta fields.
	 *
	 * @return void
	 */
	public function register_meta() {
		$meta_keys = array(
			self::META_UPVOTES,
			self::META_DOWNVOTES,
			self::META_SCORE,
			self::META_HOT_SCORE,
		);

		foreach ( $meta_keys as $meta_key ) {
			register_post_meta(
				'deal',
				$meta_key,
				array(
					'show_in_rest'      => true,
					'single'            => true,
					'type'              => 'number',
					'sanitize_callback' => array( $this, 'sanitize_numeric_meta' ),
					'auth_callback'     => '__return_true',
				)
			);
		}
	}

	/**
	 * Sanitize numeric meta values.
	 *
	 * WordPress passes additional context args to sanitize callbacks, so this
	 * method accepts them even though only the first value is used.
	 *
	 * @param mixed  $meta_value    Meta value to sanitize.
	 * @param string $meta_key      Meta key.
	 * @param string $object_type   Object type.
	 * @param string $object_subtype Object subtype.
	 *
	 * @return float
	 */
	public function sanitize_numeric_meta( $meta_value, $meta_key = '', $object_type = '', $object_subtype = '' ) {
		return (float) $meta_value;
	}

	/**
	 * Ensure vote aggregate meta values exist.
	 *
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @return void
	 */
	public function ensure_meta_defaults( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) || 'deal' !== $post->post_type ) {
			return;
		}

		$this->maybe_add_meta( $post_id, self::META_UPVOTES, 0 );
		$this->maybe_add_meta( $post_id, self::META_DOWNVOTES, 0 );
		$this->maybe_add_meta( $post_id, self::META_SCORE, 0 );
		$this->maybe_add_meta( $post_id, self::META_HOT_SCORE, 0 );
	}

	/**
	 * Maybe add post meta default.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param mixed  $default Default value.
	 *
	 * @return void
	 */
	private function maybe_add_meta( $post_id, $meta_key, $default ) {
		$current = get_post_meta( $post_id, $meta_key, true );
		if ( '' === $current ) {
			add_post_meta( $post_id, $meta_key, $default, true );
		}
	}

	/**
	 * Get full table name.
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_SLUG;
	}

	/**
	 * Get current user's vote for a deal.
	 *
	 * @param int $deal_id Deal ID.
	 * @param int $user_id User ID.
	 *
	 * @return int
	 */
	public function get_user_vote( $deal_id, $user_id ) {
		global $wpdb;

		$vote = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT vote_type FROM ' . self::get_table_name() . ' WHERE deal_id = %d AND user_id = %d LIMIT 1',
				$deal_id,
				$user_id
			)
		);

		return is_null( $vote ) ? 0 : (int) $vote;
	}

	/**
	 * Process user vote state changes.
	 *
	 * @param int $deal_id Deal ID.
	 * @param int $user_id User ID.
	 * @param int $new_vote Requested vote (1 or -1).
	 *
	 * @return array|\WP_Error
	 */
	public function process_vote( $deal_id, $user_id, $new_vote ) {
		global $wpdb;

		$deal_id  = absint( $deal_id );
		$user_id  = absint( $user_id );
		$new_vote = (int) $new_vote;

		if ( ! in_array( $new_vote, array( -1, 1 ), true ) ) {
			return new \WP_Error( 'invalid_vote_type', __( 'Invalid vote type.', 'deals-plugin' ), array( 'status' => 400 ) );
		}

		if ( 'deal' !== get_post_type( $deal_id ) || 'publish' !== get_post_status( $deal_id ) ) {
			return new \WP_Error( 'invalid_deal', __( 'Invalid deal.', 'deals-plugin' ), array( 'status' => 404 ) );
		}

		$this->ensure_meta_defaults( $deal_id, get_post( $deal_id ) );

		$table        = self::get_table_name();
		$current_vote = $this->get_user_vote( $deal_id, $user_id );
		$final_vote   = $new_vote;

		if ( 0 === $current_vote ) {
			$inserted = $wpdb->insert(
				$table,
				array(
					'deal_id'   => $deal_id,
					'user_id'   => $user_id,
					'vote_type' => $new_vote,
				),
				array( '%d', '%d', '%d' )
			);

			if ( false === $inserted ) {
				return new \WP_Error( 'vote_insert_failed', __( 'Unable to save vote.', 'deals-plugin' ), array( 'status' => 500 ) );
			}

			if ( 1 === $new_vote ) {
				$this->increment_meta( $deal_id, self::META_UPVOTES, 1 );
			} else {
				$this->increment_meta( $deal_id, self::META_DOWNVOTES, 1 );
			}
		} elseif ( $current_vote === $new_vote ) {
			$deleted = $wpdb->delete(
				$table,
				array(
					'deal_id' => $deal_id,
					'user_id' => $user_id,
				),
				array( '%d', '%d' )
			);

			if ( false === $deleted ) {
				return new \WP_Error( 'vote_delete_failed', __( 'Unable to remove vote.', 'deals-plugin' ), array( 'status' => 500 ) );
			}

			if ( 1 === $current_vote ) {
				$this->increment_meta( $deal_id, self::META_UPVOTES, -1 );
			} else {
				$this->increment_meta( $deal_id, self::META_DOWNVOTES, -1 );
			}

			$final_vote = 0;
		} else {
			$updated = $wpdb->update(
				$table,
				array( 'vote_type' => $new_vote ),
				array(
					'deal_id' => $deal_id,
					'user_id' => $user_id,
				),
				array( '%d' ),
				array( '%d', '%d' )
			);

			if ( false === $updated ) {
				return new \WP_Error( 'vote_update_failed', __( 'Unable to update vote.', 'deals-plugin' ), array( 'status' => 500 ) );
			}

			if ( 1 === $new_vote ) {
				$this->increment_meta( $deal_id, self::META_UPVOTES, 1 );
				$this->increment_meta( $deal_id, self::META_DOWNVOTES, -1 );
			} else {
				$this->increment_meta( $deal_id, self::META_UPVOTES, -1 );
				$this->increment_meta( $deal_id, self::META_DOWNVOTES, 1 );
			}
		}

		$this->recalculate_score( $deal_id );
		$this->maybe_recalculate_hot_score( $deal_id );

		return $this->get_vote_payload( $deal_id, $final_vote );
	}

	/**
	 * Increment numeric meta value atomically.
	 *
	 * @param int    $deal_id Deal ID.
	 * @param string $meta_key Meta key.
	 * @param int    $step Step increment/decrement.
	 *
	 * @return void
	 */
	private function increment_meta( $deal_id, $meta_key, $step ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->postmeta}
				 SET meta_value = GREATEST(0, CAST(meta_value AS SIGNED) + %d)
				 WHERE post_id = %d AND meta_key = %s",
				$step,
				$deal_id,
				$meta_key
			)
		);
	}

	/**
	 * Recalculate and persist score meta.
	 *
	 * @param int $deal_id Deal ID.
	 *
	 * @return int
	 */
	public function recalculate_score( $deal_id ) {
		$upvotes   = (int) get_post_meta( $deal_id, self::META_UPVOTES, true );
		$downvotes = (int) get_post_meta( $deal_id, self::META_DOWNVOTES, true );
		$score     = $upvotes - $downvotes;

		update_post_meta( $deal_id, self::META_SCORE, $score );

		return $score;
	}

	/**
	 * Placeholder for future hot score computation.
	 *
	 * @param int $deal_id Deal ID.
	 *
	 * @return float
	 */
	public function maybe_recalculate_hot_score( $deal_id ) {
		$hot_score = (float) get_post_meta( $deal_id, self::META_HOT_SCORE, true );
		update_post_meta( $deal_id, self::META_HOT_SCORE, $hot_score );
		return $hot_score;
	}

	/**
	 * Build a response payload.
	 *
	 * @param int $deal_id Deal ID.
	 * @param int $current_user_vote Current user vote.
	 *
	 * @return array
	 */
	public function get_vote_payload( $deal_id, $current_user_vote ) {
		$upvotes   = (int) get_post_meta( $deal_id, self::META_UPVOTES, true );
		$downvotes = (int) get_post_meta( $deal_id, self::META_DOWNVOTES, true );
		$score     = (int) get_post_meta( $deal_id, self::META_SCORE, true );

		return array(
			'deal_id'            => (int) $deal_id,
			'upvotes'            => $upvotes,
			'downvotes'          => $downvotes,
			'score'              => $score,
			'current_user_vote'  => (int) $current_user_vote,
			'hot_score'          => (float) get_post_meta( $deal_id, self::META_HOT_SCORE, true ),
		);
	}

	/**
	 * Enqueue voting script on deal singular pages.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_singular( 'deal' ) ) {
			return;
		}

		wp_register_script(
			'deals-votes',
			DEALS_PLUGIN_URL . 'assets/js/deal-votes.js',
			array(),
			DEALS_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			'deals-votes',
			'dealsVotes',
			array(
				'restUrl'      => esc_url_raw( rest_url( 'deals-plugin/v1/vote' ) ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'isLoggedIn'   => is_user_logged_in(),
				'loginMessage' => __( 'Please log in to vote.', 'deals-plugin' ),
				'errorMessage' => __( 'Unable to process vote. Please try again.', 'deals-plugin' ),
			)
		);

		wp_enqueue_script( 'deals-votes' );
	}

	/**
	 * Helper for vote sorting query args.
	 *
	 * @param string $sort Sort mode.
	 *
	 * @return array
	 */
	public static function get_sort_query_args( $sort ) {
		$sort = sanitize_key( $sort );

		if ( 'score' === $sort ) {
			return array(
				'meta_key' => self::META_SCORE,
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			);
		}

		if ( 'hot' === $sort ) {
			return array(
				'meta_key' => self::META_HOT_SCORE,
				'orderby'  => 'meta_value_num',
				'order'    => 'DESC',
			);
		}

		return array(
			'orderby' => 'date',
			'order'   => 'DESC',
		);
	}
}
