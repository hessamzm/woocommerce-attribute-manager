<?php
defined( 'ABSPATH' ) || exit;

final class WAM_Attribute_Parser {
	public static function parse( string $input ): array {
		$blocks = preg_split( '/\[attribute\]/i', trim( $input ), -1, PREG_SPLIT_NO_EMPTY );
		$out = array();

		foreach ( $blocks as $block ) {
			$name = '';
			$slug = '';
			$values = array();

			foreach ( preg_split( '/\r\n|\r|\n/', trim( $block ) ) as $line ) {
				$line = trim( $line );
				if ( preg_match( '/^name\s*:\s*(.+)$/iu', $line, $m ) ) {
					$name = trim( $m[1] );
				} elseif ( preg_match( '/^slug\s*:\s*(.+)$/iu', $line, $m ) ) {
					$slug = trim( $m[1] );
				} elseif ( preg_match( '/^values\s*:\s*(.+)$/iu', $line, $m ) ) {
					$values = preg_split( '/\s*\|\s*/u', trim( $m[1] ), -1, PREG_SPLIT_NO_EMPTY );
				}
			}

			$name = sanitize_text_field( $name );
			$slug = WAM_Attribute_Manager::sanitize_slug( $slug );
			$values = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $values ) ) ) );

			if ( '' !== $name ) {
				$out[] = array( 'name' => $name, 'slug' => $slug, 'values' => $values );
			}
		}
		return $out;
	}
}
