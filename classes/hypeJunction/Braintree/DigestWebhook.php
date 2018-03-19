<?php

namespace hypeJunction\Braintree;

use Braintree\Exception\InvalidSignature;
use Braintree\WebhookNotification;
use Elgg\BadRequestException;
use Elgg\Http\ResponseBuilder;
use Elgg\HttpException;
use Elgg\Request;

class DigestWebhook {

	/**
	 * Digest braintree webhook
	 *
	 * @param Request $request Request
	 *
	 * @return ResponseBuilder
	 * @throws BadRequestException
	 * @throws HttpException
	 */
	public function __invoke(Request $request) {

		elgg_set_viewtype('json');

		elgg_set_http_header('Content-Type: application/json');

		$payload = _elgg_services()->request->getContent();

		$braintree = elgg()->braintree;
		/* @var $braintree BraintreeClient */

		try {
			try {
				$webhook = $braintree->gateway->webhookNotification()->parse(
					$request->getParam('bt_signature'),
					$request->getParam('bt_payload')
				);

				/* @var $webhook WebhookNotification */
			} catch (InvalidSignature $ex) {
				throw new BadRequestException($ex->getMessage());
			}

			if (empty($payload)) {
				throw new BadRequestException('Payload is empty');
			}

			$result = elgg_trigger_plugin_hook($webhook->kind, 'braintree', ['webhook' => $webhook]);

			if ($result === false) {
				throw new HttpException('Event was not digested because one of the handlers refused to process data', ELGG_HTTP_INTERNAL_SERVER_ERROR);
			}
		} catch (\Elgg\HttpException $exception) {
			return elgg_ok_response(['error' => $exception->getMessage()], '', null, $exception->getCode());
		}

		return elgg_ok_response(['result' => $result]);
	}
}