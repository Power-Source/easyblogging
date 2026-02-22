<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/page.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'new_page' => __('Erstelle unten eine neue "statische" Seite – diese wird nicht oben in Deinen neuesten Beiträgen angezeigt.', 'wdeb'),
	'title' => __('Gib Deiner Seite hier einen Titel.', 'wdeb'),
	'body' => __('Schreibe den Inhalt Deiner Seite, lade Bilder oder Audio hoch und wähle, ob Du HTML (Code) oder Visual (wie Word) verwenden möchtest. Unter der Registerkarte „HTML“ kannst Du Einbettungscode für Videos und Widgets einfügen.', 'wdeb'),
	'publish' => __('Veröffentliche Deine Seite oder speichere sie unten als Entwurf. Du kannst es auch privat machen oder die Veröffentlichung für die Zukunft planen, indem Du auf den Link "Bearbeiten" klickst.', 'wdeb'),

	'help' => __('Eine Seite ist ein <em>eigenständiges</em> Element, das nicht oben in Deinem Blog erscheint – z.B. eine <em>Über Mich-Seite</em> oder eine Seite mit Kontaktdaten, einer Kursübersicht oder sogar einer LEBENSLAUF. Deaktiviere Kommentare für ein professionelles Erscheinungsbild.', 'wdeb'),

));