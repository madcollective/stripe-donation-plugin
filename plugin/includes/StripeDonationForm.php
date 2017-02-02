<?php

use StripeDonationForm\Settings;
use StripeDonationForm\Views\FormView;

/**
 * This is the API through which the plugin's template functions can be accessed.
 */
class StripeDonationForm {

	public static function form( $options=[] ) {
		$settings = [
			'publishable_key' => Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_TEST_MODE ) ?
				Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_TEST_PUBLIC_KEY ) :
				Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_LIVE_PUBLIC_KEY ),
			'locale' => Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_CURRENCY, setlocale( LC_MONETARY, '0' ) ),
			'use_international_currency_symbol' => Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_CURRENCY_INTERNATIONAL ),
		];
		echo FormView::render( $settings, $options );
	}

}
