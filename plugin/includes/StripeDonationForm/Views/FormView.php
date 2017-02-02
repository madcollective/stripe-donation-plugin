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
		'default_amount' => 150,
		'amounts_as_select' => false,
		'show_preset_amounts' => true,
		'allow_custom_amount' => true,
		'allow_monthly_donation' => true,
		'ask_for_email' => true,
		'ask_for_name' => true,
		'ask_for_phone' => false,
		'require_email' => false,
		'require_name' => false,
		'require_phone' => false,
		'custom_amount_label' => null,
	];

	public static function render( $settings, $options=[] ) {
		// Start with the default options and override with saved settings and then passed options
		$options = array_merge( self::$default_form_options, $settings, $options );

		ob_start();
		?>
			<form method="POST" id="stripe-donation-form">
				<span class="sdf-payment-errors"></span>

				<fieldset class="sdf-donation-details-fieldset">
					<legend><?php _e( 'Donation Details', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_amount_fields( $options ) ?>
				</fieldset>

				<fieldset class="sdf-payment-info-fieldset">
					<legend><?php _e( 'Payment Information', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_payment_info_fields( $options ) ?>
				</fieldset>

				<fieldset class="sdf-personal-info-fieldset">
					<legend><?php _e( 'Personal Information', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_personal_info_fields( $options ) ?>
				</fieldset>

				<button type="submit" class="submit"><?php _e( 'Submit Payment', 'stripe-donation-form' ); ?></button>
			</form>

			<script type="text/javascript">
				Stripe.setPublishableKey('<?php echo $options['publishable_key']; ?>');
			</script>
		<?php
		return ob_get_clean();
	}

	private static function render_amount_fields( $options ) {

		// TODO: Don't just assume US format
		setlocale( LC_MONETARY, 'en_US' );

		// Format the numbers amounts but retain the values as keys
		$amounts = array_combine(
			$options['preset_amounts'],
			array_map( function( $amount ) {
				return money_format( '%.0n', $amount );
			}, $options['preset_amounts'] )
		);

		$locale_info = localeconv();
		$currency_symbol = $locale_info['currency_symbol'];

		// Optionally append a "Custom" option to the end
		if ( $options['allow_custom_amount'] )
			$amounts['custom'] = 'Custom';

		ob_start();
		?>
			<?php if ( $options['show_preset_amounts'] ) : ?>
				<div class="form-row sdf-amount-presets">
					<label>
						<span><?php _e( 'Your Gift Amount', 'stripe-donation-form' ); ?></span>
						<?php if ( $options['amounts_as_select'] ) : ?>
							<select name="preset-amount">
								<?php foreach ( $amounts as $key => $value ) : ?>
									<option
										value="<?php echo $key; ?>"
										<?php if ( $key === $options['default_amount'] ) echo 'selected'; ?>>
										<?php echo $value; ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<ul class="sdf-radio-button-list">
								<?php foreach ( $amounts as $key => $value ) : ?>
									<?php $id = 'donation-amount-' . $key; ?>
									<li>
										<input
											type="radio"
											name="preset-amount"
											value="<?php echo $key; ?>"
											id="<?php echo $id; ?>"
											<?php if ( $key === $options['default_amount'] ) echo 'checked'; ?>>
										<label for="<?php echo $id; ?>"><?php echo $value; ?></label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_custom_amount'] ) : ?>
				<div class="form-row sdf-amount">
					<label>
						<span>
							<?php echo ( $options['custom_amount_label'] ) ? $options['custom_amount_label'] : __( 'Your Gift Amount', 'stripe-donation-form' ); ?>
							<span class="currency-symbol"><?php echo $currency_symbol; ?></span>
						</span>
						<input type="number" name="amount" pattern="^\d+(\.|\,)\d{2}$">
					</label>
				</div>
			<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	private static function render_personal_info_fields( $options ) {
		ob_start();
		?>
			<div class="form-row sdf-name">
				<label>
					<span><?php _e( 'Name', 'stripe-donation-form' ); ?></span>
					<input type="text" name="name">
				</label>
			</div>
			<div class="form-row sdf-email">
				<label>
					<span><?php _e( 'Email', 'stripe-donation-form' ); ?></span>
					<input type="email" name="email">
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

	private static function render_payment_info_fields( $options ) {
		ob_start();
		?>
			<div class="form-row sdf-card-number">
				<label>
					<span><?php _e( 'Card Number', 'stripe-donation-form' ); ?></span>
					<input type="number" size="20" data-stripe="number" pattern="[0-9]{13,16}">
				</label>
			</div>

			<div class="form-row sdf-expiration">
				<label>
					<span><?php _e( 'Expiration (MM/YY)', 'stripe-donation-form' ); ?></span>
					<input type="number" size="2" data-stripe="exp_month" pattern="\d{2}">
					<span> / </span>
					<input type="number" size="2" data-stripe="exp_year" pattern="\d{2}">
				</label>
			</div>

			<div class="form-row sdf-cvc">
				<label>
					<span><?php _e( 'CVC', 'stripe-donation-form' ); ?></span>
					<input type="number" size="4" data-stripe="cvc">
				</label>
			</div>

			<div class="form-row sdf-postal-code">
				<label>
					<span><?php _e( 'Billing ZIP Code', 'stripe-donation-form' ); ?></span>
					<input type="text" size="6" data-stripe="address_zip">
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

}
