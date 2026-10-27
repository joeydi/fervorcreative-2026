<?php
/**
 * Team block template.
 *
 * @package Fervor
 */

if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}

$eyebrow = get_field( 'eyebrow' );
$members = fervor_get_populated_items( get_field( 'members' ), array( 'name' ) );

if ( ! $members ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'team', 'section' ) ); ?>>
	<div class="container">
		<?php if ( $eyebrow ) : ?>
			<h2 class="team__eyebrow" data-reveal-item><?php echo esc_html( $eyebrow ); ?></h2>
		<?php endif; ?>

		<ul class="team__grid">
			<?php foreach ( $members as $member ) : ?>
				<li class="team__member" data-reveal-item>
					<figure class="team__photo">
						<?php
						if ( $member['photo'] ) {
							echo wp_get_attachment_image(
								$member['photo'],
								'large',
								false,
								array(
									'class'   => 'team__image',
									'alt'     => '',
									'loading' => 'lazy',
									'sizes'   => '(min-width: 768px) 33vw, 50vw',
								)
							);
						}
						?>
					</figure>
					<p class="team__name"><?php echo esc_html( $member['name'] ); ?></p>
					<?php if ( $member['title'] ) : ?>
						<p class="team__title"><?php echo esc_html( $member['title'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
