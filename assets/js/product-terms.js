(function ($) {
	'use strict';

	let productAttributes = [];

	function productId() {
		return parseInt($('#wam-load-product-attributes').data('product-id') || '0', 10);
	}

	function meta() {
		return window.WAMProductTerms || {};
	}

	function render(attributes) {
		const $box = $('#wam-product-attributes-preview');
		$box.empty();

		if (!attributes.length) {
			$box.text(meta().i18n.noAttributes);
			return;
		}

		const $table = $('<table class="widefat striped"></table>');
		$table.append(
			'<thead><tr><th>ID</th><th>Name</th><th>Slug</th><th>Taxonomy</th><th>Available Terms</th></tr></thead>'
		);

		const $body = $('<tbody></tbody>');

		attributes.forEach(function (a) {
			const terms = Array.isArray(a.available_terms) ? a.available_terms : [];
			const termNames = terms.map(function (term) {
				return term.name;
			});

			$body.append(
				$('<tr></tr>')
					.append($('<td></td>').text(a.id))
					.append($('<td></td>').text(a.name))
					.append($('<td></td>').text(a.slug))
					.append($('<td></td>').text(a.taxonomy))
					.append($('<td></td>').text(termNames.length ? termNames.join(' | ') : '—'))
			);
		});

		$table.append($body);
		$box.append($table);

		const emptyTermAttributes = attributes.filter(function (attribute) {
			return !Array.isArray(attribute.available_terms) || attribute.available_terms.length === 0;
		});

		if (emptyTermAttributes.length) {
			const names = emptyTermAttributes.map(function (attribute) {
				return attribute.name;
			});

			$box.append(
				$('<p class="description"></p>').text(
					'No WooCommerce Terms were found for: ' + names.join(', ') +
					'. Check that these Attributes have Terms in Products > Attributes.'
				)
			);
		}

	}

	function getLiveTaxonomies() {
		const taxonomies = [];

		$('.product_attributes .woocommerce_attribute.taxonomy').each(function () {
			const $row = $(this);
			const taxonomy = String($row.data('taxonomy') || '').trim();

			if (taxonomy && !taxonomies.includes(taxonomy)) {
				taxonomies.push(taxonomy);
			}
		});

		return taxonomies;
	}

	function load() {
		const id = productId();
		const cfg = meta();
		const taxonomies = getLiveTaxonomies();

		if (!id) return;

		$.ajax({
			url: cfg.ajaxUrl,
			method: 'POST',
			data: {
				action: 'wam_load_product_attributes',
				nonce: cfg.nonceApply,
				product_id: id,
				taxonomies: taxonomies
			}
		}).done(function (response) {
			if (!response.success) {
				window.alert((response.data && response.data.message) || 'Could not load the selected product attributes.');
				return;
			}

			const data = response.data || {};
			productAttributes = Array.isArray(data.attributes) ? data.attributes : [];
			window.WAMSelectedProductAttributes = productAttributes;
			render(productAttributes);

			if (!productAttributes.length) {
				window.alert(
					'No WooCommerce global Attributes were found in the current product editor. ' +
					'Add the Attributes first, then click Load Selected Attributes again.'
				);
			}
		}).fail(function (xhr) {
			console.error('WAM product attributes:', xhr.responseText);
			window.alert('Could not load the selected product attributes.');
		});
	}

	function generatePrompt() {
		const cfg = meta();
		const context = $('#wam-product-ai-context').val().trim();

		if (!productAttributes.length) {
			window.alert(cfg.i18n.loadFirst);
			return;
		}

		let output = '';
		output += 'You are a WooCommerce product data specialist responsible for selecting exact existing WooCommerce term values for a specific product.\n\n';
		output += 'PRODUCT INFORMATION\n';
		output += context + '\n\n';
		output += 'TASK\n';
		output += 'Analyze the product information and select the exact WooCommerce term values that apply to this product.\n';
		output += 'You MUST select values only from the available_terms list for each attribute.\n';
		output += 'Do not invent terms, attributes, IDs, slugs, or specifications.\n';
		output += 'If an attribute cannot be determined with reasonable certainty, omit that attribute.\n\n';
		output += 'SELECTED PRODUCT ATTRIBUTES AND AVAILABLE WOOCOMMERCE TERMS\n\n';

		productAttributes.forEach(function (a) {
			const terms = Array.isArray(a.available_terms) ? a.available_terms : [];
			const termNames = terms.map(function (term) {
				return term.name;
			});

			output += '[attribute]\n';
			output += 'id: ' + a.id + '\n';
			output += 'name: ' + a.name + '\n';
			output += 'slug: ' + a.slug + '\n';
			output += 'taxonomy: ' + a.taxonomy + '\n';
			output += 'available_terms: ' + termNames.join(' | ') + '\n\n';
		});

		output += 'OUTPUT RULES\n';
		output += '1. Return only Attributes listed above.\n';
		output += '2. Keep id, name and slug exactly as provided.\n';
		output += '3. values MUST contain only exact term names from available_terms.\n';
		output += '4. Never create a new term.\n';
		output += '5. Never translate a term.\n';
		output += '6. Never guess an unspecified specification.\n';
		output += '7. Multiple selected terms must be separated with |.\n';
		output += '8. Do not return empty values.\n';
		output += '9. Do not add explanations outside [attribute] blocks.\n\n';
		output += 'OUTPUT FORMAT\n\n';
		output += '[attribute]\n';
		output += 'id: ATTRIBUTE_ID\n';
		output += 'name: ATTRIBUTE_NAME\n';
		output += 'slug: ATTRIBUTE_SLUG\n';
		output += 'values: TERM';

		$('#wam-product-terms-input').val(output);
		window.alert(cfg.i18n.promptReady);
	}

	function syncAppliedTermsToWooCommerceUI(applied) {
		if (!Array.isArray(applied)) {
			return;
		}

		applied.forEach(function (item) {
			const taxonomy = 'pa_' + String(item.slug || '').replace(/^pa_/, '');
			const termIds = Array.isArray(item.term_ids)
				? item.term_ids.map(function (id) { return String(id); })
				: [];

			$('.product_attributes .woocommerce_attribute.taxonomy').each(function () {
				const $row = $(this);

				if (String($row.data('taxonomy') || '') !== taxonomy) {
					return;
				}

				const $select = $row.find('select.attribute_values');

				if (!$select.length) {
					return;
				}

				// WooCommerce uses term IDs as the option values in its taxonomy
				// Attribute selector. Select exactly the Terms returned by the server.
				const availableOptionValues = $select.find('option').map(function () {
					return String($(this).val());
				}).get();

				const validTermIds = termIds.filter(function (id) {
					return availableOptionValues.includes(id);
				});

				/*
				 * WooCommerce may not have populated the Select2 option list
				 * for a dynamically added taxonomy row yet. Add the exact
				 * Terms we already loaded from WooCommerce, then select them.
				 */
				if (validTermIds.length !== termIds.length) {
					const source = productAttributes.find(function (attribute) {
						return String(attribute.taxonomy || '') === taxonomy;
					});

					if (source && Array.isArray(source.available_terms)) {
						source.available_terms.forEach(function (term) {
							const termId = String(term.id);

							if (termIds.includes(termId) && !$select.find('option[value="' + CSS.escape(termId) + '"]').length) {
								$select.append(
									$('<option></option>')
										.attr('value', termId)
										.text(term.name)
										.prop('selected', true)
								);
							}
						});
					}
				}

				$select.val(termIds).trigger('change');

				// Ensure Select2, when initialized, reflects the new selection.
				if ($select.hasClass('select2-hidden-accessible')) {
					$select.trigger('change.select2');
				}

				$row.addClass('wam-terms-applied');
			});
		});

		// Tell WooCommerce's editor that the Attribute controls changed.
		$(document.body).trigger('woocommerce_attributes_saved');
	}

	function apply() {
		const cfg = meta();
		const input = $('#wam-product-terms-input').val().trim();
		const id = productId();

		if (!input) {
			window.alert(cfg.i18n.loadFirst);
			return;
		}

		const $button = $('#wam-apply-product-terms');
		$button.prop('disabled', true);

		$.ajax({
			url: cfg.ajaxUrl,
			method: 'POST',
			data: {
				action: 'wam_apply_product_terms',
				nonce: cfg.nonceApply,
				product_id: id,
				input: input,
				taxonomies: productAttributes.map(function (attribute) {
					return attribute.taxonomy;
				})
			}
		}).done(function (response) {
			if (!response.success) {
				window.alert((response.data && response.data.message) || cfg.i18n.failed);
				return;
			}

			const data = response.data || {};
			const applied = data.applied || [];

			// The server has persisted the Terms, but the current WooCommerce
			// editor DOM still contains the old empty selectors. Sync the
			// persisted Term IDs into the visible Attribute rows immediately.
			syncAppliedTermsToWooCommerceUI(applied);

			const notPersisted = applied.filter(function (item) {
				return item.persisted === false;
			});

			let message = cfg.i18n.applied + '\n\nApplied: ' + applied.length;

			if (notPersisted.length) {
				message += '\nNot persisted: ' + notPersisted.length;
				console.error('WAM terms were applied but not persisted:', notPersisted);
			}

			if ((data.skipped || []).length) {
				message += '\nSkipped: ' + data.skipped.length;
			}

			if ((data.errors || []).length) {
				message += '\nErrors: ' + data.errors.length;
			}

			window.alert(message);
		}).fail(function (xhr) {
			console.error('WAM apply product terms:', xhr.responseText);
			window.alert(cfg.i18n.failed);
		}).always(function () {
			$button.prop('disabled', false);
		});
	}

	$(document).on('click', '#wam-load-product-attributes', function (e) {
		e.preventDefault();
		load();
	});

	$(document).on('click', '#wam-generate-terms-prompt', function (e) {
		e.preventDefault();
		generatePrompt();
	});

	$(document).on('click', '#wam-apply-product-terms', function (e) {
		e.preventDefault();
		apply();
	});
})(jQuery);
