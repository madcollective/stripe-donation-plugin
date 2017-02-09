import gulp from 'gulp';
import archiver from 'gulp-archiver';

const taskZip = (sourceGlob, pluginName, destination) => () => {
	return gulp.src(sourceGlob)
		.pipe(archiver(`${pluginName}.zip`))
		.pipe(gulp.dest(destination));
};

export {taskZip};
