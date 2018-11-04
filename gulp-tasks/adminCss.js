'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/dist/css/admin.css'
        ])
            .pipe(gulp.dest('./web/build/css'))
            .pipe(plugins.cleanCss())
            .pipe(plugins.rename('admin.min.css'))
            .pipe(gulp.dest('./web/build/css'));
    };
};


