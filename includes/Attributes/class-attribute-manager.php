<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Attribute_Manager {

	public static function sanitize_slug( string $slug ): string {
		$slug = strtolower( remove_accents( trim( $slug ) ) );
		$slug = preg_replace( '/[^a-z0-9_-]+/', '-', $slug );
		return substr( trim( $slug, '-_' ), 0, 28 );
	}

	public static function create_or_get( string $name, string $slug = '' ): int|WP_Error {
		$existing = WAM_Helper::attribute_by_name( $name );
		if ( $existing ) {
			return (int) $existing->attribute_id;
		}

		$slug = self::sanitize_slug( $slug );

		if ( '' === $slug ) {
			return new WP_Error(
				'invalid_attribute_slug',
				__( 'Please provide a valid English slug for this attribute.', 'woocommerce-attribute-manager' )
			);
		}

		return wc_create_attribute( array(
			'name' => trim( $name ),
			'slug' => $slug,
			'type' => 'select',
			'order_by' => 'menu_order',
			'has_archives' => false,
		) );
	}

	public static function import( string $input ): array {
		$result = array( 'created' => array(), 'existing' => array(), 'errors' => array() );

		foreach ( WAM_Attribute_Parser::parse( $input ) as $definition ) {
			$existing = WAM_Helper::attribute_by_name( $definition['name'] );
			$id = self::create_or_get( $definition['name'], $definition['slug'] );

			if ( is_wp_error( $id ) ) {
				$result['errors'][] = array(
					'name' => $definition['name'],
					'slug' => $definition['slug'],
					'message' => $id->get_error_message(),
				);
				continue;
			}

			$terms = WAM_Term_Manager::create_terms( (int) $id, $definition['values'] );
			if ( ! empty( $terms['errors'] ) ) {
				$result['errors'] = array_merge( $result['errors'], $terms['errors'] );
			}

			$item = array(
				'id' => (int) $id,
				'name' => $definition['name'],
				'slug' => $definition['slug'],
				'terms_created' => $terms['created'],
				'terms_existing' => $terms['existing'],
			);

			$existing ? $result['existing'][] = $item : $result['created'][] = $item;
		}

		delete_transient( 'wc_attribute_taxonomies' );
		return $result;
	}

	public static function delete_many( array $ids ): array {
		$deleted = array();
		$errors = array();

		foreach ( array_unique( array_map( 'absint', $ids ) ) as $id ) {
			if ( $id < 1 ) continue;

			$attribute = wc_get_attribute( $id );
			if ( ! $attribute ) continue;

			$result = wc_delete_attribute( $id );
			if ( is_wp_error( $result ) ) {
				$errors[] = array( 'id' => $id, 'name' => $attribute->name, 'message' => $result->get_error_message() );
			} else {
				$deleted[] = array( 'id' => $id, 'name' => $attribute->name );
			}
		}

		delete_transient( 'wc_attribute_taxonomies' );
		return array( 'deleted' => $deleted, 'errors' => $errors );
	}
}
