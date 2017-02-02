import gulp    from 'gulp';
import webpack from 'webpack';
import gutil   from 'gulp-util';

const taskJs = (entryJs, destDir, destFilename, enableMaps, nodeModulesDir) => (done) => {
	const nodeEnv = enableMaps ? "development" : "production";

	webpack({
		entry: entryJs,
		target: 'web',
		output: {
			path: destDir,
			filename: destFilename,
		},
		module: {
			loaders: [
				{
					test: /\.js?$/,
					loader: 'babel'
				},
			]
		},
		devtool: enableMaps ? 'source-map' : '',
		plugins: [
			new webpack.optimize.UglifyJsPlugin(),
			new webpack.DefinePlugin({
				'process.env': {
					'NODE_ENV': nodeEnv,
				}
			}),
		],
	}, (err, stats) => {
		if (err)
			throw new gutil.PluginError("webpack", err);
		gutil.log("[webpack]", stats.toString({ }));
		done();
	});
}

export {taskJs};
