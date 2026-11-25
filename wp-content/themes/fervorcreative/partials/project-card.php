<?php
/**
 * Project card.
 *
 * @package Fervor
 *
 * @var array $args {
 *     @type int    $id   Project ID.
 *     @type string $size Card size: large, small, or full.
 * }
 */

$project_id = (int) ( $args['id'] ?? 0 );
$size       = $args['size'] ?? 'small';
$image_id   = fervor_get_project_card_image( $project_id );
$video      = get_field( 'card_video', $project_id );
$tagline    = get_field( 'tagline', $project_id );
$services   = fervor_get_project_services( $project_id );

$sizes = array(
	'full'  => '(max-width: 1248px) 100vw, 1200px',
	'large' => '(max-width: 640px) 100vw, 50vw',
	'small' => '(max-width: 640px) 100vw, 40vw',
);

?>
<article <?php fervor_attrs( array( 'class' => array( 'project-card', 'project-card--' . $size ), 'data-reveal-item' => true ) ); ?>>
	<a class="project-card__link" href="<?php echo esc_url( get_permalink( $project_id ) ); ?>">
		<figure class="project-card__media">
			<?php
			if ( ! empty( $video['url'] ) ) {
				// The card image becomes the poster; the video carries its alt text as a label.
				$poster = $image_id ? wp_get_attachment_image_url( $image_id, '2048x2048' ) : '';
				$label  = $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';
				?>
				<video
					class="project-card__image project-card__video"
					src="<?php echo esc_url( $video['url'] ); ?>"
					<?php echo $poster ? 'poster="' . esc_url( $poster ) . '"' : ''; ?>
					<?php echo $label ? 'role="img" aria-label="' . esc_attr( $label ) . '"' : 'aria-hidden="true"'; ?>
					autoplay
					muted
					loop
					playsinline></video>
				<?php
			} elseif ( $image_id ) {
				echo wp_get_attachment_image(
					$image_id,
					'full' === $size ? '2048x2048' : '1536x1536',
					false,
					array(
						'class' => 'project-card__image',
						'sizes' => $sizes[ $size ] ?? $sizes['small'],
					)
				);
			}
			?>
		</figure>
		<h3 class="project-card__title"><?php echo esc_html( get_the_title( $project_id ) ); ?></h3>
		<?php if ( $tagline ) : ?>
			<p class="project-card__tagline"><?php echo esc_html( $tagline ); ?></p>
		<?php endif; ?>
	</a>
	<?php if ( $services ) : ?>
		<p class="project-card__services"><?php echo esc_html( implode( '  |  ', $services ) ); ?></p>
	<?php endif; ?>
</article>
