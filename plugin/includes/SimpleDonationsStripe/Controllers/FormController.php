<?php

namespace SimpleDonationsStripe\Controllers;

use SimpleDonationsStripe\Plugin;
use SimpleDonationsStripe\Settings;
use SimpleDonationsStripe\Tools\Locales;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use Stripe\Plan;
use Stripe\Subscription;

/**
 * Handles form submissions
 *
 * @author     Patrick Wolfert <patrick@madcollective.com>
 */
class FormController {

	const FORM_ACTION = 'post_donate';

	const MIN_DONATION_AMOUNT = 1;
	const PHONE_NUMBER_REGEX = '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';

	/**
	 * Handles post requests for the donate action
	 */
	public static function post_donate() {
		// Collect and sanitize input
		$stripe_token = sanitize_text_field( $_POST['stripe_token'] );

		$amount  = sanitize_text_field( $_POST['amount'] );
		$name    = isset( $_POST['name']    ) ? sanitize_text_field( $_POST['name']    ) : null;
		$email   = isset( $_POST['email']   ) ? sanitize_text_field( $_POST['email']   ) : null;
		$phone   = isset( $_POST['phone']   ) ? sanitize_text_field( $_POST['phone']   ) : null;
		$monthly = isset( $_POST['monthly'] ) ? sanitize_text_field( $_POST['monthly'] ) : null;

		// Transform and determine useful things from input
		$amount = doubleval( $amount ) * self::get_currency_scale();
		$is_monthly = ( $monthly === 'on' );

		// Bundle them all up for certain function calls
		$info = compact( 'amount', 'is_monthly', 'name', 'email', 'phone' );

		// Validate input
		$validation = self::validate_post_donate( $info );

		// Process donation if valid or return errors if not
		if ( $validation === true ) {
			// Get ready to talk to Stripe
			Stripe::setApiKey( Settings::get_stripe_secret_key() );

			// Create the Stripe Customer object
			$customer = self::create_customer( $stripe_token, $email, $name, $phone );

			// Charge the customer or set up a recurring payment
			if ( $is_monthly )
				self::donate_monthly( $customer, $amount );
			else
				self::donate_single( $customer, $amount );

			// Notify listeners and send customer data
			Plugin::do_donation_success_action( $info );

			// Build our response
			$response = [
				'success' => true,
				'success_message' => Settings::get( 'success_message' ),
			];
		}
		else {
			// Build our response
			$response = [
				'errors' => $validation,
			];
		}

		// Do something with our response
		wp_send_json( $response );
	}

	/**
	 * Validates input for post_donate, returning TRUE on success and an array of
	 *   of errors if there are validation issues.
	 */
	private static function validate_post_donate( $input ) {
		$errors = array_reduce(
			self::get_required_fields(),
			function( $errors, $key ) use ( $input ) {
				if ( ! $input[$key] ) {
					return array_merge( $errors, [
						[
							'field' => $key,
							'error' => str_replace( '%s', $key, __( 'The %s field is required.', 'simple-donations-stripe' ) ),
						]
					] );
				}
				return $errors;
			},
			[]
		);

		if ( $input['amount'] < self::MIN_DONATION_AMOUNT ) {
			$locale = Settings::get( 'locale' );
			$min_amount = Locales::format_money( self::MIN_DONATION_AMOUNT, 0, $locale, false );
			$errors[] = [
				'field' => 'amount',
				'error' => __( 'Donation amount must be at least', 'simple-donations-stripe' ) . "$min_amount.",
			];
		}

		if ( $input['email'] !== null && ! filter_var( $input['email'], FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = [
				'field' => 'email',
				'error' => __( 'Invalid email provided.', 'simple-donations-stripe' ),
			];
		}

		if ( $input['phone'] !== null && ! preg_match( self::PHONE_NUMBER_REGEX, $input['phone'] ) ) {
			$errors[] = [
				'field' => 'phone',
				'error' => __( 'Invalid phone number provided.', 'simple-donations-stripe' ),
			];
		}

		if ( $errors )
			return $errors;
		else
			return true;
	}

	/**
	 * Creates and returns a Stripe Customer object
	 */
	private static function create_customer( $token, $email=null, $name=null, $phone=null ) {
		return Customer::create( [
			'email'    => $email,
			'source'   => $token,
			'metadata' => ( $name || $phone ) ? [ 'name' => $name, 'phone' => $phone ] : null,
		] );
	}

	/**
	 * Performs a single charge on a Stripe Customer
	 */
	private static function donate_single( Customer $customer, $amount ) {
		$currency = self::get_currency();

		$charge = Charge::create( [
			'customer' => $customer->id,
			'amount'   => $amount,
			'currency' => $currency,
			'statement_descriptor' => self::get_statement_descriptor(),
		] );

		return $charge;
	}

	/**
	 * Creates a plan and monthly subscription for a Stripe Customer
	 */
	private static function donate_monthly( Customer $customer, $amount ) {
		$currency = self::get_currency();

		$plan_id = $customer->id . '-' . time();

		$plan = Plan::create( [
			'id'       => $plan_id,
			'name'     => 'monthly donation',
			'amount'   => $amount,
			'currency' => $currency,
			'interval' => 'month',
			'statement_descriptor' => self::get_statement_descriptor(),
		] );

		$subscription = Subscription::create( [
			'customer' => $customer->id,
			'plan'     => $plan->id,
		] );
	}

	/**
	 * Helper function that returns the currency that Stripe should use
	 */
	private static function get_currency() {
		$locale = Settings::get( 'locale' );
		return Locales::get_currency_symbol( $locale, true );
	}

	/**
	 * Helper function that returns the scalar number to by which the amount should be multiplied
	 */
	private static function get_currency_scale() {
		return Settings::get( 'currency_scale' );
	}

	/**
	 * Helper function that returns the statement descriptor that Stripe should use
	 */
	private static function get_statement_descriptor() {
		return substr( Settings::get( 'statement_descriptor' ), 0, 22 );
	}

	private static function get_required_fields() {
		$required_fields = [ 'amount' ];

		if ( Settings::get( 'require_email' ) ) array_push( $required_fields, 'email' );
		if ( Settings::get( 'require_name'  ) ) array_push( $required_fields, 'name'  );
		if ( Settings::get( 'require_phone' ) ) array_push( $required_fields, 'phone' );

		return $required_fields;
	}

}
