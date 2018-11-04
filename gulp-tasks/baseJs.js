'use strict';

module.exports = function(gulp, plugins) {
    return function () {
        gulp.src([
                './src/AppBundle/Resources/public/components/jquery/dist/jquery.min.js',
                './src/AppBundle/Resources/public/components/moment/moment.js',
                './src/AppBundle/Resources/public/components/bootstrap/dist/js/bootstrap.min.js',
                './src/AppBundle/Resources/public/components/datatables.net/js/jquery.dataTables.min.js',
                './src/AppBundle/Resources/public/components/datatables.net-bs/js/dataTables.bootstrap.min.js',
                './src/AppBundle/Resources/public/components/jquery-slimscroll/jquery.slimscroll.min.js',
                './src/AppBundle/Resources/public/components/fastclick/lib/fastclick.js',
                './src/AppBundle/Resources/public/components/bootstrap-toggle/js/bootstrap-toggle.js',
                './src/AppBundle/Resources/public/components/bootstrap-dialog/js/bootstrap-dialog.min.js',
                './src/AppBundle/Resources/public/components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
                './src/AppBundle/Resources/public/components/bootstrap-daterangepicker/daterangepicker.js',
                './src/AppBundle/Resources/public/components/select2/dist/js/select2.full.js',
                './src/AppBundle/Resources/public/dist/js/adminlte.min.js'
            ])
            .pipe(plugins.concat('base.js'))
            .pipe(gulp.dest('./web/build/js'))
            .pipe(plugins.uglify())
            .pipe(plugins.rename('base.min.js'))
            .pipe(gulp.dest('./web/build/js'));
    };
};