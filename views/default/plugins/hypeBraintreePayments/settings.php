<?php

$entity = elgg_extract('entity', $vars);

$link = elgg_view('output/url', [
	'href' => elgg_generate_url('payments:braintree:webhooks'),
]);

$message = elgg_echo('payments:braintree:settings:webhooks', [$link]);
echo elgg_view_message('notice', $message, [
	'title' => false,
]);

$fields = [
	'sandbox_merchant_id' => 'text',
	'sandbox_private_key' => 'text',
	'sandbox_public_key' => 'text',
	'production_merchant_id' => 'text',
	'production_private_key' => 'text',
	'production_public_key' => 'text',
];

foreach ($fields as $name => $options) {
	if (is_string($options)) {
		$options = [
			'#type' => $options,
		];
	}

	$options['name'] = "params[$name]";
	$options['value'] = $entity->$name;
	$options['#label'] = elgg_echo("braintree:setting:$name");

	echo elgg_view_field($options);
}