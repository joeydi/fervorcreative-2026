/**
 * Fades content up into place, a beat apart, once it scrolls into view
 * (src/scss/_reveal.scss). Does nothing unless the head script has added
 * .has-reveal to <html>, which it skips for reduced motion.
 *
 * Markup:
 *   [data-reveal]       – its children reveal in turn when it comes into view
 *   [data-reveal-item]  – reveals on its own; items that come into view
 *                         together (a row of cards) reveal in turn
 */

// Things reveal once their top passes 90% of the way down the viewport.
const ROOT_MARGIN = '0px 0px -10% 0px';

function show(elements) {
	elements.forEach((element, i) => {
		element.style.setProperty('--reveal-index', i);
		element.classList.add('is-revealed');
	});
}

function revealAll(targets) {
	targets.filter((target) => target.hasAttribute('data-reveal')).forEach((group) => show(Array.from(group.children)));
	show(targets.filter((target) => target.hasAttribute('data-reveal-item')));
}

export default function reveal() {
	const targets = Array.from(document.querySelectorAll('[data-reveal], [data-reveal-item]'));

	if (!targets.length || !document.documentElement.classList.contains('has-reveal')) {
		return;
	}

	// Anything on screen at load reveals straight away, even below the 80%
	// line, so nothing in view waits for a scroll. Anything not rendered (in a
	// hidden tab panel) waits for the observer to see it once it's shown.
	const onScreen = targets.filter(
		(target) => target.getClientRects().length && target.getBoundingClientRect().top < window.innerHeight
	);

	revealAll(onScreen);

	const observer = new IntersectionObserver(
		(entries) => {
			const entering = entries.filter((entry) => entry.isIntersecting).map((entry) => entry.target);

			entering.forEach((target) => observer.unobserve(target));
			revealAll(entering);
		},
		{ rootMargin: ROOT_MARGIN }
	);

	targets.filter((target) => !onScreen.includes(target)).forEach((target) => observer.observe(target));
}
