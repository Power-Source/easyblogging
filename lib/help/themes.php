<?php
wp_enqueue_script('wdeb_help', WDEB_PLUGIN_URL . '/js/help/themes.js');
wp_localize_script('wdeb_help', 'l10WdebHelp', array(
	'title' => __('Ändere das Design Deines Blogs, sieh Dir eine Vorschau an und aktiviere neue Designs.', 'wdeb'),
	'current' => __('Dies ist das Design, das Du derzeit für Deinen Blog verwendest. Wenn Du es ändern möchtest, kannst Du eines der unten verfügbaren Designs auswählen.', 'wdeb'),
	'available' => __('Du kannst eines dieser Designs auswählen, und Dein Blog wird automatisch so aktualisiert, dass er so aussieht. Klicke einfach auf eines der Bilder, um eine Vorschau zu sehen, wie Dein Blog mit diesem Design aussehen wird.', 'wdeb'),

	'help' => __('Hier kannst Du das Design Deines Blogs ändern.', 'wdeb'),

));