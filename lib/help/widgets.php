<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/widgets.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'title' => __('Um Elemente zur Seitenleiste Deines Blogs hinzuzufügen, ziehe sie per Drag & Drop nach unten.', 'wdeb'),

	'help' => __('Hier kannst Du anpassen, was in Deiner Seitenleiste angezeigt wird', 'wdeb'),

));