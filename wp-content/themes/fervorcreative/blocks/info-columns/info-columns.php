<?php
/**
 * Info Columns block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$columns = fervor_get_populated_items( get_field( 'columns' ), array( 'title', 'copy' ) );

if ( ! $columns ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'info-columns', 'section' ) ); ?>>
	<div class="info-columns__grid container">
		<?php foreach ( $columns as $column ) : ?>
			<div class="info-columns__column">
				<?php if ( $column['title'] ) : ?>
					<h3 class="info-columns__title"><?php echo esc_html( $column['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( $column['copy'] ) : ?>
					<div class="info-columns__copy"><?php echo wp_kses_post( $column['copy'] ); ?></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
