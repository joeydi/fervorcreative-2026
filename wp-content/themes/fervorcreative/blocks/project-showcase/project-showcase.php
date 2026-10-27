<?php
/**
 * Project Showcase block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$background = get_field( 'background' );
$items      = array_slice( fervor_get_populated_items( get_field( 'items' ), array( 'image', 'video_file' ) ), 0, 3 );

if ( ! $items ) {
	return;
}

$classes = array( 'project-showcase', 'project-showcase--count-' . count( $items ) );

if ( get_field( 'rounded' ) ) {
	$classes[] = 'project-showcase--rounded';
}

$sizes = 1 === count( $items ) ? '(min-width: 1440px) 1420px, 100vw' : '(min-width: 768px) 25vw, 100vw';

?>
<section<?php fervor_block_attrs( $block, $classes ); ?>>
	<?php
	if ( $background ) {
		echo wp_get_attachment_image(
			$background,
			'2048x2048',
			false,
			array(
				'class'   => 'project-showcase__background',
				'alt'     => '',
				'loading' => 'lazy',
				'sizes'   => '100vw',
			)
		);
	}
	?>

	<div class="project-showcase__inner container">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$size  = ! empty( $item['video_file']['width'] ) ? $item['video_file'] : wp_get_attachment_metadata( $item['image'] );
			$ratio = ! empty( $size['width'] ) && ! empty( $size['height'] ) ? round( $size['width'] / $size['height'], 4 ) : 0;
			?>
			<figure class="project-showcase__item"<?php echo $ratio ? ' style="--ratio: ' . esc_attr( $ratio ) . '"' : ''; ?>>
				<?php if ( ! empty( $item['video_file']['url'] ) ) : ?>
					<video
						class="project-showcase__video"
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
							'class'   => 'project-showcase__image',
							'loading' => 'lazy',
							'sizes'   => $sizes,
						)
					);
					?>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
