<?php
/**
 * Branded wp-login.php screens: the logo, Söhne, and the site's buttons and
 * fields on a white card. Styles live in src/scss/login.scss.
 *
 * @package Fervor
 */

/**
 * Enqueue the login styles and fonts.
 */
function fervor_login_enqueue_assets() {
	wp_enqueue_style( 'fervor-typekit', FERVOR_TYPEKIT_URL, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- versioned by Adobe.
	fervor_vite_enqueue( 'fervor-login', 'src/js/login.js' );
}
add_action( 'login_enqueue_scripts', 'fervor_login_enqueue_assets' );

/**
 * Link the logo to the site rather than wordpress.org.
 *
 * @return string
 */
function fervor_login_headerurl() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'fervor_login_headerurl' );

/**
 * Swap the WordPress logo for ours.
 *
 * @return string
 */
function fervor_login_headertext() {
	$svg  = fervor_get_svg_attachment( (int) get_field( 'logo', 'option' ) );
	$name = esc_html( get_bloginfo( 'name', 'display' ) );

	return $svg ? $svg . '<span class="screen-reader-text">' . $name . '</span>' : $name;
}
add_filter( 'login_headertext', 'fervor_login_headertext' );

/**
 * Add a body class to scope the login styles.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function fervor_login_body_class( $classes ) {
	$classes[] = 'fervor-login';

	return $classes;
}
add_filter( 'login_body_class', 'fervor_login_body_class' );

/**
 * Title each screen above its form, e.g. "Sign in" or "Reset password".
 *
 * @param string $message Login message markup.
 * @return string
 */
function fervor_login_message( $message ) {
	$action = sanitize_key( wp_unslash( $_REQUEST['action'] ?? 'login' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
	$titles = array(
		'login'            => __( 'Sign in', 'fervorcreative' ),
		'lostpassword'     => __( 'Lost password', 'fervorcreative' ),
		'retrievepassword' => __( 'Lost password', 'fervorcreative' ),
		'rp'               => __( 'Reset password', 'fervorcreative' ),
		'resetpass'        => __( 'Reset password', 'fervorcreative' ),
		'register'         => __( 'Register', 'fervorcreative' ),
	);

	if ( isset( $_GET['checkemail'] ) || ! isset( $titles[ $action ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		return $message;
	}

	return '<p class="fervor-login__title">' . esc_html( $titles[ $action ] ) . '</p>' . $message;
}
add_filter( 'login_message', 'fervor_login_message' );
