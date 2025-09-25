module.exports = function ( grunt ) {
	'use strict';

	grunt.config.set( 'compress.version', '' );

	// Project configuration
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		compress: {},
		replace: {},
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
	} );

	grunt.loadNpmTasks( 'grunt-text-replace' );

	grunt.util.linefeed = '\n';
};
