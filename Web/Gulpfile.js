(function() {
	'use strict';
	var gulp = require('gulp'),
		bower = require('bower'),
		jshint = require('gulp-jshint'),
		uglify = require('gulp-uglify'),
		rename = require('gulp-rename'),
		watch = require('gulp-watch'),
		concat = require('gulp-concat'),
		notify = require('gulp-notify'),
		htmlReplace = require('gulp-html-replace'),
		livereload = require('gulp-livereload'),
		changed = require('gulp-changed'),
		minifyCSS = require('gulp-minify-css'),
		glob = require('glob'),
		_ = require('lodash'),
		del = require('del');

	require('shelljs/global');

	gulp.task('clean', function(cb) {
		del(['public/dist/**/*'], cb);
	});

	gulp.task('bower', function() {
		bower.commands.install();
	});

	gulp.task('scripts', function() {
		var dst = 'public/dist';
		return gulp.src('public/src/app/**/*.js')
			.pipe(jshint('.jshintrc'))
			.pipe(jshint.reporter('default'))
			.pipe(concat('app.js'))
			.pipe(gulp.dest('public/dist'))
			.pipe(rename({
				suffix: '.min'
			}))
			.pipe(uglify())
			.pipe(gulp.dest(dst));
	});

	gulp.task('scripts-debug', function() {
		var dst = 'public/dist';
		return gulp.src(['./public/src/app/app.js', 'public/src/app/**/*.js'])
			.pipe(jshint('.jshintrc'))
			.pipe(jshint.reporter('default'))
			.pipe(gulp.dest('public/dist/app'));
	});

	gulp.task('index', function() {
		return gulp.src(['public/src/index.html'])
			.pipe(htmlReplace({
				scripts: {
					src: 'app.js',
					tpl: '<script type="text/javascript" src="%s"></script>'
				}
			}))
			.pipe(gulp.dest('public/dist'));
	});

	gulp.task('index-debug', function() {
		//find all of the angular script files
		var scriptFiles = glob.sync('./public/src/app/**/*.js');
		var paths = [];
		//remove the ./ from each path
		_.forEach(scriptFiles, function(path, key) {
			paths.push(path.replace('./public/src', ''));
		});
		return gulp.src(['public/src/index.html'])
			.pipe(htmlReplace({
				scripts: {
					src: paths,
					tpl: '<script type="text/javascript" src="%s"></script>'
				},
				liveReload: {
					src: '//localhost:35729/livereload.js',
					tpl: '<script src="%s"></script>'
				}
			}))
			.pipe(gulp.dest('public/dist'));
	});

	gulp.task('angular-templates', function() {
		return gulp.src('public/src/app/**/*.html')
			.pipe(gulp.dest('public/dist/app'));
	});

	gulp.task('css', function() {
		var dst = 'public/dist';
		//main css
		return gulp.src(['public/src/css/**/*.css', 'public/src/app/**/*.css'])
			.pipe(concat('style.css'))
			.pipe(rename({
				suffix: '.min'
			}))
			.pipe(minifyCSS())
			.pipe(gulp.dest(dst));
	});

	gulp.task('content', function() {
		return gulp.src(['public/src/content/**/*'])
			.pipe(gulp.dest('public/dist/content'));
	});


	gulp.task('favicon', function() {
		return gulp.src(['public/src/favicon.ico'])
			.pipe(gulp.dest('public/dist'));
	});

	gulp.task('watch', function() {
		var src = 'public/src/**/*';
		var server = livereload();
		gulp.watch('public/dist/**/*').on('change', function(file) {
			server.changed(file.path);
		});
		gulp.watch('public/src/app/**/*', ['scripts-debug',
			'angular-templates'
		]);
		gulp.watch('public/src/index.html', ['index-debug']);
		gulp.watch('public/src/**/*.css', ['css']);
		gulp.watch('public/src/content/**/*', ['content']);
		gulp.watch('public/src/favicon.ico', ['favicon']);
	});

	// gulp.task('node-debugger', function(cb){
	//   var child = exec('node_modules\\.bin\\node-inspector', function(){
	//     console.log('inspector is listening');
	//   });
	// });
	//
	// gulp.task('serve-debug', function(){
	//   var child = exec('node --debug-brk server.js', function(){
	//       console.log('server is running in debug mode');
	//     });
	// });

	//
	// gulp.task('serve-debug', function(){
	//   var child = exec('node --debug-brk server.js', function(){
	//       console.log('server is running in debug mode');
	//     });
	// });

	gulp.task('default', ['bower', 'scripts', 'angular-templates', 'index',
		'css', 'content', 'favicon'
	]);

	gulp.task('dev', ['watch', 'bower', 'scripts-debug', 'angular-templates',
		'index-debug', 'css', 'content', 'favicon'
	]);

}());