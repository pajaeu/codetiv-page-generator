const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const cleanCSS = require('gulp-clean-css');
const jsMinify = require('gulp-terser');
const rename = require('gulp-rename');

function compileSass() {
    return gulp.src('resources/scss/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(cleanCSS())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('public/build/css'));
}

function jsScripts() {
    return gulp.src('resources/js/*.js')
        .pipe(jsMinify())
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('public/build/js'));
}

function watchFiles() {
    gulp.watch('resources/scss/*.scss', compileSass);
    gulp.watch('resources/js/*.js', jsScripts);
}

const build = gulp.series(compileSass, jsScripts);

exports.watch = watchFiles;
exports.build = build;
exports.default = gulp.series(build, watchFiles);