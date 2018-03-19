<?php

$id = "card-input-" . base_convert(mt_rand(), 10, 36);

$card = elgg_format_element('div', [
	'class' => 'card-element',
]);

$errors = elgg_format_element('div', [
	'class' => 'card-errors hidden',
]);

$hidden = elgg_view_field([
	'#type' => 'hidden',
	'name' => 'braintree_token',
	'data-required' => elgg_extract('required', $vars, false),
]);

$braintree = elgg()->braintree;
/* @var $braintree \hypeJunction\Braintree\BraintreeClient */
$token = $braintree->gateway->clientToken()->generate();

$attrs = [
	'id' => $id,
	'data-braintree' => $token,
];

$config = elgg_extract('config', $vars);
$attrs['data-config'] = json_encode($config);

echo elgg_format_element('div', $attrs, $card . $errors . $hidden);
?>

<script>
	require(['input/braintree/card'], function (card) {
		card.init('#<?= $id ?>');
	});
</script>