# Simple Donations With Stripe by Madison Ave. Collective - WordPress Plugin

A simple donation form powered by Stripe that allows users to make one-time and monthly donations

## Features

* Customers can donate one time or monthly
* Card information passes directly from the client browser to Stripe and not through your own webserver, reducing extra security obligations
* [Accessible](https://www.w3.org/TR/WCAG20/) and semantic markup that is ready to style and integrate into your theme or website
* Action hooks to capture info about the customer and the transaction for automated emails, integrations with third-party apps, etc.
* Options for which fields to include as well as which ones to require
* Options for preset donation amounts, no preset amounts, custom amounts, etc.
* Toggle for Stripe test mode so you can see it working in Stripe before going live
* Currency options and internationalization support

## Usage

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

## Development

### Hosting a local dev server
This project includes configurations for running a local dev server with WordPress
and MySQL via [Docker](https://www.docker.com/).  The following steps will create
and boot up the dev server with a WordPress site accessible at `localhost:8080`.

1. Install [Docker](https://www.docker.com/).

1. Make sure you're in the root of the project directory and run

	```
	docker-compose up -d
	```

	or

	```
	docker-compose up
	```

	if you don't want it to run in the background.

1. Install JS and PHP dependencies by running `npm install; npm run composer-setup`.

1. Set up the fresh WordPress installation with `npm run wp-setup`.

1. Build assets by running `gulp`;

1. Now you can access the site at at [http://localhost:8080](http://localhost:8080). WordPress admin username/password are `admin`/`admin`

If you ever need to access the WordPress server or MySQL server in Docker, use

```
docker exec -it <container-name> bash
```

where the `<container-name>` is the name of the Docker container.  These default
to `stripe_donation_form_wp` and `stripe_donation_form_db` for the WordPress and MySQL
containers respectively.

### Locales

In order to get currency formatting to work on the test server, one must manually choose locales to install. It doesn't come with any. First, log into the WordPress container with `docker exec -it stripe_donation_form_wp bash`. Once inside, run `dpkg-reconfigure locales` and follow the prompts to choose appropriate locales.

### Zipping it up

To package and ship the plugin, make sure to build the static assets with the `--production` flag, and then run `gulp zip`, and it will create a zip file of the plugin folder under `dist`.

## Installation for production use

Get the production version at the plugin's wordpress.org page _____

## Internationalization

If you want to contribute a translation:

1. Use the `.pot` file (`plugin/languages/simple-donations-stripe.pot`) and a tool like [Poedit](http://www.poedit.net/) or [Loco Translate](https://wordpress.org/plugins/loco-translate/) to make translations
1. Save the resulting .po and .mo files in the `plugin/languages` directory
1. Create a pull request for the translation
