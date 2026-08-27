<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/page.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'new_page' => __('Erstelle unten eine neue „statische“ Seite – diese erscheint nicht oben bei deinen neuesten Beiträgen.', 'wdeb'),
	'title' => __('Gib deiner Seite hier einen Titel.', 'wdeb'),
	'body' => __('Schreibe den Inhalt deiner Seite, lade Bilder oder Audio hoch und wähle, ob du HTML (Code) oder die visuelle Ansicht (wie Word) verwenden möchtest. Du kannst Einbettungscode für Videos und Widgets unter dem HTML-Tab einfügen.', 'wdeb'),
	'publish' => __('Veröffentliche deine Seite oder speichere sie unten als Entwurf. Du kannst sie auch privat machen oder die Veröffentlichung für die Zukunft planen, indem du auf die "Bearbeiten"-Links klickst.', 'wdeb'),

	'help' => __('Eine Seite ist ein <em>stand-alone</em> Element, das nicht oben in deinem Blog erscheint – z. B. eine <em>Über-Seite</em> oder eine Seite mit Kontaktdaten, einem Kursplan oder sogar einem Lebenslauf. Deaktiviere Kommentare für ein professionelles Erscheinungsbild.', 'wdeb'),

));