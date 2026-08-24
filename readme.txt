=== WooCommerce Attribute Manager ===
Contributors: hessamzm
Requires at least: 7.0
Requires PHP: 8.2
Requires Plugins: woocommerce
Stable tag: 2.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Attribute Manager helps you create global WooCommerce Attributes in bulk, organize reusable Attribute Groups, and apply existing Attribute Terms to products with an AI-assisted workflow.

== 2.0.3 ==
* Rebuilt the Persian MO file with a valid GNU MO binary structure and UTF-8 encoding.
* Fixed corrupted Persian characters caused by the previous MO file.
* Plugin language remains isolated from the WordPress global locale.
* Persian translations are loaded directly into the plugin text domain only.
* Selecting the plugin language no longer changes the WordPress site language.

== 2.0.2 ==
* Fixed plugin language isolation.
* Removed the global WordPress locale filter.
* Selecting Persian now changes translations only for WooCommerce Attribute Manager.
* WordPress core, WooCommerce, and other plugins keep the site's original language.

== 2.0.1 ==
* Fixed interface language selection so the selected locale is applied in the WordPress admin.
* Added a compiled Persian MO translation file.
* Expanded Persian translations for the current admin interface.
* Added a GitHub-ready README.md with installation, architecture, AI workflow, localization, and roadmap documentation.

== 2.0.0 ==
* Added Settings page.
* Added interface language selection.
* Added AI provider, API endpoint, API key, and model configuration fields for future automation.
* Added Persian translation catalog.
