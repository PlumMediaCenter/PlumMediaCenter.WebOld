var bower = require('bower'),
  gulp = require('gulp');
console.log('running gulp build');
require('./Gulpfile.js');
gulp.start('default');
console.log('gulp build finished');

console.log('installing bower components...');
bower.commands.install();
console.log('bower components installed.');