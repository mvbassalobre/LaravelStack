let mix = require('laravel-mix');
mix.webpackConfig({
    resolve: {
        modules: [
            'node_modules',
        ]
    }
});
mix.options({
    processCssUrls: true
});
mix.autoload({
    jquery: ['$', 'window.jQuery', 'jQuery', 'jquery']
});
mix.js('resources/js/app.js', 'public/assets/js/app.js')
    .sass('resources/sass/app.scss', 'public/assets/css')
    .extract(
        [
            'jquery',
            'vue',
        ]
    );