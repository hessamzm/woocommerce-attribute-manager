<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Group_Storage {

	private const OPTION_KEY = 'wam_attribute_groups';

	public static function get_all(): array {
		$groups = get_option( self::OPTION_KEY, array() );
		return is_array( $groups ) ? $groups : array();
	}

	public static function save( array $groups ): bool {
		return update_option( self::OPTION_KEY, $groups, false );
	}

	public static function delete( string $key ): bool {
		$groups = self::get_all();
		$key = sanitize_title( $key );

		if ( ! isset( $groups[ $key ] ) ) {
			return false;
		}

		unset( $groups[ $key ] );

		return self::save( $groups );
	}

	public static function delete_many( array $keys ): array {
		$groups = self::get_all();
		$deleted = array();
		$missing = array();

		foreach ( array_unique( array_map( 'sanitize_title', $keys ) ) as $key ) {
			if ( isset( $groups[ $key ] ) ) {
				$deleted[] = array(
					'key' => $key,
					'name' => $groups[ $key ]['name'] ?? $key,
				);
				unset( $groups[ $key ] );
			} else {
				$missing[] = $key;
			}
		}

		self::save( $groups );

		return array(
			'deleted' => $deleted,
			'missing' => $missing,
		);
	}
}
