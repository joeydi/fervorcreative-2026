/**
 * Block editor UI tweaks, loaded on the editor page around the canvas iframe.
 *
 * Adds a drag handle to the left edge of the Settings sidebar so it can be
 * widened. The width is kept per browser in localStorage.
 */
import '../scss/editor-ui.scss';

const STORAGE_KEY = 'fervor-sidebar-width';
const MIN_WIDTH = 280;
const MAX_WIDTH = 800;
const MIN_CANVAS = 480;
const STEP = 20;

const desktop = window.matchMedia('(min-width: 782px)');
const root = document.documentElement;

let width = MIN_WIDTH;

function maxWidth() {
	return Math.max(MIN_WIDTH, Math.min(MAX_WIDTH, window.innerWidth - MIN_CANVAS));
}

function clamp(value) {
	return Math.round(Math.min(maxWidth(), Math.max(MIN_WIDTH, value)));
}

function readStored() {
	try {
		return parseInt(window.localStorage.getItem(STORAGE_KEY), 10) || MIN_WIDTH;
	} catch {
		return MIN_WIDTH;
	}
}

function save() {
	try {
		window.localStorage.setItem(STORAGE_KEY, String(width));
	} catch {
		// Storage unavailable; the width just won't be remembered.
	}
}

function setWidth(value) {
	width = clamp(value);
	root.style.setProperty('--fervor-sidebar-width', `${width}px`);

	document.querySelectorAll('.fervor-sidebar-resizer').forEach((handle) => {
		handle.setAttribute('aria-valuenow', width);
		handle.setAttribute('aria-valuemax', maxWidth());
	});
}

function createHandle() {
	const handle = document.createElement('div');
	handle.className = 'fervor-sidebar-resizer';
	handle.tabIndex = 0;
	handle.setAttribute('role', 'separator');
	handle.setAttribute('aria-orientation', 'vertical');
	handle.setAttribute('aria-label', 'Resize sidebar');
	handle.setAttribute('aria-valuemin', MIN_WIDTH);
	handle.setAttribute('aria-valuemax', maxWidth());
	handle.setAttribute('aria-valuenow', width);
	handle.title = 'Drag to resize, double-click to reset';

	let startX = 0;
	let startWidth = 0;

	const onMove = (event) => setWidth(startWidth + (startX - event.clientX));

	const onEnd = (event) => {
		handle.releasePointerCapture?.(event.pointerId);
		handle.removeEventListener('pointermove', onMove);
		handle.removeEventListener('pointerup', onEnd);
		handle.removeEventListener('pointercancel', onEnd);
		document.body.classList.remove('is-resizing-sidebar');
		save();
	};

	handle.addEventListener('pointerdown', (event) => {
		if (event.button !== 0 || !desktop.matches) {
			return;
		}

		event.preventDefault();
		startX = event.clientX;
		startWidth = width;
		handle.setPointerCapture(event.pointerId);
		handle.addEventListener('pointermove', onMove);
		handle.addEventListener('pointerup', onEnd);
		handle.addEventListener('pointercancel', onEnd);
		document.body.classList.add('is-resizing-sidebar');
	});

	handle.addEventListener('dblclick', () => {
		setWidth(MIN_WIDTH);
		save();
	});

	handle.addEventListener('keydown', (event) => {
		const keys = {
			ArrowLeft: width + STEP,
			ArrowRight: width - STEP,
			Home: MIN_WIDTH,
			End: maxWidth(),
		};

		if (event.key in keys) {
			event.preventDefault();
			setWidth(keys[event.key]);
			save();
		}
	});

	return handle;
}

function attachHandles() {
	document.querySelectorAll('.interface-interface-skeleton__sidebar').forEach((sidebar) => {
		if (!sidebar.querySelector(':scope > .fervor-sidebar-resizer')) {
			sidebar.append(createHandle());
		}
	});
}

setWidth(readStored());

window.addEventListener('resize', () => setWidth(width));

const observer = new MutationObserver(attachHandles);

function start() {
	attachHandles();
	observer.observe(document.body, { childList: true, subtree: true });
}

if (document.body) {
	start();
} else {
	document.addEventListener('DOMContentLoaded', start, { once: true });
}
