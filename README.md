# Stripe Donation Form - WordPress Plugin

A simple donation form powered by Stripe that allows users to make one-time and monthly donations

## Features

* A collection of user-defined pre-set donation amounts with optional custom amount
* Stripe Subscriptions support for monthly donations
* Optional collection of other donator information to stay in touch
* Sensitive information passes directly from the client browser to Stripe and not through your own webserver, which reduces extra security obligations
* [Accessible](https://www.w3.org/TR/WCAG20/) and semantic markup that is ready to style and integrate into your theme or website

## Development

### Locales

In order to get currency formatting to work on the test server, one must manually choose locales to install. It doesn't come with any. First, log into the WordPress container with `docker exec -it stripe_donation_form_wp bash`. Once inside, run `dpkg-reconfigure locales` and follow the prompts to choose appropriate locales.

## Installation for production use

Get the production version at the plugin's wordpress.org page _____

## Internationalization

If you want to contribute a translation:

1. Use the `.pot` file (`plugin/languages/stripe-donation-form.pot`) and a tool like [Poedit](http://www.poedit.net/) or [Loco Translate](https://wordpress.org/plugins/loco-translate/) to make translations
1. Save the resulting .po and .mo files in the `plugin/languages` directory
1. Create a pull request for the translation
