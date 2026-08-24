<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Plugin {

	private static ?WAM_Plugin $instance = null;

	public static function instance(): WAM_Plugin {
		return self::$instance ??= new self();
	}

	private function __construct() {
		$this->load_dependencies();
	}

	private function load_dependencies(): void {
		$files = array(
			'includes/Support/class-helper.php',
			'includes/Attributes/class-attribute-parser.php',
			'includes/Attributes/class-attribute-manager.php',
			'includes/Attributes/class-term-manager.php',
			'includes/Groups/class-group-parser.php',
			'includes/Groups/class-group-storage.php',
			'includes/Groups/class-group-manager.php',
			'includes/Admin/class-admin.php',
			'includes/Admin/class-menu.php',
			'includes/Settings/class-settings.php',
			'includes/Admin/class-assets.php',
			'includes/Admin/pages/class-attributes-page.php',
			'includes/Admin/pages/class-groups-page.php',
			'includes/Admin/pages/class-ai-guide-page.php',
			'includes/Product/class-product-panel.php',
			'includes/Product/class-product-terms-ajax.php',
			'includes/Product/class-product-terms-parser.php',
			'includes/REST/class-rest-controller.php',
			'includes/REST/class-rest-routes.php',
		);

		foreach ( $files as $file ) {
			require_once WAM_DIR . $file;
		}
	}


	public function init(): void {
		/*
		 * Keep the plugin language isolated to this text domain.
		 * Never change WordPress' global locale.
		 */
		$language = class_exists( 'WAM_Settings' )
			? WAM_Settings::get( 'language', 'site' )
			: 'site';

		if ( 'site' !== $language ) {
			$mo_file = WP_PLUGIN_DIR . '/' . dirname( WAM_BASENAME ) . '/languages/woocommerce-attribute-manager-' . sanitize_key( $language ) . '.mo';

			if ( file_exists( $mo_file ) ) {
				load_textdomain( 'woocommerce-attribute-manager', $mo_file );
				return;
			}
		}

		load_plugin_textdomain(
			'woocommerce-attribute-manager',
			false,
			dirname( WAM_BASENAME ) . '/languages'
		);

		WAM_Settings::init();
		WAM_Admin::init();
		WAM_Product_Panel::init();
		WAM_REST_Routes::init();
	}
}
