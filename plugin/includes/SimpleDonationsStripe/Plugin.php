<?php

namespace SimpleDonationsStripe;

use SimpleDonationsStripe\Controllers\FormController;
use SimpleDonationsStripe\Tools;

/**
 * The core plugin class
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class Plugin {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	private $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var      string    $version    The current version of the plugin.
	 */
	private $version;

	/**
	 * @var      string    $base_url    The base url for the plugin directory.
	 */
	private $base_url;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @var SimpleDonationsStripe\Tools\Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	private $loader;

	/**
	 * @var SimpleDonationsStripe\I18n    $i18n    Loads internationalization files
	 */
	private $i18n;

	/**
	 * @var SimpleDonationsStripe\Assets    $assets    Holds functions for loading standard assets
	 */
	private $assets;

	/**
	 * @var SimpleDonationsStripe\Assets    $settings    Handles settings saving/loading and page rendering
	 */
	private $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct( $plugin_name, $base_url ) {
		$this->plugin_name = $plugin_name;
		$plugin_data = self::get_plugin_data( $this->plugin_name );
		$this->version = $plugin_data['Version'];
		$this->base_url = $base_url;

		$this->init();
		$this->register_hooks();
	}

	/**
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function init() {
		$this->loader = new Tools\Loader();
		$this->i18n = new I18n( $this->plugin_name );
		$this->assets = new Assets( $this->plugin_name, $this->version, $this->base_url );
		$this->settings = new Settings();
	}

	/**
	 * Register all of the hooks.
	 */
	private function register_hooks() {
		$this->loader->add_action( 'plugins_loaded', $this->i18n, 'load_plugin_textdomain' );

		$this->loader->add_action( 'admin_init', $this->settings, 'register_settings' );
		$this->loader->add_action( 'admin_menu', $this->settings, 'add_page_menu_item' );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->assets, 'enqueue_admin_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->assets, 'enqueue_admin_scripts' );

		$this->loader->add_action( 'wp_enqueue_scripts', $this->assets, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->assets, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_ajax_nopriv_' . FormController::FORM_ACTION, 'SimpleDonationsStripe\Controllers\FormController', 'post_donate' );
		$this->loader->add_action( 'wp_ajax_'        . FormController::FORM_ACTION, 'SimpleDonationsStripe\Controllers\FormController', 'post_donate' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Tools\Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	private static function get_plugin_data( $plugin_folder ) {
		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins_data = get_plugins( '/' . $plugin_folder );
		$plugin_data = reset( $plugins_data );
		return $plugin_data;
	}

}
