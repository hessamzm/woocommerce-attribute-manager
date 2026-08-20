<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Helper {
	public static function can_manage(): bool {
		return current_user_can( 'manage_woocommerce' );
	}

	public static function attribute_by_name( string $name ): ?object {
		foreach ( wc_get_attribute_taxonomies() as $attribute ) {
			if ( 0 === strcasecmp( $attribute->attribute_label, trim( $name ) ) ) {
				return $attribute;
			}
		}
		return null;
	}

	public static function attribute_taxonomy( int $attribute_id ): string {
		return (string) wc_attribute_taxonomy_name_by_id( $attribute_id );
	}

	public static function register_attribute_taxonomy( int $attribute_id ): bool {
		$attribute = wc_get_attribute( $attribute_id );
		if ( ! $attribute ) {
			return false;
		}

		$taxonomy = $attribute->slug;

		if ( taxonomy_exists( $taxonomy ) ) {
			return true;
		}

		$registered = register_taxonomy(
			$taxonomy,
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . $taxonomy,
				array(
					'labels' => array( 'name' => $attribute->name ),
					'hierarchical' => false,
					'show_ui' => false,
					'query_var' => true,
					'rewrite' => false,
					'show_in_nav_menus' => false,
					'capabilities' => array(
						'manage_terms' => 'manage_product_terms',
						'edit_terms' => 'manage_product_terms',
						'delete_terms' => 'manage_product_terms',
						'assign_terms' => 'edit_products',
					),
				)
			)
		);

		if ( is_wp_error( $registered ) ) {
			return false;
		}

		global $wc_product_attributes;
		if ( ! is_array( $wc_product_attributes ) ) {
			$wc_product_attributes = array();
		}
		foreach ( wc_get_attribute_taxonomies() as $data ) {
			$wc_product_attributes[ wc_attribute_taxonomy_name( $data->attribute_name ) ] = $data;
		}

		return true;
	}
}
