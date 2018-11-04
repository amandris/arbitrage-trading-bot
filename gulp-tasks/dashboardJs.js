'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/dist/js/dashboard.js'
        ])
            .pipe(plugins.concat('dashboard.js'))
            .pipe(gulp.dest('./web/build/js'))
            .pipe(plugins.uglify())
            .pipe(plugins.rename('dashboard.min.js'))
            .pipe(gulp.dest('./web/build/js'));
    };
};