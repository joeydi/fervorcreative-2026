/**
 * Tabs (blocks/tabs).
 *
 * Markup:
 *   [data-tabs]
 *     [role="tab"]         – buttons with aria-selected and aria-controls
 *     [role="tabpanel"]    – one per tab; all but the selected one are hidden
 *
 * Arrow keys move between tabs and select them, as do Home and End.
 */
function initTabs(root) {
	const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
	const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));

	const select = (index) => {
		tabs.forEach((tab, i) => {
			const selected = i === index;

			tab.setAttribute('aria-selected', String(selected));
			tab.tabIndex = selected ? 0 : -1;

			if (panels[i]) {
				panels[i].hidden = !selected;
			}
		});
	};

	tabs.forEach((tab, i) => {
		tab.addEventListener('click', () => select(i));

		tab.addEventListener('keydown', (event) => {
			const last = tabs.length - 1;
			const next = { ArrowLeft: i === 0 ? last : i - 1, ArrowRight: i === last ? 0 : i + 1, Home: 0, End: last }[event.key];

			if (next === undefined) {
				return;
			}

			event.preventDefault();
			select(next);
			tabs[next].focus();
		});
	});
}

export default function tabs() {
	document.querySelectorAll('[data-tabs]').forEach(initTabs);
}
