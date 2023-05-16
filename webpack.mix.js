const mix = require('laravel-mix');
const path = require('path');
const focusVisible = require('postcss-focus-visible');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    resolve: {
        alias: {
            '@ui': path.resolve(__dirname, 'vendor/arkecosystem/foundation/resources/assets/'),
        },
    },
})
    .options({
        processCssUrls: false,
    })
    // App
    .js('resources/js/app.js', 'public/js')
    .js('vendor/arkecosystem/foundation/resources/assets/js/crop-image.js', 'public/js/crop-image.js')
    .postCss('resources/css/app.css', 'public/css', [require('postcss-import'), require('tailwindcss'), focusVisible()])
    .copy('node_modules/swiper/swiper-bundle.min.js', 'public/js/swiper.js')
    .js('vendor/arkecosystem/foundation/resources/assets/js/file-download.js', 'public/js/file-download.js')
    .js('vendor/arkecosystem/foundation/resources/assets/js/clipboard.js', 'public/js/clipboard.js')
    .js('vendor/arkecosystem/foundation/resources/assets/js/cookieconsent.js', 'public/js/cookie-consent.js')
    .copyDirectory('resources/images', 'public/images')
    // For FiraMono font
    .copyDirectory('resources/fonts', 'public/fonts')
    // Extract node_modules
    .extract(['alpinejs']);

if (mix.inProduction()) {
    mix.version();
}
