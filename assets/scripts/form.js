import RunIf       from './run-if';
import removeClass from 'remove-class';
import addClass    from 'add-class';
import hasClass    from 'has-class';
import delegate    from 'delegate';

/**
 * From http://stackoverflow.com/a/26556347/4085004
 */
function paramsFromForm(form) {
	// Filter the form elements that we want
	var elements = []
		.filter.call(form.elements, function(el) {
			// Filter out checkboxes/radios that aren't checked
			return (
				el.checked || (
					el.type !== 'checkbox' &&
					el.type !== 'radio'
				)
			);
		})
		.filter(function(el) { return !!el.name; }) // Nameless elements die.
		.filter(function(el) { return !el.disabled; }); // Disabled elements die.

	// Build a parameters string
	return elements.map(function(el) {
		// Map each field into a name=value string, make sure to properly escape!
		return encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value);
	}).join('&');
}

/**
 * Initializes the form behavior of requesting a Stripe token and submitting to
 *   the form controller via ajax
 */
function initSubmission() {
	const form = document.querySelector('#stripe-donation-form');
	const submit = form.querySelector('.submit');
	const errorsElement = form.querySelector('.sds-payment-errors');

	form.addEventListener('submit', (event) => {
		// Disable the submit button to prevent repeated clicks
		submit.setAttribute('disabled', 'disabled');
		clearErrors();

		// Request a token from Stripe
		Stripe.card.createToken(form, stripeResponseHandler);

		// Prevent the form from being submitted
		event.preventDefault();
	});

	function stripeResponseHandler(status, response) {
		if (response.error) { // Problem!
			// Show the errors on the form
			errorsElement.textContent = response.error.message;

			// Re-enable submission
			submit.removeAttribute('disabled');
		}
		else { // Token was created!
			// Get the token ID
			const token = response.id;

			// Insert the token ID into the form so it gets submitted to the server
			var input = form.querySelector('input[name="stripe_token"]');
			if (!input) {
				input = document.createElement('input');
				input.setAttribute('type', 'hidden');
				input.setAttribute('name', 'stripe_token');
				form.appendChild(input);
			}
			input.value = token;

			// Submit the form
			ajaxSubmit();
		}
	}

	function ajaxSubmit() {
		const url = form.action;
		const xhr = new XMLHttpRequest();
		const params = paramsFromForm(form);

		xhr.open('POST', url);
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.addEventListener('load', onResponse);
		xhr.addEventListener('error', onError);
		xhr.send(params);
	}

	function onResponse(event) {
		// Re-enable submission
		submit.removeAttribute('disabled');

		if (event.target.status === 200) {
			const response = JSON.parse(event.target.response);
			if (response.success)
				showSuccess(response.success_message);
			else
				response.errors.forEach(showError);
		}
		else {
			errorsElement.innerHTML = event.target.statusText;
		}
	}

	function showSuccess(successMessage) {
		const wrapper = form.parentNode;
		const success = document.createElement('div');
		success.className = 'sds-success-message';
		success.innerHTML = successMessage;
		wrapper.innerHTML = '';
		wrapper.appendChild(success);

		var rect = success.getBoundingClientRect();
		window.scrollTo(0, Math.max(rect.top + document.body.scrollTop - 20, 0));
	}

	function onError(event) {
		// Re-enable submission
		submit.removeAttribute('disabled');
		errorsElement.innerHTML = 'Error sending form data.';
		console.error(event);
	}

	function clearErrors() {
		errorsElement.innerHTML = '';

		const fieldErrors = form.querySelector('.sds-field-error');
		if (fieldErrors) {
			[].forEach.call(fieldErrors, (el) => {
				el.parentNode.removeChild(el);
			});
		}
	}

	function showError(error) {
		if (error.field) {
			const field = form.querySelector('[name="' + error.field + '"]');
			if (field) {
				const errorElement = document.createElement('div');
				errorElement.className = 'sds-field-error';
				errorElement.innerHTML = error.error;
				field.parentNode.parentNode.appendChild(errorElement);
			}
		}
		else {
			errorsElement.innerHTML += '<p>' + error.error + '</p>';
		}
	}
}

function initAmounts() {
	const radioList   = document.querySelector('.sds-radio-button-list');
	const amountInput = document.querySelector('input[name="amount"]');
	var presetAmounts;
	var setPresetAmount;

	function valueChanged(event) {
		if (event.target.value !== 'custom')
			amountInput.value = event.target.value;
		else
			amountInput.select();
	}

	if (radioList) {
		// Listen for changes
		delegate(radioList, 'input', 'click', valueChanged);

		// Set default amount
		amountInput.value = radioList.querySelector('input:checked').value;

		// Create list of preset amounts
		presetAmounts = [].map.call(radioList.querySelectorAll('input'), (el) => el.value );

		// Define the function to set the current preset amount
		setPresetAmount = function(amount) {
			[].forEach.call(radioList.querySelectorAll('input'), (el) => {
				el.checked = (el.value === amount);
			});
		};
	}
	else {
		const presetAmountSelect = document.querySelector('select[name="preset-amount"]');

		// Listen for changes
		presetAmountSelect.addEventListener('change', valueChanged);

		// Set default amount
		amountInput.value = presetAmountSelect.value;

		// Create list of preset amounts
		presetAmounts = [].map.call(presetAmountSelect.querySelectorAll('option'), (el) => el.value );

		// Define the function to set the current preset amount
		setPresetAmount = function(amount) {
			presetAmountSelect.value = amount;
		};
	}

	function isPresetAmount(amount) {
		return presetAmounts.indexOf(amount) !== -1;
	}

	amountInput.addEventListener('change', (event) => {
		setPresetAmount(
			isPresetAmount(event.target.value) ? event.target.value : 'custom'
		);
	});
}

function initCardNumber() {
	const numberInput = document.querySelector('input[data-stripe="number"]');

	const cardTypeMap = {
		'card-type-visa':        new RegExp(/^4[0-9]{12}(?:[0-9]{3})?$/),
		'card-type-mastercard':  new RegExp(/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/),
		'card-type-amex':        new RegExp(/^3[47][0-9]{13}$/),
		'card-type-diners-club': new RegExp(/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/),
		'card-type-discover':    new RegExp(/^6(?:011|5[0-9]{2})[0-9]{12}$/),
		'card-type-jcb':         new RegExp(/^(?:2131|1800|35\d{3})\d{11}$/),
	};

	function numberChanged(event) {
		const number = numberInput.value;

		// Determine what type of card it is and apply the corresponding class to the element
		for (let key in cardTypeMap) {
			if (cardTypeMap[key].test(number))
				addClass(numberInput, key);
			else
				removeClass(numberInput, key);
		}

		event.preventDefault();
	}

	numberInput.addEventListener('change',   numberChanged);
	numberInput.addEventListener('keypress', numberChanged);
}

export function onLoad() {
	RunIf.selector('#stripe-donation-form', () => {
		initSubmission();
		initAmounts();
		initCardNumber();
	});
}
