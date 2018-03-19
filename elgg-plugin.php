<?php

return [
	'actions' => [
		'payments/checkout/braintree' => [
			'controller' => \hypeJunction\Braintree\CheckoutAction::class,
			'access' => 'public',
		],
	],
	'routes' => [
		'payments:braintree:webhooks' => [
			'path' => '/payments/braintree/webhooks',
			'controller' => \hypeJunction\Braintree\DigestWebhook::class,
			'walled' => false,
		],
	],
];
