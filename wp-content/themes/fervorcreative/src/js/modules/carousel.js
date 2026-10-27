/**
 * Scroll-snap carousels.
 *
 * Markup:
 *   [data-carousel]
 *     [data-carousel-track]     – horizontally scrolling, position: relative list of slides
 *     [data-carousel-prev]      – optional previous button
 *     [data-carousel-next]      – optional next button
 *     [data-carousel-dots]      – optional container; one button per slide is generated
 *
 * Add data-carousel-autoplay="7000" to advance automatically (paused on hover/focus,
 * and while off screen).
 * Add data-carousel-duration="800" to time each move in JS (on --ease-in-out)
 * instead of the browser's smooth scroll; autoplay then holds for the full delay
 * after each move.
 * Add data-carousel-loop to carry on from the last slide to the first, rather
 * than rewinding through them all.
 */

// --ease-in-out: cubic-bezier(0.76, 0, 0.24, 1).
const bezier = (t, p1, p2) => 3 * (1 - t) ** 2 * t * p1 + 3 * (1 - t) * t ** 2 * p2 + t ** 3;

function easeInOut(x) {
	let lo = 0;
	let hi = 1;
	let t = x;

	for (let i = 0; i < 20; i++) {
		t = (lo + hi) / 2;
		if (bezier(t, 0.76, 0.24) < x) {
			lo = t;
		} else {
			hi = t;
		}
	}

	return bezier(t, 0, 1);
}

function initCarousel(root) {
	const track = root.querySelector('[data-carousel-track]');
	const prev = root.querySelector('[data-carousel-prev]');
	const next = root.querySelector('[data-carousel-next]');
	const dotsWrap = root.querySelector('[data-carousel-dots]');

	if (!track) {
		return;
	}

	const slides = Array.from(track.children);
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const behavior = reduceMotion ? 'auto' : 'smooth';
	const duration = reduceMotion ? 0 : Number(root.dataset.carouselDuration) || 0;

	// To loop, a copy of the first slide trails the last. Moving onto it looks
	// like moving onto the first, and it then jumps back to the real one.
	const stops = [...slides];
	if (root.hasAttribute('data-carousel-loop') && slides.length > 1) {
		const clone = slides[0].cloneNode(true);
		clone.setAttribute('aria-hidden', 'true');
		clone.inert = true;
		clone.querySelectorAll('img').forEach((img) => (img.alt = ''));
		track.append(clone);
		stops.push(clone);
	}
	const loops = stops.length > slides.length;

	// Slide positions relative to the first slide; the track must be position: relative.
	const slideLeft = (slide) => slide.offsetLeft - slides[0].offsetLeft;

	const currentStop = () => {
		const left = track.scrollLeft;
		let index = 0;
		let closest = Infinity;

		stops.forEach((slide, i) => {
			const distance = Math.abs(slideLeft(slide) - left);
			if (distance < closest) {
				closest = distance;
				index = i;
			}
		});

		return index;
	};

	const currentIndex = () => currentStop() % slides.length;

	// On the trailing copy, jump back to the real first slide.
	const unwrap = () => {
		if (loops && currentStop() === stops.length - 1) {
			track.scrollLeft = 0;
		}
	};

	let animation;

	const scrollTo = (left) => {
		if (!duration) {
			track.scrollTo({ left, behavior });
			return;
		}

		cancelAnimationFrame(animation);

		const start = track.scrollLeft;
		const startTime = performance.now();

		// Snapping would fight the animation, so it's off until the move lands.
		track.style.scrollSnapType = 'none';

		const step = (now) => {
			const progress = Math.min((now - startTime) / duration, 1);
			track.scrollLeft = start + (left - start) * easeInOut(progress);

			if (progress < 1) {
				animation = requestAnimationFrame(step);
			} else {
				track.style.scrollSnapType = '';
				unwrap();
			}
		};

		animation = requestAnimationFrame(step);
	};

	const goTo = (index) => {
		const slide = stops[Math.max(0, Math.min(index, stops.length - 1))];
		scrollTo(slideLeft(slide));
	};

	const dots = dotsWrap
		? slides.map((_, i) => {
				const dot = document.createElement('button');
				dot.type = 'button';
				dot.className = 'carousel-dot';
				dot.setAttribute('aria-label', `Show slide ${i + 1}`);
				dot.addEventListener('click', () => goTo(i));
				dotsWrap.append(dot);
				return dot;
			})
		: [];

	const update = () => {
		const maxScroll = track.scrollWidth - track.clientWidth - 1;
		const index = currentIndex();

		if (prev) prev.disabled = track.scrollLeft <= 1;
		if (next) next.disabled = track.scrollLeft >= maxScroll;

		dots.forEach((dot, i) => dot.setAttribute('aria-current', i === index ? 'true' : 'false'));
	};

	prev?.addEventListener('click', () => goTo(currentStop() - 1));
	next?.addEventListener('click', () => goTo(currentStop() + 1));

	let frame;
	track.addEventListener(
		'scroll',
		() => {
			cancelAnimationFrame(frame);
			frame = requestAnimationFrame(update);
		},
		{ passive: true }
	);
	// A swipe onto the trailing copy lands on the real first slide too.
	track.addEventListener('scrollend', () => {
		if (track.style.scrollSnapType !== 'none') {
			unwrap();
		}
	});
	window.addEventListener('resize', update);
	update();

	const delay = Number(root.dataset.carouselAutoplay);
	if (delay && slides.length > 1 && !reduceMotion) {
		let paused = false;
		let inView = false;
		['mouseenter', 'focusin'].forEach((type) => root.addEventListener(type, () => (paused = true)));
		['mouseleave', 'focusout'].forEach((type) => root.addEventListener(type, () => (paused = false)));

		new IntersectionObserver(([entry]) => {
			inView = entry.isIntersecting;

			// Slides will show without anyone scrolling to them, so load them now.
			if (inView) {
				root.querySelectorAll('img[loading="lazy"]').forEach((img) => (img.loading = 'eager'));
			}
		}).observe(root);

		const advance = () => {
			if (paused || !inView || document.hidden) {
				setTimeout(advance, delay);
				return;
			}

			unwrap();
			const index = currentStop();
			goTo(loops || index < stops.length - 1 ? index + 1 : 0);
			setTimeout(advance, delay + duration);
		};

		setTimeout(advance, delay);
	}
}

export default function carousel() {
	document.querySelectorAll('[data-carousel]').forEach(initCarousel);
}
