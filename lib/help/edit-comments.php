<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/edit-comments.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'edit_comments' => __('Hier kannst Du Kommentare zu Deinen Blogbeiträgen bearbeiten, freigeben, löschen oder darauf antworten. Klicke auf die Reiter oder wähle „Filter“, um nur bestimmte Kommentare anzuzeigen.', 'wdeb'),

	'help' => __('Hier kannst Du die Kommentare auf Deinem Blog verwalten.', 'wdeb'),

));