<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Fervor
 */

/**
 * Register the Project post type and Service taxonomy.
 *
 * Projects live at /work/{slug}. The archive is disabled so the Work page
 * (slug `work`) controls the listing.
 */
function fervor_register_post_types() {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'               => 'Projects',
				'singular_name'      => 'Project',
				'add_new_item'       => 'Add New Project',
				'edit_item'          => 'Edit Project',
				'new_item'           => 'New Project',
				'view_item'          => 'View Project',
				'search_items'       => 'Search Projects',
				'not_found'          => 'No projects found',
				'not_found_in_trash' => 'No projects found in Trash',
				'all_items'          => 'All Projects',
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_rest'  => true,
			'hierarchical'  => false,
			'rewrite'       => array(
				'slug'       => 'work',
				'with_front' => false,
			),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions' ),
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-portfolio',
		)
	);

	register_taxonomy(
		'service',
		'project',
		array(
			'labels'            => array(
				'name'          => 'Services',
				'singular_name' => 'Service',
				'add_new_item'  => 'Add New Service',
				'edit_item'     => 'Edit Service',
				'all_items'     => 'All Services',
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
		)
	);
}
add_action( 'init', 'fervor_register_post_types' );
