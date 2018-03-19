hypeBraintreePayments
==================

A wrapper for Braintree's PHP SDK

## Webhooks

Configure your Braintree application to send webhooks to ```https://<your-elgg-site>/payments/braintree/webhooks```

To digest a webhook, register a plugin hook handler:

```php
elgg_register_plugin_hook_handler('subscription_went_past_due', 'braintree', HandleExpiredSubscription::class);

class HandleExpiredSubscription {
	public function __invoke(\Elgg\Hook $hook) {
		$webhook = $hook->getParam('webhook');
		/* @var $webhook \Briantree\WebhookNotification */
		
		// ... do stuff
		
		return $result; // data to send back to braintree
	}
}

```

## Card Input

To display a card input:

```php
// Card number, expiry and CVC
echo elgg_view_field([
	'#type' => 'braintree/card',
	'#label' => 'Credit or Debit Card',
	'required' => true,
]);
```

You can then retrieve the value of the Braintree token in your action:

```php
$token = get_input('braintree_token'); // Corresponds to payment_method_nonce

elgg()->{'payments.gateways.braintree'}->pay($transaction, [
	'braintree_token' => $token,
]);
```