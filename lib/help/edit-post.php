<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/edit-post.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'edit_post' => __('Im Folgenden findest Du eine Liste Deiner Blogbeiträge. Du kannst schnell wichtige Informationen dazu einsehen sowie die einzelnen Beiträge bearbeiten, löschen oder aufrufen.', 'wdeb'),

	'help' => __('Hier kannst Du die Blogbeiträge auf Deinem Blog verwalten.', 'wdeb'),

));