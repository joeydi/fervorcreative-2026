/**
 * Endless logo ticker that follows the page scroll.
 *
 * Markup:
 *   [data-ticker]              – clipping box (touch-action: pan-y); add data-ticker-speed="80" (px/s) to override the drift
 *     [data-ticker-track]      – flex row of logos; each item carries its own trailing gap
 *
 * Drifts at the base speed on its own. Scrolling down speeds it up in proportion
 * to the scroll velocity; scrolling up reverses it, and it keeps drifting that way
 * until the page scrolls down again. Every change of speed or direction eases.
 *
 * The strip also follows a dragging mouse, a swiping finger or sideways trackpad
 * scrolling 1:1. Let go, it carries on at the speed it was thrown and eases back
 * to the drift in the direction it was thrown. The box gets .is-dragging while held.
 *
 * Under prefers-reduced-motion it holds still on its own and stops where it's
 * let go, but can still be dragged to see every logo.
 */

const BOOST = 0.3; // ticker px/s per page px/s of scroll
const MAX_MULTIPLIER = 20; // cap on scroll-driven speed relative to the base drift
const MAX_THROW = 6000; // cap on release speed (px/s)
const EASE = 4; // higher settles faster
const VELOCITY_WINDOW = 100; // release speed is measured over this last stretch (ms)
const WHEEL_IDLE = 100; // a trackpad swipe counts as let go after this long without events (ms)
const WHEEL_HANDOFF = 1.5; // a fast trackpad swipe hands off to the drift once it slows to this many times the drift speed
const WHEEL_RESTART = 4; // after a handoff, a wheel event bigger than this (px) and 1.5× the last one starts a fresh swipe

const mod = (n, m) => ((n % m) + m) % m;

// Speed (px/s) over a gesture's recent samples of [time, distance moved].
function velocityOf(samples) {
	const elapsed = samples[samples.length - 1][0] - samples[0][0];
	const moved = samples[samples.length - 1][1] - samples[0][1];
	return elapsed > 0 ? (moved / elapsed) * 1000 : 0;
}

function addSample(samples, time, moved) {
	samples.push([time, moved]);
	while (samples.length > 2 && time - samples[0][0] > VELOCITY_WINDOW) {
		samples.shift();
	}
}

function initTicker(root) {
	const track = root.querySelector('[data-ticker-track]');

	if (!track) {
		return;
	}

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const originals = Array.from(track.children);
	const baseSpeed = reduceMotion ? 0 : Number(root.dataset.tickerSpeed) || 80;
	const maxSpeed = baseSpeed * MAX_MULTIPLIER;

	let setWidth = 0;
	let offset = 0;
	let velocity = baseSpeed;
	let direction = 1;
	let lastScrollY = window.scrollY;
	let lastTime = 0;
	let frame = 0;
	let drag = null;
	let wheel = null;

	const render = () => {
		if (setWidth) {
			offset = mod(offset, setWidth);
			track.style.transform = `translate3d(${-offset}px, 0, 0)`;
		}
	};

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

		render();
	};

	const tick = (time) => {
		const dt = lastTime ? Math.min((time - lastTime) / 1000, 0.1) : 0;
		lastTime = time;

		const scrollY = window.scrollY;
		const scrollVelocity = dt ? (scrollY - lastScrollY) / dt : 0;
		lastScrollY = scrollY;

		// While held, the gesture alone moves the strip.
		if (!drag && !(wheel && !wheel.released)) {
			if (scrollVelocity && baseSpeed) {
				direction = Math.sign(scrollVelocity);
			}

			const target = direction * Math.min(baseSpeed + Math.abs(scrollVelocity) * BOOST, maxSpeed);
			velocity += (target - velocity) * (1 - Math.exp(-dt * EASE));
			offset += velocity * dt;
			render();
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

	// Carry on at the release speed, heading back to the drift the way it was
	// thrown. Never slower than the drift, so a gesture that had slowed to a
	// stop (a trackpad swipe's coasting dies away before it counts as let go)
	// drifts straight on instead of stopping and picking up again.
	const release = (moved, speed) => {
		const throwDirection = Math.sign(speed) || Math.sign(moved);
		if (throwDirection) {
			direction = throwDirection;
		}
		velocity = reduceMotion ? 0 : direction * Math.min(Math.max(Math.abs(speed), baseSpeed), MAX_THROW);
		lastScrollY = window.scrollY;
	};

	// Drag and swipe. Vertical swipes still scroll the page (touch-action: pan-y),
	// and the browser cancels the pointer when one takes over.
	root.addEventListener('pointerdown', (event) => {
		if (drag || (event.pointerType === 'mouse' && event.button !== 0)) {
			return;
		}

		// No native image drag or text selection, which can swallow the release.
		if (event.pointerType === 'mouse') {
			event.preventDefault();
		}

		wheel = null;
		drag = { id: event.pointerId, x: event.clientX, from: offset, samples: [] };
		addSample(drag.samples, event.timeStamp, 0);
		root.setPointerCapture(event.pointerId);
		root.classList.add('is-dragging');
	});

	root.addEventListener('pointermove', (event) => {
		if (!drag || event.pointerId !== drag.id) {
			return;
		}

		const moved = drag.x - event.clientX;
		offset = drag.from + moved;
		render();
		addSample(drag.samples, event.timeStamp, moved);
	});

	const letGo = (event) => {
		if (!drag || event.pointerId !== drag.id) {
			return;
		}

		// A cancel (the page took over the swipe) or a dropped capture reports no
		// position of its own, so it carries on with what the moves measured.
		if (event.type === 'pointerup') {
			addSample(drag.samples, event.timeStamp, drag.x - event.clientX);
		}

		const { samples } = drag;
		drag = null;
		root.classList.remove('is-dragging');
		release(samples[samples.length - 1][1], velocityOf(samples));
	};

	// Pointer capture sends the release here wherever it happens.
	root.addEventListener('pointerup', letGo);
	root.addEventListener('pointercancel', letGo);
	root.addEventListener('lostpointercapture', letGo);
	root.addEventListener('dragstart', (event) => event.preventDefault());

	// Sideways trackpad scrolling follows 1:1 like a drag. Once the system's own
	// coasting has slowed to near the drift speed, the drift takes over and the rest
	// of the coasting is ignored (unless a fresh swipe starts), so the strip
	// never slows below its drift. A swipe that stops without coasting is let
	// go once its events stop.
	let wheelTimer;
	root.addEventListener(
		'wheel',
		(event) => {
			if (drag || Math.abs(event.deltaX) <= Math.abs(event.deltaY)) {
				return;
			}

			event.preventDefault();

			const delta = event.deltaMode === 1 ? event.deltaX * 16 : event.deltaX;
			const size = Math.abs(delta);

			clearTimeout(wheelTimer);
			wheelTimer = setTimeout(() => {
				if (wheel && !wheel.released) {
					release(wheel.moved, velocityOf(wheel.samples));
				}
				wheel = null;
			}, WHEEL_IDLE);

			if (wheel?.released) {
				if (size <= Math.max(wheel.last * 1.5, WHEEL_RESTART)) {
					wheel.last = size;
					return;
				}
				wheel = null;
			}

			if (!wheel) {
				wheel = { moved: 0, last: 0, peak: 0, released: false, samples: [] };
				addSample(wheel.samples, event.timeStamp, 0);
			}

			wheel.moved += delta;
			offset += delta;
			render();
			addSample(wheel.samples, event.timeStamp, wheel.moved);

			wheel.last = size;

			const speed = velocityOf(wheel.samples);
			wheel.peak = Math.max(wheel.peak, Math.abs(speed));
			// Coasting comes in whole px per event, so near the drift speed it
			// stutters between 1 and 2 px (60 and 120 px/s) and then stops
			// short; hand off just above the drift, where it's still smooth, and
			// let the easing bring it down.
			const handoff = baseSpeed * WHEEL_HANDOFF;
			if (baseSpeed && wheel.peak > handoff && Math.abs(speed) <= handoff) {
				wheel.released = true;
				release(wheel.moved, speed);
			}
		},
		{ passive: false }
	);

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
