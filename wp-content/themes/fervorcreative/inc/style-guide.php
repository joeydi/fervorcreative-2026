<?php
/**
 * Style guide helpers.
 *
 * The Style Guide block lists design tokens straight from src/scss/_tokens.scss
 * so the page stays in sync as tokens are added or changed.
 *
 * @package Fervor
 */

/**
 * Get design tokens from _tokens.scss, grouped by the comment above each run.
 *
 * Values are as declared, with SCSS interpolation unwrapped, e.g.
 * `--space-gutter` => `fluid(20, 48)`.
 *
 * @param string $prefix Only include tokens starting with this prefix, e.g. '--color-'.
 * @return array<string, array<string, string>> Group label => token name => declared value.
 */
function fervor_style_guide_tokens( $prefix = '--' ) {
	static $groups = null;

	if ( null === $groups ) {
		$groups = array();
		$path   = get_theme_file_path( 'src/scss/_tokens.scss' );
		$source = file_exists( $path ) ? file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$group  = '';

		foreach ( preg_split( '/\R/', $source ) as $line ) {
			if ( preg_match( '#^\s*//\s*(.+)$#', $line, $match ) ) {
				$group = trim( $match[1] );
				continue;
			}

			if ( preg_match( '/^\s*(--[\w-]+)\s*:\s*(.+?);/', $line, $match ) ) {
				$groups[ $group ][ $match[1] ] = preg_replace( '/^#\{(.*)\}$/', '$1', $match[2] );
			}
		}
	}

	$filtered = array();

	foreach ( $groups as $label => $tokens ) {
		$tokens = array_filter(
			$tokens,
			static fn( $name ) => str_starts_with( $name, $prefix ),
			ARRAY_FILTER_USE_KEY
		);

		if ( $tokens ) {
			$filtered[ $label ] = $tokens;
		}
	}

	return $filtered;
}

/**
 * Format a declared token value for display, e.g. `fluid(20, 48)` => `20–48px fluid`.
 *
 * @param string $value Declared value.
 * @return string
 */
function fervor_style_guide_value( $value ) {
	return preg_replace( '/^fluid\((\d+),\s*(\d+)\)$/', '$1–$2px fluid', $value );
}

/**
 * Keep the style guide out of search results.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function fervor_style_guide_robots( $robots ) {
	if ( is_page( 'style-guide' ) ) {
		$robots['noindex'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'fervor_style_guide_robots' );
