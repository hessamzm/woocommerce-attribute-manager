(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const attributeSelectAll = document.getElementById('wam-select-all-attributes');
		const attributeChecks = Array.from(document.querySelectorAll('.wam-attribute-checkbox'));
		const attributeForm = document.getElementById('wam-bulk-delete-form');

		if (attributeSelectAll) {
			attributeSelectAll.addEventListener('change', function () {
				attributeChecks.forEach(function (checkbox) {
					checkbox.checked = attributeSelectAll.checked;
				});
			});
		}

		if (attributeForm) {
			attributeForm.addEventListener('submit', function (event) {
				const selected = attributeChecks.filter(function (checkbox) {
					return checkbox.checked;
				});

				if (!selected.length) {
					event.preventDefault();
					window.alert('Please select at least one attribute.');
					return;
				}

				const message = window.WAMAdmin && WAMAdmin.deleteConfirm
					? WAMAdmin.deleteConfirm
					: 'Are you sure you want to permanently delete the selected attributes and their terms?';

				if (!window.confirm(message)) {
					event.preventDefault();
				}
			});
		}

		const groupSelectAll = document.getElementById('wam-select-all-groups');
		const groupChecks = Array.from(document.querySelectorAll('.wam-group-checkbox'));
		const groupForm = document.getElementById('wam-bulk-delete-groups');
		const singleForm = document.getElementById('wam-single-delete-group-form');
		const singleKey = document.getElementById('wam-single-group-key');

		if (groupSelectAll) {
			groupSelectAll.addEventListener('change', function () {
				groupChecks.forEach(function (checkbox) {
					checkbox.checked = groupSelectAll.checked;
				});
			});
		}

		document.querySelectorAll('.wam-delete-single-group').forEach(function (button) {
			button.addEventListener('click', function (event) {
				event.preventDefault();

				const name = button.getAttribute('data-group-name') || '';

				const message = 'Delete the attribute group "' + name + '"? The WooCommerce attributes and terms will not be deleted.';

				if (!window.confirm(message)) {
					return;
				}

				if (singleForm && singleKey) {
					singleKey.value = button.value;
					singleForm.submit();
				}
			});
		});

		if (groupForm) {
			groupForm.addEventListener('submit', function (event) {
				const selected = groupChecks.filter(function (checkbox) {
					return checkbox.checked;
				});

				if (!selected.length) {
					event.preventDefault();
					window.alert('Please select at least one group.');
					return;
				}

				if (!window.confirm('Delete the selected attribute groups? The WooCommerce attributes and terms will not be deleted.')) {
					event.preventDefault();
				}
			});
		}

		const copy = document.getElementById('wam-copy-ai-prompt');
		const prompt = document.getElementById('wam-ai-template');

		if (copy && prompt) {
			copy.addEventListener('click', function () {
				navigator.clipboard.writeText(prompt.value).then(function () {
					copy.textContent = 'Copied';
					setTimeout(function () {
						copy.textContent = 'Copy AI Prompt';
					}, 1500);
				});
			});
		}
	});
})();
