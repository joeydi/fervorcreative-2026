<?php
/**
 * Block registration and configuration.
 *
 * @package Fervor
 */

/**
 * Register every block in /blocks via its block.json.
 */
function fervor_register_blocks() {
	foreach ( glob( get_theme_file_path( 'blocks/*' ), GLOB_ONLYDIR ) as $block ) {
		register_block_type( $block );
	}
}
add_action( 'init', 'fervor_register_blocks' );

/**
 * Restrict the post editor inserter to a curated list of blocks.
 *
 * The site editor is left unrestricted so templates and template parts can
 * use core structural blocks.
 *
 * @param bool|string[]           $allowed Allowed block types.
 * @param WP_Block_Editor_Context $context Editor context.
 * @return bool|string[]
 */
function fervor_allowed_block_types( $allowed, $context ) {
	if ( 'core/edit-post' !== $context->name ) {
		return $allowed;
	}

	$fervor_blocks = array_map(
		static fn( $dir ) => 'fervor/' . basename( $dir ),
		glob( get_theme_file_path( 'blocks/*' ), GLOB_ONLYDIR )
	);

	return array_merge(
		array(
			'core/heading',
			'core/paragraph',
			'core/list',
			'core/list-item',
			'core/image',
			'core/embed',
			'core/html',
		),
		$fervor_blocks
	);
}
add_filter( 'allowed_block_types_all', 'fervor_allowed_block_types', 10, 2 );

/**
 * Add a "Fervor" category to the block inserter.
 *
 * @param array $categories Existing block categories.
 * @return array
 */
function fervor_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'fervor',
			'title' => 'Fervor',
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'fervor_block_category' );
