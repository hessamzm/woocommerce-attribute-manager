(function ($) {
	'use strict';

	const wam = window.WAMProduct || {};

	function addAttributeRow(attribute) {
		const $attributes = $('#product_attributes');

		if (!$attributes.length) {
			return;
		}

		const alreadyExists = $attributes.find('.woocommerce_attribute').filter(function () {
			return $(this).find('select.attribute_name option:selected').val() === attribute.taxonomy;
		}).length > 0;

		if (alreadyExists) {
			return;
		}

		const $addButton = $attributes.find('.add_attribute').first();

		if (!$addButton.length) {
			return;
		}

		$addButton.trigger('click');

		window.setTimeout(function () {
			const $row = $attributes.find('.woocommerce_attribute').last();

			if (!$row.length) {
				return;
			}

			const $select = $row.find('select.attribute_name').first();

			if (!$select.length) {
				return;
			}

			$select.val(attribute.taxonomy).trigger('change');

			window.setTimeout(function () {
				const $values = $row.find('select.attribute_values').first();

				if (!$values.length) {
					return;
				}

				const termValues = attribute.options.map(function (option) {
					return String(option.id);
				});

				$values.val(termValues).trigger('change');
			}, 150);
		}, 100);
	}

	function loadGroup() {
		const group = $('#wam_attribute_group').val();

		if (!group) {
			return;
		}

		const $button = $('#wam-add-attribute-group');
		$button.prop('disabled', true).text(wam.i18n ? wam.i18n.loading : 'Loading...');

		$.ajax({
			url: String(wam.restUrl) + 'groups/' + encodeURIComponent(group),
			method: 'GET',
			beforeSend: function (xhr) {
				xhr.setRequestHeader('X-WP-Nonce', wam.nonce);
			}
		}).done(function (response) {
			if (!response || !Array.isArray(response.attributes)) {
				return;
			}

			response.attributes.forEach(addAttributeRow);
		}).fail(function () {
			window.alert(wam.i18n ? wam.i18n.error : 'Could not load the selected group.');
		}).always(function () {
			$button.prop('disabled', false).text('Add Group');
		});
	}

	$(document).on('click', '#wam-add-attribute-group', loadGroup);

})(jQuery);
