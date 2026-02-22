<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/edit-page.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'edit_page' => __('Nachfolgend findest Du eine Liste Deiner Seiten. Du kannst schnell wichtige Informationen zu ihnen anzeigen und diese bearbeiten, löschen oder anzeigen.', 'wdeb'),

	'help' => __('Hier kannst Du die Seiten verwalten, die sich in Deinem Blog befinden', 'wdeb'),

));