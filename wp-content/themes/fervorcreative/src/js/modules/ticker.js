/**
 * Endless logo ticker that follows the page scroll.
 *
 * Markup:
 *   [data-ticker]              – clipping box; add data-ticker-speed="40" (px/s) to override the drift
 *     [data-ticker-track]      – flex row of logos; each item carries its own trailing gap
 *
 * Drifts at the base speed on its own. Scrolling down speeds it up in proportion
 * to the scroll velocity; scrolling up reverses it, and it keeps drifting that way
 * until the page scrolls down again. Every change of speed or direction eases.
 * Static under prefers-reduced-motion.
 */

const BOOST = 0.3; // ticker px/s per page px/s of scroll
const MAX_MULTIPLIER = 20; // cap on speed relative to the base drift
const EASE = 4; // higher settles faster

function initTicker(root) {
	const track = root.querySelector('[data-ticker-track]');

	if (!track || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	const originals = Array.from(track.children);
	const baseSpeed = Number(root.dataset.tickerSpeed) || 80;
	const maxSpeed = baseSpeed * MAX_MULTIPLIER;

	let setWidth = 0;
	let offset = 0;
	let velocity = baseSpeed;
	let direction = 1;
	let lastScrollY = window.scrollY;
	let lastTime = 0;
	let frame = 0;

	// Fill the box plus one extra set with copies, so wrapping by one set width is seamless.
	const layout = () => {
		track.querySelectorAll('[data-ticker-clone]').forEach((clone) => clone.remove());
		setWidth = track.scrollWidth;

		if (!setWidth) {
			return;
		}

		const copies = Math.ceil(root.clientWidth / setWidth) + 1;
		for (let i = 0; i < copies; i++) {
			originals.forEach((item) => {
				const clone = item.cloneNode(true);
				clone.setAttribute('data-ticker-clone', '');
				clone.setAttribute('aria-hidden', 'true');
				clone.querySelectorAll('img').forEach((img) => img.setAttribute('alt', ''));
				track.append(clone);
			});
		}
	};

	const tick = (time) => {
		const dt = lastTime ? Math.min((time - lastTime) / 1000, 0.1) : 0;
		lastTime = time;

		const scrollY = window.scrollY;
		const scrollVelocity = dt ? (scrollY - lastScrollY) / dt : 0;
		lastScrollY = scrollY;

		if (scrollVelocity) {
			direction = Math.sign(scrollVelocity);
		}

		const target = direction * Math.min(baseSpeed + Math.abs(scrollVelocity) * BOOST, maxSpeed);
		velocity += (target - velocity) * (1 - Math.exp(-dt * EASE));

		if (setWidth) {
			offset = (((offset + velocity * dt) % setWidth) + setWidth) % setWidth;
			track.style.transform = `translate3d(${-offset}px, 0, 0)`;
		}

		frame = requestAnimationFrame(tick);
	};

	const start = () => {
		if (frame) return;
		// Scrolling that happened while paused isn't velocity.
		lastScrollY = window.scrollY;
		lastTime = 0;
		frame = requestAnimationFrame(tick);
	};

	const stop = () => {
		cancelAnimationFrame(frame);
		frame = 0;
	};

	// Re-measure as logos load and the box resizes. Only the originals and the
	// box are watched, so adding clones doesn't retrigger it.
	let pending = 0;
	const observer = new ResizeObserver(() => {
		cancelAnimationFrame(pending);
		pending = requestAnimationFrame(layout);
	});
	observer.observe(root);
	originals.forEach((item) => observer.observe(item));

	new IntersectionObserver(([entry]) => (entry.isIntersecting ? start() : stop())).observe(root);
}

export default function ticker() {
	document.querySelectorAll('[data-ticker]').forEach(initTicker);
}
