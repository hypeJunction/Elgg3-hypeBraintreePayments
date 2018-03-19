<?php

$brands = array('visa', 'mastercard', 'amex', 'jcb', 'discover	');

array_walk($brands, function(&$elem) {
	$elem = elgg_view('output/img', [
		'src' => elgg_get_simplecache_url("payments/icons/$elem.png"),
	]);
});

echo implode('&nbsp;', $brands);


