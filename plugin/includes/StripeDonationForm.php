<?php

use StripeDonationForm\Settings;
use StripeDonationForm\Views\FormView;

/**
 * This is the API through which the plugin's template functions can be accessed.
 */
class StripeDonationForm {

	public static function form( $options=[] ) {
		echo FormView::render( Settings::get_form_settings(), $options );
	}

}
