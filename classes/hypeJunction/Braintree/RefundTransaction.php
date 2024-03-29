<?php

namespace hypeJunction\Braintree;

use Elgg\Hook;
use hypeJunction\Payments\Transaction;

class RefundTransaction {

	/**
	 * Initiate a refund
	 *
	 * @param Hook $hook Hook
	 * @return bool|null
	 */
	public function __invoke(Hook $hook) {
		if ($hook->getValue()) {
			// Transaction already refunded
			return null;
		}

		$transaction = $hook->getEntityParam();
		if (!$transaction instanceof Transaction) {
			return null;
		}

		if ($transaction->payment_method == 'braintree') {
			$adapter = elgg()->{'payments.gateways.braintree'};
			/* @var $adapter \hypeJunction\Braintree\BraintreeGateway */

			return $adapter->refund($transaction);
		}
	}
}