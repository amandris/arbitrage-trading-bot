'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/dist/css/dashboard.css'
        ])
            .pipe(gulp.dest('./web/build/css'))
            .pipe(plugins.cleanCss())
            .pipe(plugins.rename('dashboard.min.css'))
            .pipe(gulp.dest('./web/build/css'));
    };
};


