<?php
/**
 * Project Pagination block template.
 *
 * @package Fervor
 */

$project_id = get_the_ID();
$adjacent   = $project_id ? fervor_get_adjacent_projects( $project_id ) : null;

if ( ! $adjacent ) {
	if ( $is_preview ) {
		echo '<p class="container">' . esc_html__( 'Project pagination: links to the previous and next projects and back to all work.', 'fervorcreative' ) . '</p>';
	}
	return;
}

?>
<nav<?php fervor_block_attrs( $block, array( 'project-pagination' ) ); ?> aria-label="<?php esc_attr_e( 'More projects', 'fervorcreative' ); ?>">
	<div class="project-pagination__inner container">
		<a class="project-pagination__link project-pagination__link--prev" href="<?php echo esc_url( get_permalink( $adjacent['prev'] ) ); ?>" rel="prev">
			<?php fervor_icon( 'arrow-left' ); ?>
			<span><?php esc_html_e( 'Previous', 'fervorcreative' ); ?><span class="project-pagination__noun"> <?php esc_html_e( 'Project', 'fervorcreative' ); ?></span><span class="visually-hidden">: <?php echo esc_html( get_the_title( $adjacent['prev'] ) ); ?></span></span>
		</a>
		<a class="project-pagination__link project-pagination__link--all" href="<?php echo esc_url( fervor_get_work_url() ); ?>">
			<?php esc_html_e( 'Back to all Work', 'fervorcreative' ); ?>
		</a>
		<a class="project-pagination__link project-pagination__link--next" href="<?php echo esc_url( get_permalink( $adjacent['next'] ) ); ?>" rel="next">
			<span><?php esc_html_e( 'Next', 'fervorcreative' ); ?><span class="project-pagination__noun"> <?php esc_html_e( 'Project', 'fervorcreative' ); ?></span><span class="visually-hidden">: <?php echo esc_html( get_the_title( $adjacent['next'] ) ); ?></span></span>
			<?php fervor_icon( 'arrow-right' ); ?>
		</a>
	</div>
</nav>
