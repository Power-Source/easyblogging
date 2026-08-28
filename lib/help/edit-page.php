<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/edit-page.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'edit_page' => __('Im Folgenden findest Du eine Liste Deiner Seiten. Du kannst schnell wichtige Informationen dazu einsehen sowie die einzelnen Seiten bearbeiten, löschen oder aufrufen.', 'wdeb'),

	'help' => __('Hier kannst Du die Seiten auf Deinem Blog verwalten.', 'wdeb'),

));