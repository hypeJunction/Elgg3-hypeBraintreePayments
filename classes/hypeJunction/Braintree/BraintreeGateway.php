<?php

namespace hypeJunction\Braintree;

use Braintree\Error\Base;
use Braintree\Error\Card;
use Braintree\Exception;
use Braintree\MerchantAccount;
use hypeJunction\Payments\Amount;
use hypeJunction\Payments\CreditCard;
use hypeJunction\Payments\GatewayInterface;
use hypeJunction\Payments\Payment;
use hypeJunction\Payments\Refund;
use hypeJunction\Payments\Transaction;
use hypeJunction\Payments\TransactionInterface;

class BraintreeGateway implements GatewayInterface {

	/**
	 * @var BraintreeClient
	 */
	protected $client;

	/**
	 * Constructor
	 *
	 * @param BraintreeClient $client Client
	 */
	public function __construct(BraintreeClient $client) {
		$this->client = $client;
	}

	/**
	 * {@inheritdoc}
	 */
	public function id() {
		return 'braintree';
	}

	/**
	 * {@inheritdoc}
	 */
	public function pay(TransactionInterface $transaction, array $params = []) {

		$transaction->setPaymentMethod('braintree');

		$source = elgg_extract('braintree_token', $params);

		if (!$source) {
			$transaction->setStatus(TransactionInterface::STATUS_FAILED);
			$error = elgg_echo('payments:braintree:no_source');

			return elgg_error_response($error);
		}

		$merchant = $transaction->getMerchant();
		$customer = $transaction->getCustomer();

		$order = $transaction->getOrder();
		$address = null;
		if ($order) {
			$shipping = $order->getShippingAddress();
			if ($shipping) {
				$address = [
					'locality' => $shipping->locality,
					'countryCodeAlpha2' => $shipping->country_code,
					'streetAddress' => $shipping->street_address,
					'extendedAddress' => $shipping->extended_address,
					'postalCode' => $shipping->postal_code,
					'region' => $shipping->region,
				];
			}
		}

		$amount = $transaction->getAmount();

		try {
			$braintree_customer = $this->client->createCustomer($customer);

			$merchant_account_id = null;
			$braintree_merchants = $this->client->gateway->merchantAccount()->all();
			foreach ($braintree_merchants as $braintree_merchant) {
				/* @var $braintree_merchant MerchantAccount */
				if (strtoupper($braintree_merchant->currencyIsoCode) == strtoupper($transaction->getAmount()->getCurrency())) {
					$merchant_account_id = $braintree_merchant->id;
				}
			}

			$charge_params = [
				'customerId' => $braintree_customer->id,
				'amount' => $amount->getConvertedAmount(),
				//'currencyIsoCode' => $amount->getCurrency(),
				'paymentMethodNonce' => $source,
				'merchantAccountId' => $merchant_account_id,
			];

			if ($address) {
				list($first, $last) = explode(' ', $customer->getDisplayName(), 2);
				$address['firstName'] = $first;
				$address['lastName'] = $last;

				$charge_params['shipping'] = $address;
			}

			$result = $this->client->gateway->transaction()->sale($charge_params);

			if ($result->success) {
				$this->client->gateway->transaction()->submitForSettlement($result->transaction->id);

				if ($this->client->environment === 'sandbox') {
					$this->client->gateway->testing()->settle($result->transaction->id);
				}

				$transaction->braintree_charge_id = $result->transaction->id;
			} else {
				$error = elgg_echo($result->message);

				return elgg_error_response($error);
			}

			$source = (object) $result->transaction->creditCard;

			$brands = [
				'Visa' => 'visa',
				'MasterCard' => 'mastercard',
				'American Express' => 'amex',
				'JCB' => 'jcb',
				'Diners Club' => 'diners',
				'Discover' => 'discover',
			];

			$cc = new CreditCard();
			$cc->last4 = $source->last4;
			$cc->brand = elgg_extract($source->cardType, $brands, $source->cardType);
			$cc->exp_month = $source->expirationMonth;
			$cc->exp_year = $source->expirationYear;

			$transaction->setFundingSource($cc);

			$this->updateTransactionStatus($transaction);

			$data = [
				'entity' => $transaction,
				'action' => 'pay',
			];

			$message = elgg_echo("payments:braintree:pay:{$transaction->getStatus()}");

			return elgg_ok_response($data, $message);
		} catch (Exception $ex) {
			$transaction->setStatus(TransactionInterface::STATUS_FAILED);

			$error = elgg_echo("payments:braintree:card_error:{$ex->getCode()}");

			return elgg_error_response($error);
		}
	}

	/**
	 * Update transaction status
	 *
	 * @param TransactionInterface $transaction Transaction
	 *
	 * @return TransactionInterface
	 */
	public function updateTransactionStatus(TransactionInterface $transaction) {

		if (!$transaction->braintree_charge_id) {
			return $transaction;
		}

		try {
			$charge = $this->client->gateway->transaction()->find($transaction->braintree_charge_id);
		} catch (Exception $ex) {
			return $transaction;
		}

		switch ($charge->status) {
			case 'authorized' :
			case 'authorizing' :
			case 'settlement_pending' :
			case 'submitted_for_settlement' :
			case 'settling' :
				$transaction->setStatus(TransactionInterface::STATUS_PAYMENT_PENDING);
				break;

			case 'settled' :
				if (!in_array($transaction->status, [
					TransactionInterface::STATUS_PAID,
					TransactionInterface::STATUS_REFUNDED,
					TransactionInterface::STATUS_PARTIALLY_REFUNDED,
					TransactionInterface::STATUS_REFUND_PENDING
				])) {

					$payment = new Payment();
					$payment->setTimeCreated((int) $charge->createdAt->getTimestamp())
						->setAmount(Amount::fromString($charge->amount, strtoupper($charge->currencyIsoCode)))
						->setPaymentMethod('braintree')
						->setDescription(elgg_echo('payments:payment'));
					$payment->braintree_payment_id = $charge->id;
					$transaction->addPayment($payment);
					$transaction->setStatus(TransactionInterface::STATUS_PAID);

					/* @todo Figure out where to find processor fee amount */
					//$transaction->setProcessorFee(new Amount($braintree_balance_transaction->fee, $braintree_balance_transaction->currency));
				}

				break;

			case 'authorization_expired' :
			case 'settlement_declined' :
			case 'failed' :
			case 'gateway_rejected' :
			case 'processor_declined' :
			case 'voided' :
				$transaction->setStatus(TransactionInterface::STATUS_FAILED);
				break;

		}

		return $transaction;

	}

	/**
	 * {@inheritdoc}
	 */
	public function refund(TransactionInterface $transaction) {
		if (!$transaction->braintree_charge_id) {
			return $transaction;
		}

		try {
			$result = $this->client->gateway->transaction()->refund($transaction->braintree_charge_id);

			if ($result->success) {

				$braintree_refund = $this->client->gateway->transaction()->find($result->transaction->refundedTransactionId);

				$refund = new Refund();
				$refund->setTimeCreated($braintree_refund->createdAt->getTimestamp())
					->setAmount(Amount::fromString('-' . $braintree_refund->amount, strtoupper($braintree_refund->currencyIsoCode)))
					->setPaymentMethod('braintree')
					->setDescription(elgg_echo('payments:refund'));
				$refund->braintree_refund_id = $braintree_refund->id;
				$transaction->addPayment($refund);


				$transaction->setStatus(TransactionInterface::STATUS_REFUNDED);

				return true;
			} else {
				return false;
			}

		} catch (\Exception $ex) {
			elgg_log($ex->getMessage(), 'ERROR');

			return false;
		}
	}

}
