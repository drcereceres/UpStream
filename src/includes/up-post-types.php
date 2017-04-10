<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @return void
 */
function upstream_setup_post_types() {

	$project_slug 	= defined( 'UPSTREAM_PROJECT_SLUG' ) ? UPSTREAM_PROJECT_SLUG : 'project';
	$client_slug 	= defined( 'UPSTREAM_CLIENT_SLUG' ) ? UPSTREAM_CLIENT_SLUG : 'client';

	$project_labels =  apply_filters( 'upstream_project_labels', array(
		'name'                  => _x( '%2$s', 'project post type name', 'upstream' ),
		'singular_name'         => _x( '%1$s', 'singular project post type name', 'upstream' ),
		'add_new'               => __( 'New %1s', 'upstream' ),
		'add_new_item'          => __( 'Add New %1$s', 'upstream' ),
		'edit_item'             => __( 'Edit %1$s', 'upstream' ),
		'new_item'              => __( 'New %1$s', 'upstream' ),
		'all_items'             => __( '%2$s', 'upstream' ),
		'view_item'             => __( 'View %1$s', 'upstream' ),
		'search_items'          => __( 'Search %2$s', 'upstream' ),
		'not_found'             => __( 'No %2$s found', 'upstream' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'upstream' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'project post type menu name', 'upstream' ),
		'featured_image'        => __( '%1$s Image', 'upstream' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'upstream' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'upstream' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'upstream' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'upstream' ),
		'items_list_navigation' => __( '%2$s list navigation', 'upstream' ),
		'items_list'            => __( '%2$s list', 'upstream' ),
	) );

	foreach ( $project_labels as $key => $value ) {
		$project_labels[ $key ] = sprintf( $value, upstream_project_label(), upstream_project_label_plural() );
	}

	$project_args = array(
		'labels'             => $project_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'       	 => 'dashicons-arrow-up-alt',
		'menu_position'      => 56,
		'query_var'          => true,
		'rewrite'            => array('slug' => $project_slug, 'with_front' => false),
		'capability_type'    => 'project',
		'map_meta_cap' 		 => true,
		'has_archive'        => 'projects',
		'hierarchical'       => false,
		'supports'           => apply_filters( 'upstream_project_supports', array( 'title', 'revisions', 'author' ) ),
	);
	register_post_type( 'project', apply_filters( 'upstream_project_post_type_args', $project_args ) );


	/* Client Post Type */
	$client_labels =  apply_filters( 'upstream_client_labels', array(
		'name'                  => _x( '%2$s', 'project post type name', 'upstream' ),
		'singular_name'         => _x( '%1$s', 'singular project post type name', 'upstream' ),
		'add_new'               => __( 'New %1s', 'upstream' ),
		'add_new_item'          => __( 'Add New %1$s', 'upstream' ),
		'edit_item'             => __( 'Edit %1$s', 'upstream' ),
		'new_item'              => __( 'New %1$s', 'upstream' ),
		'all_items'             => __( '%2$s', 'upstream' ),
		'view_item'             => __( 'View %1$s', 'upstream' ),
		'search_items'          => __( 'Search %2$s', 'upstream' ),
		'not_found'             => __( 'No %2$s found', 'upstream' ),
		'not_found_in_trash'    => __( 'No %2$s found in Trash', 'upstream' ),
		'parent_item_colon'     => '',
		'menu_name'             => _x( '%2$s', 'project post type menu name', 'upstream' ),
		'featured_image'        => __( '%1$s Image', 'upstream' ),
		'set_featured_image'    => __( 'Set %1$s Image', 'upstream' ),
		'remove_featured_image' => __( 'Remove %1$s Image', 'upstream' ),
		'use_featured_image'    => __( 'Use as %1$s Image', 'upstream' ),
		'filter_items_list'     => __( 'Filter %2$s list', 'upstream' ),
		'items_list_navigation' => __( '%2$s list navigation', 'upstream' ),
		'items_list'            => __( '%2$s list', 'upstream' ),
	) );

	foreach ( $client_labels as $key => $value ) {
		$client_labels[ $key ] = sprintf( $value, upstream_client_label(), upstream_client_label_plural() );
	}

	$client_args = array(
		'labels'             => $client_labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => false,
		'query_var'          => true,
		'rewrite'            => array('slug' => $client_slug, 'with_front' => false),
		'capability_type'    => 'client',
		'map_meta_cap' 		 => true,
		'has_archive'        => false,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'upstream_client_supports', array( 'title', 'revisions' ) ),
	);
	register_post_type( 'client', apply_filters( 'upstream_client_post_type_args', $client_args ) );

}
add_action( 'init', 'upstream_setup_post_types', 1 );

/**
 * Registers the custom taxonomies for the projects custom post type
 *
 * @since 1.0
 * @return void
*/
function upstream_setup_taxonomies() {

	$slug = defined( 'UPSTREAM_CAT_SLUG' ) ? UPSTREAM_CAT_SLUG : 'projects';

	/** Categories */
	$category_labels = array(
		'name'              => _x( 'Category', 'taxonomy general name', 'upstream' ),
		'singular_name'     => _x( 'Category', 'taxonomy singular name', 'upstream' ),
		'search_items'      => sprintf( __( 'Search %s Categories', 'upstream' ), upstream_project_label() ),
		'all_items'         => sprintf( __( 'All %s Categories', 'upstream' ), upstream_project_label() ),
		'parent_item'       => sprintf( __( 'Parent %s Category', 'upstream' ), upstream_project_label() ),
		'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'upstream' ), upstream_project_label() ),
		'edit_item'         => sprintf( __( 'Edit %s Category', 'upstream' ), upstream_project_label() ),
		'update_item'       => sprintf( __( 'Update %s Category', 'upstream' ), upstream_project_label() ),
		'add_new_item'      => sprintf( __( 'Add New %s Category', 'upstream' ), upstream_project_label() ),
		'new_item_name'     => sprintf( __( 'New %s Category Name', 'upstream' ), upstream_project_label() ),
		'menu_name'         => __( 'Categories', 'upstream' ),
	);

	$category_args = apply_filters( 'upstream_project_category_args', array(
			'hierarchical' => true,
			'labels'       => apply_filters('_upstream_project_category_labels', $category_labels),
			'show_ui'      => true,
			'show_admin_column' => true,
			'query_var'    => 'project_category',
			'rewrite'      => array('slug' => $slug . '/category', 'with_front' => false, 'hierarchical' => true ),
			'capabilities' => array( 'manage_terms' => 'manage_project_terms','edit_terms' => 'edit_project_terms','assign_terms' => 'assign_project_terms','delete_terms' => 'delete_project_terms' )
		)
	);
	register_taxonomy( 'project_category', array('project'), $category_args );
	register_taxonomy_for_object_type( 'project_category', 'project' );

}
add_action( 'init', 'upstream_setup_taxonomies', 0 );