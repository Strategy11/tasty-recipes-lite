const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix.sass('assets/scss/editor.scss', 'assets/dist/');
mix.sass('assets/scss/print.scss', 'assets/dist/');
mix.sass('assets/scss/ratings.scss', 'assets/dist/');
mix.sass('assets/scss/recipe.scss', 'assets/dist/');
mix.sass('assets/scss/recipe-card-preview.scss', 'assets/dist/');
mix.sass('assets/scss/recipe-print-controls.scss', 'assets/dist/');
mix.sass('assets/scss/settings.scss', 'assets/dist/');

mix.js('assets/block-editor/index.js', 'assets/dist/block-editor.build.js');
mix.js('assets/recipe-editor/index.js', 'assets/dist/recipe-editor.build.js').vue({version: 2});
mix.js('assets/settings/index.js', 'assets/dist/settings.build.js').vue({version: 2});
mix.js('assets/settings/recipe-card-preview.js', 'assets/dist/settings-recipe-card-preview.build.js');
mix.js('assets/js/recipe-print-controls.js', 'assets/dist/recipe-print-controls.build.js');
