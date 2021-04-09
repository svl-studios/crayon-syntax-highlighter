// phpcs:disable

/**
 * Gulpfile.
 *
 * Gulp with WordPress.
 *
 * Implements:
 *      1. Live reloads browser with BrowserSync.
 *      2. CSS: Sass to CSS conversion, error catching, Autoprefixing, Sourcemaps,
 *         CSS minification, and Merge Media Queries.
 *      3. JS: Concatenates & uglifies Vendor and Custom JS files.
 *      4. Images: Minifies PNG, JPEG, GIF and SVG images.
 *      5. Watches files for changes in CSS or JS.
 *      6. Watches files for changes in PHP.
 *      7. Corrects the line endings.
 *      8. InjectCSS instead of browser page reload.
 *      9. Generates .pot file for i18n and l10n.
 *
 * @author Kevin Provance (@kprovance)
 * @version 2.0.0 - Rewrite for Gulp 4.0
 */

/**
 * Configuration.
 *
 * Project Configuration for gulp tasks.
 *
 * In paths you can add <<glob or array of globs>>. Edit the variables as per your project requirements.
 */

// START Editing Project Variables.
// Project related.
var projectURL = 'http://127.0.0.1';                                // Project URL. Could be something like localhost:8888.

// Translation related.
var bugReport      = 'https://github.com/urvanov-ru/crayon-syntax-highlighter/issues';          // Where can users report bugs.
var lastTranslator = 'Kevin Provance <kevin.provance@gmail.com>';   // Last translator Email ID.
var team           = 'urvanov.ru <workgithub2021@urvanov.ru>';        // Team's Email ID.

// Browsers you care about for autoprefixing.
// Browserlist https://github.com/ai/browserslist.
var AUTOPREFIXER_BROWSERS = [ 'last 2 version', '> 1%', 'ie > 10', 'ie_mob > 10', 'ff >= 30', 'chrome >= 34', 'safari >= 7', 'opera >= 23', 'ios >= 7', 'android >= 4', 'bb >= 10' ];

// STOP Editing Project Variables.

/**
 * Load Plugins.
 *
 * Load gulp plugins and assing them semantic names.
 */
var gulp = require( 'gulp' ); // Gulp of-course.

// CSS related plugins.
var sass = require( 'gulp-sass' ); // Gulp pluign for Sass compilation.

var minifycss    = require( 'gulp-uglifycss' ); // Minifies CSS files.
var autoprefixer = require( 'gulp-autoprefixer' ); // Autoprefixing magic.
var mmq          = require( 'gulp-merge-media-queries' ); // Combine matching media queries into one media query definition.

// JS related plugins.
var concat = require( 'gulp-concat' ); // Concatenates JS files.
var uglify = require( 'gulp-uglify' ); // Minifies JS files.

// Image realted plugins.
var imagemin = require( 'gulp-imagemin' ); // Minify PNG, JPEG, GIF and SVG images with imagemin.

// Utility related plugins.
var rename       = require( 'gulp-rename' );                // Renames files E.g. style.css -> style.min.css.
var lineec       = require( 'gulp-line-ending-corrector' ); // Consistent Line Endings for non UIX systems. Gulp Plugin for Line Ending Corrector (A utility that makes sure your files have consistent line endings).
var filter       = require( 'gulp-filter' );                // Enables you to work on a subset of the original files by filtering them using globbing.
var sourcemaps   = require( 'gulp-sourcemaps' );            // Maps code in a compressed file (E.g. style.css) back to itâ€™s original position in a source file.
var browserSync  = require( 'browser-sync' ).create();      // Reloads browser and injects CSS. Time-saving synchronised browser testing.
var wpPot        = require( 'gulp-wp-pot' );               // For generating the .pot file.
var sort         = require( 'gulp-sort' );                 // Recommended to prevent unnecessary changes in pot-file.
var merge        = require( 'merge-stream' );
var sassPackager = require( 'gulp-sass-packager' );

sass.compiler = require( 'node-sass' );

/**
 * Task: `browser-sync`.
 *
 * Live Reloads, CSS injections, Localhost tunneling.
 *
 * This task does the following:
 *    1. Sets the project URL
 *    2. Sets inject CSS
 *    3. You may define a custom port
 *    4. You may want to stop the browser from openning automatically
 */
gulp.task(
	'browser-sync',
	function() {
		'use strict';
		browserSync.init(
			{

				// For more options.
				// @link http://www.browsersync.io/docs/options.
				// Project URL.
				proxy: projectURL,

				// `true` Automatically open the browser with BrowserSync live server.
				// `false` Stop the browser from automatically opening.
				open: true,

				// Inject CSS changes.
				// Commnet it to reload browser for every CSS change.
				injectChanges: true,

				// Use a specific port (instead of the one auto-detected by Browsersync).
				// port: 7000.
			}
		);
	}
);

function processScss( source, dest, addMin ) {
	'use strict';

	var process = gulp.src( source, { allowEmpty: true } )
		.pipe( sourcemaps.init() )
		.pipe(
			sass(
				{
					indentType: 'tab',
					indentWidth: 1,
					errLogToConsole: true,

					// outputStyle: 'compact',
					// outputStyle: 'compressed',
					// outputStyle: 'nested'.
					outputStyle: 'compact',
					precision: 10,
				}
			)
		)

		// eslint-disable-next-line no-console
		.on( 'error', console.error.bind( console ) )
		.pipe( sourcemaps.write( { includeContent: false } ) )
		.pipe( sourcemaps.init( { loadMaps: true } ) )
		.pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )
		.pipe( sourcemaps.write( './' ) )
		.pipe( lineec() )                                       // Consistent Line Endings for non UNIX systems.
		.pipe( gulp.dest( dest ) )
		.pipe( filter( '**/*.css' ) )                   // Filtering stream to only css files.
		.pipe( mmq( { log: true } ) )                     // Merge Media Queries only for .min.css version.
		.pipe( browserSync.stream() );                          // Reloads style.css if that is enqueued.

	if ( addMin ) {
		process = process.pipe( rename( { suffix: '.min' } ) ).pipe(
			minifycss(
				{
					maxLineLen: 0,
				}
			)
		)
			.pipe( lineec() )               // Consistent Line Endings for non UNIX systems.
			.pipe( gulp.dest( dest ) )
			.pipe( filter( '**/*.css' ) )   // Filtering stream to only css files.
			.pipe( browserSync.stream() );  // Reloads style.min.css if that is enqueued.
	}

	return process;
}

/**
 * Task: `styles`.
 *
 * Compiles Sass, Autoprefixes it and Minifies CSS.
 *
 * This task does the following:
 * 1. Gets the source scss file
 * 2. Compiles Sass to CSS
 * 3. Writes Sourcemaps for it
 * 4. Autoprefixes it and generates style.css
 * 5. Renames the CSS file with suffix .min.css
 * 6. Minifies the CSS file and generates style.min.css
 * 7. Injects CSS or reloads the browser via browserSync
 *
 * @param {Array} styleArray Style array.
 */
function styles( styleArray ) {
	'use strict';

	// Core styles.
	var core = styleArray.map(
		function( file ) {
			return processScss( file.path, file.dest, false );
		}
	);

	return merge( core );
}

function combineJS( source, dest, finalFile, minOnly ) {
	'use strict';

	var stream = gulp.src( source );

	stream = stream
		.pipe( concat( finalFile + '.js' ) )
		.pipe( lineec() ); // Consistent Line Endings for non UNIX systems.

	if ( false === minOnly ) {
		stream = stream
			.pipe( gulp.dest( dest ) );
	}

	// eslint-disable-next-line no-unused-vars
	stream = stream
		.pipe(
			rename(
				{
					basename: finalFile,
					suffix: '.min',
				}
			)
		)

		.pipe( uglify() )
		.pipe( lineec() )
		.pipe( gulp.dest( dest ) );
}

/**
 * Task: `images`.
 *
 * Minifies PNG, JPEG, GIF and SVG images.
 *
 * This task does the following:
 * 1. Gets the source of images raw folder
 * 2. Minifies PNG, JPEG, GIF and SVG images
 * 3. Generates and saves the optimized images
 *
 * This task will run only once, if you want to run it
 * again, do it with the command `gulp images`.
 *
 * @param {string} imagesSrc
 * @param {string}imagesDestination
 */
function images( imagesSrc, imagesDestination ) {
	'use strict';

	gulp.src( imagesSrc )
		.pipe(
			imagemin(
				{
					progressive: true,
					optimizationLevel: 3, // 0-7 low-high
					interlaced: true,
					svgoPlugins: [ { removeViewBox: false } ],
				}
			)
		)
		.pipe( gulp.dest( imagesDestination ) );
}

/**
 * WP POT Translation File Generator.
 *
 * This task does the following:
 * 1. Gets the source of all the PHP files
 * 2. Sort files in stream by path or any custom sort comparator
 * 3. Applies wpPot with the variable set at the top of this file
 * 4. Generate a .pot file of i18n that can be used for l10n to build .mo file
 *
 * @param {string} domain
 * @param {string} srcFile
 * @param {string} destFile
 * @param {string} packageName
 * @param {string} translatePath
 */
function translate( domain, srcFile, destFile, packageName, translatePath ) {
	'use strict';

	return gulp.src( srcFile )
		.pipe( sort() )
		.pipe(
			wpPot(
				{
					domain: domain,
					destFile: destFile,
					package: packageName,
					bugReport: bugReport,
					lastTranslator: lastTranslator,
					team: team,
				}
			)
		)
		.pipe( gulp.dest( translatePath + '/' + destFile ) );
}

/**
 * Core function
 *
 * @param {Function} done
 */
function crayonMinJS( done ) {
	'use strict';

	combineJS(
		[
			'./js/src/util.js',
			'./js/src/jquery.popup.js',
			'./js/src/urvanov_syntax_highlighter.js',
		],
		'./js/min/',
		'urvanov_syntax_highlighter',
		true
	);

	combineJS(
		[
			'./js/src/util.js',
			'./js/src/jquery.popup.js',
			'./js/src/urvanov_syntax_highlighter.js',
			'./util/tag-editor/urvanov_syntax_highlighter_qt.js',
			'./util/tag-editor/urvanov_syntax_highlighter_tag_editor.js',
			'./util/tag-editor/colorbox/jquery.colorbox-min.js',
		],
		'./js/min/',
		'urvanov_syntax_highlighter.te',
		true
	);

	done();
}

function crayonStyles() {
	'use strict';

	var cssFiles;

	var arr = [
		{ path: './css/src/admin_style.scss', dest: './css/src/' },
		{ path: './css/src/global_style.scss', dest: './css/src/' },
		{ path: './css/src/urvanov_syntax_highlighter_style.scss', dest: './css/src/' },
	];

	styles( arr );

	cssFiles = gulp.src(
		[ './css/src/admin_style.scss', './css/src/global_style.scss', './css/src/urvanov_syntax_highlighter_style.scss' ],
		{ allowEmpty: true }
	)

		.pipe( sassPackager( {} ) )
		.pipe( concat( 'urvanov_syntax_highlighter.min.scss' ) )
		.pipe(
			sass(
				{
					errLogToConsole: true,
					outputStyle: 'compressed',
					precision: 10,
				}
			)
		)

		// eslint-disable-next-line no-console
		.on( 'error', console.error.bind( console ) )
		.pipe( sourcemaps.write( { includeContent: false } ) )
		.pipe( sourcemaps.init( { loadMaps: true } ) )
		.pipe( autoprefixer( AUTOPREFIXER_BROWSERS ) )
		.pipe( sourcemaps.write( './' ) )
		.pipe( lineec() )                                       // Consistent Line Endings for non UNIX systems.
		.pipe( gulp.dest( './css/min/' ) );

	return merge( cssFiles );
}

function crayonImages( done ) {
	'use strict';

	images( './css/images/**/*.{png,jpg,gif,svg}', './screenshots/' );
	images( './screenshots/*.{png,jpg,gif,svg}', './screenshots/' );
	images( './util/theme-editor/images/*.{png,jpg,gif,svg}', './util/theme-editor/images/' );

	done();
}

function crayonTranslate( done ) {
	'use strict';

	translate( 'urvanov-syntax-highlighter', './**/*.php', 'urvanov-syntax-highlighter.pot', 'urvanov-syntax-highlighter', './trans' );

	done();
}

/**
 * Tasks
 */
gulp.task( 'images', images );

gulp.task( 'crayonJS', crayonMinJS );
gulp.task( 'crayonCSS', crayonStyles );
gulp.task( 'crayonImages', crayonImages );
gulp.task( 'crayonTranslate', crayonTranslate );

gulp.task( 'crayon', gulp.series( crayonMinJS, crayonStyles, crayonImages, crayonTranslate ) );

/**
 * Watch Tasks.
 *
 * Watches for file changes and runs specific tasks.
 */
gulp.task(
	'default',
	gulp.series(
		'crayon'
	)
);
