<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/post.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'new_post' => __('Verfasse einen neuen Beitrag, der ganz oben in deinem Blog erscheinen soll.', 'wdeb'),
	'post_title' => __('Die besten Beitragstitel sind in der Regel sehr aussagekräftig.', 'wdeb'),
	'post_body' => __('Schreibe den Inhalt deines Beitrags, lade Bilder oder Audio hoch und wähle, ob du HTML (Code) oder die visuelle Ansicht (wie Word) verwenden möchtest. Du kannst Einbettungscode für Videos und Widgets unter dem HTML-Tab einfügen.', 'wdeb'),
	'publish' => __('Du kannst deinen Beitrag als Entwurf speichern oder privat machen, indem du unten neben "Sichtbarkeit" auf "Bearbeiten" klickst. Du kannst Beiträge auch für die Zukunft planen, indem du neben "Sofort" auf "Bearbeiten" klickst.', 'wdeb'),
	'tags' => __('Tags sind eine großartige Möglichkeit, Suchmaschinen zu helfen, deine Beiträge zu finden, oder um dir zu helfen, deine Inhalte zu organisieren. Füge so viele wie möglich hinzu!', 'wdeb'),
	'categories' => __('Kategorien sind ernster als Tags. Sie sind die Hauptthemen deines Blogs.', 'wdeb'),

	'help' => __('Ein Beitrag ist ein <em>stand-alone</em> Element, das ganz oben in deinem Blog erscheint – z. B. ein <em>Neuigkeiten-Beitrag</em> oder ein Beitrag über ein aktuelles Ereignis. Kommentare können aktiviert werden, um Interaktion zu ermöglichen.', 'wdeb'),

));