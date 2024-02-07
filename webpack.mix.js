// eslint-disable-next-line import/no-extraneous-dependencies
const mix = require('laravel-mix');
// const path = require('path');

mix.options({
  postCss: [
    // require('rtlcss'),
  ],
  runtimeChunkPath: 'assets',
  // processCssUrls: false,
  resourceRoot: '../../../',
});

mix.autoload({
  jquery: ['$', 'window.jQuery', 'jQuery'],
});

mix.extract('assets/vendor.js');

mix.js('admin/js/pulseshare-wordpress-elementor-admin.js', 'assets/admin/js');

mix.postCss('admin/css/pulseshare-wordpress-elementor-admin.css', 'assets/admin/css');
