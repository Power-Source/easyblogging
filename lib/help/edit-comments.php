<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/edit-comments.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'edit_comments' => __('Hier kannst Du Kommentare zu Deinen Blogbeiträgen bearbeiten, genehmigen, löschen oder darauf antworten. Klicke auf Registerkarten oder wähle "Filter", um nur bestimmte Kommentare anzuzeigen.', 'wdeb'),

	'help' => __('Hier kannst Du die Kommentare verwalten, die sich in Deinem Blog befinden', 'wdeb'),

));