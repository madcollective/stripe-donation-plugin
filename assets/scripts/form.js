import RunIf       from './run-if';
import removeClass from 'remove-class';
import addClass    from 'add-class';
import hasClass    from 'has-class';
import delegate    from 'delegate';

function initAmounts() {
	const radioList = document.querySelector('.sdf-radio-button-list');
	const amountInput = document.querySelector('input[name="amount"]');

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
	}
	else {
		const presetAmountSelect = document.querySelector('select[name="preset-amount"]');

		// Listen for changes
		presetAmountSelect.addEventListener('change', valueChanged);

		// Set default amount
		amountInput.value = presetAmountSelect.value;
	}
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
			if (cardTypeMap[key].testr(number))
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
		initAmounts();
		initCardNumber();
	});
}
