<?php
/*
Plugin Name: Customizer Zugriff erlauben
Description: Ermöglicht den Zugriff auf den Theme Customizer.
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0
Author: PSOURCE
*/

class Wdeb_Themes_AllowCustomizer {

	private function __construct() {}

	public static function serve() {
		$me = new self();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'wdeb_style-custom_stylesheet_rules',
			array( $this, 'style_overrides' )
		);

		add_action(
			'wdeb_script-custom_javascript',
			array( $this, 'script_init' )
		);
	}

	public function style_overrides() {
		echo '#primary_right td.available-theme p,
			#primary_right #current-theme .theme-options,
			#primary_right .appearance_page_premium-themes #current-theme p {
				display: block;
			}';
	}

	public function script_init() {
		echo '$(function () {
			$(".hide-if-no-customize").show();
			$(".hide-if-customize").hide();
		});';
	}
}

if ( is_admin() ) {
	Wdeb_Themes_AllowCustomizer::serve();
}