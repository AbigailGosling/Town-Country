const { src, dest, watch, series, parallel, task } = require("gulp");
const sass = require("gulp-sass");
const browserSync = require('browser-sync').create();
const plumber = require('gulp-plumber');
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');

/**
 * Performs a full page reload
 * For html/php files etc 
 */
function pageReload() {
    return browserSync.reload();
};

/**
 * Builds the front end styles
 */
task('frontStyles', () => {

    return src("./styles/grid.scss")
        .pipe(plumber())
        .pipe(sourcemaps.init()) // Creates sourcemaps
        .pipe(sass()) // Converts Sass to CSS with gulp-sass
        .pipe(autoprefixer({ grid: 'no-autoplace' })) // Autoprefixes to the last 2 browser versions
        .pipe(sourcemaps.write('.')) // Writes the sourcemaps
        .pipe(dest("./css"))
        .pipe(browserSync.stream());
})

/**
 * Watches for file changes and reloads the window
 */
task('watch', () => {
    watch(['./styles/*/**/*.scss'], parallel('frontStyles'))

    browserSync.init({
        proxy: "townandcountry",
        port: 3306,
        https: true
    });
});

/**
 * Default gulp task
 */
exports.default = series('watch')
