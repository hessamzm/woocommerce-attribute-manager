<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Helper {

	public static function can_manage(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	public static function attribute_by_name( string $name ): ?object {
		$name = trim( $name );

		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			if ( 0 === strcasecmp( $attribute->attribute_label, $name ) ) {
				return $attribute;
			}
		}

		return null;
	}

	public static function attribute_taxonomy( int $attribute_id ): string {
		return (string) wc_attribute_taxonomy_name_by_id( $attribute_id );
	}
}
