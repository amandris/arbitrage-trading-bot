'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/dist/img/*/**'
        ])
            .pipe(gulp.dest('./web/build/img'));
    };
};