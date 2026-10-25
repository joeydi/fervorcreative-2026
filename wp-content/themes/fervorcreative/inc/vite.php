<?php
/**
 * Vite asset loading.
 *
 * While `npm run dev` is running, Vite writes `dist/hot` containing the dev
 * server URL and assets are served from there with HMR. Otherwise assets are
 * resolved from the build manifest in `dist/.vite/manifest.json`.
 *
 * @package Fervor
 */

/**
 * Get the Vite dev server URL, or an empty string when it isn't running.
 *
 * @return string
 */
function fervor_vite_dev_server_url() {
	static $url = null;

	if ( null === $url ) {
		$hot = get_theme_file_path( 'dist/hot' );
		$url = file_exists( $hot ) ? untrailingslashit( trim( file_get_contents( $hot ) ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	return $url;
}

/**
 * Get the parsed Vite build manifest.
 *
 * @return array
 */
function fervor_vite_manifest() {
	static $manifest = null;

	if ( null === $manifest ) {
		$path     = get_theme_file_path( 'dist/.vite/manifest.json' );
		$manifest = file_exists( $path ) ? (array) json_decode( file_get_contents( $path ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	return $manifest;
}

/**
 * Script handles that must be output as ES modules.
 *
 * @param string|null $handle Handle to register, or null to read the list.
 * @return string[]
 */
function fervor_vite_module_handles( $handle = null ) {
	static $handles = array();

	if ( $handle ) {
		$handles[] = $handle;
	}

	return $handles;
}

/**
 * Enqueue a Vite entry point and its CSS.
 *
 * @param string $handle Script handle.
 * @param string $entry  Entry path relative to the theme root, e.g. 'src/js/main.js'.
 */
function fervor_vite_enqueue( $handle, $entry ) {
	$dev_server = fervor_vite_dev_server_url();

	if ( $dev_server ) {
		wp_enqueue_script( 'fervor-vite-client', $dev_server . '/@vite/client', array(), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_enqueue_script( $handle, $dev_server . '/' . $entry, array( 'fervor-vite-client' ), null, false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		fervor_vite_module_handles( 'fervor-vite-client' );
		fervor_vite_module_handles( $handle );
		return;
	}

	$chunk = fervor_vite_manifest()[ $entry ] ?? null;

	if ( ! $chunk ) {
		return;
	}

	$dist = get_theme_file_uri( 'dist/' );

	foreach ( fervor_vite_chunk_css( $entry ) as $index => $css ) {
		wp_enqueue_style( $handle . '-' . $index, $dist . $css, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- filenames are content-hashed.
	}

	wp_enqueue_script( $handle, $dist . $chunk['file'], array(), null, array( 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- filenames are content-hashed.
	fervor_vite_module_handles( $handle );
}

/**
 * Collect the CSS files for a manifest chunk, including CSS from imported chunks.
 *
 * @param string $key  Manifest key.
 * @param array  $seen Keys already visited.
 * @return string[]
 */
function fervor_vite_chunk_css( $key, &$seen = array() ) {
	$chunk = fervor_vite_manifest()[ $key ] ?? null;

	if ( ! $chunk || isset( $seen[ $key ] ) ) {
		return array();
	}

	$seen[ $key ] = true;
	$css          = array();

	foreach ( $chunk['imports'] ?? array() as $import ) {
		$css = array_merge( $css, fervor_vite_chunk_css( $import, $seen ) );
	}

	return array_values( array_unique( array_merge( $css, $chunk['css'] ?? array() ) ) );
}

/**
 * Output Vite scripts as ES modules.
 *
 * @param string $tag    Script tag HTML.
 * @param string $handle Script handle.
 * @return string
 */
function fervor_vite_script_type( $tag, $handle ) {
	if ( in_array( $handle, fervor_vite_module_handles(), true ) ) {
		$tag = str_replace( '<script ', '<script type="module" ', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'fervor_vite_script_type', 10, 2 );
