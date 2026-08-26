<?php
/*
Plugin Name: Kompatibilitäts-Modus für PS Easy Blogging
Description: Wenn du einen Konflikt zwischen deinem Plugin und PS Easy Blogging feststellst, versuche, dieses Add-on zu aktivieren.
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0
Author: PSOURCE
*/

class Wdeb_AdminHead_Retrigger {

	private function __construct () {}

	public static function serve () {
		$me = new Wdeb_AdminHead_Retrigger;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('admin_init', array($this, 'init'));
	}

	public function init () {
		if (defined('WDEB_CORE_ACTIONS_REDO_ADMIN_HEAD')) return false;
		define('WDEB_CORE_ACTIONS_REDO_ADMIN_HEAD', true);
	}

}
Wdeb_AdminHead_Retrigger::serve();