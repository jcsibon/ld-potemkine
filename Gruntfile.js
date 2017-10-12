module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);
  grunt.loadNpmTasks('grunt-postcss');

  // Project configuration
  grunt.initConfig({
    postcss: {
      options: {
        processors: [
          require('autoprefixer')({browsers: ['> 1%', 'iOS 7']}),
          require('cssnano')()
        ]
      },
      dist: {
        files: {
          'src/css/style.css': 'src/css/style.css'
        }
      }
    },
    concat: { 
       dist: {
         src: [
           'src/views/*/*/*.scss',
         ],
         dest: 'src/scss/views.scss'
       }
     },
    sass: {
      dist: {
        files: {
          'src/css/style.css': 'src/scss/style.scss'
        }
      }
    },
    watch: {
      options: {
        atBegin: true,
      },
      scripts: {
        files: 'src/scss/**/*.scss',
        tasks: 'css'
      }
    }
  });

  // Default tasks
  grunt.registerTask('css', ['concat', 'sass', 'postcss']);
  grunt.registerTask('default', ['watch']);

};