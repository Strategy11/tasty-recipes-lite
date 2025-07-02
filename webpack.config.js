const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { resolve } = require( 'path' );
const IgnoreEmitPlugin = require( 'ignore-emit-webpack-plugin' );

const cssOnlyEntries = [
	'editor',
	'print',
	'ratings',
	'recipe-print-controls',
	'recipe-card-preview',
	'recipe',
	'reset',
	'common',
	'edit-page',
	'index-page',
];

module.exports = {
	...defaultConfig,
	plugins: [
		...defaultConfig.plugins,
		new IgnoreEmitPlugin(
			cssOnlyEntries.map( ( entry ) => `${ entry }.build.js` )
		),
	],
	resolve: {
		...defaultConfig.resolve,
		alias: {
			'@library': resolve(
				__dirname,
				'inc/libs/tasty-framework/assets/library'
			),
		},
	},
	entry: {
		'recipes-block': resolve( __dirname, 'assets/block-editor/index.js' ),
		'onboarding-wizard': resolve(
			__dirname,
			'assets/js/onboarding-wizard/index.js'
		),
		'recipe-print-controls-js': resolve(
			__dirname,
			'assets/js/recipe-print-controls.js'
		),
		settings: resolve( __dirname, 'assets/settings/index.js' ),
		'recipe-js': [
			resolve( __dirname, 'assets/js/smooth-scroll.js' ),
			resolve( __dirname, 'assets/js/ratings.js' ),
			resolve( __dirname, 'assets/js/video-toggle.js' ),
		],
		editor: resolve( __dirname, 'assets/scss/editor.scss' ),
		print: resolve( __dirname, 'assets/scss/print.scss' ),
		ratings: resolve( __dirname, 'assets/scss/ratings.scss' ),
		recipe: resolve( __dirname, 'assets/scss/recipe.scss' ),
		reset: resolve( __dirname, 'assets/scss/reset.scss' ),
		common: resolve( __dirname, 'assets/scss/common.scss' ),
		'explorer-page': resolve(
			__dirname,
			'assets/js/recipe-explorer/explorer-page.js'
		),
		'recipe-card-preview': resolve(
			__dirname,
			'assets/scss/recipe-card-preview.scss'
		),
		'recipe-card-preview-js': resolve(
			__dirname,
			'assets/settings/recipe-card-preview.js'
		),
		'recipe-print-controls': resolve(
			__dirname,
			'assets/scss/recipe-print-controls.scss'
		),
		'recipe-editor-js': resolve(
			__dirname,
			'assets/recipe-editor/index.js'
		),
	},
	output: {
		filename: '[name].build.js',
		path: resolve( process.cwd(), 'assets/dist' ),
		clean: false,
	},
	optimization: {
		...defaultConfig.optimization,
		splitChunks: false,
	},
};
