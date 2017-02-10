=== Simple Donations With Stripe ===
Tags: donation form, stripe
Requires at least: 3.0.1
Tested up to: 4.7.2
Stable tag: 4.7
License: MIT

A simple donation form powered by Stripe that allows users to make one-time and monthly donations

== Description ==

This plugin provides a simple way to include a donation form in your theme or website. The style is
a blank slate so you have complete control over how it looks and how well it integrates into your
theme. You can include it via PHP or shortcode.

= Features: =

* Customers can donate one time or monthly
* Card information passes directly from the client browser to Stripe and not through your own webserver, reducing extra security obligations
* [Accessible](https://www.w3.org/TR/WCAG20/) and semantic markup that is ready to style and integrate into your theme or website
* Action hooks to capture info about the customer and the transaction for automated emails, integrations with third-party apps, etc.
* Options for which fields to include as well as which ones to require
* Options for preset donation amounts, no preset amounts, custom amounts, etc.
* Toggle for Stripe test mode so you can see it working in Stripe before going live
* Currency options and internationalization support

= Including the Form =

The form can be included with either a shortcode:

```
[simple-donations-stripe-form]
```

Or a function call:

```php
<?php SimpleDonationsStripe::form(); ?>
```

Function call with options to override global settings:

```php
<?php
    SimpleDonationsStripe::form( [
        'success_message' => 'Foo',
        'require_phone' => false,
    ] );
?>
```

== Installation ==

1. Upload the `simple-donations-stripe` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to `Settings > Donation Form` and input your Stripe Keys
1. Place `<?php SimpleDonationsStripe::form(); ?>` in your templates

== Frequently Asked Questions ==

= Do I need my own Stripe account? =

Yes.

= Is there a test mode I can use during development? =

Yes!

== Screenshots ==
