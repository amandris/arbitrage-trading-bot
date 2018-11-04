'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
                './src/AppBundle/Resources/public/vendor/typehead/js/typeahead.bundle.min.js',
                './src/AppBundle/Resources/public/vendor/handlebars/handlebars.min-latest.js',
                './src/AppBundle/Resources/public/dist/js/navigation.js'
            ])
            .pipe(plugins.concat('navigation.js'))
            .pipe(gulp.dest('./web/build/js'))
            .pipe(plugins.uglify())
            .pipe(plugins.rename('navigation.min.js'))
            .pipe(gulp.dest('./web/build/js'));
    };
};