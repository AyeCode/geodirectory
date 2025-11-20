import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
	plugins: [],
	build: {
		outDir: 'assets',
		emptyOutDir: true,
		manifest: true, // Keep this true so PHP can read it if we ever need cache-busting hashes later

		rollupOptions: {
			// 1. Tell Vite these exist globally, do not bundle them.
			external: ['jquery', 'bootstrap', 'alpinejs'],

			output: {
				// 2. Map external imports to global window variables
				globals: {
					jquery: 'jQuery',
					bootstrap: 'bootstrap',
					alpinejs: 'Alpine'
				},

				// 3. Output JS to assets/js/name.js
				entryFileNames: `js/[name].js`,
				chunkFileNames: `js/[name].js`,

				// 4. Output CSS/Images to assets/css/ and assets/images/
				assetFileNames: (assetInfo) => {
					const info = assetInfo.name.split('.');
					const extType = info[info.length - 1];
					if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
						return `images/[name][extname]`;
					}
					return `css/[name][extname]`;
				},
			},

			// 5. Define distinct Entry Points
			input: {
				// JavaScript
				'geodir-frontend': path.resolve(__dirname, 'resources/scripts/frontend.js'),
				'geodir-admin': path.resolve(__dirname, 'resources/scripts/admin.js'),
				'geodir-map-handler': path.resolve(__dirname, 'resources/scripts/map-handler.js'),

				// Styles (SCSS) - These will be output as separate .css files
				'geodir-frontend-styles': path.resolve(__dirname, 'resources/styles/frontend.scss'),
				'geodir-admin-styles': path.resolve(__dirname, 'resources/styles/admin.scss'),
			},
		},

		minify: 'esbuild',
		sourcemap: true,
	},
	resolve: {
		alias: {
			'@': path.resolve(__dirname, 'resources'),
		},
	},
});
