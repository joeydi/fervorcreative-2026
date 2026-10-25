<?php
/**
 * Project Grid block template.
 *
 * Lays projects out in a repeating editorial pattern: a split row (one large
 * card beside two stacked small cards), a full-width card, a mirrored split
 * row, and another full-width card.
 *
 * @package Fervor
 */

$source = get_field( 'source' ) ?: 'all';

if ( 'selected' === $source ) {
	$ids = array_map( 'intval', (array) get_field( 'projects' ) );
	$ids = array_values( array_filter( $ids, static fn( $id ) => 'publish' === get_post_status( $id ) ) );
} else {
	$ids = fervor_get_project_ids();
}

if ( ! $ids ) {
	if ( $is_preview ) {
		echo '<p class="container">' . esc_html__( 'No projects to show yet. Publish a project or choose projects in the block settings.', 'fervorcreative' ) . '</p>';
	}
	return;
}

$rows = fervor_get_project_rows( $ids );

?>
<section<?php fervor_block_attrs( $block, array( 'project-grid', 'section' ) ); ?>>
	<div class="project-grid__inner container">
		<?php foreach ( $rows as $row ) : ?>
			<div class="<?php fervor_block_classes( array( 'project-grid__row', 'project-grid__row--' . $row['layout'] ) ); ?>">
				<?php if ( 'full' === $row['layout'] ) : ?>
					<?php get_template_part( 'partials/project-card', null, array( 'id' => $row['items'][0], 'size' => 'full' ) ); ?>
				<?php else : ?>
					<div class="project-grid__large">
						<?php get_template_part( 'partials/project-card', null, array( 'id' => $row['items'][0], 'size' => 'large' ) ); ?>
					</div>
					<?php if ( count( $row['items'] ) > 1 ) : ?>
						<div class="project-grid__stack">
							<?php foreach ( array_slice( $row['items'], 1 ) as $project_id ) : ?>
								<?php get_template_part( 'partials/project-card', null, array( 'id' => $project_id, 'size' => 'small' ) ); ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
