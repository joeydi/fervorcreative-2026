<?php
/**
 * Logo Ticker block template.
 *
 * Each logo is set to an editor-chosen height (width auto) so logos of
 * different shapes balance visually. Motion lives in src/js/modules/ticker.js.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$logos = array_filter(
	(array) get_field( 'logos' ),
	static fn( $row ) => ! empty( $row['image'] )
);

if ( ! $logos ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'logo-ticker' ) ); ?>>
	<div class="container">
		<div class="logo-ticker__box" data-ticker data-reveal>
			<ul class="logo-ticker__track" data-ticker-track>
				<?php foreach ( $logos as $logo ) : ?>
					<?php $height = max( 100, min( 200, (int) ( $logo['size'] ?: 150 ) ) ); ?>
					<li class="logo-ticker__logo" style="--logo-height: <?php echo esc_attr( $height ); ?>">
						<?php
						echo wp_get_attachment_image(
							$logo['image'],
							'medium_large',
							false,
							array(
								'class'   => 'logo-ticker__image',
								'loading' => 'eager',
							)
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
