/**
 * Column grid overlay for visual debugging. Loaded only by the Vite dev server.
 *
 * Ctrl+L toggles it (matching Sketch's layout grid shortcut); the state is
 * remembered across page loads.
 */
import '../../scss/grid-overlay.scss';

const STORAGE_KEY = 'fervor-grid-overlay';
const COLUMNS = 12;

const remember = (visible) => {
	try {
		localStorage.setItem(STORAGE_KEY, visible ? '1' : '');
	} catch {
		// Storage can be unavailable (private mode, blocked site data).
	}
};

const recall = () => {
	try {
		return localStorage.getItem(STORAGE_KEY) === '1';
	} catch {
		return false;
	}
};

export default function gridOverlay() {
	const overlay = document.createElement('div');
	overlay.className = 'grid-overlay';
	overlay.setAttribute('aria-hidden', 'true');
	overlay.hidden = !recall();
	overlay.innerHTML = `<div class="container grid-overlay__columns">${'<div class="grid-overlay__column"></div>'.repeat(COLUMNS)}</div>`;
	document.body.append(overlay);

	document.addEventListener('keydown', (event) => {
		if (!event.ctrlKey || event.metaKey || event.altKey || event.shiftKey || event.key.toLowerCase() !== 'l') {
			return;
		}

		event.preventDefault();
		overlay.hidden = !overlay.hidden;
		remember(!overlay.hidden);
	});
}
