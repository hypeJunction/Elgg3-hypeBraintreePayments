<?php

namespace hypeJunction\Braintree;

use Elgg\Hook;

/**
 * Javascript config handler
 */
class SetJsData {

	/**
	 * Define braintree publishable key
	 *
	 * @param \Elgg\Hook $hook Hook info
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {
		$value = $hook->getValue();

		$svc = elgg()->braintree;
		/* @var $svc \hypeJunction\Braintree\BraintreeClient */

		$value['braintree_pk'] = $svc->public_key;

		return $value;
	}
}
