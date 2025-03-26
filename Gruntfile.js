module.exports = function ( grunt ) {
	'use strict';

	grunt.config.set( 'compress.version', '' );

	// Project configuration
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		compress: {},
		replace: {},
	} );

	// Configure the compress task for creating the plugin zip
	grunt.config.set( 'compress.dist', {
		options: {
			archive: '../tasty-recipes-lite-<%= compress.version %>.zip',
			mode: 'zip',
			level: 9, // Maximum compression
		},
		files: [
			{
				expand: true,
				cwd: './',
				src: [
					'**/*',
					'!node_modules/**',
					'!tests/**',
					'!vendor/**',
					'!.*',
					'!*.zip',
					'!**/*.less',
					'!**/*.scss',
					'!**/less/**',
					'!**/scss/**',
					'!Gruntfile.js',
					'!package.json',
					'!package-lock.json',
					'!composer.json',
					'!composer.lock',
					'!phpcs.xml',
					'!phpcs.xml.dist',
					'!phpunit.xml',
					'!phpunit.xml.dist',
					'!**/*.md',
					'!**/*.yml',
					'!zip-cli.php',
					'!stubs.php',
					'!**phpstan.**',
					'!**/*~',
					'!**/mix-manifest.json',
					'!**/webpack.mix.js',
					'!**/jest.setup.js',
					'!**/block-editor',
					'!**/recipe-editor',
					'!**/settings',
					'!**/bin',
				],
				dest: 'tasty-recipes-lite/',
			},
		],
	} );

	grunt.config.set( 'replace.setversion', {
		src: [ 'tasty-*.php' ],
		overwrite: true,
		replacements: [
			{
				from: /Version:(\s)*(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(\b)?/g,
				to: 'Version: <%= compress.version %>',
			},
			{
				from: /define\( \'TASTY_RECIPES_LITE_VERSION\', \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
				to: "define( 'TASTY_RECIPES_LITE_VERSION', '<%= compress.version %>'",
			},
			{
				from: /define\( \'TASTY_RECIPES_PLUGIN_VERSION\', \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
				to: "define( 'TASTY_RECIPES_PLUGIN_VERSION', '<%= compress.version %>'",
			},
		],
	} );

	grunt.config.set( 'replace.stabletag', {
		src: [ 'readme.txt' ],
		overwrite: true,
		replacements: [
			{
				from: /Stable tag:\ .*/g,
				to: 'Stable tag: <%= compress.version %>',
			},
		],
	} );

	grunt.config.set( 'replace.comment', {
		src: [
			'*.php',
			'**/*.php',
			'!node_modules/**',
			'!tests/**',
			'!vendor/**',
		],
		overwrite: true,
		replacements: [
			{
				from: 'since x.x',
				to: 'since <%= compress.version %>',
			},
			{
				from: 'deprecated x.x',
				to: 'deprecated <%= compress.version %>',
			},
		],
	} );

	grunt.registerTask( 'setversion', function ( v ) {
		grunt.config.set( 'compress.version', v );

		grunt.task.run( 'replace:setversion' );

		if ( ! v.includes( 'b' ) && ! v.includes( 'a' ) ) {
			// Is stable version.
			grunt.task.run( 'replace:stabletag' );
			grunt.task.run( 'replace:comment' );
		}
	} );

	grunt.registerTask( 'release', function ( v ) {
		if ( 'undefined' === typeof grunt.config.get( 'compress' ) ) {
			return;
		}

		grunt.task.run( 'setversion:' + v );
		grunt.task.run( 'compress:dist' );
	} );

	grunt.loadNpmTasks( 'grunt-text-replace' );
	grunt.loadNpmTasks( 'grunt-contrib-compress' );

	grunt.util.linefeed = '\n';
};
