<?php
/**
 * Handles all Easy Blogging plugins.
 */
class Wdeb_PluginsHandler {

	public static function init () {
		define('WDEB_PLUGIN_PLUGINS_DIR', WDEB_PLUGIN_BASE_DIR . '/lib/plugins');
		self::load_active_plugins();
	}

	public static function get_active_plugins () {
		$active = ( new Wdeb_Options )->get_options( 'wdeb_activated_plugins' );
		$active = $active ? $active : array();

		return $active;
	}

	private static function is_plugin_available( $plugin ) {
		if ( 'wdeb-pro-force_free' !== $plugin ) {
			return true;
		}

		return Wdeb_Options::is_network_activated()
			&& class_exists( 'ProSites' )
			&& function_exists( 'is_pro_site' );
	}

	public static function load_active_plugins () {
		$active = self::get_active_plugins();

		foreach ($active as $plugin) {
			if ( ! self::is_plugin_available( $plugin ) ) {
				continue;
			}

			$path = self::plugin_to_path($plugin);
			if (!file_exists($path)) continue;
			else @require_once($path);
		}
	}

	public static function get_all_plugins () {
		$all = glob(WDEB_PLUGIN_PLUGINS_DIR . '/*.php');
		$all = $all ? $all : array();
		$ret = array();
		foreach ($all as $path) {
			$plugin = pathinfo($path, PATHINFO_FILENAME);
			if ( ! self::is_plugin_available( $plugin ) ) {
				continue;
			}

			$ret[] = $plugin;
		}
		return $ret;
	}

	public static function plugin_to_path ($plugin) {
		$plugin = str_replace('/', '_', $plugin);
		return WDEB_PLUGIN_PLUGINS_DIR . '/' . "{$plugin}.php";
	}

	public static function get_plugin_info ($plugin) {
		$path = self::plugin_to_path($plugin);
		$default_headers = array(
			'Name' => 'Plugin Name',
			'Author' => 'Author',
			'Description' => 'Description',
			'Plugin URI' => 'Plugin URI',
			'Version' => 'Version',
		);
		return get_file_data($path, $default_headers, 'plugin');
	}

	public static function activate_plugin ($plugin) {
		if ( ! self::is_plugin_available( $plugin ) ) {
			return false;
		}

		$active = self::get_active_plugins();
		if (in_array($plugin, $active)) return true; // Already active

		$active[] = $plugin;
		return ( new Wdeb_Options )->set_options( $active, 'wdeb_activated_plugins' );
	}

	public static function deactivate_plugin ($plugin) {
		$active = self::get_active_plugins();
		if (!in_array($plugin, $active)) return true; // Already deactivated

		$key = array_search($plugin, $active);
		if ($key === false) return false; // Haven't found it

		unset($active[$key]);
		return ( new Wdeb_Options )->set_options( $active, 'wdeb_activated_plugins' );
	}
}