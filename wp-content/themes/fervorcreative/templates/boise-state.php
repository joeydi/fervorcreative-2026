<?php
/**
 * Template Name: Boise State
 *
 * One-off "New to blue" landing page for Boise State University partners.
 * Hard-coded rather than built from blocks: a minimal header with jump links,
 * the page's sections, then the global footer. The hero image, partner logo,
 * and contact form come from the Boise State Page fields; styles live in
 * src/scss/_boise-state.scss and load only here (inc/boise-state.php).
 *
 * @package Fervor
 */

$hero_image   = (int) get_field( 'hero_image' );
$partner_logo = (int) get_field( 'partner_logo' );
$form_id      = fervor_boise_state_form_id();
$logo_id      = (int) get_field( 'logo', 'option' );
$logo_svg     = fervor_get_svg_attachment( $logo_id );
$partner_svg  = fervor_get_svg_attachment( $partner_logo );

$stats = array(
	array( '20+', 'Years serving higher education' ),
	array( '70', 'Colleges, Initiatives, Centers and Partnerships' ),
	array( '1000+', 'Projects completed each year' ),
	array( '100%', 'Delivery on time' ),
);

$words = array( 'Bold', 'Different', 'Innovative', 'Impactful', 'Creative', 'Passionate' );

$services = array(
	array(
		'icon'  => 'explosion',
		'title' => 'Strategic services',
		'items' => array(
			'Impact Reports',
			'Student Recruitment & Engagement',
			'Research & Innovation',
			'Donor Relations & Fundraising',
			'Special Initiatives',
			'Products & Services',
			'School, College & Program Marketing',
		),
	),
	array(
		'icon'  => 'file-code',
		'title' => 'Deliverables',
		'items' => array(
			'Brochures & Print Materials',
			'Reports & Publications',
			'Websites',
			'Landing Pages',
			'Campaigns',
			'Digital Marketing',
			'Content & Creative Production',
		),
	),
);

$jump_links = array(
	'#about'              => __( 'About Fervor', 'fervorcreative' ),
	'#lets-work-together' => __( 'Let’s Work Together!', 'fervorcreative' ),
);

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<header class="boise-header">
		<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'fervorcreative' ); ?></a>

		<div class="boise-header__inner container">
			<a class="boise-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php if ( $logo_svg ) : ?>
					<?php echo $logo_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by fervor_get_svg_attachment. ?>
					<span class="visually-hidden"><?php bloginfo( 'name' ); ?></span>
				<?php elseif ( $logo_id ) : ?>
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
				<?php else : ?>
					<?php bloginfo( 'name' ); ?>
				<?php endif; ?>
			</a>

			<nav class="boise-header__nav" aria-label="<?php esc_attr_e( 'Page', 'fervorcreative' ); ?>">
				<?php foreach ( $jump_links as $href => $label ) : ?>
					<a class="btn boise-header__button" href="<?php echo esc_attr( $href ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
	</header>

	<main class="site-main" id="main">
		<section class="boise-hero" aria-labelledby="boise-hero-title">
			<?php if ( $hero_image ) : ?>
				<div class="boise-hero__media">
					<?php
					echo wp_get_attachment_image(
						$hero_image,
						'2048x2048',
						false,
						array(
							'class'         => 'boise-hero__image',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '100vw',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="boise-hero__content container">
				<p class="boise-hero__kicker"><?php esc_html_e( 'Proud to be', 'fervorcreative' ); ?></p>
				<h1 class="boise-hero__title" id="boise-hero-title"><?php esc_html_e( 'New to blue', 'fervorcreative' ); ?></h1>

				<?php if ( $partner_logo ) : ?>
					<div class="boise-hero__logo">
						<?php if ( $partner_svg ) : ?>
							<?php echo $partner_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by fervor_get_svg_attachment. ?>
							<span class="visually-hidden"><?php esc_html_e( 'Boise State University', 'fervorcreative' ); ?></span>
						<?php else : ?>
							<?php echo wp_get_attachment_image( $partner_logo, 'medium_large', false, array( 'alt' => __( 'Boise State University', 'fervorcreative' ) ) ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<p class="boise-hero__copy">
					<strong><?php esc_html_e( 'Fervor Creative is an approved vendor for Boise State University for strategy, design, content, and digital', 'fervorcreative' ); ?></strong>
					<?php esc_html_e( '– here to help you move faster, think bigger, and deliver work that truly serves students, faculty, donors, and the broader community.', 'fervorcreative' ); ?>
				</p>

				<div class="boise-hero__actions">
					<?php foreach ( $jump_links as $href => $label ) : ?>
						<a class="btn boise-hero__button" href="<?php echo esc_attr( $href ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="boise-stats" id="about" aria-labelledby="boise-stats-heading">
			<div class="boise-stats__bg" aria-hidden="true"></div>

			<div class="boise-stats__inner container">
				<div class="boise-stats__intro" data-reveal>
					<h2 class="boise-stats__heading" id="boise-stats-heading"><?php esc_html_e( 'Trusted to deliver', 'fervorcreative' ); ?></h2>
					<p class="boise-stats__copy">
						<strong><?php esc_html_e( 'Fervor Creative', 'fervorcreative' ); ?></strong>
						<?php esc_html_e( 'is a strategy, marketing, and design agency that specializes in higher education. For over 20 years, we’ve partnered with colleges and universities to solve complex challenges, clarify messaging, and build trust through thoughtful, effective communication.', 'fervorcreative' ); ?>
					</p>
				</div>

				<ul class="boise-stats__list" data-reveal>
					<?php foreach ( $stats as list( $value, $label ) ) : ?>
						<li class="boise-stats__item">
							<span class="boise-stats__value" data-count-up><?php echo esc_html( $value ); ?></span>
							<span class="boise-stats__label"><?php echo esc_html( $label ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>

		<section class="boise-callout" aria-labelledby="boise-callout-heading">
			<div class="boise-callout__inner container">
				<div class="boise-callout__intro" data-reveal>
					<h2 class="boise-callout__heading" id="boise-callout-heading"><?php esc_html_e( 'We get blue', 'fervorcreative' ); ?></h2>
					<p><?php esc_html_e( 'We understand that Blue isn’t just an identity – it’s a mindset. It reflects a willingness to challenge convention, ask better questions, and pursue outcomes that don’t yet exist.', 'fervorcreative' ); ?></p>
					<p><?php esc_html_e( 'That’s the same mindset we bring to our work with higher education partners every day.', 'fervorcreative' ); ?></p>
				</div>

				<div class="boise-callout__words" aria-hidden="true">
					<ul class="boise-callout__track">
						<?php foreach ( $words as $word ) : ?>
							<li class="boise-callout__word"><?php echo esc_html( $word ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<p class="visually-hidden"><?php echo esc_html( implode( ', ', $words ) ); ?></p>
			</div>
		</section>

		<section class="boise-services" aria-labelledby="boise-services-heading">
			<div class="boise-services__inner container">
				<div class="boise-services__intro" data-reveal>
					<h2 class="boise-services__heading" id="boise-services-heading"><?php esc_html_e( 'We can help', 'fervorcreative' ); ?></h2>
					<p><?php esc_html_e( 'We partner with teams to deliver work that moves initiatives forward and resonates across departments, audiences, and stakeholders. We bring clarity and care to the work from start to finish.', 'fervorcreative' ); ?></p>
					<p><?php esc_html_e( 'At Fervor, there’s no handoff between strategy and execution. You work directly with the creative team, so ideas move faster and the work stays true to your goals from start to finish.', 'fervorcreative' ); ?></p>
				</div>

				<div class="boise-services__groups" data-reveal>
					<?php foreach ( $services as $group ) : ?>
						<div class="boise-services__group">
							<h3 class="boise-services__group-title">
								<span class="boise-services__icon"><?php fervor_icon( $group['icon'] ); ?></span>
								<?php echo esc_html( $group['title'] ); ?>
							</h3>
							<ul class="boise-services__list">
								<?php foreach ( $group['items'] as $item ) : ?>
									<li><?php echo esc_html( $item ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="boise-contact" id="lets-work-together" aria-labelledby="boise-contact-heading">
			<div class="boise-contact__bg" aria-hidden="true"></div>

			<div class="boise-contact__inner container">
				<div class="boise-contact__intro" data-reveal>
					<h2 class="boise-contact__heading" id="boise-contact-heading"><?php esc_html_e( 'Let’s work together!', 'fervorcreative' ); ?></h2>
					<p><?php esc_html_e( 'Have an idea, need support, or just want to explore what’s possible? Send us a quick request — we’ll take it from there.', 'fervorcreative' ); ?></p>
				</div>

				<?php if ( $form_id && function_exists( 'gravity_form' ) ) : ?>
					<div class="boise-contact__form">
						<?php gravity_form( $form_id, false, false, false, null, true ); ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<?php echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer","className":"site-footer-part"} /-->' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the theme's own footer part, as in templates/page.html. ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
