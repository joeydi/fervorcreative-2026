<?php
/**
 * Site Footer block template.
 *
 * Content is managed in Site Options.
 *
 * @package Fervor
 */

$cta_heading = get_field( 'footer_heading', 'option' );
$cta_link    = get_field( 'footer_link', 'option' );
$cta_image   = (int) get_field( 'footer_image', 'option' );
$address     = get_field( 'address', 'option' );
$phone       = get_field( 'phone', 'option' );
$social      = fervor_get_populated_items( get_field( 'social_links', 'option' ), array( 'icon', 'url' ), 'and' );

$classes = array(
	'site-footer',
	$cta_image ? 'site-footer--has-image' : '',
);

?>
<div<?php fervor_block_attrs( $block, $classes ); ?>>
	<?php if ( $cta_image ) : ?>
		<div class="site-footer__media" aria-hidden="true">
			<?php
			echo wp_get_attachment_image(
				$cta_image,
				'2048x2048',
				false,
				array(
					'class'   => 'site-footer__image',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '100vw',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="site-footer__inner container">
		<?php if ( $cta_heading || $cta_link ) : ?>
			<div class="site-footer__cta" data-reveal>
				<?php if ( $cta_heading ) : ?>
					<h2 class="site-footer__heading"><?php echo esc_html( $cta_heading ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $cta_link['url'] ) ) : ?>
					<a class="btn site-footer__button" <?php fervor_link_attrs( $cta_link ); ?>><?php echo esc_html( $cta_link['title'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="site-footer__meta" data-reveal>
			<?php if ( $address || $phone ) : ?>
				<p class="site-footer__contact">
					<?php if ( $address ) : ?>
						<span><?php echo esc_html( $address ); ?></span>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<?php if ( $social ) : ?>
				<ul class="site-footer__social">
					<?php foreach ( $social as $item ) : ?>
						<li>
							<a class="site-footer__social-link" href="<?php echo esc_url( $item['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php fervor_icon( 'social/' . $item['icon'] ); ?>
								<span class="visually-hidden"><?php echo esc_html( ucwords( str_replace( '-', ' ', $item['icon'] ) ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</div>
