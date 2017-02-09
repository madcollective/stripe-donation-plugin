<?php

namespace SimpleDonationsStripe\Views;

use SimpleDonationsStripe\Settings;
use SimpleDonationsStripe\Controllers\FormController;
use SimpleDonationsStripe\Tools\Locales;

/**
 * Renders the form
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class FormView {

	/**
	 * Renders the form
	 *
	 * @return string Form HTML
	 */
	public static function render( $options=[] ) {
		// Start with the default options and override with saved settings and then passed options
		$options = array_merge( Settings::get_form_settings(), $options );

		// If we're allowing a monthly donation, we should ask for
		if ( $options['allow_monthly_donation'] ) {
			$options['ask_for_email'] = true;
			$options['ask_for_name'] = true;
		}

		$action = admin_url( 'admin-ajax.php' ) . '?action=' . FormController::FORM_ACTION;

		ob_start();
		?>
			<div class="sds-form-wrapper">
				<form action="<?php echo $action; ?>" method="POST" id="stripe-donation-form">
					<span class="sds-payment-errors"></span>

					<fieldset class="sds-donation-details-fieldset">
						<legend><?php _e( 'Donation Details', 'simple-donations-stripe' ); ?></legend>
						<?php echo self::render_amount_fields( $options ) ?>
					</fieldset>

					<fieldset class="sds-payment-info-fieldset">
						<legend><?php _e( 'Payment Information', 'simple-donations-stripe' ); ?></legend>
						<?php echo self::render_payment_info_fields( $options ) ?>
					</fieldset>

					<?php if ( $options['ask_for_name'] || $options['ask_for_email'] || $options['ask_for_phone'] ) : ?>
						<fieldset class="sds-personal-info-fieldset">
							<legend><?php _e( 'Personal Information', 'simple-donations-stripe' ); ?></legend>
							<?php echo self::render_personal_info_fields( $options ) ?>
						</fieldset>
					<?php endif; ?>

					<button type="submit" class="submit"><?php _e( 'Submit Payment', 'simple-donations-stripe' ); ?></button>
				</form>

				<script type="text/javascript">
					Stripe.setPublishableKey('<?php echo $options['publishable_key']; ?>');
				</script>
			</div>
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
			$amounts['custom'] = __( 'Other', 'simple-donations-stripe' );

		ob_start();
		?>
			<?php if ( $options['show_preset_amounts'] ) : ?>
				<div class="form-row sds-amount-presets">
					<label>
						<span><?php _e( 'Your Gift Amount', 'simple-donations-stripe' ); ?></span>
						<?php if ( $options['amounts_as_select'] ) : ?>
							<select name="preset-amount">
								<?php foreach ( $amounts as $key => $value ) : ?>
									<option
										value="<?php echo $key; ?>"
										<?php if ( $key == $options['default_amount'] ) echo 'selected'; ?>>
										<?php echo $value; ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<ul class="sds-radio-button-list">
								<?php foreach ( $amounts as $key => $value ) : ?>
									<?php $id = 'donation-amount-' . $key; ?>
									<li>
										<input
											type="radio"
											name="preset-amount"
											value="<?php echo $key; ?>"
											id="<?php echo $id; ?>"
											<?php if ( $key == $options['default_amount'] ) echo 'checked'; ?>>
										<label for="<?php echo $id; ?>"><?php echo $value; ?></label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_custom_amount'] ) : ?>
				<div class="form-row sds-amount">
					<label>
						<span>
							<?php echo ( $options['custom_amount_label'] ) ? $options['custom_amount_label'] : __( 'Your Gift Amount', 'simple-donations-stripe' ); ?>
							<span class="currency-symbol"><?php echo $currency_symbol; ?></span>
						</span>
						<input type="number" name="amount" pattern="^\d+(\.|\,)\d{2}$" min="1" required>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_monthly_donation'] ) : ?>
				<div class="form-row sds-monthly">
					<label>
						<input type="checkbox" name="monthly">
						<span>
							<?php _e( 'Make this my monthly donation.', 'simple-donations-stripe' ); ?>
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
				<div class="form-row sds-name">
					<label>
						<span><?php _e( 'Name', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="name" <?php if ( $options['require_name'] ) echo 'required'; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['ask_for_email'] ) : ?>
				<div class="form-row sds-email">
					<label>
						<span><?php _e( 'Email Address', 'simple-donations-stripe' ); ?></span>
						<input type="email" name="email" <?php if ( $options['require_email'] ) echo 'required'; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['ask_for_phone'] ) : ?>
				<div class="form-row sds-phone">
					<label>
						<span><?php _e( 'Phone Number', 'simple-donations-stripe' ); ?></span>
						<input type="tel" name="phone" <?php if ( $options['require_phone'] ) echo 'required'; ?> pattern="^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$">
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
			<div class="form-row sds-card-number">
				<label>
					<span><?php _e( 'Card Number', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="20" data-stripe="number" pattern="[0-9]{13,16}" required>
				</label>
			</div>

			<div class="form-row sds-expiration">
				<label>
					<span><?php _e( 'Expiration (MM/YY)', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="2" data-stripe="exp_month" pattern="\d{2}" required>
					<span class="sds-slash"> / </span>
					<input type="number" size="2" data-stripe="exp_year" pattern="\d{2}" required>
				</label>
			</div>

			<div class="form-row sds-cvc">
				<label>
					<span><?php _e( 'CVC', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="4" data-stripe="cvc" required>
				</label>
			</div>

			<div class="form-row sds-postal-code">
				<label>
					<span><?php _e( 'Billing ZIP Code', 'simple-donations-stripe' ); ?></span>
					<input type="text" size="6" data-stripe="address_zip" required>
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

}
