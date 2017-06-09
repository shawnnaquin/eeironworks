module.exports = function(grunt) {

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		env : {
			options : {
				/* Shared Options Hash */
				//globalOption : 'foo'
			},
			dev: {
				NODE_ENV : 'DEVELOPMENT'
			},
			prod : {
				NODE_ENV : 'PRODUCTION'
			}
		},

		// evaluates variables set in env task above
		preprocess: {
			def: {
				expand: true,
				cwd: '<%= pkg.destination %>',
				ext: '.html',
				src: ['*.html'],
				dest: '<%= pkg.destination %>'
			}
		},

		assemble: {
			options: {
				flatten: true,
				assets: '<%= pkg.assetsPath %>',
				data: '<%= pkg.buildPath %>assemble/_data/*.{json,yml}',
				helpers: [
					'<%= pkg.buildPath %>/js/handlebar-helpers/split-num.js',
					'<%= pkg.buildPath %>/js/handlebar-helpers/sup-ordinalize.js'
				],
				// Templates
				partials:  '<%= pkg.buildPath %>assemble/_includes/**/*.hbs',
				layoutdir: '<%= pkg.buildPath %>assemble/_layouts',
				layout: 'default.hbs',
			},
			site: {
				src: ['<%= pkg.buildPath %>assemble/_pages/*.hbs'],
				dest: '<%= pkg.destination %>'
			}
		},

		// something to do with having handlebars templates in assemble(it breaks and dies without)
		replace: {
			def : {
				files: [{
					expand: true,
					cwd: '<%= pkg.destination %>',
					src: ['*.html'],
					dest: '<%= pkg.destination %>'
				}],
				options: {
					patterns: [{
						match: /\{%/g,
						replacement: '{{'
					},{
						match: /%\}/g,
						replacement: '}}'
					},{
						match: /\{%%/g,
						replacement: '{{{'
					},{
						match: /%%\}/g,
						replacement: '}}}'
					}]
				}
			}
		},

		prettify: {
			options: {
				indent: 4,
				indent_char: ' ',
				indent_inner_html: false,
				condense: true,
			},
			def: {
				expand: true,
				wd: '<%= pkg.destination %>',
				ext: '.html',
				src: ['*.html'],
				dest: '<%= pkg.destination %>'
			}
		},

		jshint: {
			src: [
				'Gruntfile.js',
				'<%= pkg.buildPath %>js/app/**/*.js'
			],
			options: {
				smarttabs: true,
				supernew: true,
				reporter: require('jshint-stylish'),
			}
		},

		concat: {
			options: {
				separator : ';',
				stripBanners : true,
				process: function(src, filepath) {
					return '//####' + filepath + '\n' + src;
				}
			},
			def: {
				files: {
					'<%= pkg.assetsPath %>js/head.js' : '<%= pkg.buildPath %>js/head/*.js',
					'<%= pkg.assetsPath %>js/compat.js' : '<%= pkg.buildPath %>js/compat/*.js',

					'<%= pkg.assetsPath %>js/<%= pkg.name %>.js': [

						// use one or the other
						'<%= pkg.buildPath %>js/vendor/jquery-1.11.3.js',
						// '<%= pkg.buildPath %>js/vendor/jquery-2.1.4.js',
						// '<%= pkg.buildPath %>js/vendor/jquery-ui.js',

						// add/uncomment additional foundation libraries after this line
            '<%= pkg.npmPath %>bootstrap-sass/assets/javascripts/bootstrap.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.core.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.keyboard.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.box.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.mediaQuery.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.triggers.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.motion.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.offcanvas.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.reveal.js',

            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.nest.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.touch.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.util.timerAndImageLoader.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.accordion.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.accordionMenu.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.sticky.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.dropdownMenu.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.drilldown.js',
            // '<%= pkg.npmPath %>foundation-sites/js/foundation.interchange.js',


            '<%= pkg.buildPath %>js/lib/05-wordsearch.js',
            '<%= pkg.buildPath %>js/lib/05-crossword.js',

						'<%= pkg.buildPath %>js/app/polyfills.js',
						'<%= pkg.buildPath %>js/app/app.js',
						'<%= pkg.buildPath %>js/app/modules/*.js',
						'<%= pkg.buildPath %>js/app/init.js'
					],
				}
			}
		},

    babel: {
      options: {
        presets: [ 'babel-preset-es2015' ],
        // compact: true,
      },

      dist: {
        files: {
          '<%= pkg.assetsPath %>js/<%= pkg.name %>.js' : '<%= pkg.assetsPath %>js/<%= pkg.name %>.js',
        },
      },
    },

		uglify: {
			options: {
				screwIE8: true,
				preserveComments: false,
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
			},
			def: {
				files: {
					'<%= pkg.assetsPath %>js/head.min.js' : ['<%= pkg.assetsPath %>js/head.js' ],
					'<%= pkg.assetsPath %>js/compat.min.js' : ['<%= pkg.assetsPath %>js/compat.js' ],
					'<%= pkg.assetsPath %>js/<%= pkg.name %>.min.js' : ['<%= pkg.assetsPath %>js/<%= pkg.name %>.js']
				}
			}
		},

    sass: {
      options: {
        outputStyle: 'expanded',
        sourceComments: true,
        sourceMap: true,
        includePaths: [
          // '<%= pkg.npmPath %>foundation-sites/scss'
          '<%= pkg.npmPath %>bootstrap-sass/assets/stylesheets'
        ]
      },
      dist: {
        files: {
          '<%= pkg.assetsPath %>css/main.css': '<%= pkg.buildPath %>scss/main.scss'
        }
      }
    },

		css_purge: {
			// production: {
				options: {
					"verbose": false,
					"no_duplicate_property": true,
				},
				files: {
					'<%= pkg.assetsPath %>css/main.css': ['<%= pkg.assetsPath %>css/main.css']
				}
			// }
		},

		postcss: {
			development: {
				files: {
					'<%= pkg.assetsPath %>css/main.css' : '<%= pkg.assetsPath %>css/main.css',
				},
				options: {
					processors: [
						require('autoprefixer')({
							browsers: [
                "Android 2.3",
                "Android >= 4",
                "Chrome >= 20",
                "Firefox >= 24",
                "Explorer >= 8",
                "iOS >= 6",
                "Opera >= 12",
                "Safari >= 6"
							]
						})
					]
				}
			},

			production: {
				files: {
					'<%= pkg.assetsPath %>css/main.min.css' : '<%= pkg.assetsPath %>css/main.css',
				},
				options: {
					processors: [
						require('autoprefixer')({
							browsers: [
								'last 2 versions',
								'ie 9',
							]
						}),
						require('csswring')({
							removeAllComments: true
						})
					]
				}
			}
		},

		watch: {
			scripts: {
				files: [ '<%= pkg.buildPath %>js/app/**/*.js', ],
				tasks: [ 'jshint', 'concat', 'babel' ]
			},

			scss: {
				files: [ '**/*.scss' ],
				tasks: [ 'sass', 'postcss:development', 'css_purge' ]
			},

			assemble: {
				files: [ '<%= pkg.buildPath %>assemble/**/*.hbs', ],
				tasks: [ 'env:dev', 'assemble', 'preprocess', 'replace'] // 'prettify'
			}
		},

		browserSync: {
			bsFiles: {
				src : [
					'<%= pkg.assetsPath %>css/*.css',
					'<%= pkg.assetsPath %>js/*.js',
					'*.html'
				]

			},
			options: {
				watchTask: true,
				server: {
		            baseDir: "./"
		        }
			}
		}

	});

	// Default task(s).
	grunt.registerTask('default',    ['env:dev', 'jshint', 'concat', 'babel', 'sass', 'postcss:development', 'assemble', 'preprocess', 'replace']);
	grunt.registerTask('browser',    ['default', 'browserSync', 'watch']);
	grunt.registerTask('production', ['env:prod', 'jshint', 'concat', 'babel', 'uglify', 'sass', 'postcss:production', 'assemble', 'preprocess', 'replace', 'css_purge']);

};
