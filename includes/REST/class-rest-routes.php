<?php

defined( 'ABSPATH' ) || exit;

final class WAM_REST_Routes {

	public static function init(): void {
		add_action( 'rest_api_init', array( __CLASS__, 'register' ) );
	}

	public static function register(): void {
		register_rest_route(
			'wam/v1',
			'/groups/(?P<group>[a-z0-9\-]+)',
			array(
				'methods' => WP_REST_Server::READABLE,
				'callback' => array( 'WAM_REST_Controller', 'get_group' ),
				'permission_callback' => array( 'WAM_REST_Controller', 'permissions' ),
				'args' => array(
					'group' => array(
						'required' => true,
						'sanitize_callback' => 'sanitize_title',
					),
				),
			)
		);
	}
}
