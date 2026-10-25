<?php
/**
 * Numbered Carousel block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$items = fervor_get_populated_items( get_field( 'items' ), array( 'copy' ) );

if ( ! $items ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'numbered-carousel', 'section' ) ); ?> data-carousel>
	<div class="numbered-carousel__viewport container">
		<ol class="numbered-carousel__track" data-carousel-track>
			<?php foreach ( $items as $index => $item ) : ?>
				<li class="numbered-carousel__item">
					<span class="numbered-carousel__number" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<p class="numbered-carousel__copy"><?php echo esc_html( $item['copy'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>

	<?php if ( count( $items ) > 1 ) : ?>
		<div class="numbered-carousel__controls container">
			<button class="circle-btn" type="button" data-carousel-prev aria-label="<?php esc_attr_e( 'Previous', 'fervorcreative' ); ?>"><?php fervor_icon( 'arrow-left' ); ?></button>
			<button class="circle-btn" type="button" data-carousel-next aria-label="<?php esc_attr_e( 'Next', 'fervorcreative' ); ?>"><?php fervor_icon( 'arrow-right' ); ?></button>
		</div>
	<?php endif; ?>
</section>
