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

// A full video with sound, from a file or Vimeo, plays over the item's image or looping video.
foreach ( $items as &$item ) {
	$item['play_src']   = $item['play_file']['url'] ?? '';
	$item['play_vimeo'] = $item['play_vimeo'] ?? '';
}
unset( $item );

if ( ! $items ) {
	return;
}

$sizes = 'full' === $layout ? '(min-width: 1249px) 1200px, 100vw' : '(min-width: 768px) 50vw, 100vw';

$image_attrs = array(
	'class'   => 'project-media__image',
	'loading' => 'lazy',
	'sizes'   => $sizes,
);

if ( ! function_exists( 'fervor_project_media_still' ) ) {
	/**
	 * Output an item's looping video, or its image when it has none.
	 *
	 * @param array $item        Repeater row.
	 * @param array $image_attrs Attributes for the image.
	 */
	function fervor_project_media_still( $item, $image_attrs ) {
		if ( empty( $item['video_file']['url'] ) ) {
			echo wp_get_attachment_image( $item['image'], '2048x2048', false, $image_attrs );
			return;
		}
		?>
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
		<?php
	}
}

?>
<section<?php fervor_block_attrs( $block, array( 'project-media', 'project-media--' . $layout ) ); ?>>
	<div class="project-media__inner container">
		<div class="project-media__grid">
			<?php foreach ( $items as $item ) : ?>
				<figure class="project-media__item" data-reveal-item>
					<?php if ( $item['video'] ) : ?>
						<div class="project-media__embed"><?php echo $item['video']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from ACF. ?></div>
					<?php elseif ( $item['play_src'] || $item['play_vimeo'] ) : ?>
						<div
							class="project-media__player"
							data-player
							<?php echo $item['play_vimeo'] ? 'data-player-vimeo="' . esc_url( $item['play_vimeo'] ) . '"' : 'data-player-src="' . esc_url( $item['play_src'] ) . '"'; ?>>
							<?php fervor_project_media_still( $item, $image_attrs ); ?>
							<div class="project-media__stage" data-player-stage></div>
							<div class="project-media__controls" data-player-controls>
								<button type="button" class="project-media__toggle" data-player-toggle aria-label="<?php esc_attr_e( 'Play video', 'fervorcreative' ); ?>" data-label-pause="<?php esc_attr_e( 'Pause video', 'fervorcreative' ); ?>">
									<span class="project-media__icon project-media__icon--play"><?php fervor_icon( 'play' ); ?></span>
									<span class="project-media__icon project-media__icon--pause"><?php fervor_icon( 'pause' ); ?></span>
								</button>
								<input type="range" class="project-media__progress" data-player-progress min="0" max="1" step="any" value="0" aria-label="<?php esc_attr_e( 'Seek', 'fervorcreative' ); ?>">
								<button type="button" class="project-media__mute" data-player-mute aria-pressed="false" aria-label="<?php esc_attr_e( 'Mute', 'fervorcreative' ); ?>">
									<span class="project-media__icon project-media__icon--sound"><?php fervor_icon( 'volume' ); ?></span>
									<span class="project-media__icon project-media__icon--muted"><?php fervor_icon( 'volume-off' ); ?></span>
								</button>
							</div>
						</div>
					<?php elseif ( ! empty( $item['video_file']['url'] ) ) : ?>
						<?php fervor_project_media_still( $item, $image_attrs ); ?>
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
							data-slideshow-intro
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
