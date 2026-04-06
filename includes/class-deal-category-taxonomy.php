<?php
/**
 * Deal category taxonomy registration.
 *
 * @package DealsPlugin
 */

namespace DealsPlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Deal Category taxonomy.
 */
class Deal_Category_Taxonomy {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Register the Deal Category taxonomy.
	 *
	 * @return void
	 */
	public function register() {
		$labels = array(
			'name'                       => _x( 'Deal Categories', 'Taxonomy general name', 'deals-plugin' ),
			'singular_name'              => _x( 'Deal Category', 'Taxonomy singular name', 'deals-plugin' ),
			'search_items'               => __( 'Search Deal Categories', 'deals-plugin' ),
			'popular_items'              => __( 'Popular Deal Categories', 'deals-plugin' ),
			'all_items'                  => __( 'All Deal Categories', 'deals-plugin' ),
			'parent_item'                => __( 'Parent Deal Category', 'deals-plugin' ),
			'parent_item_colon'          => __( 'Parent Deal Category:', 'deals-plugin' ),
			'edit_item'                  => __( 'Edit Deal Category', 'deals-plugin' ),
			'view_item'                  => __( 'View Deal Category', 'deals-plugin' ),
			'update_item'                => __( 'Update Deal Category', 'deals-plugin' ),
			'add_new_item'               => __( 'Add New Deal Category', 'deals-plugin' ),
			'new_item_name'              => __( 'New Deal Category Name', 'deals-plugin' ),
			'separate_items_with_commas' => __( 'Separate deal categories with commas', 'deals-plugin' ),
			'add_or_remove_items'        => __( 'Add or remove deal categories', 'deals-plugin' ),
			'choose_from_most_used'      => __( 'Choose from the most used deal categories', 'deals-plugin' ),
			'not_found'                  => __( 'No deal categories found.', 'deals-plugin' ),
			'no_terms'                   => __( 'No deal categories', 'deals-plugin' ),
			'items_list_navigation'      => __( 'Deal categories list navigation', 'deals-plugin' ),
			'items_list'                 => __( 'Deal categories list', 'deals-plugin' ),
			'back_to_items'              => __( '&larr; Back to Deal Categories', 'deals-plugin' ),
			'menu_name'                  => __( 'Deal Categories', 'deals-plugin' ),
			'name_field_description'     => __( 'The name is how it appears on your site.', 'deals-plugin' ),
			'parent_field_description'   => __( 'Assign a parent term to create a hierarchy.', 'deals-plugin' ),
			'slug_field_description'     => __( 'The “slug” is the URL-friendly version of the name.', 'deals-plugin' ),
			'desc_field_description'     => __( 'The description is not prominent by default.', 'deals-plugin' ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'publicly_queryable' => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'         => 'deal-category',
				'with_front'   => false,
				'hierarchical' => true,
			),
		);

		register_taxonomy( 'deal_category', array( 'deal' ), $args );
	}
}
