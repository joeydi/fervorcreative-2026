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
$items  = fervor_get_populated_items( get_field( 'items' ), array( 'image', 'video', 'video_file' ) );
$items  = array_slice( $items, 0, 'full' === $layout ? 1 : 2 );

if ( ! $items ) {
	return;
}

$sizes = 'full' === $layout ? '(min-width: 1249px) 1200px, 100vw' : '(min-width: 768px) 50vw, 100vw';

?>
<section<?php fervor_block_attrs( $block, array( 'project-media', 'project-media--' . $layout ) ); ?>>
	<div class="project-media__inner container">
		<div class="project-media__grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="project-media__item" data-reveal-item>
					<?php if ( $item['video'] ) : ?>
						<div class="project-media__embed"><?php echo $item['video']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from ACF. ?></div>
					<?php elseif ( ! empty( $item['video_file']['url'] ) ) : ?>
						<video
							class="project-media__video"
							src="<?php echo esc_url( $item['video_file']['url'] ); ?>"
							<?php echo $item['image'] ? 'poster="' . esc_url( wp_get_attachment_image_url( $item['image'], '2048x2048' ) ) . '"' : ''; ?>
							<?php echo ! empty( $item['video_file']['width'] ) ? 'width="' . (int) $item['video_file']['width'] . '" height="' . (int) $item['video_file']['height'] . '"' : ''; ?>
							autoplay
							muted
							loop
							playsinline
							aria-hidden="true"></video>
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
