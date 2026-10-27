<?php
/**
 * Style Guide block template.
 *
 * Token lists come from src/scss/_tokens.scss (inc/style-guide.php). Resolved
 * values for the current viewport are filled in by src/js/modules/style-guide.js.
 *
 * @package Fervor
 */

$families = array(
	array(
		'name'    => 'Söhne',
		'token'   => '--font-sans',
		'use'     => __( 'Body copy, statements, and UI', 'fervorcreative' ),
		'italic'  => true,
		'weights' => array(
			300 => 'Leicht',
			400 => 'Buch',
			500 => 'Kräftig',
			600 => 'Halbfett',
			700 => 'Dreiviertelfett',
		),
	),
	array(
		'name'    => 'Kepler',
		'token'   => '--font-serif',
		'use'     => __( 'Subheadings and quotes', 'fervorcreative' ),
		'italic'  => false,
		'weights' => array( 400 => 'Regular' ),
	),
	array(
		'name'    => 'Good Condensed',
		'token'   => '--font-display',
		'use'     => __( 'Hero headings', 'fervorcreative' ),
		'italic'  => false,
		'weights' => array( 700 => 'Bold' ),
	),
);

$styles = array(
	array(
		'name'   => __( 'Display', 'fervorcreative' ),
		'class'  => 'display',
		'detail' => '@include display — Good Condensed 700, line height 1, tracking 25',
		'size'   => '--font-size-display',
		'sample' => __( 'Brands with fervor', 'fervorcreative' ),
	),
	array(
		'name'   => __( 'Statement', 'fervorcreative' ),
		'class'  => 'statement',
		'detail' => '@include statement — Söhne 300, line height 1.125, tracking −20',
		'size'   => '--font-size-statement',
		'sample' => __( 'We believe there is a better way to manage design.', 'fervorcreative' ),
	),
	array(
		'name'   => __( 'Subheading', 'fervorcreative' ),
		'class'  => 'subheading',
		'detail' => '@include subheading — Kepler 400, line height 1.25. Also used for quotes.',
		'size'   => '--font-size-subheading',
		'sample' => __( 'Brand strategy and identity', 'fervorcreative' ),
	),
	array(
		'name'   => __( 'Numeral', 'fervorcreative' ),
		'class'  => 'numeral',
		'detail' => '@include numeral — Good Condensed 700, primary, line height 1. Numbered list markers.',
		'size'   => '--font-size-numeral',
		'sample' => '01',
	),
	array(
		'name'   => __( 'Eyebrow', 'fervorcreative' ),
		'class'  => 'eyebrow',
		'detail' => '@include eyebrow — Söhne 700, uppercase, line height 1.33, tracking 75',
		'size'   => '--font-size-eyebrow',
		'sample' => __( 'Fervor is different', 'fervorcreative' ),
	),
	array(
		'name'   => __( 'Body', 'fervorcreative' ),
		'class'  => 'style-guide__body',
		'detail' => 'Body default — Söhne 400, line height 1.6',
		'size'   => '--font-size-base',
		'sample' => __( 'At our core, we are a branding agency. All design is driven by strategy, and every project starts with listening.', 'fervorcreative' ),
	),
	array(
		'name'   => __( 'Small', 'fervorcreative' ),
		'class'  => 'style-guide__small',
		'detail' => '@include small — Söhne 400, line height 1.4',
		'size'   => '--font-size-small',
		'sample' => __( 'Over 90% of our dollar goes towards the creative process, not administrative overhead.', 'fervorcreative' ),
	),
);

$colors  = fervor_style_guide_tokens( '--color-' );
$sizes   = fervor_style_guide_tokens( '--font-size-' );
$spacing = fervor_style_guide_tokens( '--space-' );

?>
<div<?php fervor_block_attrs( $block, array( 'style-guide' ) ); ?> data-style-guide>
	<header class="style-guide__intro section">
		<div class="container">
			<h1 class="style-guide__eyebrow"><?php esc_html_e( 'Style guide', 'fervorcreative' ); ?></h1>
			<p class="style-guide__heading"><?php esc_html_e( 'Type, color, and spacing for fervorcreative.com.', 'fervorcreative' ); ?></p>
			<p class="style-guide__note">
				<?php
				printf(
					/* translators: %s: current viewport width, filled in by script. */
					esc_html__( 'Tokens come from src/scss/_tokens.scss. Resolved values are shown at the current window width: %s.', 'fervorcreative' ),
					'<strong data-style-guide-width>—</strong>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
				);
				?>
			</p>
			<nav class="style-guide__toc" aria-label="<?php esc_attr_e( 'Style guide sections', 'fervorcreative' ); ?>">
				<a href="#type"><?php esc_html_e( 'Type', 'fervorcreative' ); ?></a>
				<a href="#color"><?php esc_html_e( 'Color', 'fervorcreative' ); ?></a>
				<a href="#spacing"><?php esc_html_e( 'Spacing', 'fervorcreative' ); ?></a>
			</nav>
		</div>
	</header>

	<section class="style-guide__section section section--surface" id="type" aria-labelledby="type-heading">
		<div class="container">
			<h2 class="style-guide__section-title" id="type-heading"><?php esc_html_e( 'Type', 'fervorcreative' ); ?></h2>

			<h3 class="style-guide__group-title"><?php esc_html_e( 'Families', 'fervorcreative' ); ?></h3>
			<div class="style-guide__families">
				<?php foreach ( $families as $family ) : ?>
					<div class="style-guide__family" style="font-family: var(<?php echo esc_attr( $family['token'] ); ?>)">
						<p class="style-guide__family-sample" aria-hidden="true">Aa</p>
						<p class="style-guide__family-name"><?php echo esc_html( $family['name'] ); ?></p>
						<p class="style-guide__meta"><code><?php echo esc_html( $family['token'] ); ?></code> · <?php echo esc_html( $family['use'] ); ?></p>
						<ul class="style-guide__weights">
							<?php foreach ( $family['weights'] as $weight => $label ) : ?>
								<li style="font-weight: <?php echo (int) $weight; ?>">
									<?php echo esc_html( $label . ' ' . $weight ); ?>
									<?php if ( $family['italic'] ) : ?>
										<em><?php esc_html_e( 'Kursiv', 'fervorcreative' ); ?></em>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>

			<h3 class="style-guide__group-title"><?php esc_html_e( 'Styles', 'fervorcreative' ); ?></h3>
			<div class="style-guide__rows">
				<?php foreach ( $styles as $style ) : ?>
					<div class="style-guide__row">
						<div class="style-guide__label">
							<p class="style-guide__name"><?php echo esc_html( $style['name'] ); ?></p>
							<p class="style-guide__meta"><?php echo esc_html( $style['detail'] ); ?></p>
							<p class="style-guide__meta"><code><?php echo esc_html( $style['size'] ); ?></code> · <code data-token="<?php echo esc_attr( $style['size'] ); ?>"></code></p>
						</div>
						<p class="style-guide__specimen <?php echo esc_attr( $style['class'] ); ?>"><?php echo esc_html( $style['sample'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $sizes as $group => $tokens ) : ?>
				<h3 class="style-guide__group-title"><?php echo esc_html( $group ); ?></h3>
				<table class="style-guide__table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Token', 'fervorcreative' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Declared', 'fervorcreative' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Now', 'fervorcreative' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Sample', 'fervorcreative' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $tokens as $name => $value ) : ?>
							<tr>
								<td><code><?php echo esc_html( $name ); ?></code></td>
								<td><?php echo esc_html( fervor_style_guide_value( $value ) ); ?></td>
								<td><code data-token="<?php echo esc_attr( $name ); ?>"></code></td>
								<td class="style-guide__size-sample" style="font-size: var(<?php echo esc_attr( $name ); ?>)">Aa</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="style-guide__section section" id="color" aria-labelledby="color-heading">
		<div class="container">
			<h2 class="style-guide__section-title" id="color-heading"><?php esc_html_e( 'Color', 'fervorcreative' ); ?></h2>

			<?php foreach ( $colors as $group => $tokens ) : ?>
				<h3 class="style-guide__group-title"><?php echo esc_html( $group ); ?></h3>
				<ul class="style-guide__swatches">
					<?php foreach ( $tokens as $name => $value ) : ?>
						<li class="style-guide__swatch">
							<span class="style-guide__swatch-color" style="background-color: var(<?php echo esc_attr( $name ); ?>)"></span>
							<code class="style-guide__swatch-name"><?php echo esc_html( $name ); ?></code>
							<?php if ( ! preg_match( '/^#[0-9a-f]{3,8}$/i', $value ) ) : ?>
								<span class="style-guide__meta"><?php echo esc_html( $value ); ?></span>
							<?php endif; ?>
							<code class="style-guide__meta" data-token="<?php echo esc_attr( $name ); ?>" data-token-type="color"></code>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="style-guide__section section section--surface" id="spacing" aria-labelledby="spacing-heading">
		<div class="container">
			<h2 class="style-guide__section-title" id="spacing-heading"><?php esc_html_e( 'Spacing', 'fervorcreative' ); ?></h2>

			<?php foreach ( $spacing as $group => $tokens ) : ?>
				<h3 class="style-guide__group-title"><?php echo esc_html( $group ); ?></h3>
				<table class="style-guide__table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Token', 'fervorcreative' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Declared', 'fervorcreative' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Now', 'fervorcreative' ); ?></th>
							<th scope="col"><span class="visually-hidden"><?php esc_html_e( 'Size', 'fervorcreative' ); ?></span></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $tokens as $name => $value ) : ?>
							<tr>
								<td><code><?php echo esc_html( $name ); ?></code></td>
								<td><?php echo esc_html( fervor_style_guide_value( $value ) ); ?></td>
								<td><code data-token="<?php echo esc_attr( $name ); ?>"></code></td>
								<td class="style-guide__bar-cell"><span class="style-guide__bar" style="width: var(<?php echo esc_attr( $name ); ?>)"></span></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
		</div>
	</section>
</div>
