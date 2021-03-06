import gulp from 'gulp';
import wpPot from 'gulp-wp-pot';

const taskPot = (sourceGlob, pluginName, destination) => () => {
	return gulp.src(sourceGlob)
		.pipe(wpPot( {
			domain: pluginName,
			package: pluginName
		} ))
		.pipe(gulp.dest(destination));
};

export {taskPot};
