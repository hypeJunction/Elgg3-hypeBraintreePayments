<?php

namespace hypeJunction\Braintree;

use Braintree\Configuration;
use Braintree\Customer;
use Braintree\Exception\NotFound;
use Braintree\Gateway;
use Braintree\ResourceCollection;
use ElggUser;

/**
 * @property string  $environment
 * @property string  $merchant_id
 * @property string  $private_key
 * @property string  $public_key
 * @property Gateway $gateway
 */
class BraintreeClient {

	/**
	 * Configure the client
	 */
	public function setup() {

		$this->environment = elgg_get_plugin_setting('environment', 'hypePayments');

		switch ($this->environment) {
			default :
				$this->merchant_id = elgg_get_plugin_setting('sandbox_merchant_id', 'hypeBraintreePayments');
				$this->private_key = elgg_get_plugin_setting('sandbox_private_key', 'hypeBraintreePayments');
				$this->public_key = elgg_get_plugin_setting('sandbox_public_key', 'hypeBraintreePayments');
				break;

			case 'production' :
				$this->merchant_id = elgg_get_plugin_setting('production_merchant_id', 'hypeBraintreePayments');
				$this->private_key = elgg_get_plugin_setting('production_private_key', 'hypeBraintreePayments');
				$this->public_key = elgg_get_plugin_setting('production_public_key', 'hypeBraintreePayments');
				break;
		}

		$this->gateway = new Gateway([
			'environment' => $this->environment,
			'merchantId' => $this->merchant_id,
			'publicKey' => $this->public_key,
			'privateKey' => $this->private_key,
		]);

		Configuration::$global = $this->gateway->config;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get($name) {
		return $this->$name;
	}

	/**
	 * Create a new customer
	 *
	 * @param ElggUser $user   User
	 * @param array    $params Params
	 *
	 * @return Customer
	 */
	public function createCustomer(ElggUser $user = null, array $params = []) {

		if ($user && $user->braintree_id) {
			try {
				return $this->getCustomer($user->braintree_id);
			} catch (\Exception $ex) {

			}
		}

		if ($user) {
			list($first, $last) = explode(' ', $user->getDisplayName(), 2);
			$params['firstName'] = $first;
			$params['lastName'] = $last;
			$params['email'] = $user->email;
		}

		$result = $this->gateway->customer()->create($params);

		if ($result->success) {
			$user->braintree_id = $result->customer->id;

			return $result->customer;
		}

		return false;
	}

	/**
	 * Get a customer from id
	 *
	 * @param string $customer_id Customer ID
	 *
	 * @return Customer
	 * @throws NotFound
	 */
	public function getCustomer($customer_id) {
		return $this->gateway->customer()->find($customer_id, null);
	}

	/**
	 * Get all customers
	 *
	 * @return ResourceCollection
	 */
	public function getCustomers() {
		return $this->gateway->customer()->all();
	}
}