import gulp from 'gulp';
import wpPot from 'gulp-wp-pot';

const pluginName = 'stripe-donation-form';

gulp.task('pot', function () {
	return gulp.src(settings.phpGlobs)
		.pipe(wpPot( {
			domain: pluginName,
			package: pluginName
		} ))
		.pipe(gulp.dest(`plugin/languages/${pluginName}.pot`));
});
