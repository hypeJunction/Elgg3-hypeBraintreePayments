define(function (require) {

	var elgg = require('elgg');
	var braintree = require('braintree');
	var Ajax = require('elgg/Ajax');
	var spinner = require('elgg/spinner');


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

				$form.uniqueId();

				$(document).on('submit', '#' + $form.attr('id'), function (e) {
					if (!$(this).has('[data-braintree]')) {
						return;
					}

					e.preventDefault();

					$('[type="submit"]', $form).prop('disabled', true);

					spinner.start(elgg.echo('payments:braintree:card:processing'));

					dropinInstance.requestPaymentMethod(function (err, payload) {

						var $token = $form.find('[name="braintree_token"]');

						if (payload.nonce || !$token.data('required')) {
							$token.val(payload.nonce);
							$form.get(0).submit();
						} else if (err) {
							$elem.find('.card-error').text(err.message);
						}

						spinner.stop();
					});

					return false;
				});
			});
		}
	};

	return api;
});