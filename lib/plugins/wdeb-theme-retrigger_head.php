<?php
/*
Plugin Name: Kompatibilitätsmodus
Description: Wenn Du einen Konflikt mit Deinem Plugin und Easy Blogging feststellst, aktiviere dieses Add-On.
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.1
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