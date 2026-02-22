<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/post.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'new_post' => __('Schreibe einen neuen Beitrag, der oben in Deinem Blog erscheint.', 'wdeb'),
	'post_title' => __('Die besten Beitragstitel sind normalerweise sehr beschreibend.', 'wdeb'),
	'post_body' => __('Schreibe den Inhalt Deines Beitrags, lade Bilder oder Audio hoch und wähle, ob Du HTML (Code) oder Visual (wie Word) verwenden möchtest. Unter der Registerkarte „HTML“ kannst Du Einbettungscode für Videos und Widgets einfügen.', 'wdeb'),
	'publish' => __('Du kannst Deinen Beitrag als Entwurf speichern oder ihn privat machen, indem Du unten neben „Sichtbarkeit“ auf „Bearbeiten“ klickst. Du kannst auch die zukünftige Veröffentlichung von Beiträgen planen, indem Du neben "Sofort" auf "Bearbeiten" klickst. ', 'wdeb'),
	'tags' => __('Tags sind eine großartige Möglichkeit, Suchmaschinen dabei zu helfen, Deine Beiträge zu finden oder Dir bei der Organisation Deiner Inhalte zu helfen. Füge so viele hinzu, wie Du kannst!', 'wdeb'),
	'categories' => __('Kategorien sind ernster als Tags. Sie sind die Hauptthemen Deines Blogs.', 'wdeb'),

	'help' => __('Eine lange Reihe von Hilfe hier', 'wdeb'),

));