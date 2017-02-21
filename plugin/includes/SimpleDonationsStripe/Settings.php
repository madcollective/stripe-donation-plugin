<?php

namespace SimpleDonationsStripe;

use SimpleDonationsStripe\Plugin;
use SimpleDonationsStripe\Tools\CountryData;
use SimpleDonationsStripe\Tools\Locales;

define('DEFAULT_LOCALE', setlocale( LC_MONETARY, '0' ));
define('DEFAULT_STATEMENT_DESCRIPTOR', substr( get_bloginfo( 'name' ), 0, 22 ));
define('DEFAULT_MONTHLY_NOTE', __( 'We will automatically receive your gift each month. If you ever wish to change the frequency or amount of your gift, please contact us.', 'simple-donations-stripe' ));
define('DEFAULT_SUCCESS_MESSAGE', '<p>' . __( 'Donation received. Thank you for your contribution!', 'simple-donations-stripe' ) . '</p>' );

/**
 * Functions for loading static assets
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class Settings {

	const PAGE_MENU_SLUG  = 'sds_settings';
	const SETTINGS_STRIPE = 'sds_stripe_settings';
	const SETTINGS_FORM   = 'sds_form_settings';

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
		'custom_amount_label' => null,
		'monthly_note_text' => DEFAULT_MONTHLY_NOTE,
		'success_message' => DEFAULT_SUCCESS_MESSAGE,
		'fields_displayed' => [ 'name' => 'name', 'email' => 'email', 'phone' => 'phone' ],
		'fields_required' => [ 'name' => 'name', 'email' => 'email' ],
		'address_fields' => [
			'address_1' => 'address_1',
			'address_2' => 'address_2',
			'address_zip' => 'address_zip',
			'address_city' => 'address_city',
			'address_state' => 'address_state',
		],
	];

	private static $field_options = [
		'name'            => 'Name',
		'name_first'      => 'First Name',
		'name_last'       => 'Last Name',
		'email'           => 'Email Address',
		'phone'           => 'Phone Number',
		'mailing_address' => 'Mailing Address Fields',
	];

	private static $address_field_options = [
		'address_1'        => 'Address',
		'address_2'        => 'Address Line 2',
		'address_zip'      => 'ZIP',
		'address_postal'   => 'Postal',
		'address_city'     => 'City',
		'address_state'    => 'State',
		'address_province' => 'Province',
		'address_country'  => 'Country',
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
				'title' => __( 'Stripe Settings', 'simple-donations-stripe' )
			],
			[
				'id' => self::SETTINGS_FORM,
				'title' => __( 'Form Settings', 'simple-donations-stripe' )
			]
		]);

		// Define the Stripe Settings fields
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'live_secret_key',
			'label'             => __( 'Live Secret API Key', 'simple-donations-stripe' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'live_public_key',
			'label'             => __( 'Live Publishable API Key', 'simple-donations-stripe' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_mode',
			'label'             => __( 'Enable Test Mode', 'simple-donations-stripe' ),
			'desc'              => __( 'Enabled (See <a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a> for more info)', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_secret_key',
			'label'             => __( 'Test Secret API Key', 'simple-donations-stripe' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'test_public_key',
			'label'             => __( 'Test Publishable API Key', 'simple-donations-stripe' ),
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'locale',
			'label'             => __( 'Currency', 'simple-donations-stripe' ),
			'desc'              => __( 'List is based on locales available on your server', 'simple-donations-stripe' ),
			'type'              => 'select',
			'options'           => Locales::get_currency_options(),
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'use_international_currency_symbol',
			'label'             => __( 'Currency Symbol', 'simple-donations-stripe' ),
			'desc'              => __( 'Use international currency symbol', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'currency_scale',
			'label'             => __( 'Currency Scale', 'simple-donations-stripe' ),
			'desc'              => __( 'Number to multiply the amount by to get the smallest currency unit. For example, if the currency were USD, this value would be 100 to get to cents.', 'simple-donations-stripe' ),
			'default'           => self::$stripe_fields['currency_scale'],
			'min'               => 1,
			'type'              => 'number',
			'sanitize_callback' => 'doubleval',
		] );
		$this->settings_api->add_field( self::SETTINGS_STRIPE, [
			'name'              => 'statement_descriptor',
			'label'             => __( 'Statement Description', 'simple-donations-stripe' ),
			'desc'              => __( 'Text to be displayed on your donator\'s credit card statement (max length of 22 characters)', 'simple-donations-stripe' ),
			'default'           => self::$stripe_fields['statement_descriptor'],
			'type'              => 'text',
			'sanitize_callback' => 'sanitize_text_field',
		] );

		// Define the Form Settings fields
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'show_preset_amounts',
			'label'             => __( 'Preset Amounts Enabled', 'simple-donations-stripe' ),
			'desc'              => __( 'Show preset amounts', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['show_preset_amounts'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'amounts_as_select',
			'label'             => __( 'Preset Amounts Display', 'simple-donations-stripe' ),
			'desc'              => __( 'Display preset amounts as dropdown select', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'preset_amounts',
			'label'             => __( 'Preset Amount Values', 'simple-donations-stripe' ),
			'desc'              => __( 'Comma separated values, currency symbols omitted', 'simple-donations-stripe' ),
			'type'              => 'text',
			'default'           => join( ',', self::$form_fields['preset_amounts'] ),
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'default_amount',
			'label'             => __( 'Default Donation Amount', 'simple-donations-stripe' ),
			'desc'              => __( 'Should match one of the above options', 'simple-donations-stripe' ),
			'default'           => self::$form_fields['default_amount'],
			'type'              => 'number',
			'sanitize_callback' => 'doubleval',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'allow_custom_amount',
			'label'             => __( 'Custom Donation Amount', 'simple-donations-stripe' ),
			'desc'              => __( 'Allow custom donation amount', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['allow_custom_amount'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'allow_monthly_donation',
			'label'             => __( 'Monthly Donations', 'simple-donations-stripe' ),
			'desc'              => __( 'Allow monthly donations', 'simple-donations-stripe' ),
			'type'              => 'checkbox',
			'default'           => self::$form_fields['allow_monthly_donation'] ? 'on' : 'off',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'monthly_note_text',
			'label'             => __( 'Note for monthly donations', 'simple-donations-stripe' ),
			'type'              => 'textarea',
			'default'           => self::$form_fields['monthly_note_text'],
			'sanitize_callback' => 'sanitize_text_field',
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'success_message',
			'label'             => __( 'Success Message', 'simple-donations-stripe' ),
			'desc'              => __( 'Message to show users after they have successfully made a donation', 'simple-donations-stripe' ),
			'type'              => 'wysiwyg',
			'default'           => self::$form_fields['success_message'],
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'fields_displayed',
			'label'             => __( 'Included Fields', 'simple-donations-stripe' ),
			'type'              => 'multicheck',
			'options'           => self::$field_options,
			'default'           => self::$form_fields['fields_displayed'],
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'fields_required',
			'label'             => __( 'Required Fields', 'simple-donations-stripe' ),
			'type'              => 'multicheck',
			'options'           => self::$field_options,
			'default'           => self::$form_fields['fields_required'],
		] );
		$this->settings_api->add_field( self::SETTINGS_FORM, [
			'name'              => 'address_fields',
			'label'             => __( 'Address Fields', 'simple-donations-stripe' ),
			'type'              => 'multicheck',
			'options'           => self::$address_field_options,
			'default'           => self::$form_fields['address_fields'],
		] );

		// Initialize them
		$this->settings_api->admin_init();
	}

	public function add_page_menu_item() {
		add_options_page(
			__( 'Donation Form', 'simple-donations-stripe' ),
			__( 'Donation Form', 'simple-donations-stripe' ),
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

		if ( 'fields_displayed' === $field || 'fields_required' === $field )
			return self::parse_field_options( self::$field_options, $value );
		else if ( 'address_fields' === $field )
			return self::parse_field_options( self::$address_field_options, $value );
		else if ( $value === $setting_default )
			return $value;
		else if ( is_bool( $setting_default ) )
			return ( 'on' === $value );
		else if ( 'preset_amounts' === $field )
			return array_map( 'doubleval', explode( ',', $value ) );
		else
			return $value;
	}

	private static function parse_field_options( $options, $values ) {
		return array_combine(
			array_keys( $options ),
			array_map(
				function( $field ) use ( $values ) { return isset( $values[$field] ); },
				array_keys( $options )
			)
		);
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

	/**
	 * Does the action for successful donations so developers can gain access to
	 *   that information for integrations or whatever they want.
	 */
	public static function get_states_or_provinces() {
		$default_list = CountryData::$us_states;
		return apply_filters(Plugin::FILTER_STATES_PROVINCES, $default_list );
	}

}
