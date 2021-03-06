<?php

/**
 * Autoloader
 */
spl_autoload_register(function($class) {
	// Project-specific namespace prefix
	$prefix = 'SimpleDonationsStripe';

	// Base directory for the namespace prefix
	$base_dir = __DIR__ . '/' . $prefix . '/';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// no, move to the next registered autoloader
		return;
	}

	// Get the relative class name
	$relative_class = substr( $class, $len );

	// Replace the namespace prefix with the base directory, replace namespace
	// separators with directory separators in the relative class name, append
	// with .php
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	// If the file exists, require it
	if ( file_exists( $file ) ) {
		require $file;
	}
});

/**
 * Include other autoloaders
 */
call_user_func( function() {
	$autoload_paths = [
		realpath( __DIR__ . '/../vendor/autoload.php' ), // composer vendor autoloader
	];

	foreach ( $autoload_paths as $path ) {
		if ( file_exists( $path ) ) {
			require( $path );
			break;
		}
	}
} );
