<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Group_Parser {

	public static function parse( string $input ): array {
		$input = trim( $input );

		if ( '' === $input ) {
			return array();
		}

		$blocks = preg_split( '/\[group\]/i', $input, -1, PREG_SPLIT_NO_EMPTY );
		$groups = array();

		foreach ( $blocks as $block ) {
			$name = '';
			$attributes = array();

			$lines = preg_split( '/\r\n|\r|\n/', trim( $block ) );

			foreach ( $lines as $line ) {
				$line = trim( $line );

				if ( preg_match( '/^name\s*:\s*(.+)$/iu', $line, $matches ) ) {
					$name = sanitize_text_field( $matches[1] );
				}

				if ( preg_match( '/^attributes\s*:\s*(.+)$/iu', $line, $matches ) ) {
					$attributes = preg_split(
						'/\s*\|\s*/u',
						trim( $matches[1] ),
						-1,
						PREG_SPLIT_NO_EMPTY
					);
				}
			}

			$attributes = array_values(
				array_unique(
					array_filter(
						array_map( 'sanitize_text_field', $attributes )
					)
				)
			);

			if ( '' !== $name && ! empty( $attributes ) ) {
				$groups[] = array(
					'name' => $name,
					'attributes' => $attributes,
				);
			}
		}

		return $groups;
	}
}
