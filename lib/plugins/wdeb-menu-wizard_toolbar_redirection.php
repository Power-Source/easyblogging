<?php
/*
Plugin Name: Umleitung Admin-Symbolleiste für den Assistentenmodus
Description: Leitet Nicht-Assistenten-Links von der Admin-Symbolleiste um.
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.1
Author: PSOURCE
*/

class Wdeb_Menu_WizardToolbarRedirection {

	private function __construct () {}

	public static function serve () {
		$me = new Wdeb_Menu_WizardToolbarRedirection;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('wdeb-menu-wizard-after_menu_items', array($this, 'output_javascript'));
	}

	function output_javascript () {
		$confirmation_msg = esc_js(__('Wenn Du diesem Link folgst, wird der Assistentenmodus beendet. Bist Du sicher, dass Du fortfahren möchtest?', 'wdeb'));
		echo <<<EoWizardRedirectionJs
<script type="text/javascript">
(function ($) {
$(function () {
	var links = $("#wpadminbar a")
	;
	links.each(function () {
		var me = $(this)
			href = me.attr("href"),
			new_href = href,
			separator = href.match(/\?/) ? '&' : '?',
			in_menu = $('.wdeb_wizard_step a[href="' + href + '"]')
		;
		if (in_menu.length) return true; // Link exists in the menu, no need to rebind
		if (href.match(/^#/)) return true; // Don't do this for local links

		new_href += separator + 'wdeb_off';

		me
			.attr("href", new_href)
			.off("click")
			.on("click", function () {
				if (!confirm("{$confirmation_msg}")) return false;
				return true;
			})
		;
	});
});
})(jQuery);
</script>
EoWizardRedirectionJs;
	}
}
if (is_admin()) Wdeb_Menu_WizardToolbarRedirection::serve();