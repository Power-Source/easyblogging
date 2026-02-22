<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/themes.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'title' => __('Ändere das Design Deines Blogs, zeige eine Vorschau an und aktiviere neue Themes.', 'wdeb'),
	'current' => __('Dies ist das Design, das Du derzeit für Deinen Blog verwendest. Wenn Du es ändern möchtest, kannst Du unten aus den verfügbaren Designs auswählen', 'wdeb'),
	'available' => __('Du kannst eines dieser Designs auswählen und Dein Blog wird automatisch so aktualisiert, dass er wie folgt aussieht. Klicke einfach auf eines der Bilder, um eine Vorschau zu erhalten, wie Dein Blog mit diesem Design aussehen wird.', 'wdeb'),

	'help' => __('Hier kannst Du das Theme Deines Blogs ändern', 'wdeb'),

));