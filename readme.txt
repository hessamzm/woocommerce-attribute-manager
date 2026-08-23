=== WooCommerce Attribute Manager ===
Contributors: hessamzm
Requires at least: 7.0
Requires PHP: 8.2
Requires Plugins: woocommerce
Stable tag: 1.4.9
License: GPLv2 or later

== 1.4.9 ==
* Fixed persistence by explicitly writing WooCommerce's canonical _product_attributes meta after applying Terms.
* Explicitly persists taxonomy Term relationships with wp_set_object_terms().
* Verifies both _product_attributes and taxonomy relationships after saving.
* The UI sync now adds missing term options to dynamically-created WooCommerce Attribute selectors before selecting them.
* No new WooCommerce Terms are created.
