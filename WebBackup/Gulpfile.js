var gulp = require('gulp'),
jshint = require('gulp-jshint'),
uglify = require('gulp-uglify'),
rename = require('gulp-rename'),
concat = require('gulp-concat'),
notify = require('gulp-notify'),
livereload = require('gulp-livereload'),
del = require('del');

gulp.task('scripts', function () {
	return gulp.src('web/public/src/app/**/*.js')
	.pipe(jshint('.jshintrc'))
	.pipe(jshint.reporter('default'))
	.pipe(concat('app.js'))
	.pipe(gulp.dest('web/public/dist'))
	.pipe(rename({
			suffix : '.min'
		}))
	.pipe(uglify())
	.pipe(gulp.dest('web/public/dist'))
	.pipe(notify({
			message : 'Scripts task complete'
		}));
});
