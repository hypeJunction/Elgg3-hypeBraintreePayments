define(function (require) {

	var elgg = require('elgg');
	var braintree = require('braintree');
	var Ajax = require('elgg/Ajax');
	var Form = require('ajax/Form');

	var api = {
		init: function (id) {
			var $elem = $(id);
			var container = $elem.find('.card-element')[0];

			var ajax = new Ajax(false);

			var config = $elem.data('config') || {};
			$.extend(config, {
				authorization: $elem.data('braintree'),
				container: container
			});

			braintree.create(config, function (error, dropinInstance) {
				if (error) {
					// Handle any errors that might've occurred when creating Drop-in
					$elem.find('.card-error').text(error.message);
					return;
				}

				var $form = $elem.closest('form');
				var form = new Form($form);

				form.onSubmit(function (resolve, reject) {
					if (!$form.has('[data-braintree]')) {
						return resolve();
					}

					$token = $form.find('[name="braintree_token"]');
					if ($token.val()) {
						return resolve();
					}

					dropinInstance.requestPaymentMethod(function (err, payload) {
						var $token = $form.find('[name="braintree_token"]');

						if (payload.nonce || !$token.data('required')) {
							$token.val(payload.nonce);
							return resolve();
						} else if (err) {
							$elem.find('.card-error').text(err.message);
							return reject(err.message);
						}
					});

					return false;
				});
			});
		}
	};

	return api;
});