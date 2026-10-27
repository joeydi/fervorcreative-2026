<?php
/**
 * Project query and layout helpers.
 *
 * @package Fervor
 */

/**
 * Get published project IDs in display order (menu order, then newest first).
 *
 * @param array $args Extra WP_Query arguments.
 * @return int[]
 */
function fervor_get_project_ids( $args = array() ) {
	return get_posts(
		array_merge(
			array(
				'post_type'      => 'project',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'fields'         => 'ids',
				'no_found_rows'  => true,
			),
			$args
		)
	);
}

/**
 * Get the service names assigned to a project.
 *
 * @param int|WP_Post $post Project post.
 * @return string[]
 */
function fervor_get_project_services( $post ) {
	$terms = get_the_terms( $post, 'service' );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}

	return wp_list_pluck( $terms, 'name' );
}

/**
 * Get the image ID used for a project's card: the Card Image field, falling
 * back to the featured image.
 *
 * @param int $post_id Project ID.
 * @return int
 */
function fervor_get_project_card_image( $post_id ) {
	return (int) ( get_field( 'card_image', $post_id ) ?: get_post_thumbnail_id( $post_id ) );
}

/**
 * Get the previous and next projects, wrapping around at either end.
 *
 * @param int $post_id Current project ID.
 * @return array{prev: int, next: int}|null Null when there are fewer than two projects.
 */
function fervor_get_adjacent_projects( $post_id ) {
	$ids   = fervor_get_project_ids();
	$count = count( $ids );
	$index = array_search( (int) $post_id, $ids, true );

	if ( $count < 2 || false === $index ) {
		return null;
	}

	return array(
		'prev' => $ids[ ( $index - 1 + $count ) % $count ],
		'next' => $ids[ ( $index + 1 ) % $count ],
	);
}

/**
 * Get the URL of the Work page (slug `work`), which lists all projects.
 *
 * @return string
 */
function fervor_get_work_url() {
	$page = get_page_by_path( 'work' );

	return $page ? get_permalink( $page ) : home_url( '/work/' );
}

/**
 * Group project IDs into the editorial grid's repeating row pattern:
 * a split row (one large + two stacked), a full row, a mirrored split row,
 * then another full row.
 *
 * @param int[] $ids Project IDs.
 * @return array<int, array{layout: string, items: int[]}>
 */
function fervor_get_project_rows( $ids ) {
	$pattern = array(
		array( 'split', 3 ),
		array( 'full', 1 ),
		array( 'split-reverse', 3 ),
		array( 'full', 1 ),
	);

	$rows = array();
	$step = 0;

	while ( $ids ) {
		list( $layout, $size ) = $pattern[ $step % count( $pattern ) ];

		$rows[] = array(
			'layout' => $layout,
			'items'  => array_splice( $ids, 0, $size ),
		);

		++$step;
	}

	return $rows;
}
