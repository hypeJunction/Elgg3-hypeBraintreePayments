<?php

return [
	'braintree' => \DI\object(\hypeJunction\Braintree\BraintreeClient::class)
		->method('setup'),

	'payments.gateways.braintree' => \DI\object(\hypeJunction\Braintree\BraintreeGateway::class)
		->constructor(\DI\get('braintree')),

];
