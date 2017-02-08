import gulp from 'gulp';
import zip from 'gulp-zip';

const taskZip = (sourceGlob, pluginName, destination) => () => {
	return gulp.src(sourceGlob)
		.pipe(zip(`${pluginName}.zip`))
		.pipe(gulp.dest(destination));
};

export {taskZip};
