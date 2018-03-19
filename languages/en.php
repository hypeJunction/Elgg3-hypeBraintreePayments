<?php

return [
	'payments:braintree:settings' => 'Braintree Settings',
	'payments:braintree:transactions' => 'Braintree Transactions',

	'braintree:setting:sandbox_merchant_id' => 'Sandbox Merchant ID',
	'braintree:setting:sandbox_private_key' => 'Sandbox Private Key',
	'braintree:setting:sandbox_public_key' => 'Sandbox Public Key',
	'braintree:setting:production_merchant_id' => 'Production Merchant ID',
	'braintree:setting:production_private_key' => 'Production Private Key',
	'braintree:setting:production_public_key' => 'Production Public Key',

	'payments:braintree:card:processing' => 'Validating ...',

	'payments:braintree:settings:webhooks' => 'Please configure your Braintree app to send webhooks to %s',

	'payments:method:braintree' => 'Credit Card',
	'field:braintree:card' => 'Credit or Debit Card',

	'payments:braintree:no_source' => 'Payment source is missing',
	'payments:braintree:card_error' => 'Payment with this card has failed',

	'payments:braintree:card_error:invalid_number' => 'The card number is not a valid credit card number',

	'payments:braintree:card_error:invalid_expiry_month' => 'The card\'s expiration month is invalid',
	'payments:braintree:card_error:invalid_expiry_year' => 'The card\'s expiration year is invalid',
	'payments:braintree:card_error:invalid_cvc' => 'The card\'s security code is invalid',
	'payments:braintree:card_error:incorrect_number' => 'The card number is incorrect',
	'payments:braintree:card_error:expired_card' => 'The card has expired',
	'payments:braintree:card_error:incorrect_cvc' => 'The card\'s security code is incorrect',
	'payments:braintree:card_error:incorrect_zip' => 'The card\'s zip code failed validation',
	'payments:braintree:card_error:card_declined' => 'The card was declined',
	'payments:braintree:card_error:missing' => 'There is no card on a customer that is being charged',
	'payments:braintree:card_error:processing_error' => 'An error occurred while processing the card',

	'payments:braintree:api_error' => 'There was a problem contacting the card processor. Please try again later',

	'payments:charges:braintree_fee' => 'Processing Fee',

	'payments:braintree:card' => 'Credit Card',
	'payments:braintree:card:name' => 'Cardholder Name',
	'payments:braintree:card:number' => 'Card Number',
	'payments:braintree:card:expiry' => 'Expires',
	'payments:braintree:card:cvc' => 'CVC',

	'payments:braintree:billing' => 'Billing Address',
	'payments:braintree:card:address_line1'=> 'Street Address',
	'payments:braintree:card:address_line2'=> 'Street Address 2',
	'payments:braintree:card:address_city'=> 'City/Town',
	'payments:braintree:card:address_state'=> 'Region/State',
	'payments:braintree:card:address_zip'=> 'Postal Code',
	'payments:braintree:card:address_country'=> 'Country',

	'payments:braintree:validating' => 'Validating...',

	'payments:braintree:pay:paid' => 'Your payment was successfully received',
	'payments:braintree:pay:failed' => 'Payment has failed',
	'payments:braintree:pay:payment_pending' => 'The charge was successful and the payment is pending',
];