/**
 * Slideshows: slides stacked in one frame and moved with transforms rather
 * than scrolled, so they wrap endlessly in either direction. The incoming
 * slide covers the leaving one, which moves at half speed.
 *
 * Markup:
 *   [data-slideshow]   – the frame: position: relative, overflow clipped,
 *                        touch-action: pan-y
 *     > *              – the slides, stacked (position: absolute; inset: 0)
 *
 * Add data-slideshow-autoplay="3000" to hold each slide that long between
 * moves (paused while pressed, hovered, focused, off screen or in a hidden
 * tab), and data-slideshow-duration="800" for the length of each move.
 *
 * Slides follow a dragging mouse, a swiping finger or sideways trackpad
 * scrolling, then carry on with the momentum they're let go with and settle
 * on a whole slide, like scroll snapping: a hard flick passes several. The
 * frame gets .is-ready once running and .is-dragging while held.
 */

const bezier = (t, p1, p2) => 3 * (1 - t) ** 2 * t * p1 + 3 * (1 - t) * t ** 2 * p2 + t ** 3;

function cubicBezier(x1, y1, x2, y2) {
	return (x) => {
		let lo = 0;
		let hi = 1;
		let t = x;

		for (let i = 0; i < 20; i++) {
			t = (lo + hi) / 2;
			if (bezier(t, x1, x2) < x) {
				lo = t;
			} else {
				hi = t;
			}
		}

		return bezier(t, y1, y2);
	};
}

const easeInOut = cubicBezier(0.76, 0, 0.24, 1); // --ease-in-out

// An ease that starts at `slope` times its average speed and comes to rest at
// the end: the cubic through (0, 0) and (1, 1) that's flat at 1. Between 0 and
// 3 it never overshoots; below 0 it first carries on the other way.
const carry = (slope) => (t) => (slope - 2) * t ** 3 + (3 - 2 * slope) * t ** 2 + slope * t;

// A release moves on once dragged this far (a share of the width, up to a cap
// in px) or flicked this fast (px per ms) and at least this far (px);
// otherwise it springs back.
const SWIPE_SHARE = 0.15;
const SWIPE_MAX = 80;
const SWIPE_VELOCITY = 0.3;
const FLICK_MIN = 30;

// A flick coasts as far as its release speed carries it in this long (ms),
// then settles on the nearest whole slide.
const MOMENTUM = 250;

// Time to settle a whole slide without momentum (ms), and the shortest settle.
const SETTLE_DURATION = 450;
const SETTLE_MIN = 150;

// Release speed is measured over the last stretch of a gesture (ms).
const VELOCITY_WINDOW = 100;

// A trackpad swipe counts as let go once its scrolling has died away for this
// many events in a row (the system's own coasting has begun), or paused this
// long (ms).
const WHEEL_FADING = 4;
const WHEEL_IDLE = 100;

const mod = (n, m) => ((n % m) + m) % m;

// Speed (px per ms) over a gesture's recent samples of [time, distance moved].
function velocityOf(samples) {
	const elapsed = samples[samples.length - 1][0] - samples[0][0];
	const moved = samples[samples.length - 1][1] - samples[0][1];
	return elapsed > 0 ? moved / elapsed : 0;
}

function addSample(samples, time, moved) {
	samples.push([time, moved]);
	while (samples.length > 2 && time - samples[0][0] > VELOCITY_WINDOW) {
		samples.shift();
	}
}

function initSlideshow(root) {
	const slides = Array.from(root.children);
	const count = slides.length;

	if (count < 2) {
		return;
	}

	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const delay = reduceMotion ? 0 : Number(root.dataset.slideshowAutoplay) || 0;
	const duration = Number(root.dataset.slideshowDuration) || 800;

	// Where the slideshow is, in slides. Everything moves by changing this.
	let position = 0;
	let current = -1;

	// A slide's offset from the current position, wrapped to [-count/2, count/2),
	// so the next slide is always just ahead and the previous just behind.
	const offset = (i) => {
		const d = mod(i - position, count);
		return d >= count / 2 ? d - count : d;
	};

	// Incoming slides (0 to 1 ahead) move at full speed on top; leaving slides
	// (0 to 1 behind) move at half speed beneath them.
	const render = () => {
		slides.forEach((slide, i) => {
			const d = offset(i);
			const visible = d > -1 && d < 1;

			slide.style.visibility = visible ? '' : 'hidden';
			slide.style.zIndex = d >= 0 ? 2 : 1;
			slide.style.transform = visible && d !== 0 ? `translate3d(${d < 0 ? d * 50 : d * 100}%, 0, 0)` : '';
		});

		const index = mod(Math.round(position), count);
		if (index !== current) {
			current = index;
			slides.forEach((slide, i) => {
				slide.inert = i !== index;
				if (i === index) {
					slide.removeAttribute('aria-hidden');
				} else {
					slide.setAttribute('aria-hidden', 'true');
				}
			});
		}
	};

	let frame;
	const requestRender = () => {
		cancelAnimationFrame(frame);
		frame = requestAnimationFrame(render);
	};

	// Autoplay waits out the delay after each move lands, then moves on if
	// nothing is holding it.
	let timer;
	let hovered = false;
	let focused = false;
	let inView = false;
	let drag = null;
	let wheel = null;
	let animation = null;

	const schedule = () => {
		clearTimeout(timer);
		if (delay) {
			timer = setTimeout(advance, delay);
		}
	};

	const land = () => {
		animation = null;
		position = mod(Math.round(position), count);
		render();
		schedule();
	};

	const stop = () => {
		cancelAnimationFrame(animation);
		animation = null;
		clearTimeout(timer);
	};

	const animateTo = (target, ms, ease) => {
		stop();

		const from = position;
		const start = performance.now();

		if (!ms || from === target) {
			position = target;
			land();
			return;
		}

		const step = (now) => {
			const progress = Math.min((now - start) / ms, 1);
			position = from + (target - from) * ease(progress);
			render();

			if (progress < 1) {
				animation = requestAnimationFrame(step);
			} else {
				land();
			}
		};

		animation = requestAnimationFrame(step);
	};

	// Let go of a gesture that moved the slides `moved` px at `velocity` px per
	// ms (both positive going forward). A flick coasts on and settles on the
	// nearest whole slide, always at least the next; a slower drag past the
	// threshold moves one slide; anything less springs back. The settle starts
	// at the release speed, so the motion carries straight on.
	const fling = (moved, velocity) => {
		const width = root.clientWidth;
		let target = Math.round(position);

		if (Math.abs(velocity) > SWIPE_VELOCITY && Math.abs(moved) > FLICK_MIN) {
			const reach = Math.min(Math.abs(velocity) * MOMENTUM, (count - 1) * width) * Math.sign(velocity);
			target = Math.round(position + reach / width);
			target = velocity > 0 ? Math.max(target, Math.floor(position) + 1) : Math.min(target, Math.ceil(position) - 1);
		} else if (Math.abs(moved) > Math.min(SWIPE_MAX, width * SWIPE_SHARE)) {
			target = moved > 0 ? Math.floor(position) + 1 : Math.ceil(position) - 1;
		}

		// Settle in a time that suits the distance, starting at the release speed;
		// a flick too fast for that settles sooner, so it never overshoots.
		const distance = target - position;
		const speed = velocity / width;
		let ms = Math.max(SETTLE_DURATION * Math.sqrt(Math.abs(distance)), SETTLE_MIN);
		let slope = distance ? (speed * ms) / distance : 0;

		if (slope > 3) {
			ms = Math.max((3 * distance) / speed, SETTLE_MIN);
			slope = Math.min((speed * ms) / distance, 3);
		}

		animateTo(target, reduceMotion ? 0 : ms, carry(Math.max(slope, -1)));
	};

	function advance() {
		if (drag || wheel || animation || hovered || focused || !inView || document.hidden) {
			schedule();
			return;
		}

		animateTo(Math.round(position) + 1, duration, easeInOut);
	}

	// Drag and swipe. Vertical swipes still scroll the page (touch-action:
	// pan-y), and the browser cancels the pointer when one takes over.
	root.addEventListener('pointerdown', (event) => {
		if (drag || (event.pointerType === 'mouse' && event.button !== 0)) {
			return;
		}

		// No native image drag or text selection, which can swallow the release.
		if (event.pointerType === 'mouse') {
			event.preventDefault();
		}

		stop();
		wheel = null;

		drag = { id: event.pointerId, x: event.clientX, from: position, samples: [] };
		addSample(drag.samples, event.timeStamp, 0);
		root.setPointerCapture(event.pointerId);
		root.classList.add('is-dragging');
	});

	root.addEventListener('pointermove', (event) => {
		if (!drag || event.pointerId !== drag.id) {
			return;
		}

		const moved = drag.x - event.clientX;
		position = drag.from + moved / root.clientWidth;
		requestRender();
		addSample(drag.samples, event.timeStamp, moved);
	});

	const release = (event) => {
		if (!drag || event.pointerId !== drag.id) {
			return;
		}

		const moved = drag.x - event.clientX;
		addSample(drag.samples, event.timeStamp, moved);
		const velocity = velocityOf(drag.samples);

		drag = null;
		root.classList.remove('is-dragging');
		cancelAnimationFrame(frame);

		// A cancel (the page took over the swipe) or a dropped capture springs back.
		if (event.type === 'pointerup') {
			fling(moved, velocity);
		} else {
			fling(0, 0);
		}
	};

	// Pointer capture sends the release here wherever it happens.
	root.addEventListener('pointerup', release);
	root.addEventListener('pointercancel', release);
	root.addEventListener('lostpointercapture', release);
	root.addEventListener('dragstart', (event) => event.preventDefault());

	// Sideways trackpad scrolling follows 1:1 like a drag. Once the swipe is let
	// go, it flings like a released drag, and the rest of the system's coasting
	// is ignored unless a fresh swipe starts.
	let wheelTimer;

	const letGo = () => {
		wheel.released = true;
		fling(wheel.moved, velocityOf(wheel.samples));
	};

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
					letGo();
				}
				wheel = null;
			}, WHEEL_IDLE);

			if (wheel?.released) {
				if (size <= wheel.last * 1.5) {
					wheel.last = size;
					return;
				}
				wheel = null;
			}

			if (!wheel) {
				stop();
				wheel = { moved: 0, last: 0, fading: 0, released: false, samples: [] };
				addSample(wheel.samples, event.timeStamp, 0);
			}

			wheel.moved += delta;
			position += delta / root.clientWidth;
			requestRender();
			addSample(wheel.samples, event.timeStamp, wheel.moved);

			wheel.fading = size < wheel.last ? wheel.fading + 1 : 0;
			wheel.last = size;

			if (wheel.fading >= WHEEL_FADING) {
				letGo();
			}
		},
		{ passive: false }
	);

	// Hover only counts for a mouse; a tap shouldn't hold autoplay.
	root.addEventListener('pointerenter', (event) => {
		if (event.pointerType === 'mouse') hovered = true;
	});
	root.addEventListener('pointerleave', (event) => {
		if (event.pointerType === 'mouse') {
			hovered = false;
			schedule();
		}
	});
	root.addEventListener('focusin', () => (focused = true));
	root.addEventListener('focusout', () => {
		focused = false;
		schedule();
	});

	new IntersectionObserver(([entry]) => {
		inView = entry.isIntersecting;
		if (inView && !animation && !drag) {
			schedule();
		}
	}).observe(root);

	render();
	root.classList.add('is-ready');
}

export default function slideshow() {
	document.querySelectorAll('[data-slideshow]').forEach(initSlideshow);
}
