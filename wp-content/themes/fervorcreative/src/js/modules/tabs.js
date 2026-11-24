/**
 * Tabs (blocks/tabs).
 *
 * Markup:
 *   [data-tabs]
 *     [role="tab"]         – buttons with aria-selected and aria-controls
 *     [role="tabpanel"]    – one per tab; all but the selected one are hidden
 *
 * Arrow keys move between tabs and select them, as do Home and End.
 *
 * Each tab has a link: its id without the "tab-" prefix that blocks/tab
 * gives it, so the Initiatives tab (#tab-initiatives) is #initiatives.
 * Opening the page with that hash, or following an in-page link to it,
 * selects the tab and scrolls to the tab row. Selecting a tab updates the
 * address bar to match (the first tab clears the hash) without adding to
 * the history, so the URL can be copied and sent as is.
 */
const slugOf = (tab) => tab.id.replace(/^tab-/, '');

function initTabs(root) {
	const tabs = Array.from(root.querySelectorAll('[role="tab"]'));
	const panels = tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));
	const list = root.querySelector('[role="tablist"]');

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

	const updateUrl = (index) => {
		const hash = index === 0 ? '' : `#${slugOf(tabs[index])}`;

		history.replaceState(history.state, '', `${location.pathname}${location.search}${hash}`);
	};

	// Selects the tab the hash names, if it names one of these.
	const fromHash = ({ scroll }) => {
		const slug = decodeURIComponent(location.hash.slice(1));
		const index = slug ? tabs.findIndex((tab) => slugOf(tab) === slug) : -1;

		if (index === -1) {
			return;
		}

		select(index);

		if (scroll) {
			list.scrollIntoView({ block: 'start' });
		}
	};

	tabs.forEach((tab, i) => {
		tab.addEventListener('click', () => {
			select(i);
			updateUrl(i);
		});

		tab.addEventListener('keydown', (event) => {
			const last = tabs.length - 1;
			const next = { ArrowLeft: i === 0 ? last : i - 1, ArrowRight: i === last ? 0 : i + 1, Home: 0, End: last }[event.key];

			if (next === undefined) {
				return;
			}

			event.preventDefault();
			select(next);
			updateUrl(next);
			tabs[next].focus();
		});
	});

	window.addEventListener('hashchange', () => fromHash({ scroll: true }));

	// Select straight away so the right panel shows; scroll once the page has
	// laid out its images and fonts so the tab row doesn't drift out of place.
	fromHash({ scroll: false });

	if (location.hash) {
		const scroll = () => fromHash({ scroll: true });

		if (document.readyState === 'complete') {
			scroll();
		} else {
			window.addEventListener('load', scroll, { once: true });
		}
	}
}

export default function tabs() {
	document.querySelectorAll('[data-tabs]').forEach(initTabs);
}
