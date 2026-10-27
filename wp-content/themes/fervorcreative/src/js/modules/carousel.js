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
 */
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

	// Slide positions relative to the first slide; the track must be position: relative.
	const slideLeft = (slide) => slide.offsetLeft - slides[0].offsetLeft;

	const currentIndex = () => {
		const left = track.scrollLeft;
		let index = 0;
		let closest = Infinity;

		slides.forEach((slide, i) => {
			const distance = Math.abs(slideLeft(slide) - left);
			if (distance < closest) {
				closest = distance;
				index = i;
			}
		});

		return index;
	};

	const goTo = (index) => {
		const slide = slides[Math.max(0, Math.min(index, slides.length - 1))];
		track.scrollTo({ left: slideLeft(slide), behavior });
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

	prev?.addEventListener('click', () => goTo(currentIndex() - 1));
	next?.addEventListener('click', () => goTo(currentIndex() + 1));

	let frame;
	track.addEventListener(
		'scroll',
		() => {
			cancelAnimationFrame(frame);
			frame = requestAnimationFrame(update);
		},
		{ passive: true }
	);
	window.addEventListener('resize', update);
	update();

	const delay = Number(root.dataset.carouselAutoplay);
	if (delay && slides.length > 1 && !reduceMotion) {
		let paused = false;
		let inView = false;
		['mouseenter', 'focusin'].forEach((type) => root.addEventListener(type, () => (paused = true)));
		['mouseleave', 'focusout'].forEach((type) => root.addEventListener(type, () => (paused = false)));
		new IntersectionObserver(([entry]) => (inView = entry.isIntersecting)).observe(root);

		setInterval(() => {
			if (!paused && inView && !document.hidden) {
				const index = currentIndex();
				goTo(index >= slides.length - 1 ? 0 : index + 1);
			}
		}, delay);
	}
}

export default function carousel() {
	document.querySelectorAll('[data-carousel]').forEach(initCarousel);
}
