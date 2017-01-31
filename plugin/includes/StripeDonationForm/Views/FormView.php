<?php

namespace StripeDonationForm\Views;

/**
 * Renders the form
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class FormView {

	public static $default_form_options = [
		'preset_amounts' => [
			25, 150, 500, 1000
		],
		'amounts_as_select' => false,
		'allow_custom_amount' => true,
		'allow_monthly_donation' => true,
		'ask_for_email' => true,
		'ask_for_name' => true,
		'ask_for_phone' => false,
		'require_email' => false,
		'require_name' => false,
		'require_phone' => false,
	];

	public static function render( $settings, $options=[] ) {

		$pk = '';

		ob_start();
		?>
			<form method="POST" id="stripe-donation-form">
				<span class="payment-errors"></span>

				<fieldset class="donation-details-fieldset">
					<legend><?php _e( 'Donation Details', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_amount_fields() ?>
				</fieldset>

				<fieldset class="payment-info-fieldset">
					<legend><?php _e( 'Payment Information', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_payment_info_fields() ?>
				</fieldset>

				<fieldset class="personal-info-fieldset">
					<legend><?php _e( 'Personal Information', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_personal_info_fields() ?>
				</fieldset>

				<button type="submit" class="submit"><?php _e( 'Submit Payment', 'stripe-donation-form' ); ?></button>
			</form>

			<script type="text/javascript">
				Stripe.setPublishableKey('<?php echo $pk; ?>');
			</script>
		<?php
		return ob_get_clean();
	}

	private static function render_amount_fields( $amounts, $allow_custom_amount, $use_select ) {

		// TODO: Don't just assume US format
		setlocale( LC_MONETARY, 'en_US' );

		// Format the numbers amounts but retain the values as keys
		$amounts = array_combine(
			$amounts,
			array_map( function( $amount ) {
				return money_format( '%n', $amount );
			}, $amounts )
		);

		// Optionally append a "Custom" option to the end
		if ( $allow_custom_amount )
			$amounts['custom'] = 'Custom';

		ob_start();
		?>
			<div class="form-row">
				<label>
					<span><?php _e( 'Card Number', 'stripe-donation-form' ); ?></span>
					<?php if ( $use_select ) : ?>
						<select name="preset-amount">
							<?php foreach ( $amounts as $key => $value ) : ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<ul name="radio-button-list">
							<?php foreach ( $amounts as $key => $value ) : ?>
								<?php $id = 'donation-amount-' . $key; ?>
								<li>
									<input type="radio" value="<?php echo $key; ?>" id="<?php echo $id; ?>">
									<label for="<?php echo $id; ?>"><?php echo $value; ?></label>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

	private static function render_personal_info_fields() {
		ob_start();
		?>
			<div class="form-row">
				<label>
					<span><?php _e( 'Card Number', 'stripe-donation-form' ); ?></span>
					<input type="text" size="20" data-stripe="number">
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

	private static function render_payment_info_fields() {
		ob_start();
		?>
			<div class="form-row">
				<label>
					<span><?php _e( 'Card Number', 'stripe-donation-form' ); ?></span>
					<input type="text" size="20" data-stripe="number">
				</label>
			</div>

			<div class="form-row">
				<label>
					<span><?php _e( 'Expiration (MM/YY)', 'stripe-donation-form' ); ?></span>
					<input type="text" size="2" data-stripe="exp_month">
				</label>
				<span> / </span>
				<input type="text" size="2" data-stripe="exp_year">
			</div>

			<div class="form-row">
				<label>
					<span><?php _e( 'CVC', 'stripe-donation-form' ); ?></span>
					<input type="text" size="4" data-stripe="cvc">
				</label>
			</div>

			<div class="form-row">
				<label>
					<span><?php _e( 'Billing ZIP Code', 'stripe-donation-form' ); ?></span>
					<input type="text" size="6" data-stripe="address_zip">
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

}
