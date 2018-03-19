<?php

require_once __DIR__ . '/autoloader.php';

return function () {

	elgg_register_event_handler('init', 'system', function () {

		elgg()->payments->registerGateway(elgg()->{'payments.gateways.braintree'});

		elgg_register_plugin_hook_handler('elgg.data', 'page', \hypeJunction\Braintree\SetJsData::class);

		elgg_define_js('braintree', [
			'src' => 'https://js.braintreegateway.com/web/dropin/1.9.4/js/dropin.min.js',
			'exports' => 'window.braintree',
		]);

		elgg_extend_view('elgg.css', 'input/braintree/card.css');

		elgg_register_ajax_view('payments/method/braintree/form');

		elgg_register_plugin_hook_handler('refund', 'payments', \hypeJunction\Braintree\RefundTransaction::class);

		elgg_register_plugin_hook_handler('charge.failed', 'braintree', \hypeJunction\Braintree\DigestChargeWebhook::class);
		elgg_register_plugin_hook_handler('charge.pending', 'braintree', \hypeJunction\Braintree\DigestChargeWebhook::class);
		elgg_register_plugin_hook_handler('charge.refunded', 'braintree', \hypeJunction\Braintree\DigestChargeWebhook::class);
		elgg_register_plugin_hook_handler('charge.succeeded', 'braintree', \hypeJunction\Braintree\DigestChargeWebhook::class);
		elgg_register_plugin_hook_handler('charge.updated', 'braintree', \hypeJunction\Braintree\DigestChargeWebhook::class);

		elgg_register_plugin_hook_handler('register', 'menu:page', \hypeJunction\Braintree\PageMenu::class);
	});

};
