<?php
/**
 * ACF block preview helpers.
 *
 * Shows a block's preview.png in the inserter and on freshly inserted blocks
 * until at least one field has been filled in.
 *
 * @package Fervor
 */

/**
 * Check whether any ACF fields on the current block have a non-empty value.
 *
 * @return bool
 */
function fervor_has_populated_fields() {
	$fields = get_fields();

	if ( ! $fields || ! is_array( $fields ) ) {
		return false;
	}

	foreach ( $fields as $value ) {
		if ( ! empty( $value ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render a block's preview.png in the editor when no fields are populated.
 *
 * Place at the top of every ACF block template:
 *   if ( fervor_block_preview( $block, $is_preview ) ) { return; }
 *
 * @param array $block      The ACF block array.
 * @param bool  $is_preview Whether the block is rendering in the editor.
 * @return bool True if a preview image was rendered and the template should stop.
 */
function fervor_block_preview( $block, $is_preview ) {
	if ( ! $is_preview || fervor_has_populated_fields() ) {
		return false;
	}

	$file = 'blocks/' . str_replace( 'fervor/', '', $block['name'] ) . '/preview.png';

	if ( ! file_exists( get_theme_file_path( $file ) ) ) {
		return false;
	}
	?>
	<div class="block-preview-image">
		<img src="<?php echo esc_url( get_theme_file_uri( $file ) ); ?>" alt="" style="display:block;width:100%;height:auto;" />
	</div>
	<?php
	return true;
}
