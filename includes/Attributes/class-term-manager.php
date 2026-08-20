<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Term_Manager {
	public static function create_terms( int $attribute_id, array $terms ): array {
		$taxonomy = WAM_Helper::attribute_taxonomy( $attribute_id );
		$result = array( 'created' => array(), 'existing' => array(), 'errors' => array() );

		if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			$result['errors'][] = array(
				'name' => '',
				'message' => sprintf(
					__( 'Attribute taxonomy "%s" is not registered.', 'woocommerce-attribute-manager' ),
					$taxonomy
				),
			);
			return $result;
		}

		foreach ( $terms as $term_name ) {
			$term_name = trim( (string) $term_name );
			if ( '' === $term_name ) continue;

			$existing = term_exists( $term_name, $taxonomy );
			if ( $existing ) {
				$result['existing'][] = $term_name;
				continue;
			}

			$inserted = wp_insert_term( $term_name, $taxonomy );
			if ( is_wp_error( $inserted ) ) {
				$result['errors'][] = array(
					'name' => $term_name,
					'message' => $inserted->get_error_message(),
				);
			} else {
				$result['created'][] = $term_name;
			}
		}

		return $result;
	}
}
