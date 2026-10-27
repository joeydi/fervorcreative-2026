<?php
/**
 * Capabilities block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$eyebrow = get_field( 'eyebrow' );
$heading = get_field( 'heading' );
$columns = fervor_get_populated_items( get_field( 'columns' ), array( 'title', 'items' ) );

$classes = array(
	'capabilities',
	'section',
	fervor_block_has_style( $block, 'surface' ) ? 'section--surface' : '',
);

?>
<section<?php fervor_block_attrs( $block, $classes ); ?>>
	<div class="container">
		<?php if ( $eyebrow || $heading ) : ?>
			<div class="capabilities__header" data-reveal>
				<?php if ( $eyebrow ) : ?>
					<p class="capabilities__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="capabilities__heading"><?php echo wp_kses( $heading, array( 'br' => array() ) ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $columns ) : ?>
			<div class="capabilities__columns">
				<?php foreach ( $columns as $column ) : ?>
					<div class="capabilities__column" data-reveal-item>
						<?php $icon_svg = fervor_get_svg_attachment( (int) $column['icon'] ); ?>
						<?php if ( $icon_svg ) : ?>
							<span class="capabilities__icon" aria-hidden="true" data-capability-icon><?php echo $icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by fervor_get_svg_attachment. ?></span>
						<?php elseif ( $column['icon'] ) : ?>
							<?php echo wp_get_attachment_image( $column['icon'], 'thumbnail', false, array( 'class' => 'capabilities__icon', 'alt' => '' ) ); ?>
						<?php endif; ?>

						<?php if ( $column['title'] ) : ?>
							<h3 class="capabilities__title"><?php echo esc_html( $column['title'] ); ?></h3>
						<?php endif; ?>

						<?php $items = fervor_lines( $column['items'] ); ?>
						<?php if ( $items ) : ?>
							<ul class="capabilities__list">
								<?php foreach ( $items as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
