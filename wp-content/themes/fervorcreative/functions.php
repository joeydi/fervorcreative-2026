<?php
/**
 * Fervor Creative functions and definitions.
 *
 * @package Fervor
 */

require 'inc/vite.php';
require 'inc/setup.php';
require 'inc/block-preview.php';
require 'inc/register-blocks.php';
require 'inc/register-cpt.php';
require 'inc/register-options.php';
require 'inc/projects.php';
require 'inc/style-guide.php';

/**
 * Echo a sanitized class string from an array of classes.
 *
 * @param string[] $classes CSS classes.
 */
function fervor_block_classes( $classes ) {
	$classes = array_unique( array_filter( array_map( 'trim', $classes ) ) );

	echo esc_attr( implode( ' ', $classes ) );
}

/**
 * Echo escaped id and class attributes for an ACF block wrapper element.
 *
 * Outputs ` id="…"` only when the block has an anchor set, and appends
 * editor-added classes (including `is-style-*` variants) to the class list.
 *
 * @param array    $block   The ACF block array.
 * @param string[] $classes CSS classes for the block.
 */
function fervor_block_attrs( $block, $classes ) {
	$anchor = trim( $block['anchor'] ?? '' );

	if ( '' !== $anchor ) {
		echo ' id="' . esc_attr( $anchor ) . '"';
	}

	$block_class_name = trim( $block['className'] ?? '' );
	if ( '' !== $block_class_name ) {
		$classes[] = $block_class_name;
	}

	$classes = array_unique( array_filter( array_map( 'trim', $classes ) ) );

	if ( ! empty( $classes ) ) {
		echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
	}
}

/**
 * Check whether a block has a given `is-style-*` variant selected.
 *
 * @param array  $block The ACF block array.
 * @param string $style Style name without the `is-style-` prefix.
 * @return bool
 */
function fervor_block_has_style( $block, $style ) {
	return false !== strpos( $block['className'] ?? '', 'is-style-' . $style );
}

/**
 * Echo escaped href, target, and (optionally) rel attributes for an ACF link array.
 *
 * @param array $link ACF link array with keys: url, target.
 */
function fervor_link_attrs( $link ) {
	$url    = $link['url'] ?? '';
	$target = $link['target'] ?? '';

	echo 'href="' . esc_url( $url ) . '"';

	if ( '_blank' === $target ) {
		echo ' target="_blank" rel="noopener noreferrer"';
	}
}

/**
 * Echo an escaped HTML attribute string from an associative array.
 *
 * Pass true for boolean attributes, false or null to omit an attribute,
 * and an array to join values with spaces.
 *
 * @param array $attrs Attribute name/value pairs.
 */
function fervor_attrs( array $attrs ): void {
	$parts = array();

	foreach ( $attrs as $name => $value ) {
		if ( false === $value || null === $value ) {
			continue;
		}

		$name = esc_attr( $name );

		if ( true === $value ) {
			$parts[] = $name;
			continue;
		}

		if ( is_array( $value ) ) {
			$value = implode( ' ', array_unique( array_filter( array_map( 'trim', $value ) ) ) );
		}

		$parts[] = $name . '="' . esc_attr( $value ) . '"';
	}

	echo implode( ' ', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Allowed tags and attributes for inline SVG output.
 *
 * @return array
 */
function fervor_svg_allowed_html() {
	$shape = array(
		'd'               => true,
		'x'               => true,
		'y'               => true,
		'x1'              => true,
		'x2'              => true,
		'y1'              => true,
		'y2'              => true,
		'cx'              => true,
		'cy'              => true,
		'r'               => true,
		'rx'              => true,
		'ry'              => true,
		'width'           => true,
		'height'          => true,
		'points'          => true,
		'fill'            => true,
		'fill-rule'       => true,
		'clip-rule'       => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
		'transform'       => true,
		'class'           => true,
	);

	return array(
		'svg'      => array(
			'xmlns'       => true,
			'viewbox'     => true,
			'width'       => true,
			'height'      => true,
			'fill'        => true,
			'stroke'      => true,
			'class'       => true,
			'role'        => true,
			'aria-hidden' => true,
			'aria-label'  => true,
			'focusable'   => true,
		),
		'title'    => array(),
		'g'        => $shape,
		'path'     => $shape,
		'rect'     => $shape,
		'circle'   => $shape,
		'ellipse'  => $shape,
		'line'     => $shape,
		'polyline' => $shape,
		'polygon'  => $shape,
	);
}

/**
 * Get an uploaded SVG attachment as inline markup.
 *
 * @param int $attachment_id Attachment ID.
 * @return string Sanitized SVG markup, or empty string if the attachment isn't an SVG.
 */
function fervor_get_svg_attachment( $attachment_id ) {
	if ( ! $attachment_id || 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return '';
	}

	$file = get_attached_file( $attachment_id );

	return $file && file_exists( $file ) ? wp_kses( file_get_contents( $file ), fervor_svg_allowed_html() ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Get an inline SVG icon from the theme's /icons/ directory.
 *
 * @param string $icon Icon path relative to /icons/, without extension (e.g. 'arrow-right', 'social/instagram').
 * @return string Sanitized SVG markup, or empty string if not found.
 */
function get_fervor_icon( $icon ) {
	$segments = array_map( 'sanitize_file_name', explode( '/', $icon ) );
	$path     = get_theme_file_path( 'icons/' . implode( '/', $segments ) . '.svg' );

	if ( ! file_exists( $path ) ) {
		return '';
	}

	return wp_kses( file_get_contents( $path ), fervor_svg_allowed_html() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

/**
 * Output an inline SVG icon.
 *
 * @param string $icon Icon path relative to /icons/, without extension.
 */
function fervor_icon( $icon ) {
	echo get_fervor_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized by get_fervor_icon.
}

/**
 * Filter an ACF repeater field to rows where the specified sub-fields are non-empty.
 *
 * Supports dot notation for nested keys (e.g. 'link.url').
 *
 * @param mixed      $items  Raw value from get_field().
 * @param string[]   $fields Sub-field keys to check.
 * @param 'or'|'and' $logic  'or' keeps rows where ANY field is populated, 'and' requires ALL.
 * @return array
 */
function fervor_get_populated_items( $items, array $fields, string $logic = 'or' ): array {
	if ( ! is_array( $items ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$items,
			static function ( $item ) use ( $fields, $logic ) {
				if ( ! is_array( $item ) ) {
					return false;
				}

				foreach ( $fields as $field ) {
					$value = $item;
					foreach ( explode( '.', $field ) as $segment ) {
						$value = is_array( $value ) ? ( $value[ $segment ] ?? '' ) : '';
					}

					$populated = is_array( $value ) ? ! empty( $value ) : '' !== trim( (string) $value );

					if ( 'or' === $logic && $populated ) {
						return true;
					}

					if ( 'and' === $logic && ! $populated ) {
						return false;
					}
				}

				return 'and' === $logic;
			}
		)
	);
}

/**
 * Split a textarea value into trimmed, non-empty lines.
 *
 * @param string|null $text Raw textarea value.
 * @return string[]
 */
function fervor_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) $text ) ) ) );
}

/**
 * Get the top-level items of the menu assigned to a theme location.
 *
 * @param string $location Menu location slug registered in inc/setup.php.
 * @return WP_Post[]
 */
function fervor_get_menu_items( $location ) {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $locations[ $location ] );

	if ( ! $items ) {
		return array();
	}

	return array_values(
		array_filter(
			$items,
			function ( $item ) {
				return ! (int) $item->menu_item_parent;
			}
		)
	);
}
