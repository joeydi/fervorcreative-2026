import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

const root = path.dirname(fileURLToPath(import.meta.url));
const hotFile = path.resolve(root, 'dist/hot');

/**
 * Writes dist/hot while the dev server runs so PHP knows to load assets from
 * Vite instead of the built manifest, and full-reloads the page on PHP edits.
 */
function wordpress() {
	return {
		name: 'fervor-wordpress',
		configureServer(server) {
			server.httpServer?.once('listening', () => {
				const { port } = server.httpServer.address();
				fs.mkdirSync(path.dirname(hotFile), { recursive: true });
				fs.writeFileSync(hotFile, `http://localhost:${port}`);
			});

			const removeHotFile = () => fs.rmSync(hotFile, { force: true });
			process.on('exit', removeHotFile);
			process.on('SIGINT', () => process.exit());
			process.on('SIGTERM', () => process.exit());

			server.watcher.add(['**/*.php', 'theme.json', 'templates/*.html', 'parts/*.html']);
			server.watcher.on('change', (file) => {
				if (/\.(php|html)$|theme\.json$/.test(file)) {
					server.ws.send({ type: 'full-reload' });
				}
			});
		},
	};
}

export default defineConfig(({ command, mode }) => ({
	base: command === 'build' ? './' : '/',
	plugins: [wordpress()],
	css: {
		devSourcemap: true,
		preprocessorOptions: {
			scss: {
				loadPaths: [path.resolve(root, 'src/scss')],
			},
		},
	},
	server: {
		host: 'localhost',
		port: 5173,
		strictPort: true,
		cors: true,
		origin: 'http://localhost:5173',
	},
	build: {
		outDir: 'dist',
		emptyOutDir: true,
		manifest: true,
		sourcemap: mode === 'development',
		rollupOptions: {
			input: {
				main: path.resolve(root, 'src/js/main.js'),
				editor: path.resolve(root, 'src/js/editor.js'),
			},
		},
	},
}));
