'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
            './src/AppBundle/Resources/public/components/font-awesome/fonts/**/*',
            './src/AppBundle/Resources/public/components/bootstrap/fonts/**/*'
        ])
            .pipe(gulp.dest('./web/build/fonts'));
    };
};