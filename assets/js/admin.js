(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		const selectAll = document.getElementById('wam-select-all-attributes');
		const checks = Array.from(document.querySelectorAll('.wam-attribute-checkbox'));
		const form = document.getElementById('wam-bulk-delete-form');
		const copy = document.getElementById('wam-copy-ai-prompt');
		const prompt = document.getElementById('wam-ai-template');

		if (selectAll) {
			selectAll.addEventListener('change', function () {
				checks.forEach(function (c) { c.checked = selectAll.checked; });
			});
		}

		if (form) {
			form.addEventListener('submit', function (e) {
				const selected = checks.filter(function (c) { return c.checked; });
				if (!selected.length) {
					e.preventDefault();
					window.alert('Please select at least one attribute.');
					return;
				}
				const msg = window.WAMAdmin ? WAMAdmin.deleteConfirm : 'Confirm permanent deletion?';
				if (!window.confirm(msg)) e.preventDefault();
			});
		}

		if (copy && prompt) {
			copy.addEventListener('click', function () {
				navigator.clipboard.writeText(prompt.value).then(function () {
					copy.textContent = 'Copied';
					setTimeout(function () { copy.textContent = 'Copy AI Prompt'; }, 1500);
				});
			});
		}
	});
})();
