<?php
/**
 * Project Text block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$heading = get_field( 'heading' );
$copy    = get_field( 'copy' );

if ( ! $copy ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'project-text' ) ); ?>>
	<div class="project-text__inner container" data-reveal>
		<?php if ( $heading ) : ?>
			<h2 class="project-text__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<div class="project-text__copy"><?php echo wp_kses_post( $copy ); ?></div>
	</div>
</section>
