module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		copy: {
			main: {
				files: [
					{expand: true, cwd: 'bower_components/nette-forms/src/assets/', src: 'netteForms.js', dest: 'Resources/public/vendor/nette-forms/'},
					{expand: true, cwd: 'bower_components/bootstrap/dist/', src: '**', dest: 'Resources/public/vendor/bootstrap/'},
					{expand: true, cwd: 'bower_components/jasny-bootstrap/dist/', src: '**', dest: 'Resources/public/vendor/jasny-bootstrap/'},
					{expand: true, cwd: 'bower_components/font-awesome/css/', src: 'font-awesome.min.css', dest: 'Resources/public/vendor/font-awesome/css/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/font-awesome/', src: 'fonts/**', dest: 'Resources/public/vendor/font-awesome/'},
					{expand: true, cwd: 'bower_components/jquery/', src: 'jquery.min.js', dest: 'Resources/public/vendor/jquery/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/jquery/', src: 'jquery-migrate.min.js', dest: 'Resources/public/vendor/jquery/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/jquery-ui/ui/minified/', src: 'jquery-ui.min.js', dest: 'Resources/public/vendor/jquery-ui/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/smalot-bootstrap-datetimepicker/css/', src: 'bootstrap-datetimepicker.min.css', dest: 'Resources/public/vendor/bootstrap-datetimepicker/css/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/smalot-bootstrap-datetimepicker/js/', src: 'bootstrap-datetimepicker.min.js', dest: 'Resources/public/vendor/bootstrap-datetimepicker/js/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/smalot-bootstrap-datetimepicker/js/locales/', src: '**', dest: 'Resources/public/vendor/bootstrap-datetimepicker/js/locales/'},
					{expand: true, cwd: 'bower_components/jquery-hashchange/', src: 'jquery.ba-hashchange.min.js', dest: 'Resources/public/vendor/jquery-hashchange/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/dynatree/dist/', src: 'jquery.dynatree.min.js', dest: 'Resources/public/vendor/dynatree/', filter: 'isFile'},
					{expand: true, cwd: 'bower_components/dynatree/dist/', src: 'skin/**', dest: 'Resources/public/vendor/dynatree/'},
					{expand: true, cwd: 'bower_components/dynatree/dist/', src: 'skin-vista/**', dest: 'Resources/public/vendor/dynatree/'},
					{expand: true, cwd: 'bower_components/select2/', src: '**', dest: 'Resources/public/vendor/select2/'},
					{expand: true, cwd: 'bower_components/typeahead.js/dist/', src: 'typeahead.bundle.min.js', dest: 'Resources/public/vendor/typeahead.js/'},
					{expand: true, cwd: 'bower_components/holderjs/', src: 'holder.js', dest: 'Resources/public/vendor/holder/'},
					{expand: true, cwd: 'node_modules/hogan.js/web/builds/3.0.2/', src: 'hogan-3.0.2.min.js', dest: 'Resources/public/vendor/hogan/'},
					{expand: true, cwd: 'bower_components/nette.ajax.js/', src: 'nette.ajax.js', dest: 'Resources/public/vendor/nette.ajax.js/'},
					{expand: true, cwd: 'bower_components/history.nette.ajax.js/client-side', src: 'history.ajax.js', dest: 'Resources/public/vendor/history.ajax.js/'},
				]
			}
		},

	  	uglify: {
	  		options: {
		        beautify: true
		    },
			js: {
				files: {
					'Resources/public/js/application.min.js': ['Resources/public/js/*.js', '!js/*.min.js']
				}
			}
		},

		sass: {
			dist: {
				options: {
					style: 'expanded'
				},
				files: {
					'Resources/public/css/application.css': 'Resources/public/css/application.scss'
				}
			}
		},

		cssmin: {
			combine: {
				files: {
					'Resources/public/css/application.min.css': ['Resources/public/css/application.css']
				}
			},
			minify: {
				expand: true,
				cwd: 'css/',
				src: ['index.css', 'legacy_ie.css'],
				dest: 'css/'
			}
		},

		imagemin: {
			dynamic: {
				options: {
					optimizationLevel: 3
				},
				files: [{
					expand: true,
					cwd: 'Resources/public/',
					src: ['**/*.{png,jpg,gif}'],
					dest: 'Resources/public/'
				}]
			}
		},
		
		autoprefixer: {
		  dist: {
		    options: {
		      browsers: ['last 1 version', '> 1%', 'ie 8', 'ie 7']
		    },
		    files: {
		      'css/index.css': ['css/index.css']
		    }
		  }
		},

		watch: {
			css: {
				files: ['Resources/public/*/*.scss'],
				tasks: ['sass', 'autoprefixer', 'cssmin']
			},

			imagemin: {
				files: [
					'Resources/public/*/*.jpg',
					'Resources/public/*/*.jpeg',
					'Resources/public/*/*.png',
					'Resources/public/*/*.gif'
				],
				tasks: ['imagemin']
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.loadNpmTasks('grunt-autoprefixer');

	grunt.registerTask('default', ['copy', 'sass', 'uglify', 'autoprefixer', 'cssmin', 'imagemin']);
	grunt.registerTask('watch', ['watch']);
};
