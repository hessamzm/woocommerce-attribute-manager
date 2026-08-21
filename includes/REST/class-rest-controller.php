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
}
