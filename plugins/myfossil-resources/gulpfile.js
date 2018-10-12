// Project configuration
var project   = 'myfossil';

// Initialization sequence
var gulp      = require('gulp')
  , gutil     = require('gulp-util')
  , plugins   = require('gulp-load-plugins')({ camelize: true })
  , lr        = require('tiny-lr')
  , server    = lr()
  , build     = './static/'
;

/**
 * Paths to scripts that will ultimately become gulp tasks.
 */
var script_sets = {
    'public-places': ['assets/src/js/public/places.js'],
    'public-events': ['assets/src/js/public/events.js'],
    'public-docs': ['assets/src/js/public/docs.js'],
    'admin': ['assets/src/js/admin/*.js']
};

var scripts = [];

for ( var set in script_sets ) {
    ( function( set ) {
        var gulp_task_name = 'scripts-' + set;
        var filename = set + '.js';

        scripts.push( gulp_task_name );

        gulp.task( gulp_task_name, 
            function() {
                return gulp.src( script_sets[set] )
                  .pipe( plugins.sourcemaps.init() ) 
                  .pipe( plugins.jshint( '.jshintrc' ) ) 
                  .pipe( plugins.concat( filename ) )
                  .pipe( gulp.dest( 'assets/staging' ) )
                  .pipe( plugins.rename( { suffix: '.min' } ) )
                  .pipe( plugins.uglify() )
                  .pipe( plugins.sourcemaps.write() )
                  .pipe( gulp.dest( build + '/js/') );
            }
        );
    })( set );

}

gulp.task('scripts', scripts );

gulp.task('clean', function() {
    return gulp.src(build+'**/.DS_Store', { read: false })
            .pipe(plugins.clean());
});

gulp.task('watch', function() {
    server.listen(35729, function (err) { // Listen on port 35729

        gulp.watch('assets/src/js/**/*.js', ['scripts']);

        gulp.watch('**/*.php').on('change', function(file) { 
            plugins.livereload(server).changed(file.path); 
        });

    });

});

gulp.task('build', ['scripts']);
gulp.task('default', ['build', 'watch']);
