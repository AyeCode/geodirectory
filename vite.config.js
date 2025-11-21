import { defineConfig } from 'vite';
import path from 'path';

/**
 * Plugin to wrap output in IIFE to prevent global namespace pollution
 * Excludes plupload script which needs immediate execution to register Alpine components
 */
function wrapIIFE() {
	return {
		name: 'wrap-iife',
		generateBundle(options, bundle) {
			for (const fileName in bundle) {
				const file = bundle[fileName];
				// Skip plupload - it needs immediate execution for Alpine component registration
				if (file.type === 'chunk' && file.fileName.endsWith('.js') && !file.fileName.includes('geodir-plupload')) {
					// Wrap the code in an IIFE
					file.code = `(function(){${file.code}})();`;
				}
			}
		}
	};
}

export default defineConfig({
	plugins: [wrapIIFE()],
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

				// 4. Output JS to assets/js/name.js
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
				'geodir-add-listing': path.resolve(__dirname, 'resources/scripts/add-listing.js'),
				'geodir-plupload': path.resolve(__dirname, 'resources/scripts/plupload.js'),

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
