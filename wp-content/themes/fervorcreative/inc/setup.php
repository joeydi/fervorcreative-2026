<?php
/**
 * Theme setup, asset loading, and ACF configuration.
 *
 * @package Fervor
 */

/**
 * Register theme supports.
 */
function fervor_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	remove_theme_support( 'core-block-patterns' );

	register_nav_menus(
		array(
			'primary' => __( 'Main Menu', 'fervorcreative' ),
		)
	);
}
add_action( 'after_setup_theme', 'fervor_setup' );

/**
 * Adobe Fonts kit: Kepler and Good Condensed.
 */
const FERVOR_TYPEKIT_URL = 'https://use.typekit.net/olo8rkh.css';

/**
 * Enqueue front-end assets.
 */
function fervor_enqueue_assets() {
	wp_enqueue_style( 'fervor-typekit', FERVOR_TYPEKIT_URL, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- versioned by Adobe.
	fervor_vite_enqueue( 'fervor-main', 'src/js/main.js' );
}
add_action( 'wp_enqueue_scripts', 'fervor_enqueue_assets' );

/**
 * Carry the header's scrolled state across page view transitions.
 *
 * Leaving a scrolled page, the next page's header starts scrolled and drops
 * into place (header.js toggles the same class) as the new page fades in.
 * It's inline in the head because `pagereveal` can fire before main.js runs.
 */
function fervor_print_header_reveal_script() {
	$script = <<<'JS'
(() => {
	const key = 'fervor-header-scrolled';
	const header = () => document.querySelector('[data-header]');

	addEventListener('pageswap', (event) => {
		if (event.viewTransition && header()?.classList.contains('is-scrolled')) {
			try {
				sessionStorage.setItem(key, '1');
			} catch {}
		}
	});

	addEventListener('pagereveal', (event) => {
		let fromScrolled = false;

		try {
			fromScrolled = sessionStorage.getItem(key) === '1';
			sessionStorage.removeItem(key);
		} catch {}

		const root = header();

		if (!fromScrolled || !event.viewTransition || !root || root.classList.contains('is-scrolled')) {
			return;
		}

		// Jump straight to the scrolled state for the new page's first frame.
		root.classList.add('is-scrolled');
		root.getAnimations({ subtree: true }).forEach((animation) => animation.finish());
		event.viewTransition.ready.then(() => root.classList.toggle('is-scrolled', scrollY > 0));
	});
})();
JS;

	wp_print_inline_script_tag( $script );
}
add_action( 'wp_head', 'fervor_print_header_reveal_script' );

/**
 * Turn on in-view reveals (src/js/modules/reveal.js) before the first paint.
 *
 * Content only starts hidden under .has-reveal, so it stays visible without
 * JS, with reduced motion, and in the editor. It's inline in the head so
 * nothing on screen shows and then blinks out while main.js loads.
 */
function fervor_print_reveal_script() {
	wp_print_inline_script_tag( "if (!matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('has-reveal');" );
}
add_action( 'wp_head', 'fervor_print_reveal_script' );

/**
 * Enqueue editor styles inside the block editor iframe.
 */
function fervor_enqueue_editor_assets() {
	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_style( 'fervor-typekit', FERVOR_TYPEKIT_URL, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- versioned by Adobe.
	fervor_vite_enqueue( 'fervor-editor', 'src/js/editor.js' );
}
add_action( 'enqueue_block_assets', 'fervor_enqueue_editor_assets' );

/**
 * Preconnect to Adobe Fonts so the kit and its font files start loading sooner.
 *
 * @param array  $urls          URLs to print for the relation type.
 * @param string $relation_type The relation type, e.g. 'preconnect'.
 * @return array
 */
function fervor_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array(
			'href'        => 'https://use.typekit.net',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'fervor_resource_hints', 10, 2 );

/**
 * Save and load ACF field groups from the theme's acf-json directory.
 */
add_filter(
	'acf/settings/save_json',
	function () {
		return get_theme_file_path( 'acf-json' );
	}
);

add_filter(
	'acf/settings/load_json',
	function ( $paths ) {
		$paths[] = get_theme_file_path( 'acf-json' );
		return array_unique( $paths );
	}
);

/**
 * Disable comments site-wide.
 */
add_action(
	'init',
	function () {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	},
	100
);
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_action(
	'admin_menu',
	function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
