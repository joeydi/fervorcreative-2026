<?php
/**
 * Section Intro block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$eyebrow     = get_field( 'eyebrow' );
$heading     = get_field( 'heading' );
$heading_tag = 'h1' === get_field( 'heading_level' ) ? 'h1' : 'h2';
$copy        = get_field( 'copy' );

?>
<section<?php fervor_block_attrs( $block, array( 'section-intro', 'section' ) ); ?>>
	<div class="container">
		<?php if ( $eyebrow ) : ?>
			<p class="section-intro__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<?php if ( $heading ) : ?>
			<<?php echo esc_html( $heading_tag ); ?> class="section-intro__heading"><?php echo wp_kses( $heading, array( 'br' => array() ) ); ?></<?php echo esc_html( $heading_tag ); ?>>
		<?php endif; ?>

		<?php if ( $copy ) : ?>
			<div class="section-intro__copy"><?php echo wp_kses_post( $copy ); ?></div>
		<?php endif; ?>
	</div>
</section>
