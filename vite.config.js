import { defineConfig } from 'vite';
import laravel, { refreshPaths } from 'laravel-vite-plugin';

export default defineConfig({
	plugins: [
		laravel({
			input: [
				'resources/css/app.css',
				'resources/js/app.js',
				'resources/js/filament-app.js',
			],
			refresh: [
				...refreshPaths,
				'app/Livewire/**',
			],
		}),
	],
});
