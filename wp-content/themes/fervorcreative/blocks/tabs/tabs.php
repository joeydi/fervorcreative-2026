<?php
/**
 * Tabs block template.
 *
 * Each Tab block inside renders its own panel (blocks/tab); this reads their
 * titles and ids back out of the rendered panels to build the tab buttons,
 * and hides every panel but the first until src/js/modules/tabs.js takes over.
 *
 * @package Fervor
 */

if ( $is_preview ) {
	?>
	<section<?php fervor_block_attrs( $block, array( 'tabs' ) ); ?>>
		<InnerBlocks allowedBlocks="<?php echo esc_attr( wp_json_encode( array( 'fervor/tab' ) ) ); ?>" />
	</section>
	<?php
	return;
}

$tabs   = array();
$panels = new WP_HTML_Tag_Processor( $content );

while ( $panels->next_tag( array( 'class_name' => 'tabs__panel' ) ) ) {
	if ( $tabs ) {
		$panels->set_attribute( 'hidden', true );
	}

	$tabs[] = array(
		'id'    => $panels->get_attribute( 'aria-labelledby' ),
		'panel' => $panels->get_attribute( 'id' ),
		'title' => $panels->get_attribute( 'data-tab-title' ),
	);
}

if ( ! $tabs ) {
	return;
}

?>
<section<?php fervor_block_attrs( $block, array( 'tabs' ) ); ?> data-tabs>
	<div class="tabs__nav container">
		<div class="tabs__list" role="tablist" data-reveal>
			<?php foreach ( $tabs as $i => $tab ) : ?>
				<button
					<?php
					fervor_attrs(
						array(
							'type'          => 'button',
							'class'         => 'tabs__tab',
							'role'          => 'tab',
							'id'            => $tab['id'],
							'aria-controls' => $tab['panel'],
							'aria-selected' => 0 === $i ? 'true' : 'false',
							'tabindex'      => 0 === $i ? false : '-1',
						)
					);
					?>
				><?php echo esc_html( $tab['title'] ); ?></button>
			<?php endforeach; ?>
		</div>
	</div>

	<?php echo $panels->get_updated_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered inner blocks. ?>
</section>
