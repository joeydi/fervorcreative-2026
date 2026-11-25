// Boise State page only (templates/boise-state.php, enqueued by inc/boise-state.php).
// Runs alongside main.js, which already handles reveals and the footer.
import '../scss/boise-state.scss';

/**
 * Counts each [data-count-up] number up from zero once it scrolls into view,
 * keeping any suffix ("1000+", "100%"). The final text is in the markup, so
 * without JS or with reduced motion the numbers just show.
 */
function countUp() {
	const elements = document.querySelectorAll('[data-count-up]');

	if (!elements.length || matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const duration = 1100;
	const easeOut = (t) => 1 - (1 - t) ** 3;

	const run = (element) => {
		const [, prefix, number, suffix] = element.textContent.trim().match(/^(\D*)(\d+)(\D*)$/) || [];

		if (!number) {
			return;
		}

		const target = Number(number);
		const start = performance.now();

		const tick = (now) => {
			const progress = Math.min((now - start) / duration, 1);

			element.textContent = `${prefix}${Math.round(target * easeOut(progress))}${suffix}`;

			if (progress < 1) {
				requestAnimationFrame(tick);
			}
		};

		requestAnimationFrame(tick);
	};

	const observer = new IntersectionObserver(
		(entries) => {
			entries
				.filter((entry) => entry.isIntersecting)
				.forEach((entry) => {
					observer.unobserve(entry.target);
					run(entry.target);
				});
		},
		{ rootMargin: '0px 0px -10% 0px' }
	);

	elements.forEach((element) => observer.observe(element));
}

countUp();
