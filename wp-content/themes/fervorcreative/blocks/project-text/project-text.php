<?php
/**
 * Project Text block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$label = get_field( 'label' );
$copy  = get_field( 'copy' );

if ( ! $copy ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'project-text' ) ); ?>>
	<div class="project-text__inner container">
		<?php if ( $label ) : ?>
			<p class="project-text__label"><?php echo esc_html( $label ); ?></p>
		<?php endif; ?>
		<div class="project-text__copy"><?php echo wp_kses_post( $copy ); ?></div>
	</div>
</section>
