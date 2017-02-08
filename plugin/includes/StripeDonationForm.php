<?php

use StripeDonationForm\Views\FormView;

/**
 * This is the API through which the plugin's template functions can be accessed.
 */
class StripeDonationForm {

	public static function form( $options=[] ) {
		echo FormView::render( $options );
	}

}
