<?php
/**
 * Hero block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$image_id = (int) get_field( 'image' );
$video    = get_field( 'video' );
$title    = get_field( 'title' );

$classes = array(
	'hero',
	$title ? 'hero--has-title' : '',
);

?>
<section<?php fervor_block_attrs( $block, $classes ); ?>>
	<div class="hero__media">
		<?php if ( ! empty( $video['url'] ) ) : ?>
			<video
				class="hero__video"
				src="<?php echo esc_url( $video['url'] ); ?>"
				<?php echo $image_id ? 'poster="' . esc_url( wp_get_attachment_image_url( $image_id, '2048x2048' ) ) . '"' : ''; ?>
				autoplay
				muted
				loop
				playsinline
				aria-hidden="true"></video>
		<?php elseif ( $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'2048x2048',
				false,
				array(
					'class'         => 'hero__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'sizes'         => '100vw',
				)
			);
			?>
		<?php endif; ?>
	</div>

	<?php if ( $title ) : ?>
		<div class="hero__content container">
			<h1 class="hero__title"><?php echo esc_html( $title ); ?></h1>
		</div>
	<?php endif; ?>
</section>
