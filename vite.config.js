import { defineConfig } from 'vite';
import path from 'path';
import fs from 'fs';

/**
 * Vite plugin to force duplication of shared modules into each entry bundle.
 * Prevents automatic code splitting by making each import appear unique to Rollup.
 */
function duplicateSharedModule(modulePath) {
	const normalizedPath = path.normalize(modulePath);

	return {
		name: 'duplicate-shared-module',
		enforce: 'pre', // Run before Vite's default resolution

		resolveId(source, importer) {
			if (!importer) return null;

			// Check if source matches our target (could be relative path)
			if (source.includes('rating-input.js')) {
				// Resolve the full path
				const resolved = path.resolve(path.dirname(importer), source);
				const normalizedResolved = path.normalize(resolved);

				if (normalizedResolved === normalizedPath) {
					// Create unique virtual ID per importer to prevent deduplication
					// Use importer path as distinguisher so Rollup treats them as separate modules
					return `${normalizedPath}?inline-for=${encodeURIComponent(importer)}`;
				}
			}

			return null;
		},

		load(id) {
			// Check if this is one of our virtual IDs
			if (id.startsWith(normalizedPath + '?inline-for=')) {
				// Return the actual file content
				// Each "virtual" module gets the same content but Rollup treats them as distinct
				return fs.readFileSync(normalizedPath, 'utf-8');
			}

			return null;
		}
	};
}

/**
 * Plugin to wrap output in IIFE to prevent global namespace pollution
 * Converts ES module format to IIFE by removing export statements
 * Excludes plupload script which needs immediate execution to register Alpine components
 * Third-party map libraries (goMap, leaflet, oms) are left unwrapped - they manage their own globals
 */
function wrapIIFE() {
	return {
		name: 'wrap-iife',
		generateBundle(options, bundle) {
			for (const fileName in bundle) {
				const file = bundle[fileName];

				// Skip non-JS files
				if (file.type !== 'chunk' || !file.fileName.endsWith('.js')) {
					continue;
				}

				// Skip third-party map libraries - they manage their own globals
				if (file.fileName.includes('goMap') ||
					file.fileName.includes('oms') ||
					file.fileName.includes('leaflet')) {
					continue;
				}

				// Skip plupload - it needs immediate execution for Alpine component registration
				if (file.fileName.includes('geodir-plupload')) {
					continue;
				}

				// Remove any ES module export statements since we're converting to IIFE
				let code = file.code;

				// Remove export statements (export { ... }, export default ..., etc)
				code = code.replace(/^export\s+\{[^}]*\}\s*;?\s*$/gm, '');
				code = code.replace(/^export\s+default\s+/gm, '');
				code = code.replace(/^export\s+/gm, '');

				// Wrap the code in an IIFE
				file.code = `(function(){${code}})();`;
			}
		}
	};
}

export default defineConfig({
	plugins: [
		duplicateSharedModule(
			path.resolve(__dirname, 'resources/scripts/shared/rating-input.js')
		),
		wrapIIFE()
	],
	build: {
		outDir: 'assets',
		emptyOutDir: true,
		manifest: true, // Keep this true so PHP can read it if we ever need cache-busting hashes later

		// Disable code splitting completely - inline everything
		rollupOptions: {
			// 1. Tell Vite these exist globally, do not bundle them.
			external: ['jquery', 'bootstrap', 'alpinejs'],

			output: {
				// Use ES format first, then let wrapIIFE convert it
				format: 'es',

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
				// JavaScript - Our Code
				'geodir-frontend': path.resolve(__dirname, 'resources/scripts/frontend.js'),
				'geodir-admin': path.resolve(__dirname, 'resources/scripts/admin.js'),
				'geodir-map-handler': path.resolve(__dirname, 'resources/scripts/map-handler.js'),
				'geodir-maps': path.resolve(__dirname, 'resources/scripts/maps/index.js'),
				'geodir-add-listing': path.resolve(__dirname, 'resources/scripts/add-listing.js'),
				'geodir-plupload': path.resolve(__dirname, 'resources/scripts/plupload.js'),

				// JavaScript - Third-Party Map Libraries
				// 'goMap': path.resolve(__dirname, 'resources/vendor/maps/goMap.js'),
				'oms': path.resolve(__dirname, 'resources/vendor/maps/oms.js'),
				'oms-leaflet': path.resolve(__dirname, 'resources/vendor/maps/oms-leaflet.js'),
				'leaflet': path.resolve(__dirname, 'resources/vendor/maps/leaflet/leaflet.js'),
				'leaflet-geocode': path.resolve(__dirname, 'resources/vendor/maps/leaflet/osm.geocode.js'),

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
