<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Product_Terms_Ajax {

	public static function init(): void {
		add_action( 'wp_ajax_wam_load_product_attributes', array( __CLASS__, 'load_attributes' ) );
		add_action( 'wp_ajax_wam_apply_product_terms', array( __CLASS__, 'apply' ) );
	}


	public static function load_attributes(): void {
		check_ajax_referer( 'wam_product_terms', 'nonce' );

		if ( ! WAM_Helper::can_manage() ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to read product attributes.', 'woocommerce-attribute-manager' ) ),
				403
			);
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'woocommerce-attribute-manager' ) ), 404 );
		}

		$raw_taxonomies = isset( $_POST['taxonomies'] ) && is_array( $_POST['taxonomies'] )
			? wp_unslash( $_POST['taxonomies'] )
			: array();

		$taxonomies = array();

		foreach ( $raw_taxonomies as $taxonomy ) {
			$taxonomy = sanitize_text_field( $taxonomy );

			if ( taxonomy_is_product_attribute( $taxonomy ) ) {
				$taxonomies[] = $taxonomy;
			}
		}

		if ( empty( $taxonomies ) ) {
			foreach ( $product->get_attributes() as $attribute ) {
				if ( $attribute instanceof WC_Product_Attribute && $attribute->is_taxonomy() ) {
					$taxonomies[] = $attribute->get_name();
				}
			}
		}

		$attributes = array();

		foreach ( array_unique( $taxonomies ) as $taxonomy ) {
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

			$available_terms = array();

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$available_terms[] = array(
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
				'available_terms' => $available_terms,
			);
		}

		wp_send_json_success(
			array(
				'product_id' => $product_id,
				'attributes' => $attributes,
			)
		);
	}

	public static function apply(): void {
		check_ajax_referer( 'wam_product_terms', 'nonce' );

		if ( ! WAM_Helper::can_manage() ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to update products.', 'woocommerce-attribute-manager' ) ),
				403
			);
		}

		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$input = isset( $_POST['input'] ) ? sanitize_textarea_field( wp_unslash( $_POST['input'] ) ) : '';
		$product = $product_id ? wc_get_product( $product_id ) : false;

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'woocommerce-attribute-manager' ) ), 404 );
		}

		$definitions = WAM_Product_Terms_Parser::parse( $input );

		if ( empty( $definitions ) ) {
			wp_send_json_error( array( 'message' => __( 'No valid attribute definitions were found.', 'woocommerce-attribute-manager' ) ), 400 );
		}

		$attached = array();

		// Saved product Attributes.
		foreach ( $product->get_attributes() as $attribute ) {
			if ( $attribute instanceof WC_Product_Attribute && $attribute->is_taxonomy() ) {
				$attached[ $attribute->get_name() ] = $attribute;
			}
		}

		// The Product Data > Attributes panel can contain unsaved global Attributes.
		// The browser sends their taxonomies so they can be attached before terms are applied.
		$raw_taxonomies = isset( $_POST['taxonomies'] ) && is_array( $_POST['taxonomies'] )
			? wp_unslash( $_POST['taxonomies'] )
			: array();

		$live_taxonomies = array();

		foreach ( $raw_taxonomies as $taxonomy ) {
			$taxonomy = sanitize_text_field( $taxonomy );

			if ( taxonomy_is_product_attribute( $taxonomy ) ) {
				$live_taxonomies[] = $taxonomy;
			}
		}

		$position = count( $attached );

		foreach ( array_unique( $live_taxonomies ) as $taxonomy ) {
			if ( isset( $attached[ $taxonomy ] ) ) {
				continue;
			}

			$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );

			if ( ! $attribute_id ) {
				continue;
			}

			$attribute = new WC_Product_Attribute();
			$attribute->set_id( (int) $attribute_id );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( array() );
			$attribute->set_position( $position++ );
			$attribute->set_visible( true );
			$attribute->set_variation( false );

			$attached[ $taxonomy ] = $attribute;
		}

		$applied = array();
		$skipped = array();
		$errors = array();

		foreach ( $definitions as $definition ) {
			$taxonomy = wc_attribute_taxonomy_name( $definition['slug'] );

			if ( ! isset( $attached[ $taxonomy ] ) ) {
				$skipped[] = array(
					'name'   => $definition['name'],
					'slug'   => $definition['slug'],
					'reason' => __( 'This attribute is not attached to the product or was not present in the current WooCommerce editor.', 'woocommerce-attribute-manager' ),
				);
				continue;
			}

			$term_ids = array();
			$missing = array();

			foreach ( $definition['values'] as $term_name ) {
				$term_name = trim( $term_name );

				if ( '' === $term_name ) {
					continue;
				}

				$term = term_exists( $term_name, $taxonomy );

				if ( ! $term ) {
					$missing[] = $term_name;
					continue;
				}

				$term_ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}

			$term_ids = array_values( array_unique( $term_ids ) );

			if ( empty( $term_ids ) ) {
				$skipped[] = array(
					'name'    => $definition['name'],
					'slug'    => $definition['slug'],
					'missing' => $missing,
					'reason'  => __( 'None of the supplied term values exist for this attribute.', 'woocommerce-attribute-manager' ),
				);
				continue;
			}

			$relationship = wp_set_object_terms(
				$product_id,
				$term_ids,
				$taxonomy,
				false
			);

			if ( is_wp_error( $relationship ) ) {
				$errors[] = array(
					'name'    => $definition['name'],
					'slug'    => $definition['slug'],
					'message' => $relationship->get_error_message(),
				);
				continue;
			}

			$attached[ $taxonomy ]->set_options( $term_ids );

			$applied[] = array(
				'id'        => wc_attribute_taxonomy_id_by_name( $taxonomy ),
				'name'      => $definition['name'],
				'slug'      => $definition['slug'],
				'values'    => $definition['values'],
				'term_ids'  => $term_ids,
				'missing'   => $missing,
			);
		}

		/*
		 * Persist the Attribute definitions using WooCommerce's canonical
		 * _product_attributes post meta structure as the final write.
		 *
		 * This is intentionally done after WC_Product::save(). Some product
		 * editor states can rebuild the Attribute meta from a stale object
		 * snapshot, so the explicit meta write guarantees that the current
		 * Attribute -> Term IDs are what is stored for this product.
		 */
		$product->set_attributes( array_values( $attached ) );
		$product->save();

		$stored_attributes = array();

		foreach ( $attached as $taxonomy => $attribute ) {
			if ( ! $attribute instanceof WC_Product_Attribute || ! $attribute->is_taxonomy() ) {
				continue;
			}

			$stored_attributes[ $taxonomy ] = array(
				'name'         => $taxonomy,
				'value'        => '',
				'position'     => (int) $attribute->get_position(),
				'is_visible'   => $attribute->get_visible() ? 1 : 0,
				'is_variation' => $attribute->get_variation() ? 1 : 0,
				'is_taxonomy'  => 1,
			);
		}

		update_post_meta( $product_id, '_product_attributes', $stored_attributes );

		clean_post_cache( $product_id );
		wc_delete_product_transients( $product_id );

		$verify_raw_attributes = get_post_meta( $product_id, '_product_attributes', true );
		$verify_product = wc_get_product( $product_id );
		$verified = array();

		if ( $verify_product ) {
			foreach ( $verify_product->get_attributes() as $verify_attribute ) {
				if ( ! $verify_attribute instanceof WC_Product_Attribute || ! $verify_attribute->is_taxonomy() ) {
					continue;
				}

				$verify_taxonomy = $verify_attribute->get_name();

				if ( isset( $attached[ $verify_taxonomy ] ) ) {
					$verified[ $verify_taxonomy ] = array_map( 'absint', $verify_attribute->get_options() );
				}
			}
		}

		foreach ( $applied as &$item ) {
			$taxonomy = wc_attribute_taxonomy_name( $item['slug'] );
			$persisted_term_ids = $verified[ $taxonomy ] ?? array();

			/*
			 * The source of truth for the product's selected Terms is the
			 * taxonomy relationship. Also verify the Attribute definition
			 * exists in _product_attributes.
			 */
			$relationship_ids = wp_get_object_terms(
				$product_id,
				$taxonomy,
				array(
					'fields' => 'ids',
				)
			);

			if ( ! is_wp_error( $relationship_ids ) ) {
				$persisted_term_ids = array_map( 'absint', $relationship_ids );
			}

			$meta_attached = is_array( $verify_raw_attributes ) && isset( $verify_raw_attributes[ $taxonomy ] );

			$item['persisted_term_ids'] = $persisted_term_ids;
			$item['attribute_meta_persisted'] = $meta_attached;
			$item['persisted'] = $meta_attached && ! empty( array_intersect( $item['term_ids'], $persisted_term_ids ) );
		}
		unset( $item );

		wp_send_json_success(
			array(
				'applied' => $applied,
				'skipped' => $skipped,
				'errors'  => $errors,
			)
		);
	}
}
