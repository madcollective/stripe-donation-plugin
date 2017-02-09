import gulp     from 'gulp';
import minimist from 'minimist';
import path     from 'path';

import {taskJs}  from './tasks/javascript';
import {taskPot} from './tasks/pot';
import {taskZip} from './tasks/zip';

const argv = minimist(process.argv.slice(2));

const pluginName = 'stripe-donation-form';

const config = {
	// Flags
	mapsEnabled: !argv.production,

	// General
	assetDistFolder: path.join(__dirname, 'theme', 'dist'),
	nodeModulesDir:  path.join(__dirname, 'node_modules'),

	// JavaScript
	jsPublicEntry: path.join(__dirname, 'assets', 'scripts', 'public.js'),
	jsAdminEntry:  path.join(__dirname, 'assets', 'scripts', 'admin.js'),
	jsDistDir:     path.join(__dirname, 'plugin', 'dist', 'scripts'),
	jsPublicDistFilename: 'public.js',
	jsAdminDistFilename:  'admin.js',

	// PHP
	phpGlobs: [
		'plugin/**/*.php',
		'!vendor/**'
	],

	// I18n
	potDestination: `plugin/languages/${pluginName}.pot`,

	// Zip
	zipGlob: [
		'plugin/**/*',
		'!.DS_Store',
		'!Thumbs.db',
	],
	zipDestination: path.join(__dirname, 'dist'),
};

gulp.task('js-public', taskJs(config.jsPublicEntry, config.jsDistDir, config.jsPublicDistFilename, config.mapsEnabled, config.nodeModulesDir));
gulp.task('js-admin', taskJs(config.jsAdminEntry, config.jsDistDir, config.jsAdminDistFilename, config.mapsEnabled, config.nodeModulesDir));
gulp.task('js', ['js-public', 'js-admin']);
gulp.task('pot', taskPot(config.phpGlobs, pluginName, config.potDestination));
gulp.task('zip', taskZip(config.zipGlob, pluginName, config.zipDestination));

gulp.task('watch', ['js-public', 'js-admin'], () => {
	gulp.watch([
		'assets/scripts/**/*.js',
	], ['js']);
});

gulp.task('default', ['js-public', 'js-admin']);
