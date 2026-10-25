<?php
/**
 * Project Media block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$layout = get_field( 'layout' ) ?: 'full';
$label  = get_field( 'label' );
$items  = fervor_get_populated_items( get_field( 'items' ), array( 'image', 'video' ) );
$items  = array_slice( $items, 0, 'full' === $layout ? 1 : 2 );

if ( ! $items ) {
	return;
}

$sizes = 'full' === $layout ? '(max-width: 1248px) 100vw, 1200px' : '(max-width: 640px) 100vw, 50vw';

?>
<section<?php fervor_block_attrs( $block, array( 'project-media', 'project-media--' . $layout ) ); ?>>
	<div class="project-media__inner container">
		<?php if ( $label ) : ?>
			<p class="project-media__label"><?php echo esc_html( $label ); ?></p>
		<?php endif; ?>

		<div class="project-media__grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="project-media__item">
					<?php if ( $item['video'] ) : ?>
						<div class="project-media__embed"><?php echo $item['video']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from ACF. ?></div>
					<?php else : ?>
						<?php
						echo wp_get_attachment_image(
							$item['image'],
							'2048x2048',
							false,
							array(
								'class'   => 'project-media__image',
								'loading' => 'lazy',
								'sizes'   => $sizes,
							)
						);
						?>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
