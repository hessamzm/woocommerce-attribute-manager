<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Admin_Assets {
	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function enqueue( string $hook ): void {
		if ( ! str_contains( $hook, 'wam-' ) ) return;

		wp_enqueue_style( 'wam-admin', WAM_URL . 'assets/css/admin.css', array(), WAM_VERSION );
		wp_enqueue_script( 'wam-admin', WAM_URL . 'assets/js/admin.js', array(), WAM_VERSION, true );

		wp_localize_script( 'wam-admin', 'WAMAdmin', array(
			'deleteConfirm' => __( 'Are you sure you want to permanently delete the selected WooCommerce attributes and their terms?', 'woocommerce-attribute-manager' ),
		) );
	}
}
