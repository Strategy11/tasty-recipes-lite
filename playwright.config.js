const path = require( 'node:path' );
const { defineConfig, devices } = require( '@playwright/test' );

const baseUrl = process.env.WP_BASE_URL || 'http://localhost:3000';
const artifactsPath = process.env.WP_ARTIFACTS_PATH ||
	path.join( process.cwd(), 'artifacts' );
const storageStatePath = process.env.STORAGE_STATE_PATH ||
	path.join( artifactsPath, 'storage-states/admin.json' );

module.exports = defineConfig( {
	testDir: './tests/playwright/e2e',
	outputDir: 'test-results',
	globalSetup: require.resolve( './tests/playwright/global-setup.js' ),
	fullyParallel: false,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	workers: 1,
	timeout: 60 * 1000,
	expect: {
		timeout: 10 * 1000,
	},
	reporter: process.env.CI
		? [
				[ 'list' ],
				[ 'html', { open: 'never' } ],
		  ]
		: 'list',
	use: {
		baseURL: baseUrl,
		viewport: {
			width: 1280,
			height: 720,
		},
		storageState: storageStatePath,
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
	},
	projects: [
		{
			name: 'chromium',
			use: {
				...devices[ 'Desktop Chrome' ],
			},
		},
	],
	webServer: {
		command: 'pnpm run env start',
		url: baseUrl,
		reuseExistingServer: ! process.env.CI,
		timeout: 300 * 1000,
		stdout: 'pipe',
		stderr: 'pipe',
	},
} );
