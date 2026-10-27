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

$image_attrs = array(
	'class'   => 'project-media__image',
	'loading' => 'lazy',
	'sizes'   => $sizes,
);

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
					<?php elseif ( ! empty( $item['slides'] ) ) : ?>
						<?php
						// A slideshow opens on the item's image, and every slide crops to its shape.
						$slides = array_merge( array( $item['image'] ), array_map( 'intval', (array) $item['slides'] ) );
						$meta   = wp_get_attachment_metadata( $item['image'] );
						$ratio  = ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ? $meta['width'] . ' / ' . $meta['height'] : '';
						?>
						<div
							class="project-media__slideshow"
							<?php echo $ratio ? 'style="--ratio: ' . esc_attr( $ratio ) . '"' : ''; ?>
							data-slideshow
							data-slideshow-autoplay="3000"
							data-slideshow-duration="800"
							aria-roledescription="<?php esc_attr_e( 'carousel', 'fervorcreative' ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %s: project title */ __( '%s images', 'fervorcreative' ), get_the_title() ) ); ?>">
							<?php foreach ( $slides as $slide ) : ?>
								<div class="project-media__slide" role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'fervorcreative' ); ?>">
									<?php echo wp_get_attachment_image( $slide, '2048x2048', false, $image_attrs + array( 'draggable' => 'false' ) ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<?php echo wp_get_attachment_image( $item['image'], '2048x2048', false, $image_attrs ); ?>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
