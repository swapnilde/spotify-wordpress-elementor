const gulp            = require( 'gulp' );
const fs              = require( 'fs' );
const gulpStylelint   = require( 'gulp-stylelint' );
const eslint          = require( 'gulp-eslint' );
const wpPot           = require( 'gulp-wp-pot' );
const checktextdomain = require( 'gulp-checktextdomain' );
const del             = require( 'del' );
const zip             = require( 'gulp-zip' );
const bump            = require( 'gulp-bump' );
const replace         = require( 'gulp-replace' );
const prompt          = require( 'gulp-prompt' );
const wait            = require( 'gulp-wait' );
const readme          = require( 'gulp-readme-to-markdown' );

var getPkgInfo = function () {
	return JSON.parse( fs.readFileSync( './package.json', 'utf8' ) );
};

gulp.task(
	'admin-css-lint',
	function (cb) {
		return gulp.src( 'admin/css/*.css' )
		.pipe(
			gulpStylelint(
				{
					failAfterError: true,
					reporters: [
					{formatter: 'string', console: true}
					],
					fix: false
				}
			)
		);
		cb();
	}
);

gulp.task(
	'public-css-lint',
	function (cb) {
		return gulp.src( 'frontend/css/*.css' )
		.pipe(
			gulpStylelint(
				{
					failAfterError: true,
					reporters: [
					{formatter: 'string', console: true}
					],
					fix: false
				}
			)
		);
		cb();
	}
);

gulp.task(
	'admin-js-lint',
	function (cb) {
		return gulp.src( 'admin/js/*.js' )
		.pipe(
			eslint(
				{
					quiet: true,
					fix: false,
					globals: [
					'jQuery',
					'$'
					],
					useEslintrc: true,
					configFile: '.eslintrc'
				}
			)
		)
		.pipe( eslint.formatEach( 'compact', process.stderr ) )
		.pipe( eslint.failAfterError() );
		cb();
	}
);

gulp.task(
	'public-js-lint',
	function (cb) {
		return gulp.src( 'frontend/js/*.js' )
		.pipe(
			eslint(
				{
					quiet: true,
					fix: false,
					globals: [
					'jQuery',
					'$',
					],
					useEslintrc: true,
					configFile: '.eslintrc'
				}
			)
		)
		.pipe( eslint.formatEach( 'compact', process.stderr ) )
		.pipe( eslint.failAfterError() );
		cb();
	}
);

gulp.task(
	'wp-pot',
	function (cb) {
		return gulp.src( '**/*.php' )
		.pipe(
			wpPot(
				{
					domain: 'pulseshare',
					package: 'PulseShare'
				}
			)
		)
		.pipe( gulp.dest( 'languages/pulseshare.pot' ) );
		cb();
	}
);

gulp.task(
	'checktextdomain',
	function (cb) {
		return gulp
		.src( '**/*.php' )
		.pipe(
			checktextdomain(
				{
					text_domain: 'pulseshare', // Specify allowed domain(s).
					keywords: [ // List keyword specifications.
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
					],
					correct_domain: true, // Automatically fix incorrect domains.
					report_variable_domain: false, // Report incorrect variable domains.
				}
			)
		);
		cb();
	}
);

gulp.task(
	'clean',
	function (cb) {
		(async() => {
			// set 'dryRun' to true to see which file will be deleted.
			const deletedFilePaths = await del( ['*.zip'], {dryRun: false} );
			const deletedDirectoryPaths = await del( ['build'], {dryRun: false} );
			console.log( 'Deleted files:\n', deletedFilePaths.join( '\n' ) );
			console.log('Deleted directories:\n', deletedDirectoryPaths.join('\n'));
		})();
		cb();
	}
);

gulp.task(
	'dist',
	function (cb) {
		return gulp.src(
			[
				"**/*",
				"!node_modules{,/**}",
				"!.eslintrc",
				"!.stylelintrc",
				"!Gulpfile.js",
				"!.gitignore",
				"!gulpfile.babel.js",
				"!package.json",
				"!package-lock.json",
				"!composer.json",
				"!composer.lock",
				"!vendor{,/**}",
				"!phpcs.xml.dist",
				"!psalm.xml",
				"!*.zip",
				"!webpack.mix.js",
				"!admin/js{,/**}",
				"!frontend/js{,/**}",
				"!admin/css{,/**}",
				"!frontend/css{,/**}",
				"!**/*.LICENSE.txt",
				"!mix-manifest.json",
				"!app{,/**}",
				"!tailwind.config.js",
				"!blocks{,/**}",
			]
		)
        .pipe( gulp.dest( './build' ) );
		cb();
	}
);

gulp.task(
	'zip',
	function (cb) {
		return gulp.src(
			[
			"**/*",
			"!node_modules{,/**}",
			"!.eslintrc",
			"!.stylelintrc",
			"!Gulpfile.js",
			"!.gitignore",
			"!gulpfile.babel.js",
			"!package.json",
			"!package-lock.json",
			"!composer.json",
			"!composer.lock",
			"!vendor{,/**}",
			"!phpcs.xml.dist",
			"!psalm.xml",
			"!*.zip",
			"!webpack.mix.js",
			"!admin/js{,/**}",
			"!frontend/js{,/**}",
			"!admin/css{,/**}",
			"!frontend/css{,/**}",
			"!**/*.LICENSE.txt",
			"!mix-manifest.json",
			"!app{,/**}",
			"!tailwind.config.js",
			"!blocks{,/**}",
			]
		)
		.pipe( zip( getPkgInfo().name + '.zip' ) )
		.pipe( gulp.dest( './' ) );
		cb();
	}
);

gulp.task(
	'bump',
	function (cb) {
		var pkg = gulp.src( 'package.json' );
		return gulp.src( '*' )
		.pipe(
			prompt.prompt(
				{
					type: 'checkbox',
					name: 'bump',
					message: 'What type of bump would you like to do?',
					choices: ['major', 'minor', 'patch', 'prerelease' ]
				},
				function (res) {
					var bumpType = res.bump[0];
					pkg.pipe( bump( {type: bumpType} ) )
					.pipe( gulp.dest( './' ) )
					.pipe( wait( 10000 ) );
				}
			)
		);
		cb();
	}
);

gulp.task(
	'plugin-version',
	function (cb) {
		return gulp.src( 'pulseshare.php' )
		.pipe( replace( /Version: \d{1,2}\.\d{1,2}\.\d{1,2}/g, 'Version: ' + getPkgInfo().version ) )
		.pipe( replace( /PULSESHARE_VERSION', '.*?'/g, 'PULSESHARE_VERSION\', \'' + getPkgInfo().version + '\'' ) )
		.pipe( gulp.dest( './' ) )
		.pipe( wait( 10000 ) );
		cb();
	}
);

gulp.task(
	'plugin-comment',
	function (cb) {
		return gulp.src( '**/*.php' )
		.pipe( replace( 'x.x.x', getPkgInfo().version ) )
		.pipe( gulp.dest( './' ) );
		cb();
	}
);

gulp.task(
	'readme',
	function (cb) {
		return gulp.src( 'readme.txt' )
		.pipe(
			readme(
				{
					details: true,
					screenshot_ext: ['jpg', 'jpg', 'png'],
				}
			)
		)
		.pipe( gulp.dest( './' ) );
		cb();
	}
);

gulp.task( 'lintcss', gulp.series( 'admin-css-lint','public-css-lint' ) );
gulp.task( 'lintjs', gulp.parallel( 'admin-js-lint','public-js-lint' ) );
gulp.task( 'checkdomain', gulp.series( 'checktextdomain' ) );
gulp.task( 'pot', gulp.series( 'wp-pot' ) );
gulp.task( 'zip', gulp.series( 'clean', 'zip' ) );
gulp.task( 'bumpup', gulp.series( 'bump', 'plugin-version', 'plugin-comment' ) );
gulp.task( 'readme', gulp.series( 'readme' ) );
