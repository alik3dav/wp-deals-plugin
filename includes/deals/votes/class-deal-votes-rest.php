<?php
/**
 * REST controller for deal voting.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and handles deal vote REST routes.
 */
class Deal_Votes_REST {

	/**
	 * Vote service.
	 *
	 * @var Deal_Votes
	 */
	private $votes;

	/**
	 * Constructor.
	 *
	 * @param Deal_Votes $votes Vote service.
	 */
	public function __construct( Deal_Votes $votes ) {
		$this->votes = $votes;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'deals-plugin/v1',
			'/vote',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'vote' ),
				'permission_callback' => array( $this, 'can_vote' ),
				'args'                => array(
					'deal_id'   => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
						'validate_callback' => function( $value ) {
							return absint( $value ) > 0;
						},
					),
					'vote_type' => array(
						'required'          => true,
						'sanitize_callback' => 'intval',
						'validate_callback' => function( $value ) {
							return in_array( (int) $value, array( -1, 1 ), true );
						},
					),
				),
			)
		);
	}

	/**
	 * Permission check for voting.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return true|\WP_Error
	 */
	public function can_vote( \WP_REST_Request $request ) {
		if ( ! is_user_logged_in() ) {
			return new \WP_Error( 'rest_forbidden', __( 'Authentication required.', 'deals-plugin' ), array( 'status' => 401 ) );
		}

		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new \WP_Error( 'rest_invalid_nonce', __( 'Invalid security token.', 'deals-plugin' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Process vote action.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function vote( \WP_REST_Request $request ) {
		$deal_id   = absint( $request->get_param( 'deal_id' ) );
		$vote_type = (int) $request->get_param( 'vote_type' );
		$user_id   = get_current_user_id();

		$result = $this->votes->process_vote( $deal_id, $user_id, $vote_type );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new \WP_REST_Response( $result, 200 );
	}
}
