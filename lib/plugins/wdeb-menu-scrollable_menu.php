<?php
/*
Plugin Name: Scrollbares Menü
Description: Ermöglicht das Scrollen des Menüs auf kleinen Bildschirmen. Ermöglicht auch ausführlichere Assistentenschritt-Titel.
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0.1
Author: PSOURCE
*/

class Wdeb_Menu_ScrollableMenu {
	
	private function __construct () {}
	
	public static function serve () {
		$me = new Wdeb_Menu_ScrollableMenu;
		$me->_add_hooks();
	}
	
	private function _add_hooks () {
		add_filter('wdeb_menu-wizard-non_breaking_space', array($this, 'handle_whitespace'));
		add_action('wdeb_script-custom_javascript', array($this, 'handle_javascript'));
		add_action('wdeb_style-custom_stylesheet_rules', array($this, 'handle_css'));
	}
	
	function handle_whitespace () {
		return ' ';
	}

	function handle_javascript() {
		echo '
	function wdeb_menu_make_scrollable() {
		var $menu = $("#menu");
		var $primaryLeft = $("#primary_left");

		if (!$menu.length || !$primaryLeft.length) {
			return;
		}

		var topPos = $menu.height() + $menu.position().top;

		if (topPos < $(window).height()) {
			return;
		}

		$menu.height($(window).height() - $menu.position().top);
		$primaryLeft.css("z-index", "999");

		if (!$primaryLeft.hasClass("wdeb-scrollable")) {
			$primaryLeft
				.addClass("wdeb-scrollable")
				.on("mouseenter.wdebScrollable", function () {
					if ($menu.hasClass("hover-active")) {
						return;
					}

					$menu
						.addClass("hover-active")
						.find("ul")
							.css("position", "relative")
							.end()
						.css({
							"overflow-y": "scroll",
							"overflow-x": "hidden"
						})
						.width($menu.width() - 15);
				})
				.on("mouseleave.wdebScrollable", function () {
					$menu
						.removeClass("hover-active")
						.css({
							"overflow-y": "hidden",
							"overflow-x": "auto",
							"width": "100%"
						});
				});
		}
	}

	function wdeb_menu_reset_scrollable() {
		var $menu = $("#menu");
		var $primaryLeft = $("#primary_left");

		if (!$menu.length || !$primaryLeft.length) {
			return;
		}

		$menu
			.removeClass("hover-active")
			.find("ul")
				.css("position", "static")
				.end()
			.css({
				"height": "auto",
				"overflow-y": "hidden",
				"overflow-x": "hidden",
				"width": "100%"
			});

		$primaryLeft.css("z-index", "");

		wdeb_menu_make_scrollable();
	}

	$(window)
		.on("load.wdebScrollable", wdeb_menu_make_scrollable)
		.on("resize.wdebScrollable", wdeb_menu_reset_scrollable);
	';
	}
	
	function handle_css () {
		$theme_url = WDEB_PLUGIN_THEME_URL;
		echo <<<EoWdebScrollableMenuCss
		#menu .wdeb_wizard_step a {
			height: auto;
		}
		#menu .wdeb_wizard_step.current {
			background:url('{$theme_url}/assets/menu_current-large.png') top right no-repeat;
		}
		@media (max-width: 1280px) {
			#menu ul li a, #menu ul li a:hover {
				height: auto !important;
			}
		}
		EoWdebScrollableMenuCss;
	}
}

if (is_admin()) Wdeb_Menu_ScrollableMenu::serve();
