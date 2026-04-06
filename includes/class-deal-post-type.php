<?php
/**
 * Deal custom post type registration.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Deal custom post type.
 */
class Deal_Post_Type {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the Deal custom post type.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                  => _x( 'Deals', 'Post type general name', 'deals-plugin' ),
			'singular_name'         => _x( 'Deal', 'Post type singular name', 'deals-plugin' ),
			'menu_name'             => _x( 'Deals', 'Admin Menu text', 'deals-plugin' ),
			'name_admin_bar'        => _x( 'Deal', 'Add New on Toolbar', 'deals-plugin' ),
			'add_new'               => __( 'Add New', 'deals-plugin' ),
			'add_new_item'          => __( 'Add New Deal', 'deals-plugin' ),
			'new_item'              => __( 'New Deal', 'deals-plugin' ),
			'edit_item'             => __( 'Edit Deal', 'deals-plugin' ),
			'view_item'             => __( 'View Deal', 'deals-plugin' ),
			'all_items'             => __( 'All Deals', 'deals-plugin' ),
			'search_items'          => __( 'Search Deals', 'deals-plugin' ),
			'parent_item_colon'     => __( 'Parent Deals:', 'deals-plugin' ),
			'not_found'             => __( 'No deals found.', 'deals-plugin' ),
			'not_found_in_trash'    => __( 'No deals found in Trash.', 'deals-plugin' ),
			'archives'              => __( 'Deal Archives', 'deals-plugin' ),
			'attributes'            => __( 'Deal Attributes', 'deals-plugin' ),
			'insert_into_item'      => __( 'Insert into deal', 'deals-plugin' ),
			'uploaded_to_this_item' => __( 'Uploaded to this deal', 'deals-plugin' ),
			'filter_items_list'     => __( 'Filter deals list', 'deals-plugin' ),
			'items_list_navigation' => __( 'Deals list navigation', 'deals-plugin' ),
			'items_list'            => __( 'Deals list', 'deals-plugin' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'deals',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-tickets-alt',
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'comments', 'revisions', 'custom-fields' ),
		);

		register_post_type( 'deal', $args );
	}
}
