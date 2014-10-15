module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        bower: {
            dev: {
                dest: 'web/public/components'
            }
        },
        concat: {
            js: {
                options: {
                    separator: "\n",
                },
                src: ['web/src/app.js', 'web/src/app/**/*.js'],
                dest: 'web/public/app.js',
            }
        },
        jsdoc: {
            dist: {
                src: ['models/User.js'],
                options: {
                    destination: 'web/public/apidoc'
                }
            }
        },
        jshint: {
            options: {
                jshintrc: 'jshintrc',
            },
            beforeconcat: ['web/src/app/**/*.js']
        },
        uglify: {
            options: {
                banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd hh:MM:ss") %> */',
                sourceMap: true
            },
            dist: {
                src: 'web/public/app.js',
                dest: 'web/public/app.min.js'
            }
        },
        nodemon: {
            serve: {
                script: './app.js',
                options: {
                    //nodeArgs: ['--debug']
                }
            }
        },
        copy: {
            partials: {
                expand: true,
                flatten: true,
                src: ['web/src/app/partials/*.html'],
                dest: 'web/public/partials'
            },
            content: {
                expand: true,
                src: ['**.*'],
                dest: 'web/public/content',
                cwd: 'web/src/content'
            },
            staticFiles: {
                expand: true,
                flatten: true,
                src: ['web/src/index.html', 'web/src/favicon.ico'],
                dest: 'web/public'
            },
            lib: {
                expand: true,
                src: ['**/*.*'],
                dest: 'web/public/lib',
                cwd: 'web/src/lib'
            }
        },
        concurrent: {
            debug: {
                tasks: ['watch', 'nodemon', 'node-inspector'],
                options: {
                    logConcurrentOutput: true
                }
            }
        },
        'node-inspector': {
            dev: {}
        },
        watch: {
            app: {
                files: ['web/src/**/*'],
                tasks: ['build'],
                options: {
                    interrupt: true,
                    livereload: true
                },
            },
            bower: {
                files: 'bower.json',
                tasks: ['bower']
            }
        },
    });

    //load all grunt tasks that have been included using npm
    require('load-grunt-tasks')(grunt);

    //create tasks
    grunt.registerTask('default', ['build']);
    grunt.registerTask('build', ['newer:jshint', 'newer:concat', 'newer:copy:partials', 'newer:copy:content', 'newer:copy:staticFiles', 'newer:copy:lib']);
    grunt.registerTask('serve', ['nodemon']);
   // grunt.registerTask('debugger', ['node-inspector']);
    grunt.registerTask('debug', ['concurrent:debug']);
};