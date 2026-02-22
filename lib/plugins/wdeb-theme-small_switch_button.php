<?php
/*
Plugin Name: Kleine Schaltfläche
Description: Ersetze die Standard-Umschalttaste für den einfachen Modus durch eine kleinere, symbolbasierte Version.
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.0
Author: PSOURCE
*/

class Wdeb_Theme_SmallSwitchButton {
	
	private function __construct () {}

	public static function serve () {
		$me = new Wdeb_Theme_SmallSwitchButton;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		add_action('admin_footer', array($this, 'add_icon_styles'));
	}

	function add_icon_styles () {
		$img_url = WDEB_PLUGIN_URL . '/img/easy-mode-small.png';
		echo <<<EoSmallSwitchCss
<style type="text/css">
.wdeb_switch.small_switch {
	margin-top: 1px !important;
	padding: 0 2px !important;
	border-radius: 5px;
	background: url({$img_url}) center center no-repeat #ccc;
}
</style>
<script type="text/javascript">
(function ($) {
$(function () {
	var link = $(".wdeb_switch").removeClass("button").addClass("small_switch").find("a")
		text = link.text()
	;
	link.text('').attr("title", text);
});
})(jQuery);
</script>
EoSmallSwitchCss;
	}
}
if (is_admin()) Wdeb_Theme_SmallSwitchButton::serve();