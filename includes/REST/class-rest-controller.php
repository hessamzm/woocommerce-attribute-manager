<?php
defined( 'ABSPATH' ) || exit;

final class WAM_REST_Controller {
	public static function permissions(): bool {
		return WAM_Helper::can_manage();
	}

	public static function get_group( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$raw_key = (string) $request->get_param( 'group' );
		$key = sanitize_title( rawurldecode( $raw_key ) );
		$group = WAM_Group_Manager::get( $key );

		if ( ! $group ) {
			return new WP_Error(
				'group_not_found',
				__( 'Attribute group not found.', 'woocommerce-attribute-manager' ),
				array( 'status' => 404 )
			);
		}

		return new WP_REST_Response(
			array(
				'name' => $group['name'],
				'attributes' => WAM_Group_Manager::resolve( $group ),
			),
			200
		);
	}
	public static function get_product_attributes( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$product_id = absint( $request->get_param( 'product_id' ) );
		$product    = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product ) {
			return new WP_Error(
				'product_not_found',
				__( 'Product not found.', 'woocommerce-attribute-manager' ),
				array( 'status' => 404 )
			);
		}

		$taxonomies = $request->get_param( 'taxonomies' );
		$taxonomies = is_array( $taxonomies ) ? $taxonomies : array();

		$taxonomies = array_values(
			array_filter(
				array_map(
					static function ( $taxonomy ) {
						return sanitize_text_field( wp_unslash( $taxonomy ) );
					},
					$taxonomies
				),
				static function ( $taxonomy ) {
					return taxonomy_is_product_attribute( $taxonomy );
				}
			)
		);

		// For saved products, fall back to the persisted product attributes.
		if ( empty( $taxonomies ) ) {
			foreach ( $product->get_attributes() as $attribute ) {
				if ( $attribute instanceof WC_Product_Attribute && $attribute->is_taxonomy() ) {
					$taxonomies[] = $attribute->get_name();
				}
			}
		}

		$taxonomies = array_values( array_unique( $taxonomies ) );
		$attributes = array();

		foreach ( $taxonomies as $taxonomy ) {
			$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );

			if ( ! $attribute_id ) {
				continue;
			}

			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			$options = array();

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[] = array(
						'id'   => (int) $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					);
				}
			}

			$attributes[] = array(
				'id'             => (int) $attribute_id,
				'name'           => wc_attribute_label( $taxonomy ),
				'slug'           => str_replace( 'pa_', '', $taxonomy ),
				'taxonomy'       => $taxonomy,
				'available_terms' => $options,
			);
		}

		return new WP_REST_Response(
			array(
				'product_id' => $product_id,
				'attributes' => $attributes,
			),
			200
		);
	}

}
