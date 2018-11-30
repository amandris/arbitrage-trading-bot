'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
                './src/AppBundle/Resources/public/components/jquery/dist/jquery.min.js',
                './src/AppBundle/Resources/public/components/bootstrap/dist/js/bootstrap.min.js',
                './src/AppBundle/Resources/public/components/bootstrap-dialog/js/bootstrap-dialog.min.js',
                './src/AppBundle/Resources/public/dist/js/adminlte.min.js'
            ])
            .pipe(plugins.concat('base.js'))
            .pipe(gulp.dest('./web/build/js'))
            .pipe(plugins.uglify())
            .pipe(plugins.rename('base.min.js'))
            .pipe(gulp.dest('./web/build/js'));
    };
};