<?php

namespace hypeJunction\Braintree;

use Elgg\Hook;

class PageMenu {

	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();

		$menu[] = \ElggMenuItem::factory([
			'name' => 'payments:braintree:settings',
			'parent_name' => 'payments',
			'href' => 'admin/plugin_settings/hypeBraintreePayments',
			'text' => elgg_echo('payments:braintree:settings'),
			'icon' => 'cog',
			'context' => ['admin'],
			'section' => 'configure',
		]);

		$menu[] = \ElggMenuItem::factory([
			'name' => 'payments:braintree:transactions',
			'parent_name' => 'payments',
			'href' => 'admin/payments/braintree',
			'text' => elgg_echo('payments:braintree:transactions'),
			'icon' => 'exchange',
			'context' => ['admin'],
			'section' => 'configure',
		]);

		return $menu;
	}
}