# Fervor Creative

Block theme for fervorcreative.com. Custom ACF blocks, a Vite asset pipeline, BEM CSS, and CSS custom property design tokens. Block structure follows the conventions of the Astro theme.

## Requirements

- Node.js 20+
- WordPress 6.6+ with ACF Pro
- Safe SVG (for SVG logo uploads)

## Setup

```sh
npm install
npm run build
```

## Build System

| Command | Description |
|---|---|
| `npm run dev` | Vite dev server with HMR. Writes `dist/hot`; PHP loads assets from the dev server while it exists. PHP and template edits trigger a full reload. |
| `npm run build` | Production build to `dist/` with a manifest. |
| `npm run watch` | Rebuild `dist/` on change (no dev server). |

How it works:

- `src/js/main.js` (front end) and `src/js/editor.js` (block editor iframe) are the entry points.
- Both import `src/js/styles.js`: web fonts, `src/scss/main.scss`, then every `blocks/*/*.scss` via `import.meta.glob`. All CSS ships as one stylesheet.
- `inc/vite.php` enqueues entries from `dist/.vite/manifest.json`, or from the dev server when `dist/hot` exists.
- `dist/` is gitignored. Build before deploying.

## Content Model

- **Projects** (`project` post type) live at `/work/{slug}`. Their archive is disabled; the Work page (slug `work`) lists them with the Project Grid block. Order projects with the Order field (menu order).
- **Services** (`service` taxonomy) are shown on project cards and the project masthead.
- **Project Details** fields: Tagline, Intro, and Card Image (falls back to the featured image).
- **Main Menu** (Appearance → Menus): assign a menu to the Main Menu location for the header links. Only top-level items are shown.
- **Site Options** (ACF options page): logo, footer call to action, address, phone, and social links. Social icon choices come from `icons/social/*.svg`.

## Templates

| Template | Contents |
|---|---|
| `page.html` / `index.html` | Header, post content, footer |
| `single-project.html` | Header, Project Masthead, post content, Project Pagination, footer |
| `404.html` | Header, Not Found, footer |
| `page-style-guide.html` | Header, Style Guide, footer. Applies to the page with the slug `style-guide`. |

The header overlays the page in white when the first block in the content is a Hero; otherwise it sits on white with a red logo. Below 768px (`md`) the menu collapses behind a toggle that opens a full-screen panel (`src/js/modules/menu.js`).

## Blocks

| Block | Used on |
|---|---|
| Hero | Home, About, Contact |
| Section Intro | Everywhere: eyebrow, statement heading, optional copy |
| Project Grid | Home, Work |
| Capabilities | Home (Gray style), About |
| Clients | Home, About (Gray style) |
| Numbered Carousel | About |
| Image Collage | About, Contact |
| Team | About |
| Testimonials | About, projects |
| Info Columns | Contact |
| Project Media | Projects: one or two images or videos with an optional side label |
| Project Text | Projects |
| Site Header, Site Footer, Project Masthead, Project Pagination, Not Found, Style Guide | Templates only (hidden from the inserter) |

### Adding a block

```
blocks/[name]/
  ├── block.json
  ├── [name].php
  ├── [name].scss
  └── preview.png   (optional: shown in the inserter until fields are filled)
```

1. Create the folder and files. Blocks are registered automatically and added to the editor allowlist.
2. Add fields in ACF with the location rule "Block is equal to …". ACF saves them to `acf-json/`.
3. Run `npm run dev`. New SCSS files are picked up by the glob import.

## PHP Conventions

Start every block template with the preview guard:

```php
if ( fervor_block_preview( $block, $is_preview ) ) {
	return;
}
```

Helpers in `functions.php`:

| Function | Description |
|---|---|
| `fervor_block_attrs( $block, $classes )` | Echoes `id`/`class` on the block wrapper, including anchor and style classes. |
| `fervor_block_classes( $classes )` | Echoes a sanitized class string from an array. |
| `fervor_block_has_style( $block, $style )` | Whether an `is-style-*` variant is selected. |
| `fervor_link_attrs( $link )` | Echoes `href` (and `target`/`rel` for new tabs) from an ACF link array. |
| `fervor_attrs( $attrs )` | Echoes escaped attributes from an associative array. |
| `fervor_get_populated_items( $items, $fields, $logic )` | Filters repeater rows to those with populated fields. Supports dot notation. |
| `fervor_lines( $text )` | Splits a textarea into trimmed, non-empty lines. |
| `fervor_icon( $slug )` | Echoes an inline SVG from `/icons/`, e.g. `arrow-right` or `social/instagram`. |
| `fervor_get_menu_items( $location )` | Top-level items of the menu assigned to a theme location. |

Project helpers live in `inc/projects.php`.

## SCSS Conventions

- `@use "mixins" as *;` at the top of block stylesheets (`src/scss` is on the load path). Never `@import`.
- BEM: `.block`, `.block__element`, `.block--modifier`. One `__` per class. Write modifiers as siblings: `&--modifier &__element`.
- Use CSS custom properties from `src/scss/_tokens.scss` for color, type, and spacing.
- Shared type styles are mixins: `eyebrow`, `statement`, `display`.
- Media queries are mobile first: base styles are the smallest layout, and `@include sm`, `md`, `lg`, `xl`, `xxl` (576, 768, 992, 1280, 1440px, from `_variables.scss`) apply from that width up. Don't write `max-width` queries.
- Sections get vertical rhythm from `.section`; Gray style variants add `.section--surface`.

## Style Guide

`/style-guide` shows the type, color, and spacing tokens. Create a page with the slug `style-guide` to enable it (it's excluded from search engines). Token names, groups, and declared values are read from `src/scss/_tokens.scss`, so new tokens appear automatically as long as they use the `--color-`, `--font-size-`, or `--space-` prefix. Group headings come from the comment above each run of tokens. Resolved values update with the window width.

## Fonts

| Token | Family | Weights | Source |
|---|---|---|---|
| `--font-sans` | Söhne | Leicht 300, Buch 400, Kräftig 500, Halbfett 600, Dreiviertelfett 700 (+ Kursiv) | Self-hosted from `src/fonts/`, declared in `src/scss/_fonts.scss` |
| `--font-serif` | Kepler | Regular 400 | Adobe Fonts kit `olo8rkh`, enqueued in `inc/setup.php` |
| `--font-display` | Good Condensed | Bold 700 | Adobe Fonts kit `olo8rkh` |

Kepler is for subheadings and quotes; Good Condensed is for hero headings. Add new weights to the Adobe Fonts kit before using them, or the browser will substitute the nearest one.
