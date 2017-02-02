<?php

namespace StripeDonationForm;

/**
 * Functions for loading static assets
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class Assets {

	/**
	 * The ID of this plugin.
	 *
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @var      string    $base_url    The base url for the plugin directory.
	 */
	private $base_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $base_url ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->base_url = $base_url;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->plugin_name, $this->base_url . 'css/public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name . '-js', $this->base_url . 'dist/scripts/public.js', [], $this->version, false );
		wp_enqueue_script( 'stripe-js', '//js.stripe.com/v2/' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_admin_styles() {
		// wp_enqueue_style( $this->plugin_name, $this->base_url . 'css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_admin_scripts() {
		// wp_enqueue_script( $this->plugin_name, $this->base_url . 'js/admin.js', array( 'jquery' ), $this->version, false );
	}

}
