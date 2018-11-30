'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/components/bootstrap/dist/css/bootstrap.min.css',
            './src/AppBundle/Resources/public/components/font-awesome/css/font-awesome.min.css',
            './src/AppBundle/Resources/public/dist/css/AdminLTE.min.css',
            './src/AppBundle/Resources/public/dist/css/skins/skin-blue.min.css',
            './src/AppBundle/Resources/public/components/bootstrap-dialog/css/bootstrap-dialog.min.css',
            './src/AppBundle/Resources/public/dist/css/base.css'
        ])
        .pipe(plugins.concat('base.css'))
        .pipe(gulp.dest('./web/build/css'))
        .pipe(plugins.cleanCss())
        .pipe(plugins.rename('base.min.css'))
        .pipe(gulp.dest('./web/build/css'));
    };
};
