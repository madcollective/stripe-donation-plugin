<?php

namespace StripeDonationForm\Controllers;

use StripeDonationForm\Settings;
use StripeDonationForm\Tools\Locales;

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

	/**
	 * Handles post requests for the donate action
	 */
	public static function post_donate() {
		// Collect and sanitize input
		$stripe_token = sanitize_text_field( $_POST['stripe_token'] );

		$amount  = sanitize_text_field( $_POST['amount'] );
		$monthly = sanitize_text_field( $_POST['monthly'] );
		$name    = sanitize_text_field( $_POST['name'] );
		$email   = sanitize_text_field( $_POST['email'] );
		$phone   = sanitize_text_field( $_POST['phone'] );

		// Transform and determine useful things from input
		$amount = floatval( $amount ) * self::get_currency_scale();
		$is_monthly = ( $monthly === 'on' );

		// Validate input
		$validation = self::validate_post_donate( $amount, $is_monthly, $name, $email, $phone );

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

			// Build our response
			$response = [
				'success' => true,
				'stripe_token' => $stripe_token,
			];
		}
		else {
			// Build our response
			$response = [
				'errors' => $validation,
				'stripe_token' => $stripe_token,
			];
		}

		// Do something with our response
		wp_send_json( $response );
	}

	/**
	 * Validates input for post_donate, returning TRUE on success and an array of
	 *   of errors if there are validation issues.
	 */
	private static function validate_post_donate( $amount, $is_monthly, $name, $email, $phone ) {
		$errors = [];

		if ( $amount < self::MIN_DONATION_AMOUNT ) {
			$locale = Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_CURRENCY, setlocale( LC_MONETARY, '0' ) );
			$min_amount = Locales::format_money( self::MIN_DONATION_AMOUNT, 0, $locale, false );
			$errors[] = [
				'field' => 'amount',
				'error' => __( 'Donation amount must be at least', 'stripe-donation-form' ) . "$min_amount.",
			];
		}

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = [
				'field' => 'email',
				'error' => __( 'Invalid email provided.', 'stripe-donation-form' ),
			];
		}

		if ( ! preg_match( '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/', $phone ) ) {
			$errors[] = [
				'field' => 'phone',
				'error' => __( 'Invalid phone number provided.', 'stripe-donation-form' ),
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
		$locale = Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_CURRENCY, setlocale( LC_MONETARY, '0' ) );
		return Locales::get_currency_symbol( $locale, true );
	}

	/**
	 * Helper function that returns the scalar number to by which the amount should be multiplied
	 */
	private static function get_currency_scale() {
		return Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_CURRENCY_SCALE, 100 );
	}

	/**
	 * Helper function that returns the statement descriptor that Stripe should use
	 */
	private static function get_statement_descriptor() {
		return substr( Settings::get( Settings::SETTINGS_STRIPE, Settings::FIELD_STATEMENT_DESCRIPTOR ), 0, 22 );
	}

}