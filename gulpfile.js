var concat = require('gulp-concat');
var flatten = require('gulp-flatten');
var gulp = require('gulp');
var livereload = require('gulp-livereload');
var minifyCss = require('gulp-minify-css');
var sourcemaps = require('gulp-sourcemaps');
var templateCache = require('gulp-angular-templatecache');
var uglify = require('gulp-uglify');

var paths = {
    css: ['src/app.css', 'src/**/*.css'],
    scripts: ['src/app.js', 'src/**/*.js'],
    templates: ['src/**/*.html']
};
function scripts() {
    return gulp.src(paths.scripts)
        .pipe(sourcemaps.init())
        .pipe(concat('app.min.js'))
        //.pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('dist'));
}

function templates() {
    return gulp.src(paths.templates)
        .pipe(flatten())
        .pipe(templateCache({ module: 'app' }))
        .pipe(gulp.dest('dist'));
}

function css() {
    return gulp.src(paths.css)
        .pipe(sourcemaps.init())
        .pipe(concat('app.min.css'))
        .pipe(minifyCss())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('dist'));

};

gulp.task('default', gulp.parallel(scripts, templates, css));

gulp.task('watch', gulp.series('default', function watchInner() {
    gulp.watch(paths.scripts, scripts);
    gulp.watch(paths.templates, templates);
    gulp.watch(paths.css, css);

    gulp.watch(['index.php', 'dist/**/*.*']).on('change', function (filePath) {
        livereload.changed(filePath);
    });
    livereload.listen({ port: 35729 });
}));

