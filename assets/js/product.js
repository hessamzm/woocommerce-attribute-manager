(function ($) {
	'use strict';

	const wam = window.WAMProduct || {};

	function getMeta() {
		if (typeof window.woocommerce_admin_meta_boxes === 'undefined') {
			throw new Error('WooCommerce admin meta boxes data is unavailable.');
		}
		return window.woocommerce_admin_meta_boxes;
	}

	function selectedTaxonomies() {
		const result = [];
		$('.product_attributes .woocommerce_attribute').each(function () {
			const $row = $(this);
			if ($row.css('display') !== 'none' && $row.hasClass('taxonomy')) {
				const taxonomy = $row.data('taxonomy');
				if (taxonomy && !result.includes(taxonomy)) {
					result.push(taxonomy);
				}
			}
		});
		return result;
	}

	function syncAttributeSearch() {
		const selected = selectedTaxonomies();
		$('select.wc-attribute-search').data('disabled-items', selected);
		$('select.wc-attribute-search option').each(function () {
			const value = $(this).val();
			if (value && selected.includes(value)) {
				$(this).prop('disabled', true);
			} else {
				$(this).prop('disabled', false);
			}
		});
		$('select.wc-attribute-search').trigger('change.select2');
	}

	function initializeAttributeRow($row) {
		// WooCommerce initializes the dynamically injected attribute row and
		// its term selector. We intentionally do NOT select any terms here:
		// the product editor user must choose the values for this specific product.
		$(document.body).trigger('wc-enhanced-select-init');

		$row.find('select.attribute_values').val(null).trigger('change');
		$row.find('input[name$="[is_variation]"]').prop('checked', false);
	}

	function mergeExistingAttribute($row) {
		// Do not alter the selected terms of an existing product attribute.
		// A group only defines which attribute rows are available.
		$row.find('select.attribute_values').trigger('change');
	}

	function getNewAttributeRow(index, taxonomy) {
		return new Promise(function (resolve, reject) {
			const meta = getMeta();

			$.post({
				url: meta.ajax_url,
				data: {
					action: 'woocommerce_add_attribute',
					product_type: $('#product-type').val(),
					taxonomy: taxonomy || '',
					i: index,
					security: meta.add_attribute_nonce
				},
				success: resolve,
				error: function (jqXHR, textStatus, errorThrown) {
					reject({ jqXHR, textStatus, errorThrown });
				}
			});
		});
	}

	async function addAttribute(attribute) {
		const taxonomy = attribute.taxonomy;
		if (!taxonomy) return;

		const existingTaxonomies = selectedTaxonomies();

		if (existingTaxonomies.includes(taxonomy)) {
			$('.product_attributes .woocommerce_attribute').each(function () {
				const $row = $(this);
				if ($row.data('taxonomy') === taxonomy) {
					mergeExistingAttribute($row);
				}
			});
			return;
		}

		const index = $('.product_attributes .woocommerce_attribute').length;
		const html = await getNewAttributeRow(index, taxonomy);
		const $row = $(html);

		if (!$row.length || !$row.hasClass('woocommerce_attribute')) {
			throw new Error('WooCommerce returned an invalid attribute row.');
		}

		$('#product_attributes .product_attributes').append($row);

		// Native WooCommerce lifecycle for dynamically added controls.
		$(document.body).trigger('wc-enhanced-select-init');

		$('.product_attributes .woocommerce_attribute').each(function (i) {
			$(this).find('.attribute_position').val(i);
		});

		initializeAttributeRow($row);
		$row.find('h3').trigger('click');

		$(document.body).trigger('woocommerce_added_attribute');

		if (typeof window.jQuery.maybe_disable_save_button === 'function') {
			window.jQuery.maybe_disable_save_button();
		}

		syncAttributeSearch();
	}

	function encodeGroupKey(value) {
		value = String(value || '');
		try {
			value = decodeURIComponent(value);
		} catch (error) {
			// Keep the original value if it is not percent-encoded.
		}
		return encodeURIComponent(value);
	}

	async function addGroup() {
		const group = $('#wam_attribute_group').val();
		if (!group) return;

		const $button = $('#wam-add-attribute-group');

		try {
			$('#product_attributes').block({
				message: null,
				overlayCSS: { background: '#fff', opacity: 0.6 }
			});

			$button.prop('disabled', true);

			const response = await $.ajax({
				url: String(wam.restUrl) + 'groups/' + encodeGroupKey(group),
				method: 'GET',
				beforeSend: function (xhr) {
					xhr.setRequestHeader('X-WP-Nonce', wam.nonce);
				}
			});

			if (!response || !Array.isArray(response.attributes)) {
				throw new Error('Invalid attribute group response.');
			}

			// Remove only WooCommerce's empty temporary custom row.
			$('.product_attributes .woocommerce_attribute').each(function () {
				const $row = $(this);
				if (!$row.hasClass('taxonomy')) {
					const name = $row.find('input.attribute_name').val() || '';
					const value = $row.find('textarea[name^="attribute_values"]').val() || '';
					if (!name && !value) $row.remove();
				}
			});

			for (const attribute of response.attributes) {
				await addAttribute(attribute);
			}

			$('.product_attributes .woocommerce_attribute').each(function (i) {
				$(this).find('.attribute_position').val(i);
			});

			syncAttributeSearch();
		} catch (error) {
			console.error('WooCommerce Attribute Manager:', error);
			window.alert(
				(wam.i18n && wam.i18n.error)
					? wam.i18n.error
					: 'Could not add the selected attribute group.'
			);
		} finally {
			$('#product_attributes').unblock();
			$button.prop('disabled', false);
		}
	}

	$(document).on('click', '#wam-add-attribute-group', function (event) {
		event.preventDefault();
		addGroup();
		return false;
	});

	$(document.body).on(
		'woocommerce_added_attribute woocommerce_attributes_saved',
		syncAttributeSearch
	);

	$(document).on('click', '#product_attributes .remove_row', function () {
		window.setTimeout(syncAttributeSearch, 0);
	});
})(jQuery);
