<?php
/**
 * Plugin Name: WooCommerce Attribute Manager
 * Plugin URI: https://github.com/hessamzm
 * Description: Create WooCommerce attributes and reusable attribute groups.
 * Version: 1.1.0
 * Author: hessamzm
 * Author URI: https://github.com/hessamzm
 * Text Domain: woocommerce-attribute-manager
 * Domain Path: /languages
 * Requires at least: 7.0
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 *
 * @package WooCommerce_Attribute_Manager
 */

defined( 'ABSPATH' ) || exit;

define( 'WAM_VERSION', '1.1.0' );
define( 'WAM_FILE', __FILE__ );
define( 'WAM_DIR', plugin_dir_path( __FILE__ ) );
define( 'WAM_URL', plugin_dir_url( __FILE__ ) );
define( 'WAM_BASENAME', plugin_basename( __FILE__ ) );

require_once WAM_DIR . 'includes/class-requirements.php';
require_once WAM_DIR . 'includes/class-plugin.php';

add_action(
	'plugins_loaded',
	static function (): void {
		if ( WAM_Requirements::check() ) {
			WAM_Plugin::instance()->init();
		}
	},
	20
);
