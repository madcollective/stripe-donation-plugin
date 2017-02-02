<?php

namespace StripeDonationForm;

use StripeDonationForm\Tools\Locales;

/**
 * Functions for loading static assets
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class Settings {

	const PAGE_MENU_SLUG  = 'stripe_donation_form_settings';
	const SETTINGS_STRIPE = 'stripe_donation_form_stripe_settings';
	const SETTINGS_FORM   = 'stripe_donation_form_form_settings';

	const FIELD_LIVE_SECRET_KEY = 'live_secret_key';
	const FIELD_LIVE_PUBLIC_KEY = 'live_public_key';
	const FIELD_TEST_SECRET_KEY = 'test_secret_key';
	const FIELD_TEST_PUBLIC_KEY = 'test_public_key';
	const FIELD_TEST_MODE = 'test_mode';
	const FIELD_CURRENCY = 'currency';
	const FIELD_CURRENCY_INTERNATIONAL = 'use_international_currency_symbol';

	/**
	 * @var WeDevs_Settings_API    $settings_api    Tool for creating settings pages more easily
	 */
	private $settings_api;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->settings_api = new \WeDevs_Settings_API();
	}

	public function register_settings() {
		// Define the sections
		$this->settings_api->set_sections([
			[
				'id' => self::SETTINGS_STRIPE,
				'title' => __( 'Stripe Settings', 'stripe-donation-form' )
			],
			[
				'id' => self::SETTINGS_FORM,
				'title' => __( 'Form Settings', 'stripe-donation-form' )
			]
		]);

		// Define the Stripe Settings fields
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_LIVE_SECRET_KEY,
			'label'             => __( 'Live Secret API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field'
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_LIVE_PUBLIC_KEY,
			'label'             => __( 'Live Publishable API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field'
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_TEST_MODE,
			'label'             => __( 'Enable Test Mode', 'stripe-donation-form' ),
			'desc'              => __( 'Enabled', 'stripe-donation-form' ),
			'type'              => 'checkbox',
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_TEST_SECRET_KEY,
			'label'             => __( 'Test Secret API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field'
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_TEST_PUBLIC_KEY,
			'label'             => __( 'Test Publishable API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field'
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_CURRENCY,
			'label'             => __( 'Currency', 'stripe-donation-form' ),
			'desc'              => __( 'List is based on locales available on your server', 'stripe-donation-form' ),
			'type'              => 'select',
			'options'           => Locales::get_supported_locales(),
		] );

		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => self::FIELD_CURRENCY_INTERNATIONAL,
			'label'             => __( 'Currency Symbol', 'stripe-donation-form' ),
			'desc'              => __( 'Use international currency symbol', 'stripe-donation-form' ),
			'type'              => 'checkbox',
		] );

		// Define the Form Settings fields



		// Initialize them
		$this->settings_api->admin_init();
	}

	public function add_page_menu_item() {
		add_options_page(
			__( 'Donation Form', 'stripe-donation-form' ),
			__( 'Donation Form', 'stripe-donation-form' ),
			'delete_pages', // One of the admin capabilities
			self::PAGE_MENU_SLUG,
			array($this, 'print_settings_page')
		);
	}

	public function print_settings_page() {
		echo '<div class="wrap">';
		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();
		echo '</div>';
	}

	public static function get( $section, $field, $default='' ) {
		$settings_api = new \WeDevs_Settings_API();
		return $settings_api->get_option( $field, $section, $default );
	}

}
