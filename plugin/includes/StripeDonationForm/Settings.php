<?php

namespace StripeDonationForm;

use StripeDonationForm\Tools\Locales;

define('DEFAULT_LOCALE', setlocale( LC_MONETARY, '0' ));
define('DEFAULT_STATEMENT_DESCRIPTOR', substr( get_bloginfo( 'name' ), 0, 22 ));
define('DEFAULT_MONTHLY_NOTE', __( 'We will automatically receive your gift each month. If you ever wish to change the frequency or amount of your gift, please contact us.', 'stripe-donation-form' ));
define('DEFAULT_SUCCESS_MESSAGE', '<p>' . __( 'Donation received. Thank you for your contribution!', 'stripe-donation-form' ) . '</p>' );

/**
 * Functions for loading static assets
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class Settings {

	const PAGE_MENU_SLUG  = 'stripe_donation_form_settings';
	const SETTINGS_STRIPE = 'stripe_donation_form_stripe_settings';
	const SETTINGS_FORM   = 'stripe_donation_form_form_settings';

	private static $stripe_fields = [
		'live_secret_key' => '',
		'live_public_key' => '',
		'test_secret_key' => '',
		'test_public_key' => '',
		'test_mode' => false,
		'locale' => DEFAULT_LOCALE,
		'use_international_currency_symbol' => false,
		'currency_scale' => 100,
		'statement_descriptor' => DEFAULT_STATEMENT_DESCRIPTOR,
	];

	private static $form_fields = [
		'preset_amounts' => [
			25, 150, 500, 1000
		],
		'default_amount' => 150,
		'amounts_as_select' => false,
		'show_preset_amounts' => true,
		'allow_custom_amount' => true,
		'allow_monthly_donation' => true,
		'ask_for_email' => true,
		'ask_for_name' => true,
		'ask_for_phone' => false,
		'require_name' => true,
		'require_email' => true,
		'require_phone' => false,
		'custom_amount_label' => null,
		'monthly_note_text' => DEFAULT_MONTHLY_NOTE,
		'success_message' => DEFAULT_SUCCESS_MESSAGE,
	];


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
			'name'              => 'live_secret_key',
			'label'             => __( 'Live Secret API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'live_public_key',
			'label'             => __( 'Live Publishable API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_mode',
			'label'             => __( 'Enable Test Mode', 'stripe-donation-form' ),
			'desc'              => __( 'Enabled (See <a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a> for more info)', 'stripe-donation-form' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_secret_key',
			'label'             => __( 'Test Secret API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_public_key',
			'label'             => __( 'Test Publishable API Key', 'stripe-donation-form' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'locale',
			'label'             => __( 'Currency', 'stripe-donation-form' ),
			'desc'              => __( 'List is based on locales available on your server', 'stripe-donation-form' ),
			'type'              => 'select',
			'options'           => Locales::get_currency_options(),
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'use_international_currency_symbol',
			'label'             => __( 'Currency Symbol', 'stripe-donation-form' ),
			'desc'              => __( 'Use international currency symbol', 'stripe-donation-form' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'currency_scale',
			'label'             => __( 'Currency Scale', 'stripe-donation-form' ),
			'desc'              => __( 'Number to multiply the amount by to get the smallest currency unit. For example, if the currency were USD, this value would be 100 to get to cents.', 'stripe-donation-form' ),
			'default'           => self::$stripe_fields['currency_scale'],
			'min'               => 1,
			'type'              => 'number',
			'sanitize_callback' => 'doubleval',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'statement_descriptor',
			'label'             => __( 'Statement Description', 'stripe-donation-form' ),
			'desc'              => __( 'Text to be displayed on your donator\'s credit card statement (max length of 22 characters)', 'stripe-donation-form' ),
			'default'           => self::$stripe_fields['statement_descriptor'],
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		// Define the Form Settings fields
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'show_preset_amounts',
			'label'             => __( 'Preset Amounts Enabled', 'stripe-donation-form' ),
			'desc'              => __( 'Show preset amounts', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['show_preset_amounts'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'amounts_as_select',
			'label'             => __( 'Preset Amounts Display', 'stripe-donation-form' ),
			'desc'              => __( 'Display preset amounts as dropdown select', 'stripe-donation-form' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'preset_amounts',
			'label'             => __( 'Preset Amount Values', 'stripe-donation-form' ),
			'desc'              => __( 'Comma separated values, currency symbols omitted', 'stripe-donation-form' ),
			'type'              => 'text',
			'default'           => join( ',', self::$form_fields['preset_amounts'] ),
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'default_amount',
			'label'             => __( 'Default Donation Amount', 'stripe-donation-form' ),
			'desc'              => __( 'Should match one of the above options', 'stripe-donation-form' ),
			'default'           => self::$form_fields['default_amount'],
			'type'              => 'number',
			'sanitize_callback' => 'doubleval',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'allow_custom_amount',
			'label'             => __( 'Custom Donation Amount', 'stripe-donation-form' ),
			'desc'              => __( 'Allow custom donation amount', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['allow_custom_amount'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'allow_monthly_donation',
			'label'             => __( 'Monthly Donations', 'stripe-donation-form' ),
			'desc'              => __( 'Allow monthly donations', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['allow_monthly_donation'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'monthly_note_text',
			'label'             => __( 'Note for monthly donations', 'stripe-donation-form' ),
			'type'              => 'textarea',
			'default'           => self::$form_fields['monthly_note_text'],
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'success_message',
			'label'             => __( 'Success Message', 'stripe-donation-form' ),
			'desc'              => __( 'Message to show users after they have successfully made a donation', 'stripe-donation-form' ),
			'type'              => 'wysiwyg',
			'default'           => self::$form_fields['success_message'],
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'require_name',
			'label'             => __( 'Require Name', 'stripe-donation-form' ),
			'desc'              => __( 'Name required', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['require_name'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'require_email',
			'label'             => __( 'Require Email', 'stripe-donation-form' ),
			'desc'              => __( 'Email required', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['require_email'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'require_phone',
			'label'             => __( 'Require Phone', 'stripe-donation-form' ),
			'desc'              => __( 'Phone required', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['require_phone'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'ask_for_name',
			'label'             => __( 'Show Name Field', 'stripe-donation-form' ),
			'desc'              => __( 'Name field included', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['ask_for_name'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'ask_for_email',
			'label'             => __( 'Show Email Field', 'stripe-donation-form' ),
			'desc'              => __( 'Email field included', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['ask_for_email'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'ask_for_phone',
			'label'             => __( 'Show Phone Field', 'stripe-donation-form' ),
			'desc'              => __( 'Phone field included', 'stripe-donation-form' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['ask_for_phone'] ? 'on' : 'off',
		] );

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

	public static function get( $field, $default=null ) {
		$section = null;
		$setting_default = '';

		if ( array_key_exists( $field, self::$stripe_fields ) ) {
			$section = self::SETTINGS_STRIPE;
			$setting_default = self::$stripe_fields[$field];
		}
		else if ( array_key_exists( $field, self::$form_fields ) ) {
			$section = self::SETTINGS_FORM;
			$setting_default = self::$form_fields[$field];
		}
		else {
			return;
		}

		$settings_api = new \WeDevs_Settings_API();
		$value = $settings_api->get_option( $field, $section, ( $default === null ) ? $setting_default : $default );

		if ( $value === $setting_default )
			return $value;
		else if ( is_bool( $setting_default ) )
			return ( $value === 'on' );
		else if ( is_array( $setting_default ) )
			return array_map( 'doubleval', explode( ',', $value ) );
		else
			return $value;
	}

	public static function get_stripe_secret_key() {
		if ( self::get( 'test_mode' ) )
			return self::get( 'test_secret_key' );
		else
			return self::get( 'live_secret_key' );
	}

	public static function get_stripe_public_key() {
		if ( self::get( 'test_mode' ) )
			return self::get( 'test_public_key' );
		else
			return self::get( 'live_public_key' );
	}

	public static function get_form_settings() {
		$field_keys = array_merge(
			array_keys( self::$form_fields ),
			[
				'test_mode',
				'locale',
				'use_international_currency_symbol'
			]
		);

		return array_merge(
			array_combine(
				$field_keys,
				array_map(
					function( $key ) { return Settings::get( $key ); },
					$field_keys
				)
			),
			[ 'publishable_key' => self::get_stripe_public_key() ]
		);
	}

}
