'use strict';

const gulp            = require('gulp');
const plugins         = require('gulp-load-plugins')();
const sequence        = require('gulp-sequence');

function getTask(task) {
    return require('./gulp-tasks/' + task)(gulp, plugins);
}

/** COPY FONTS **/
gulp.task('fonts', getTask('fonts'));

/** COPY FONTS **/
gulp.task('img', getTask('img'));

/** 1. Base assets **/
gulp.task('base:css', getTask('baseCss'));
gulp.task('base:js', getTask('baseJs'));
gulp.task('base', ['base:css', 'base:js']);

/** 2. Dashboard assets **/
gulp.task('dashboard:css', getTask('dashboardCss'));
gulp.task('dashboard:js', getTask('dashboardJs'));
gulp.task('dashboard', ['dashboard:css', 'dashboard:js']);


/** Default Gulp Task **/
gulp.task('default', sequence(
    'img',
    'fonts',
    'base',
    'dashboard'
));

