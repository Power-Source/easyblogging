<?php
/*
Plugin Name: Schaltfläche in Adminbar zum Wechseln des Modus
Description: Ersetzen Sie die Standardschaltfläche für den einfachen Modus durch einen Menüeintrag in der Admin-Symbolleiste.
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.0
Author: PSOURCE
*/

class Wdeb_Theme_ToolbarSwitchButton {
	
	private function __construct () {}

	public static function serve () {
		$me = new Wdeb_Theme_ToolbarSwitchButton;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('admin_print_scripts', array($this, 'dequeue_scripts'), 19);
		add_action('admin_bar_menu', array($this, 'add_to_admin_bar'), 99);
	}

	function dequeue_scripts () {
		wp_dequeue_script('wdeb_switch');
	}

	function add_to_admin_bar () {
		global $wp_admin_bar;
		$data = new Wdeb_Options;
		$show_easy_bar = (int) $data->get_option('easy_bar');
		$toolbar_switch_button = $data->get_option('toolbar_switch_button');
		$show_toolbar_switch = (null !== $toolbar_switch_button)
			? (int) $toolbar_switch_button
			: $show_easy_bar
		;

		if (!$show_toolbar_switch) {
			return false;
		}

		if (!(defined('WDEB_IS_IN_EASY_MODE') && WDEB_IS_IN_EASY_MODE)) {
			$href = apply_filters('wdeb_easy_mode_init', WDEB_LANDING_PAGE . '?wdeb_on');
			$title = __('Aktiviere Easy-Modus', 'wdeb');
		} else {
			$auto_enter_roles = $data->get_option('auto_enter_role');
			if (!$auto_enter_roles || !wdeb_current_user_can($auto_enter_roles)) {
				$href = apply_filters('wdeb_easy_mode_init', WDEB_LANDING_PAGE . '?wdeb_off');
				$title = __('Beende Easy-Modus', 'wdeb');
			} else return false; // Not showing Beende Easy-Modus link if not applicable
		}
		$wp_admin_bar->add_menu(array(
			'parent' => 'site-name',
			'id' => 'wdeb-my_sites-ttsb',
			'title' => $title,
			'href' => $href,
		));
	}
}
if (is_admin()) Wdeb_Theme_ToolbarSwitchButton::serve();