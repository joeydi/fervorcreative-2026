import './styles';
import capabilityIcons from './modules/capability-icons';
import carousel from './modules/carousel';
import header from './modules/header';
import menu from './modules/menu';
import player from './modules/player';
import reveal from './modules/reveal';
import slideshow from './modules/slideshow';
import styleGuide from './modules/style-guide';
import tabs from './modules/tabs';
import ticker from './modules/ticker';

capabilityIcons();
carousel();
header();
menu();
player();
reveal();
slideshow();
styleGuide();
tabs();
ticker();

// Dropped from production builds entirely.
if (import.meta.env.DEV) {
	import('./modules/grid-overlay').then(({ default: gridOverlay }) => gridOverlay());
}
