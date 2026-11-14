<?php
/**
 * Installs the defaults.
 */
class Wdeb_Installer {

	/**
	 * @public
	 * @static
	 */
	public static function check () {
		$is_installed = ( new Wdeb_Options )->get_options( 'wdeb' );
		if (!$is_installed) Wdeb_Installer::install();
	}

	/**
	 * @private
	 * @static
	 */
	public static function install () {
		$me = new Wdeb_Installer;
		$me->create_default_options();
	}

	/**
	 * @private
	 */
	function create_default_options () {
		( new Wdeb_Options )->set_options( array (
			'post_boxes' => array (),
			'page_boxes' => array (),
			'screen_options' => '0',
			'auto_enter_role' => '0',
			'hijack_start_page' => '0',
			'plugin_theme' => 'default',
			'dashboard_right_now' => '1',
		), 'wdeb' );
	}
}