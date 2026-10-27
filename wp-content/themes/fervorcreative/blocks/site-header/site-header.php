<?php
/**
 * Site Header block template.
 *
 * The logo is set in Site Options and the links come from the menu assigned to
 * the Main Menu location. When the page opens with a Hero block
 * the header overlays it in white; otherwise it sits on white with a red logo.
 *
 * @package Fervor
 */

$logo_id  = (int) get_field( 'logo', 'option' );
$logo_svg = fervor_get_svg_attachment( $logo_id );
$menu     = fervor_get_menu_items( 'primary' );

$current_url = is_singular() ? untrailingslashit( get_permalink() ) : '';
$work_url    = untrailingslashit( home_url( '/work' ) );

?>
<div<?php fervor_block_attrs( $block, array( 'site-header' ) ); ?> data-header data-menu>
	<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'fervorcreative' ); ?></a>

	<div class="site-header__inner container">
		<a class="site-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( $logo_svg ) : ?>
				<?php echo $logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by fervor_get_svg_attachment. ?>
				<span class="visually-hidden"><?php bloginfo( 'name' ); ?></span>
			<?php elseif ( $logo_id ) : ?>
				<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
			<?php else : ?>
				<span class="site-header__wordmark"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<?php if ( $menu ) : ?>
			<button class="site-header__toggle" type="button" aria-expanded="false" aria-controls="site-menu" data-menu-toggle>
				<?php fervor_icon( 'menu' ); ?>
				<span class="visually-hidden"><?php esc_html_e( 'Menu', 'fervorcreative' ); ?></span>
			</button>

			<nav class="site-header__nav" id="site-menu" aria-label="<?php esc_attr_e( 'Main', 'fervorcreative' ); ?>">
				<ul class="site-header__menu">
					<?php
					foreach ( $menu as $item ) :
						$item_url   = untrailingslashit( strtok( $item->url, '?#' ) );
						$is_current = $current_url === $item_url || ( is_singular( 'project' ) && $work_url === $item_url );
						?>
						<li>
							<a class="site-header__link" <?php fervor_link_attrs( array( 'url' => $item->url, 'target' => $item->target ) ); ?><?php echo $is_current ? ' aria-current="page"' : ''; ?>>
								<?php echo esc_html( $item->title ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</div>
