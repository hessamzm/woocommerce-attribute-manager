<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Product_Terms_Parser {

	public static function parse( string $input ): array {
		$blocks = preg_split( '/\[attribute\]/i', trim( $input ), -1, PREG_SPLIT_NO_EMPTY );
		$result = array();

		foreach ( $blocks as $block ) {
			$name = '';
			$slug = '';
			$values = array();

			foreach ( preg_split( '/\r\n|\r|\n/', trim( $block ) ) as $line ) {
				$line = trim( $line );

				if ( preg_match( '/^name\s*:\s*(.+)$/iu', $line, $m ) ) {
					$name = sanitize_text_field( $m[1] );
				} elseif ( preg_match( '/^slug\s*:\s*(.+)$/iu', $line, $m ) ) {
					$slug = WAM_Attribute_Manager::sanitize_slug( $m[1] );
				} elseif ( preg_match( '/^values\s*:\s*(.+)$/iu', $line, $m ) ) {
					$values = preg_split( '/\s*\|\s*/u', trim( $m[1] ), -1, PREG_SPLIT_NO_EMPTY );
				}
			}

			if ( '' === $name || '' === $slug || empty( $values ) ) {
				continue;
			}

			$result[] = array(
				'name'   => $name,
				'slug'   => $slug,
				'values' => array_values(
					array_unique(
						array_filter(
							array_map( 'sanitize_text_field', $values )
						)
					)
				),
			);
		}

		return $result;
	}
}
