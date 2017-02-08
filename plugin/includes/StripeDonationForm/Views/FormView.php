<?php

namespace StripeDonationForm\Views;

use StripeDonationForm\Controllers\FormController;
use StripeDonationForm\Tools\Locales;

/**
 * Renders the form
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class FormView {

	/**
	 * @var array    $default_form_options    The default options for FormView::render
	 */
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

	/**
	 * Renders the form
	 *
	 * @return string Form HTML
	 */
	public static function render( $settings, $options=[] ) {
		// Start with the default options and override with saved settings and then passed options
		$options = array_merge( self::$default_form_options, $settings, $options );

		// If we're allowing a monthly donation, we should ask for
		if ( $options['allow_monthly_donation'] ) {
			$options['ask_for_email'] = true;
			$options['ask_for_name'] = true;
			$options['ask_for_phone'] = true;
		}

		$action = admin_url( 'admin-ajax.php' ) . '?action=' . FormController::FORM_ACTION;

		ob_start();
		?>
			<form action="<?php echo $action; ?>" method="POST" id="stripe-donation-form">
				<span class="sdf-payment-errors"></span>

				<fieldset class="sdf-donation-details-fieldset">
					<legend><?php _e( 'Donation Details', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_amount_fields( $options ) ?>
				</fieldset>

				<fieldset class="sdf-payment-info-fieldset">
					<legend><?php _e( 'Payment Information', 'stripe-donation-form' ); ?></legend>
					<?php echo self::render_payment_info_fields( $options ) ?>
				</fieldset>

				<?php if ( $options['ask_for_name'] || $options['ask_for_email'] || $options['ask_for_phone'] ) : ?>
					<fieldset class="sdf-personal-info-fieldset">
						<legend><?php _e( 'Personal Information', 'stripe-donation-form' ); ?></legend>
						<?php echo self::render_personal_info_fields( $options ) ?>
					</fieldset>
				<?php endif; ?>

				<button type="submit" class="submit"><?php _e( 'Submit Payment', 'stripe-donation-form' ); ?></button>
			</form>

			<script type="text/javascript">
				Stripe.setPublishableKey('<?php echo $options['publishable_key']; ?>');
			</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the amount fields
	 *
	 * @return string Partial form HTML
	 */
	private static function render_amount_fields( $options ) {
		$international = $options['use_international_currency_symbol'];

		$currency_symbol = Locales::get_currency_symbol( $options['locale'], $international );

		$amounts = array_combine(
			$options['preset_amounts'],
			Locales::format_money( $options['preset_amounts'], 0, $options['locale'], $international )
		);

		// Optionally append a "Custom" option to the end
		if ( $options['allow_custom_amount'] )
			$amounts['custom'] = __( 'Other', 'stripe-donation-form' );

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
						<input type="number" name="amount" pattern="^\d+(\.|\,)\d{2}$" min="1">
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_monthly_donation'] ) : ?>
				<div class="form-row sdf-monthly">
					<label>
						<input type="checkbox" name="monthly">
						<span>
							<?php _e( 'Make this my monthly donation.', 'stripe-donation-form' ); ?>
							<small><?php echo $options['monthly_note_text']; ?></small>
						</span>
					</label>
				</div>
			<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the personal info fields
	 *
	 * @return string Partial form HTML
	 */
	private static function render_personal_info_fields( $options ) {
		ob_start();
		?>
			<?php if ( $options['ask_for_name'] ) : ?>
				<div class="form-row sdf-name" <?php if ( $options['require_name'] ) echo 'required'; ?>>
					<label>
						<span><?php _e( 'Name', 'stripe-donation-form' ); ?></span>
						<input type="text" name="name">
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['ask_for_email'] ) : ?>
				<div class="form-row sdf-email" <?php if ( $options['require_email'] ) echo 'required'; ?>>
					<label>
						<span><?php _e( 'Email Address', 'stripe-donation-form' ); ?></span>
						<input type="email" name="email">
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['ask_for_phone'] ) : ?>
				<div class="form-row sdf-phone" <?php if ( $options['require_phone'] ) echo 'required'; ?>>
					<label>
						<span><?php _e( 'Phone Number', 'stripe-donation-form' ); ?></span>
						<input type="tel" name="phone" pattern="^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$">
					</label>
				</div>
			<?php endif; ?>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the payment info fields
	 *
	 * @return string Partial form HTML
	 */
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
					<span class="sdf-slash"> / </span>
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
