var concat = require('gulp-concat');
var flatten = require('gulp-flatten');
var gulp = require('gulp');
var livereload = require('gulp-livereload');
var minifyCss = require('gulp-minify-css');
var rename = require('gulp-rename');
var sourcemaps = require('gulp-sourcemaps');
var templateCache = require('gulp-angular-templatecache');
var uglify = require('gulp-uglify');

var paths = {
    css: ['src/**/*.css'],
    scripts: ['src/app.js', 'src/**/*.js'],
    templates: ['src/**/*.html']
};

gulp.task('scripts', function () {
    return gulp.src(paths.scripts)
            .pipe(sourcemaps.init())
            .pipe(concat('app.min.js'))
            //.pipe(uglify())
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest('dist'));
});

gulp.task('templates', function () {
    return gulp.src(paths.templates)
            .pipe(flatten())
            .pipe(templateCache({module: 'app'}))
            .pipe(gulp.dest('dist'));
});

gulp.task('css', function () {
    return gulp.src(paths.css)
            .pipe(minifyCss())
            .pipe(rename('app.min.css'))
            .pipe(gulp.dest('dist'));

});

gulp.task('watch', ['default'], function () {
    var server = livereload();
    gulp.watch(paths.scripts, ['scripts']);
    gulp.watch(paths.templates, ['templates']);
    gulp.watch(paths.css, ['css']);

     gulp.watch(['index.php', 'dist/**/*.*']).on('change', function (file) {
        server.changed(file.path);
    });
    livereload.listen({port: 35729});
});

gulp.task('default', ['scripts', 'templates', 'css']);
