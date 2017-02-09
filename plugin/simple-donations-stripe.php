<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Donations With Stripe
 * Plugin URI:        https://www.madcollective.com
 * Description:       A simple donation form powered by Stripe that allows users to make one-time and monthly donations
 * Version:           0.9.0
 * Author:            Madison Ave. Collective
 * Author URI:        https://www.madcollective.com
 * License:           MIT
 * Text Domain:       simple-donations-stripe
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Require the project-specific autoloader
 */
require plugin_dir_path( __FILE__ ) . 'includes/autoload.php';

/**
 * Require the plugin's API class
 */
require plugin_dir_path( __FILE__ ) . 'includes/SimpleDonationsStripe.php';

/**
 * Instantiate the core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
call_user_func( function() {
	$url = plugin_dir_url( __FILE__ );
	$plugin = new SimpleDonationsStripe\Plugin( 'simple-donations-stripe', $url );
	$plugin->run();
} );
