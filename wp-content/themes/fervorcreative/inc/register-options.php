<?php
/**
 * ACF options pages.
 *
 * @package Fervor
 */

add_action(
	'acf/init',
	function () {
		acf_add_options_page(
			array(
				'page_title' => 'Site Options',
				'menu_title' => 'Site Options',
				'menu_slug'  => 'site-options',
				'capability' => 'edit_posts',
				'redirect'   => false,
				'icon_url'   => 'dashicons-admin-generic',
				'position'   => 59,
			)
		);
	}
);

/**
 * Populate social icon choices from the theme's /icons/social/ directory.
 *
 * @param array $field ACF field definition.
 * @return array
 */
function fervor_load_social_icon_choices( $field ) {
	$choices = array();

	foreach ( (array) glob( get_theme_file_path( 'icons/social/*.svg' ) ) as $file ) {
		$slug             = basename( $file, '.svg' );
		$choices[ $slug ] = ucwords( str_replace( '-', ' ', $slug ) );
	}

	ksort( $choices );
	$field['choices'] = $choices;

	return $field;
}
add_filter( 'acf/load_field/key=field_fc_options_social_icon', 'fervor_load_social_icon_choices' );
