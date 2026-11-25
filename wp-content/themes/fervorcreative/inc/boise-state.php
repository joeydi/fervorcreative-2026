<?php
/**
 * Boise State page template (templates/boise-state.php).
 *
 * The page's stylesheet, scripts, and Gravity Forms assets load only on pages
 * using this template.
 *
 * @package Fervor
 */

const FERVOR_BOISE_STATE_TEMPLATE = 'templates/boise-state.php';

/**
 * Whether the current request is a page using the Boise State template.
 *
 * @return bool
 */
function fervor_is_boise_state() {
	return is_page_template( FERVOR_BOISE_STATE_TEMPLATE );
}

/**
 * Gravity Forms form ID set in the page's Contact Form ID field.
 *
 * @return int Form ID, or 0 when unset or Gravity Forms isn't active.
 */
function fervor_boise_state_form_id() {
	if ( ! class_exists( 'GFAPI' ) ) {
		return 0;
	}

	$form_id = (int) get_field( 'contact_form', get_queried_object_id() );

	return $form_id && GFAPI::form_id_exists( $form_id ) ? $form_id : 0;
}

/**
 * Enqueue the page's styles and scripts, and the contact form's, so its CSS
 * lands in the head rather than when the form renders.
 */
function fervor_boise_state_enqueue_assets() {
	if ( ! fervor_is_boise_state() ) {
		return;
	}

	fervor_vite_enqueue( 'fervor-boise-state', 'src/js/boise-state.js' );

	$form_id = fervor_boise_state_form_id();

	if ( $form_id && function_exists( 'gravity_form_enqueue_scripts' ) ) {
		gravity_form_enqueue_scripts( $form_id, true );
	}
}
add_action( 'wp_enqueue_scripts', 'fervor_boise_state_enqueue_assets', 20 );

/**
 * Skip Gravity Forms' theme styles (Orbital) on this page's form: it's styled
 * to the brand in _boise-state.scss. Its basic layout styles still load.
 *
 * @param string $slug Form theme slug.
 * @param array  $form Form object.
 * @return string
 */
function fervor_boise_state_form_theme( $slug, $form ) {
	if ( fervor_is_boise_state() && (int) rgar( $form, 'id' ) === fervor_boise_state_form_id() ) {
		return 'gravity-theme';
	}

	return $slug;
}
add_filter( 'gform_form_theme_slug', 'fervor_boise_state_form_theme', 10, 2 );
