<?php
/*
Plugin Name: Easy Blogging
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Description: Ändert den ClassicPress-Verwaltungsbereich so, dass er standardmäßig einen "Anfänger" -Bereich enthält, mit der Option, zum normalen "Erweitert" -Bereich zu wechseln.
Version: 1.0.2
Text Domain: wdeb
Author: PSOURCE
Author URI: https://n3rds.work


Copyright 2020-2026 PSOURCE (https://n3rds.work)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


define ('WDEB_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)));

//Setup proper paths/URLs and load text domains
if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDEB_PLUGIN_LOCATION', 'mu-plugins');
	define ('WDEB_PLUGIN_BASE_DIR', WPMU_PLUGIN_DIR);
	define ('WDEB_PLUGIN_URL', str_replace('http://', (($_SERVER["HTTPS"] ?? '') == 'on' ? 'https://' : 'http://'), WPMU_PLUGIN_URL));
	$textdomain_handler = 'load_muplugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . WDEB_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('WDEB_PLUGIN_LOCATION', 'subfolder-plugins');
	define ('WDEB_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WDEB_PLUGIN_SELF_DIRNAME);
	define ('WDEB_PLUGIN_URL', str_replace('http://', (($_SERVER["HTTPS"] ?? '') == 'on' ? 'https://' : 'http://'), WP_PLUGIN_URL) . '/' . WDEB_PLUGIN_SELF_DIRNAME);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDEB_PLUGIN_LOCATION', 'plugins');
	define ('WDEB_PLUGIN_BASE_DIR', WP_PLUGIN_DIR);
	define ('WDEB_PLUGIN_URL', str_replace('http://', (($_SERVER["HTTPS"] ?? '') == 'on' ? 'https://' : 'http://'), WP_PLUGIN_URL));
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	// No textdomain is loaded because we can't determine the plugin location.
	// No point in trying to add textdomain to string and/or localizing it.
	wp_die(__('Es gab ein Problem beim Bestimmen, wo das Easy Blogging-Plugin installiert ist. Bitte erneut installieren.'));
}
$textdomain_handler('wdeb', false, WDEB_PLUGIN_SELF_DIRNAME . '/languages/');

define('WDEB_LOGO_URL', WDEB_PLUGIN_URL . '/img/logo.png');
define('WDEB_LANDING_PAGE', 'index.php');

require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_installer.php';
Wdeb_Installer::check();

require_once WDEB_PLUGIN_BASE_DIR . '/lib/wdeb_callbacks.php';
require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_options.php';
//Wdeb_Options::populate(); // Deprecated

require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_plugins_handler.php';
Wdeb_PluginsHandler::init();

add_action('wp_logout', 'wdeb_reset_autostart');

if (is_admin()) {
	require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_admin_form_renderer.php';
	require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_admin_pages.php';
	require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_tooltips.php';
	require_once WDEB_PLUGIN_BASE_DIR . '/lib/class_wdeb_wizard.php';
	Wdeb_AdminPages::serve();
	Wdeb_Tooltips::serve();
	Wdeb_Wizard::serve();
}
