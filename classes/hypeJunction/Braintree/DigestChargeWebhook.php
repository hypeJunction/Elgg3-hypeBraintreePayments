<?php

namespace hypeJunction\Braintree;

use Elgg\Hook;
use hypeJunction\Payments\Transaction;

class DigestChargeWebhook {

	/**
	 * Digest charge webhook and update transaction status
	 *
	 * @param Hook $hook Hook
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function __invoke(Hook $hook) {

		elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DISABLED_ENTITIES, function () use ($hook) {
			$event = $hook->getParam('event');
			/* @var $event \Braintree\Event */

			$charge = $event->data->object;

			$transactions = elgg_get_entities([
				'types' => 'object',
				'subtypes' => Transaction::SUBTYPE,
				'metadata_name_value_pairs' => [
					'name' => 'braintree_charge_id',
					'value' => $charge->id,
				],
				'limit' => 0,
			]);

			if (!$transactions) {
				return;
			}

			/* @var $transactions \hypeJunction\Payments\Transaction[] */

			$adapter = elgg()->{'payments.gateways.braintree'};
			/* @var $adapter \hypeJunction\Braintree\BraintreeGateway */

			foreach ($transactions as $transaction) {
				$adapter->updateTransactionStatus($transaction);
			}

			return $transactions;
		});
	}
}