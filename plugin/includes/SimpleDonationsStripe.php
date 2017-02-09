<?php

use SimpleDonationsStripe\Views\FormView;

/**
 * This is the API through which the plugin's template functions can be accessed.
 */
class SimpleDonationsStripe {

	public static function form( $options=[] ) {
		echo FormView::render( $options );
	}

}
