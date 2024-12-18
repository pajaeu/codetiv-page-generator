const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');

function compileSass() {
    return gulp.src('resources/scss/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('public/build/css'));
}

function minifyCss() {
    return gulp.src('resources/css/*.css')
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('public/build/css'));
}

function watchFiles() {
    gulp.watch('resources/scss/*.scss', compileSass);
    gulp.watch('resources/css/*.css', minifyCss);
}

const build = gulp.series(compileSass, watchFiles);

exports.sass = compileSass;
exports.watch = watchFiles;
exports.default = build;
