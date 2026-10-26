/**
 * Fixed site header that shrinks once the page scrolls.
 *
 * Markup:
 *   [data-header]  – header root; gets .is-scrolled while the page is scrolled
 */

function initHeader(root) {
	const update = () => root.classList.toggle('is-scrolled', window.scrollY > 0);

	update();
	window.addEventListener('scroll', update, { passive: true });
}

export default function header() {
	document.querySelectorAll('[data-header]').forEach(initHeader);
}
