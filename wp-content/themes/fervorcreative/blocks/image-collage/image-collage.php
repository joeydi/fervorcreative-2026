<?php
/**
 * Image Collage block template.
 *
 * Arranges two or three images in a staggered layout. Images keep their
 * natural aspect ratio.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$images = array_slice( array_filter( (array) get_field( 'images' ) ), 0, 3 );

if ( ! $images ) {
	return;
}

$classes = array(
	'image-collage',
	'section',
	'image-collage--count-' . count( $images ),
);

?>
<section<?php fervor_block_attrs( $block, $classes ); ?>>
	<div class="image-collage__grid container">
		<?php foreach ( $images as $index => $image_id ) : ?>
			<figure class="image-collage__item">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'1536x1536',
					false,
					array(
						'class'   => 'image-collage__image',
						'loading' => 'lazy',
						'sizes'   => '(max-width: 640px) 100vw, 50vw',
					)
				);
				?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
