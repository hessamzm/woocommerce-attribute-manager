<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Group_Manager {

	public static function import( string $input ): array {
		$groups = WAM_Group_Parser::parse( $input );
		$stored = WAM_Group_Storage::get_all();

		foreach ( $groups as $group ) {
			$key = sanitize_title( $group['name'] );

			$stored[ $key ] = array(
				'name' => $group['name'],
				'attributes' => $group['attributes'],
			);
		}

		WAM_Group_Storage::save( $stored );

		return $stored;
	}

	public static function get( string $key ): ?array {
		$groups = WAM_Group_Storage::get_all();
		return $groups[ sanitize_title( $key ) ] ?? null;
	}

	public static function resolve( array $group ): array {
		$resolved = array();

		foreach ( $group['attributes'] ?? array() as $name ) {
			$attribute = WAM_Helper::attribute_by_name( $name );

			if ( ! $attribute ) {
				continue;
			}

			$taxonomy = WAM_Helper::attribute_taxonomy( (int) $attribute->attribute_id );
			$terms = get_terms(
				array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				)
			);

			$options = array();

			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[] = array(
						'id' => (int) $term->term_id,
						'name' => $term->name,
						'slug' => $term->slug,
					);
				}
			}

			$resolved[] = array(
				'id' => (int) $attribute->attribute_id,
				'name' => $attribute->attribute_label,
				'slug' => $attribute->attribute_name,
				'taxonomy' => $taxonomy,
				'options' => $options,
			);
		}

		return $resolved;
	}
}
