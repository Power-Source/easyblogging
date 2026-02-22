<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Wdeb_AdminFormRenderer {

	function _get_option ($key = false, $pfx = 'wdeb') {
		$opt = defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN ? get_site_option($pfx) : get_option($pfx);
		if (!$key) return $opt;
		return is_array($opt) ? ($opt[$key] ?? null) : null;
	}

	function _create_checkbox ($name, $pfx = 'wdeb') {
		$opt = $this->_get_option($name, $pfx);
		//$value = @$opt[$name]; //PhP8 Fix
		$value = $opt ?? null; // Use the null coalescing operator for PHP 7+
		return
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-yes' value='1' " . ((int)$value ? 'checked="checked" ' : '') . " /> " .
			"<label for='{$name}-yes'>" . __('Ja', 'wdeb') . "</label>" .
			'&nbsp;' .
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-no' value='0' " . (!(int)$value ? 'checked="checked" ' : '') . " /> " .
			"<label for='{$name}-no'>" . __('Nein', 'wdeb') . "</label>" .
		"";
	}

	function _create_radiobox ($name, $value) {
		$opt = $this->_get_option($name);
		$checked = ($opt == $value) ? true : false;
		return "<input type='radio' name='wdeb[{$name}]' id='{$name}-{$value}' value='{$value}' " . ($checked ? 'checked="checked" ' : '') . " /> ";
	}

	function create_metaboxes_posts_box () {
		$boxes = array (
			'postexcerpt' => __('Auszug'),
			'postimagediv' => __('Ausgewähltes Bild'),
			'trackbacksdiv' => __('Trackbacks senden'),
			'postcustom' => __('Benutzerdefinierte Felder'),
			'commentstatusdiv' => __('Diskussion'),
			'slugdiv' => __('Slug'),
			'authordiv' => __('Autor'),
			'formatdiv' => __('Format'),
			'categorydiv' => __('Kategorien'),
			'tagsdiv-post_tag' => __('Beitrag Tags'),
			'revisionsdiv' => __('Revisionen'),
		);
		$opt = $this->_get_option('post_boxes');
		$opt = is_array($opt) ? $opt : array();
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo "<input type='hidden' name='wdeb[post_boxes][{$bid}]' value='0' />" .
				"<input {$checked} type='checkbox' name='wdeb[post_boxes][{$bid}]' value='{$bid}' id='wdeb_post_boxes_{$bid}' /> " .
				"<label for='wdeb_post_boxes_{$bid}'>{$label}</label><br />\n";
		}
		_e('<p><b>Warnung:</b> Alle anderen Felder werden entsprechend ihren Bildschirmeinstellungen ein- oder ausgeblendet</p>', 'wdeb');
	}

	function create_metaboxes_pages_box () {
		$boxes = array (
			'postcustom' => __('Benutzerdefinierte Felder'),
			'postimagediv' => __('Ausgewähltes Bild'),
			'commentstatusdiv' => __('Diskussion'),
			'slugdiv' => __('Slug'),
			'authordiv' => __('Autor'),
			'pageparentdiv' => __('Seitenattribute'),
		);
		$opt = $this->_get_option('page_boxes');
		$opt = is_array($opt) ? $opt : array();
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo "<input type='hidden' name='wdeb[page_boxes][{$bid}]' value='0' />" .
				"<input type='checkbox' {$checked} name='wdeb[page_boxes][{$bid}]' value='{$bid}' id='wdeb_page_boxes_{$bid}' /> " .
				"<label for='wdeb_page_boxes_{$bid}'>{$label}</label><br />\n";
		}
		_e('<p><b>Warnung:</b> Alle anderen Felder werden entsprechend ihren Bildschirmeinstellungen ein- oder ausgeblendet</p>', 'wdeb');
	}

	function create_admin_bar_box () {
		echo $this->_create_checkbox('admin_bar');
		_e('<p>Zeige die ClassicPress-Admin-Leiste im einfachen Modus an.</p>', 'wdeb');
	}

	function create_screen_options_box () {
		echo $this->_create_checkbox('screen_options');
		_e('<p>Zeige im einfachen Modus kontextbezogene Hilfe- und Bildschirmoptionen an.</p>', 'wdeb');
	}

	function create_easy_bar_box () {
		echo $this->_create_checkbox('easy_bar');
		_e('<p>Zeige die permanente Easy Bar oben rechts im Easy-Modus an.</p>', 'wdeb');
	}

	function create_auto_enter_role_box () {
		global $wp_roles;
		/*
		$_roles = array (
			'administrator' => __('Site Admin'),
			'editor' => __('Editor'),
			'author' => __('Author'),
			'contributor' => __('Contributor'),
			'subscriber' => __('Subscriber'),
		);
		*/
		if (!isset($wp_roles)) $wp_roles = new WP_Roles();
		$_roles = $wp_roles->get_names();
		$roles = $this->_get_option('auto_enter_role');
		$roles = is_array($roles) ? $roles : array();

		foreach ($_roles as $role=>$label) {
			$checked = in_array($role, $roles) ? 'checked="checked"' : '';
			echo '' .
				"<input type='checkbox' name='wdeb[auto_enter_role][{$role}]' id='wdeb-auto_enter_role-{$role}' value='{$role}' {$checked} />" .
				' ' .
				"<label for='wdeb-auto_enter_role-{$role}'>{$label}</label>" .
			"<br />";
		}
		_e('<p>Benutzer mit ausgewählten Rollen müssen den einfachen Modus verwenden.</p>', 'wdeb');
	}

	function create_plugin_theme_box () {
    $themes_dir = apply_filters('wdeb_plugin_themes_dir', WDEB_PLUGIN_BASE_DIR . '/themes/');
    $themes_url = apply_filters('wdeb_plugin_themes_url', WDEB_PLUGIN_URL . '/themes/');

    if(function_exists( 'scandir' )) {
    $themes = scandir($themes_dir);
           } else {

        $themes = apply_filters('wdeb_plugin_themes_list', array(
			"default" => __("Standard %s", 'wdeb'),
			"stripes_red" => __("Streifen rot %s", 'wdeb'),
            "stripes_orange" => __("Streifen orange %s", 'wdeb'),
            "stripes_green" => __("Streifen grün %s", 'wdeb')
		));
            }

		foreach ($themes as $theme) {
        if ($theme == '.' || $theme == '..') {

            } else {

			$img = $themes_url . $theme . '/screenshot.png';
            echo "<label style='overflow: hidden; margin-bottom: 20px; float:left; width: 233px; height: 550px;' for='plugin_theme-{$theme}'>";
			echo $this->_create_radiobox('plugin_theme', $theme) . $theme . '<br />';
            echo "<img src='" . $img . "' />";
            echo "</label>";
            }
		}
	}

	function create_hijack_start_page_box () {
		echo $this->_create_checkbox('hijack_start_page');
		_e(
			'<p>Wenn diese Option auf "Ja" gesetzt ist, können neue Benutzer bei der ersten Anmeldung zwischen dem einfachen und dem erweiterten Modus wählen.</p>' .
			'<p>Ihre Auswahl wird ab diesem Zeitpunkt gespeichert und verwendet, solange diese Option aktiviert ist.</p>',
		'wdeb');
	}

	function create_show_logout_box () {
		echo $this->_create_checkbox('show_logout');
	}

	function create_logo_box () {
		$opts = new Wdeb_Options;
		$logo = $opts->get_logo();
		if ($logo) {
			printf (__("Aktuelles Logo:<br /> %s", 'wdeb'), "<img id='wdeb-logo-logo_output' src='{$logo}' /><br />");
			echo '<a href="#remove-logo" id="wdeb-logo-remove_logo">' . __('Logo zurücksetzen', 'wdeb') . '</a><br />';
		}
		echo "<input type='hidden' name='wdeb[wdeb_logo]' id='wdeb-logo-custom_logo' value='{$logo}' />";
		_e('Lade Dein eigenes Logo hoch:<br /><em>*geeignete Logo-Abmessung: Breite=150px Höhe=80px oder mehr</em><br />', 'wdeb');
		echo " <input type='file' name='wdeb_logo' />";

	}

	function create_dashboard_widget_box () {
		echo
			'<labeld for="show_dashboard_widget-yes">' . __('Dashboard-Widget anzeigen', 'wdeb') . '</label> ',
			$this->_create_checkbox('show_dashboard_widget'),
		'<br />';
		echo
			'<labeld for="widget_title">' . __('Widget-Titel', 'wdeb') . '</label> ',
			'<input type="text" class="widefat" id="widget_title" name="wdeb[widget_title]" value="' .
				stripslashes($this->_get_option('widget_title')) .
			'" />',
		'<br />';
		echo '<label for="widget_contents">' . __('Widget-Inhalt', 'wdeb') . '</label><br />';
		echo '<textarea id="widget_contents" class="widefat" rows="8" name="wdeb[widget_contents]">' .
			stripslashes($this->_get_option('widget_contents')) .
		'</textarea>';
	}

	function create_dashboard_right_now_widget_box () {
		echo $this->_create_checkbox('dashboard_right_now');
	}

/*** Tooltips ***/
	function create_show_tooltips_box () {
		echo $this->_create_checkbox('show_tooltips', 'wdeb_help');
	}

/*** Wizard ***/
	function create_wizard_enabled_box () {
		echo $this->_create_checkbox('wizard_enabled', 'wdeb_wizard');
	}

	function create_wizard_steps_box () {
		$opts = new Wdeb_Options;
		$steps = $opts->get_option('wizard_steps', 'wdeb_wizard');
		$steps = is_array($steps) ? $steps : array();

		echo "<ul id='wdeb_steps'>";
		$count = 1;
		foreach ($steps as $step) {
			echo '<li class="wdeb_step">' .
				'<h4>' .
					'<span class="wdeb_step_count">' . $count . '</span>' .
					':&nbsp;' .
					'<span class="wdeb_step_title">' . $step['title'] . '</span>' .
				'</h4>' .
				'<div class="wdeb_step_actions">' .
					'<a href="#" class="wdeb_step_delete">' . __('Löschen', 'wdeb') . '</a>' .
					'&nbsp;|&nbsp;' .
					'<a href="#" class="wdeb_step_edit">' . __('Bearbeiten', 'wdeb') . '</a>' .
				'</div>' .
				'<input type="hidden" class="wdeb_step_url" name="wdeb_wizard[wizard_steps][' . $count . '][url]" value="' . esc_url($step['url']) . '" />' .
				'<input type="hidden" class="wdeb_step_title" name="wdeb_wizard[wizard_steps][' . $count . '][title]" value="' . htmlspecialchars($step['title'], ENT_QUOTES) . '" />' .
				'<input type="hidden" class="wdeb_step_help" name="wdeb_wizard[wizard_steps][' . $count . '][help]" value="' . htmlspecialchars($step['help'], ENT_QUOTES) . '" />' .
			"</li>\n";
			$count++;
		}
		echo "</ul>";
		if ($opts->get_option('wizard_enabled', 'wdeb_wizard')) {
			_e('<p>Ziehe die Schritte per Drag&Drop, um sie in der gewünschten Reihenfolge zu sortieren.</p>', 'wdeb');
		} else {
			_e('<p>Aktiviere den Assistenten und ziehe die Schritte per Drag&Drop, um sie in der gewünschten Reihenfolge zu sortieren.</p>', 'wdeb');
		}
	}

	function create_wizard_add_step_box () {
		// URL
		echo '<label for="wdeb_last_wizard_step_url">' . __('URL:', 'wdeb') . '</label><br />';
		echo '<select id="wdeb_last_wizard_step_url_type" name="wdeb_wizard[wizard_steps][_last_][url_type]">';
		echo '<option value="/wp-admin">' . __('Verwaltungsseite (z.B. "/post-new.php" oder "/themes.php")', 'wdeb') . '&nbsp;</option>';
		echo '<option value="">' . __('Webseiten-Seite (z.B. "/" oder "/2007-06-05/ein-alter-beitrag")', 'wdeb') . '&nbsp;</option>';
		echo '</select> <span id="wdeb_url_preview">Vorschau: <code></code></span><br />';
		echo "<input type='text' class='widefat' id='wdeb_last_wizard_step_url' name='wdeb_wizard[wizard_steps][_last_][url]' /> <br />";

		// Title
		echo '<label for="wdeb_last_wizard_step_title">' . __('Titel:', 'wdeb') . '</label>';
		echo "<input type='text' class='widefat' id='wdeb_last_wizard_step_title' name='wdeb_wizard[wizard_steps][_last_][title]' /> <br />";

		// Help string
		echo '<label for="wdeb_last_wizard_step_help">' . __('Hilfe:', 'wdeb') . '</label>';
		echo "<textarea class='widefat' id='wdeb_last_wizard_step_help' name='wdeb_wizard[wizard_steps][_last_][help]'></textarea> <br />";

		echo "<input type='submit' value='" . __('Hinzufügen', 'wdeb') . "' />";
	}
}