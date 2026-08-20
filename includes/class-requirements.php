<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Requirements {

	private const MIN_PHP = '8.2';
	private const MIN_WP  = '7.0';
	private const MIN_WC  = '10.0';

	public static function check(): bool {
		global $wp_version;

		if ( version_compare( PHP_VERSION, self::MIN_PHP, '<' ) ) {
			self::notice(
				sprintf(
					/* translators: %s: PHP version. */
					__( 'WooCommerce Attribute Manager requires PHP %s or higher.', 'woocommerce-attribute-manager' ),
					self::MIN_PHP
				)
			);
			return false;
		}

		if ( version_compare( $wp_version, self::MIN_WP, '<' ) ) {
			self::notice(
				sprintf(
					/* translators: %s: WordPress version. */
					__( 'WooCommerce Attribute Manager requires WordPress %s or higher.', 'woocommerce-attribute-manager' ),
					self::MIN_WP
				)
			);
			return false;
		}

		if ( ! class_exists( 'WooCommerce' ) || ! defined( 'WC_VERSION' ) ) {
			self::notice(
				__( 'WooCommerce Attribute Manager requires WooCommerce to be installed and active.', 'woocommerce-attribute-manager' )
			);
			return false;
		}

		if ( version_compare( WC_VERSION, self::MIN_WC, '<' ) ) {
			self::notice(
				sprintf(
					/* translators: %s: WooCommerce version. */
					__( 'WooCommerce Attribute Manager requires WooCommerce %s or higher.', 'woocommerce-attribute-manager' ),
					self::MIN_WC
				)
			);
			return false;
		}

		return true;
	}

	private static function notice( string $message ): void {
		add_action(
			'admin_notices',
			static function () use ( $message ): void {
				printf(
					'<div class="notice notice-error"><p>%s</p></div>',
					esc_html( $message )
				);
			}
		);
	}
}
