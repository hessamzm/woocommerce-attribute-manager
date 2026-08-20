<?php

defined( 'ABSPATH' ) || exit;

final class WAM_Admin {

	public static function init(): void {
		WAM_Menu::init();
		WAM_Admin_Assets::init();
	}
}
