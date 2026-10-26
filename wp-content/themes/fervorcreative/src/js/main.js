import './styles';
import carousel from './modules/carousel';
import header from './modules/header';
import menu from './modules/menu';
import styleGuide from './modules/style-guide';

carousel();
header();
menu();
styleGuide();

// Dropped from production builds entirely.
if (import.meta.env.DEV) {
	import('./modules/grid-overlay').then(({ default: gridOverlay }) => gridOverlay());
}
