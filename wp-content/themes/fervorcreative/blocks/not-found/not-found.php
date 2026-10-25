<?php
/**
 * Not Found block template.
 *
 * @package Fervor
 */

?>
<section<?php fervor_block_attrs( $block, array( 'not-found', 'section' ) ); ?>>
	<div class="container">
		<p class="not-found__eyebrow"><?php esc_html_e( 'Page not found', 'fervorcreative' ); ?></p>
		<h1 class="not-found__heading"><?php esc_html_e( 'We couldn’t find the page you were looking for.', 'fervorcreative' ); ?></h1>
		<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'fervorcreative' ); ?></a>
	</div>
</section>
