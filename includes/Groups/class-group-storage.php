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
}
