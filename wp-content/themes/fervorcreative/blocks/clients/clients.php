<?php
/**
 * Clients block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$eyebrow = get_field( 'eyebrow' );
$heading = get_field( 'heading' );
$logos   = array_filter( (array) get_field( 'logos' ) );

$classes = array(
	'clients',
	'section',
	fervor_block_has_style( $block, 'surface' ) ? 'section--surface' : '',
);

?>
<section<?php fervor_block_attrs( $block, $classes ); ?>>
	<div class="container">
		<?php if ( $eyebrow || $heading ) : ?>
			<div class="clients__header" data-reveal>
				<?php if ( $eyebrow ) : ?>
					<p class="clients__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( $heading ) : ?>
					<h2 class="clients__heading"><?php echo wp_kses( $heading, array( 'br' => array() ) ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $logos ) : ?>
			<ul class="clients__logos">
				<?php foreach ( $logos as $logo_id ) : ?>
					<li class="clients__logo" data-reveal-item>
						<?php
						echo wp_get_attachment_image(
							$logo_id,
							'medium_large',
							false,
							array(
								'class'   => 'clients__image',
								'loading' => 'lazy',
							)
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
