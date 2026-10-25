<?php
/**
 * Project Masthead block template.
 *
 * Reads the current project's title, Tagline and Intro fields, and Services.
 *
 * @package Fervor
 */

$project_id = get_the_ID();

if ( ! $project_id || 'project' !== get_post_type( $project_id ) ) {
	if ( $is_preview ) {
		echo '<p class="container">' . esc_html__( 'Project masthead: shows the project title, tagline, intro, and services.', 'fervorcreative' ) . '</p>';
	}
	return;
}

$tagline  = get_field( 'tagline', $project_id );
$intro    = get_field( 'intro', $project_id );
$services = fervor_get_project_services( $project_id );

?>
<section<?php fervor_block_attrs( $block, array( 'project-masthead' ) ); ?>>
	<div class="project-masthead__inner container">
		<div class="project-masthead__main">
			<h1 class="project-masthead__title"><?php echo esc_html( get_the_title( $project_id ) ); ?></h1>
			<?php if ( $tagline ) : ?>
				<p class="project-masthead__tagline"><?php echo esc_html( $tagline ); ?></p>
			<?php endif; ?>
			<?php if ( $intro ) : ?>
				<div class="project-masthead__intro"><?php echo wp_kses_post( wpautop( $intro ) ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( $services ) : ?>
			<div class="project-masthead__services">
				<h2 class="visually-hidden"><?php esc_html_e( 'Services', 'fervorcreative' ); ?></h2>
				<ul class="project-masthead__list">
					<?php foreach ( $services as $service ) : ?>
						<li><?php echo esc_html( $service ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
</section>
