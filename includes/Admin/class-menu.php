<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Menu {

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register' ) );
	}

	public static function register(): void {
		$capability = 'manage_woocommerce';

		add_menu_page(
			__( 'Attribute Manager', 'woocommerce-attribute-manager' ),
			__( 'Attribute Manager', 'woocommerce-attribute-manager' ),
			$capability,
			'wam-attributes',
			array( 'WAM_Attributes_Page', 'render' ),
			'dashicons-editor-table',
			56
		);

		add_submenu_page(
			'wam-attributes',
			__( 'Create Attributes', 'woocommerce-attribute-manager' ),
			__( 'Create Attributes', 'woocommerce-attribute-manager' ),
			$capability,
			'wam-attributes',
			array( 'WAM_Attributes_Page', 'render' )
		);

		add_submenu_page(
			'wam-attributes',
			__( 'Attribute Groups', 'woocommerce-attribute-manager' ),
			__( 'Attribute Groups', 'woocommerce-attribute-manager' ),
			$capability,
			'wam-groups',
			array( 'WAM_Groups_Page', 'render' )
		);

		add_submenu_page(
			'wam-attributes',
			__( 'AI Structure Guide', 'woocommerce-attribute-manager' ),
			__( 'AI Structure Guide', 'woocommerce-attribute-manager' ),
			$capability,
			'wam-ai-guide',
			array( 'WAM_AI_Guide_Page', 'render' )
		);

		add_submenu_page(
			'wam-attributes',
			__( 'Settings', 'woocommerce-attribute-manager' ),
			__( 'Settings', 'woocommerce-attribute-manager' ),
			$capability,
			'wam-settings',
			array( 'WAM_Settings', 'render' )
		);
	}
}
