/**
 * Plays the animated capability icons (blocks/capabilities/capability-icons.scss)
 * once as they scroll into view, a beat apart, and again when their column is
 * hovered.
 *
 * Markup:
 *   [data-capability-icon]  – wraps an inline SVG; gets .is-playing to run it
 */

const STAGGER = 350;

const isRunning = (icon) => icon.getAnimations({ subtree: true }).some((animation) => animation.playState === 'running');

// Removing and re-adding the class restarts the CSS animations.
function play(icon) {
	icon.classList.remove('is-playing');
	void icon.offsetWidth;
	icon.classList.add('is-playing');
}

export default function capabilityIcons() {
	const icons = Array.from(document.querySelectorAll('[data-capability-icon]'));

	if (!icons.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries
				.filter((entry) => entry.isIntersecting)
				.forEach((entry, i) => {
					observer.unobserve(entry.target);
					setTimeout(() => play(entry.target), i * STAGGER);
				});
		},
		{ threshold: 1 }
	);

	icons.forEach((icon) => {
		observer.observe(icon);
		icon.closest('.capabilities__column')?.addEventListener('mouseenter', () => {
			if (!isRunning(icon)) {
				play(icon);
			}
		});
	});
}
