<?php
/**
 * Tab block template.
 *
 * One panel of a Tabs block; the parent builds the tab buttons from the
 * id and data-tab-title on each panel.
 *
 * @package Fervor
 */

$title = get_field( 'title' );

if ( $is_preview ) {
	?>
	<div class="tabs__panel">
		<p class="tabs__editor-label container"><?php echo esc_html( $title ? $title : __( 'Untitled tab', 'fervorcreative' ) ); ?></p>
		<InnerBlocks />
	</div>
	<?php
	return;
}

if ( ! $title ) {
	return;
}

$id = 'tab-' . sanitize_title( $title );

?>
<div
	<?php
	fervor_attrs(
		array(
			'class'           => 'tabs__panel',
			'id'              => $id . '-panel',
			'role'            => 'tabpanel',
			'aria-labelledby' => $id,
			'tabindex'        => '0',
			'data-tab-title'  => $title,
		)
	);
	?>
><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered inner blocks. ?></div>
