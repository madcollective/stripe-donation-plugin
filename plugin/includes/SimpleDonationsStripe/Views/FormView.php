<?php

namespace SimpleDonationsStripe\Views;

use SimpleDonationsStripe\Settings;
use SimpleDonationsStripe\Controllers\FormController;
use SimpleDonationsStripe\Tools\Locales;
use SimpleDonationsStripe\Tools\CountryData;

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
		if ( $options['allow_monthly_donation'] )
			$options['fields_displayed']['email'] = true;

		$display_personal_info_section = (
			$options['fields_displayed']['name'] ||
			$options['fields_displayed']['name_first'] ||
			$options['fields_displayed']['name_last'] ||
			$options['fields_displayed']['email'] ||
			$options['fields_displayed']['phone']
		);

		$action = admin_url( 'admin-ajax.php' ) . '?action=' . FormController::FORM_ACTION;

		ob_start();
		?>
			<div class="sds-form-wrapper">
				<form action="<?php echo $action; ?>" method="POST" id="stripe-donation-form">
					<span class="sds-payment-errors"></span>

					<fieldset class="sds-donation-details-fieldset">
						<legend><?php _e( 'Donation Details', 'simple-donations-stripe' ); ?></legend>
						<div class="sds-fields">
							<?php echo self::render_amount_fields( $options ) ?>
						</div>
					</fieldset>

					<?php if ( $display_personal_info_section ) : ?>
						<fieldset class="sds-personal-info-fieldset">
							<legend><?php _e( 'Personal Information', 'simple-donations-stripe' ); ?></legend>
							<div class="sds-fields">
								<?php echo self::render_personal_info_fields( $options ) ?>
							</div>
						</fieldset>
					<?php endif; ?>

					<fieldset class="sds-payment-info-fieldset">
						<legend><?php _e( 'Payment Information', 'simple-donations-stripe' ); ?></legend>
						<div class="sds-fields">
							<?php echo self::render_payment_info_fields( $options ) ?>
						</div>
					</fieldset>

					<?php if ( $options['fields_displayed']['mailing_address'] ) : ?>
						<fieldset class="sds-mailing-address-fieldset">
							<legend><?php _e( 'Mailing Address', 'simple-donations-stripe' ); ?></legend>
							<div class="sds-fields">
								<?php echo self::render_mailing_address_fields( $options ) ?>
							</div>
						</fieldset>
					<?php endif; ?>

					<button type="submit" class="sds-submit"><?php _e( 'Submit Payment', 'simple-donations-stripe' ); ?></button>

					<a href="https://stripe.com/" class="sds-powered-by-stripe">Powered by Stripe</a>
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

		$default = ( 0 == $options['default_amount'] ) ? null : intval( $options['default_amount'] );

		ob_start();
		?>
			<?php if ( $options['show_preset_amounts'] ) : ?>
				<div class="field-wrapper sds-amount-presets">
					<label>
						<span><?php _e( 'Your Gift Amount', 'simple-donations-stripe' ); ?></span>
						<?php if ( $options['amounts_as_select'] ) : ?>
							<select name="preset-amount" class="sds-field">
								<?php foreach ( $amounts as $key => $value ) : ?>
									<option
										value="<?php echo $key; ?>"
										<?php if ( $key === $default ) echo 'selected'; ?>>
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
											class="sds-field"
											<?php if ( $key === $default ) echo 'checked'; ?>>
										<label for="<?php echo $id; ?>"><?php echo $value; ?></label>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_custom_amount'] ) : ?>
				<div class="field-wrapper sds-amount">
					<label>
						<span>
							<?php echo ( $options['custom_amount_label'] ) ? $options['custom_amount_label'] : __( 'Your Gift Amount', 'simple-donations-stripe' ); ?>
							<span class="currency-symbol"><?php echo $currency_symbol; ?></span>
						</span>
						<input type="number" name="amount" class="sds-field" pattern="^\d+(\.|\,)\d{2}$" min="1" required>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['allow_monthly_donation'] ) : ?>
				<div class="field-wrapper sds-monthly">
					<label><?php _e( 'Frequency', 'simple-donations-stripe' ); ?></label>
					<ul class="sds-radio-button-list">
						<li>
							<input type="radio" name="monthly" value="no" id="monthly_no" class="sds-field" checked>
							<label for="monthly_no">One Time</label>
						</li>
						<li>
							<input type="radio" name="monthly" value="yes" id="monthly_yes" class="sds-field">
							<label for="monthly_yes">Monthly</label>
						</li>
					</ul>
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
			<?php if ( $options['fields_displayed']['name'] ) : ?>
				<div class="field-wrapper sds-name">
					<label>
						<span><?php _e( 'Name', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="name" class="sds-field" <?php echo ( $options['fields_required']['name'] ) ? 'required' : '' ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['fields_displayed']['name_first'] ) : ?>
				<div class="field-wrapper sds-name-first">
					<label>
						<span><?php _e( 'First Name', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="name_first" class="sds-field" <?php echo ( $options['fields_required']['name_first'] ) ? 'required' : '' ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['fields_displayed']['name_last'] ) : ?>
				<div class="field-wrapper sds-name-last">
					<label>
						<span><?php _e( 'Last Name', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="name_last" class="sds-field" <?php echo ( $options['fields_required']['name_last'] ) ? 'required' : '' ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['fields_displayed']['email'] ) : ?>
				<div class="field-wrapper sds-email">
					<label>
						<span><?php _e( 'Email Address', 'simple-donations-stripe' ); ?></span>
						<input type="email" name="email" class="sds-field" <?php echo ( $options['fields_required']['email'] ) ? 'required' : '' ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $options['fields_displayed']['phone'] ) : ?>
				<div class="field-wrapper sds-phone">
					<label>
						<span><?php _e( 'Phone Number', 'simple-donations-stripe' ); ?></span>
						<input type="tel" name="phone" class="sds-field" <?php echo ( $options['fields_required']['phone'] ) ? 'required' : '' ?> pattern="^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$">
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
			<div class="field-wrapper sds-card-number">
				<label>
					<span><?php _e( 'Card Number', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="20" data-stripe="number" class="sds-field" pattern="[0-9]{13,16}" required>
				</label>
			</div>

			<ul class="sds-card-types">
				<li class="visa"></li>
				<li class="mastercard"></li>
				<li class="amex"></li>
				<li class="diners-club"></li>
				<li class="discover"></li>
				<li class="jcb"></li>
			</ul>

			<div class="field-wrapper sds-expiration">
				<label>
					<span><?php _e( 'Expiration (MM/YY)', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="2" data-stripe="exp_month" class="sds-field" pattern="\d{2}" required>
					<span class="sds-slash"> / </span>
					<input type="number" size="2" data-stripe="exp_year" class="sds-field" pattern="\d{2}" required>
				</label>
			</div>

			<div class="field-wrapper sds-cvc">
				<label>
					<span><?php _e( 'CVC', 'simple-donations-stripe' ); ?></span>
					<input type="number" size="4" data-stripe="cvc" class="sds-field" required>
				</label>
			</div>

			<div class="field-wrapper sds-postal-code">
				<label>
					<span><?php _e( 'Billing ZIP Code', 'simple-donations-stripe' ); ?></span>
					<input type="text" size="6" data-stripe="address_zip" class="sds-field" required>
				</label>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the personal info fields
	 *
	 * @return string Partial form HTML
	 */
	private static function render_mailing_address_fields( $options ) {
		$required = $options['fields_required']['mailing_address'] ? 'required' : '';
		$fields = $options['address_fields'];
		$states = Settings::get_states_or_provinces();
		$states_options = array_reduce( array_keys( $states ), function( $html, $key ) use ( $states ) {
			return $html . '<option value="' . $key . '">' . $states[$key] . '</options>' . "\n";
		} );
		$country_options = array_reduce( CountryData::$countries, function( $html, $country ) {
			return $html . '<option value="' . $country . '">' . $country . '</options>' . "\n";
		} );

		ob_start();
		?>
			<?php if ( $fields['address_1'] ) : ?>
				<div class="field-wrapper sds-address-1">
					<label>
						<span><?php _e( 'Address', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="address_1" class="sds-field" <?php echo $required; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_2'] ) : ?>
				<div class="field-wrapper sds-address-2">
					<label>
						<span><?php _e( 'Address Line 2', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="address_2" class="sds-field" <?php echo $required; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_zip'] ) : ?>
				<div class="field-wrapper sds-address-zip">
					<label>
						<span><?php _e( 'ZIP', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="address_zip" class="sds-field" pattern="^\d{5}(?:[-\s]\d{4})?$" <?php echo $required; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_postal'] ) : ?>
				<div class="field-wrapper sds-address-zip">
					<label>
						<span><?php _e( 'Postal', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="address_postal" class="sds-field" <?php echo $required; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_city'] ) : ?>
				<div class="field-wrapper sds-address-city">
					<label>
						<span><?php _e( 'City', 'simple-donations-stripe' ); ?></span>
						<input type="text" name="address_city" class="sds-field" size="6" <?php echo $required; ?>>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_state'] ) : ?>
				<div class="field-wrapper sds-address-state">
					<label>
						<span><?php _e( 'State', 'simple-donations-stripe' ); ?></span>
						<select name="address_state" class="sds-field" <?php echo $required; ?>>
							<?php echo $states_options; ?>
						</select>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_province'] ) : ?>
				<div class="field-wrapper sds-address-province">
					<label>
						<span><?php _e( 'Province', 'simple-donations-stripe' ); ?></span>
						<select name="address_province" class="sds-field" <?php echo $required; ?>>
							<?php echo $states_options; ?>
						</select>
					</label>
				</div>
			<?php endif; ?>
			<?php if ( $fields['address_country'] ) : ?>
				<div class="field-wrapper sds-address-country">
					<label>
						<span><?php _e( 'Country', 'simple-donations-stripe' ); ?></span>
						<select name="address_country" class="sds-field" <?php echo $required; ?>>
							<?php echo $country_options; ?>
						</select>
					</label>
				</div>
			<?php endif; ?>
		<?php
		return ob_get_clean();
	}

}
