<?php
/**
 * Testimonials block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$items = fervor_get_populated_items( get_field( 'items' ), array( 'quote' ) );

if ( ! $items ) {
	return;
}

$has_multiple = count( $items ) > 1;

?>
<section<?php fervor_block_attrs( $block, array( 'testimonials', 'section' ) ); ?><?php echo $has_multiple ? ' data-carousel data-carousel-autoplay="8000"' : ''; ?> aria-label="<?php esc_attr_e( 'Testimonials', 'fervorcreative' ); ?>">
	<div class="container" data-reveal>
		<div class="testimonials__track" data-carousel-track>
			<?php foreach ( $items as $item ) : ?>
				<figure class="testimonials__item">
					<blockquote class="testimonials__quote">
						<p><?php echo esc_html( $item['quote'] ); ?></p>
					</blockquote>
					<?php if ( $item['name'] || $item['role'] ) : ?>
						<figcaption class="testimonials__cite">
							<?php echo esc_html( implode( ', ', array_filter( array( $item['name'], $item['role'] ) ) ) ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( $has_multiple ) : ?>
			<div class="testimonials__dots" data-carousel-dots></div>
		<?php endif; ?>
	</div>
</section>
