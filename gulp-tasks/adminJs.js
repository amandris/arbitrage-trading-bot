'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/dist/js/admin.js'
        ])
            .pipe(plugins.concat('admin.js'))
            .pipe(gulp.dest('./web/build/js'))
            .pipe(plugins.uglify())
            .pipe(plugins.rename('admin.min.js'))
            .pipe(gulp.dest('./web/build/js'));
    };
};