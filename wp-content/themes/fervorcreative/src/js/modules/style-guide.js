/**
 * Style guide token values.
 *
 * Markup:
 *   [data-style-guide]                  – root
 *     [data-style-guide-width]          – filled with the current viewport width
 *     [data-token="--name"]             – filled with the token's resolved value
 *       data-token-type="color"         – shown as hex; lengths are shown in px
 *
 * Values are measured from the live page, so fluid tokens show what they
 * compute to at the current width. They update on resize.
 */
function initStyleGuide(root) {
	const width = root.querySelector('[data-style-guide-width]');
	const tokens = root.querySelectorAll('[data-token]');

	// A hidden element the tokens are applied to so the browser resolves them.
	const probe = document.createElement('div');
	probe.style.cssText = 'position: absolute; visibility: hidden; height: 0; pointer-events: none;';
	root.append(probe);

	// Normalise any CSS color (hex, rgb, oklch, color-mix…) to hex via canvas.
	const context = document.createElement('canvas').getContext('2d', { willReadFrequently: true });
	const toHex = (color) => {
		context.clearRect(0, 0, 1, 1);
		context.fillStyle = color;
		context.fillRect(0, 0, 1, 1);
		const [r, g, b] = context.getImageData(0, 0, 1, 1).data;
		return `#${[r, g, b].map((channel) => channel.toString(16).padStart(2, '0')).join('')}`;
	};

	const resolve = (token, type) => {
		if (type === 'color') {
			probe.style.color = `var(${token})`;
			return toHex(getComputedStyle(probe).color);
		}

		probe.style.width = `var(${token})`;
		return `${Math.round(probe.getBoundingClientRect().width * 10) / 10}px`;
	};

	const update = () => {
		if (width) {
			width.textContent = `${window.innerWidth}px`;
		}

		tokens.forEach((el) => {
			el.textContent = resolve(el.dataset.token, el.dataset.tokenType);
		});
	};

	let frame;
	window.addEventListener('resize', () => {
		cancelAnimationFrame(frame);
		frame = requestAnimationFrame(update);
	});

	update();
}

export default function styleGuide() {
	document.querySelectorAll('[data-style-guide]').forEach(initStyleGuide);
}
