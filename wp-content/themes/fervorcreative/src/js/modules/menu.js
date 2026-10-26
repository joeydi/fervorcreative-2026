/**
 * Full-screen mobile menu.
 *
 * Markup:
 *   [data-menu]                – header root; gets .is-open while the menu is open
 *                                and .is-closing while it fades out
 *     [data-menu-toggle]       – button with aria-expanded and aria-controls
 *
 * While open, page scroll is locked and the rest of the page is made inert.
 * Escape closes the menu, as does widening past the mobile breakpoint.
 */

// Matches the `md` mixin ($md: 768px).
const desktop = window.matchMedia('(min-width: 768px)');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

function initMenu(root) {
	const toggle = root.querySelector('[data-menu-toggle]');

	if (!toggle) {
		return;
	}

	// The header block sits inside a template part; its siblings are the rest of the page.
	const part = root.closest('.wp-block-template-part') || root;
	const siblings = Array.from(part.parentElement.children).filter((el) => el !== part);

	const isOpen = () => root.classList.contains('is-open') && !root.classList.contains('is-closing');

	let closeTimer;

	const finishClose = () => {
		clearTimeout(closeTimer);
		root.classList.remove('is-open', 'is-closing');
		document.documentElement.classList.remove('has-open-menu');
	};

	const setOpen = (open, animate = true) => {
		toggle.setAttribute('aria-expanded', String(open));
		siblings.forEach((el) => (el.inert = open));

		if (open) {
			clearTimeout(closeTimer);
			root.classList.remove('is-closing');
			root.classList.add('is-open');
			document.documentElement.classList.add('has-open-menu');
		} else if (animate && !reduceMotion.matches) {
			// Keep the panel up until its fade-out animation ends, with a fallback in
			// case animationend never fires and the page would stay scroll-locked.
			root.classList.add('is-closing');
			closeTimer = setTimeout(finishClose, 500);
		} else {
			finishClose();
		}
	};

	root.addEventListener('animationend', (event) => {
		if (event.target === root && root.classList.contains('is-closing')) {
			finishClose();
		}
	});

	toggle.addEventListener('click', () => setOpen(!isOpen()));

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && isOpen()) {
			setOpen(false);
			toggle.focus();
		}
	});

	desktop.addEventListener('change', (event) => {
		if (event.matches && root.classList.contains('is-open')) {
			setOpen(false, false);
		}
	});
}

export default function menu() {
	document.querySelectorAll('[data-menu]').forEach(initMenu);
}
