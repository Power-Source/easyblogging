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
		$value = $opt ?? null;
		$checked = (int)$value ? 'checked' : '';
		return sprintf(
			'<label class="wdeb-toggle-switch">
				<input type="hidden" name="%s[%s]" value="0" />
				<input type="checkbox" name="%s[%s]" value="1" %s />
				<span class="wdeb-toggle-slider"></span>
			</label>',
			$pfx,
			$name,
			$pfx,
			$name,
			$checked
		);
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
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Beitrags-Meta-Felder', 'wdeb') . '</label><small>' . __('Blende diese Felder auf den Seiten "Beitrag bearbeiten" aus', 'wdeb') . '</small></div>';
		echo '<div class="wdeb-form-control">';
		echo '<div class="wdeb-checkbox-list">';
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo '<div class="wdeb-checkbox-item">';
			echo "<input type='hidden' name='wdeb[post_boxes][{$bid}]' value='0' />";
			echo "<input {$checked} type='checkbox' name='wdeb[post_boxes][{$bid}]' value='{$bid}' id='wdeb_post_boxes_{$bid}' />";
			echo "<label for='wdeb_post_boxes_{$bid}'>{$label}</label>";
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="wdeb-alert wdeb-alert-info">';
		echo '<strong>' . __('Info:', 'wdeb') . '</strong> ';
		echo __('Alle anderen Felder werden entsprechend ihren Bildschirmeinstellungen ein- oder ausgeblendet.', 'wdeb');
		echo '</div>';
		echo '</div></div>';
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
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Seiten-Meta-Felder', 'wdeb') . '</label><small>' . __('Blende diese Felder auf den Seiten "Seite bearbeiten" aus', 'wdeb') . '</small></div>';
		echo '<div class="wdeb-form-control">';
		echo '<div class="wdeb-checkbox-list">';
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo '<div class="wdeb-checkbox-item">';
			echo "<input type='hidden' name='wdeb[page_boxes][{$bid}]' value='0' />";
			echo "<input type='checkbox' {$checked} name='wdeb[page_boxes][{$bid}]' value='{$bid}' id='wdeb_page_boxes_{$bid}' />";
			echo "<label for='wdeb_page_boxes_{$bid}'>{$label}</label>";
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="wdeb-alert wdeb-alert-info">';
		echo '<strong>' . __('Info:', 'wdeb') . '</strong> ';
		echo __('Alle anderen Felder werden entsprechend ihren Bildschirmeinstellungen ein- oder ausgeblendet.', 'wdeb');
		echo '</div>';
		echo '</div></div>';
	}

	function create_admin_bar_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Admin-Leiste anzeigen', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('admin_bar');
		echo '<p>' . __('Zeige die ClassicPress-Admin-Leiste im einfachen Modus an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

	function create_screen_options_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Bildschirmoptionen anzeigen', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('screen_options');
		echo '<p>' . __('Zeige im einfachen Modus kontextbezogene Hilfe- und Bildschirmoptionen an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

	function create_easy_bar_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Easy Bar anzeigen', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('easy_bar');
		echo '<p>' . __('Zeige die permanente Easy Bar oben rechts im Easy-Modus an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

	function create_auto_enter_role_box () {
		global $wp_roles;
		if (!isset($wp_roles)) $wp_roles = new WP_Roles();
		$_roles = $wp_roles->get_names();
		$roles = $this->_get_option('auto_enter_role');
		$roles = is_array($roles) ? $roles : array();

		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Erzwingte Easy Mode Rollen', 'wdeb') . '</label><small>' . __('Benutzer mit ausgewählten Rollen müssen den einfachen Modus verwenden', 'wdeb') . '</small></div>';
		echo '<div class="wdeb-form-control">';
		echo '<div class="wdeb-checkbox-list">';
		foreach ($_roles as $role=>$label) {
			$checked = in_array($role, $roles) ? 'checked="checked"' : '';
			echo '<div class="wdeb-checkbox-item">';
			echo "<input type='checkbox' name='wdeb[auto_enter_role][{$role}]' id='wdeb-auto_enter_role-{$role}' value='{$role}' {$checked} />";
			echo "<label for='wdeb-auto_enter_role-{$role}'>{$label}</label>";
			echo '</div>';
		}
		echo '</div>';
		echo '</div></div>';
	}

	function create_plugin_theme_box () {
		$themes_dir = apply_filters('wdeb_plugin_themes_dir', WDEB_PLUGIN_BASE_DIR . '/themes/');
		$themes_url = apply_filters('wdeb_plugin_themes_url', WDEB_PLUGIN_URL . '/themes/');

		if(function_exists('scandir')) {
			$themes = scandir($themes_dir);
		} else {
			$themes = apply_filters('wdeb_plugin_themes_list', array(
				"default" => __("Standard %s", 'wdeb'),
				"stripes_red" => __("Streifen rot %s", 'wdeb'),
				"stripes_orange" => __("Streifen orange %s", 'wdeb'),
				"stripes_green" => __("Streifen grün %s", 'wdeb')
			));
		}

		$current_theme = $this->_get_option('plugin_theme');

		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Plugin-Theme', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo '<div class="wdeb-theme-gallery">';

		foreach ($themes as $theme) {
			if (in_array($theme, array('.', '..'))) {
				continue;
			}

			$img = $themes_url . $theme . '/screenshot.png';
			$is_selected = ($current_theme == $theme) ? 'selected' : '';

			echo '<label class="wdeb-theme-item ' . $is_selected . '" for="plugin_theme-' . esc_attr($theme) . '">';
			echo '<input type="radio" name="wdeb[plugin_theme]" id="plugin_theme-' . esc_attr($theme) . '" value="' . esc_attr($theme) . '" ' . ($current_theme == $theme ? 'checked' : '') . ' />';
			echo '<div class="wdeb-theme-screenshot">';
			echo '<img src="' . esc_url($img) . '" alt="' . esc_attr($theme) . '" />';
			echo '</div>';
			echo '<div class="wdeb-theme-name">' . esc_html($theme) . '</div>';
			echo '</label>';
		}

		echo '</div>';
		echo '</div></div>';
	}

	function create_hijack_start_page_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Startseite nach Login', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('hijack_start_page');
		echo '<p>' . __('Wenn aktiviert, können neue Benutzer bei der ersten Anmeldung zwischen dem einfachen und dem erweiterten Modus wählen.', 'wdeb') . '</p>';
		echo '<p>' . __('Ihre Auswahl wird ab diesem Zeitpunkt gespeichert und verwendet, solange diese Option aktiviert ist.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

	function create_show_logout_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Abmelden-Link anzeigen', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('show_logout');
		echo '<p>' . __('Zeige den Abmelden-Link im Easy-Modus an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

	function create_logo_box () {
		$opts = new Wdeb_Options;
		$logo = $opts->get_logo();
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Plugin-Logo', 'wdeb') . '</label><small>' . __('Empfohlene Größe: 150x80px oder größer', 'wdeb') . '</small></div>';
		echo '<div class="wdeb-form-control">';
		wp_nonce_field('wdeb_logo_upload', 'wdeb_logo_nonce');
		if ($logo) {
			echo '<div class="wdeb-logo-preview">';
			printf('<img src="%s" alt="Logo" />', esc_url($logo));
			echo '</div>';
			echo '<div class="wdeb-logo-actions">';
			echo '<a href="#remove-logo" id="wdeb-logo-remove_logo">' . __('Logo zurücksetzen', 'wdeb') . '</a>';
			echo '</div>';
		}
		echo '<input type="hidden" name="wdeb[wdeb_logo]" id="wdeb-logo-custom_logo" value="' . esc_url($logo) . '" />';
		echo '<div class="wdeb-file-input-wrapper">';
		echo '<input type="file" name="wdeb_logo" id="wdeb_logo_file" />';
		echo '<label for="wdeb_logo_file" class="wdeb-file-input-label">' . __('Logo hochladen', 'wdeb') . '</label>';
		echo '</div>';
		echo '</div></div>';
	}

	function create_dashboard_widget_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Dashboard-Widget', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('show_dashboard_widget');
		echo '<p>' . __('Zeige ein Dashboard-Widget mit benutzerdefinierten Inhalten an.', 'wdeb') . '</p>';
		echo '</div></div>';

		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label for="widget_title">' . __('Widget-Titel', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo '<input type="text" class="widefat" id="widget_title" name="wdeb[widget_title]" value="' .
			esc_attr(stripslashes($this->_get_option('widget_title'))) .
		'" />';
		echo '</div></div>';

		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label for="widget_contents">' . __('Widget-Inhalt', 'wdeb') . '</label><small>' . __('HTML wird unterstützt', 'wdeb') . '</small></div>';
		echo '<div class="wdeb-form-control">';
		echo '<textarea id="widget_contents" class="widefat" rows="8" name="wdeb[widget_contents]">' .
			esc_textarea(stripslashes($this->_get_option('widget_contents'))) .
		'</textarea>';
		echo '</div></div>';
	}

	function create_dashboard_right_now_widget_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Dashboard "Right Now" Widget', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('dashboard_right_now');
		echo '<p>' . __('Zeige das "Right Now" Dashboard-Widget an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

/*** Tooltips ***/
	function create_show_tooltips_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Tooltips anzeigen', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('show_tooltips', 'wdeb_help');
		echo '<p>' . __('Zeige kontextbezogene Tooltips im Easy-Modus an.', 'wdeb') . '</p>';
		echo '</div></div>';
	}

/*** Wizard ***/
	function create_wizard_enabled_box () {
		echo '<div class="wdeb-form-group">';
		echo '<div class="wdeb-form-label"><label>' . __('Assistent aktivieren', 'wdeb') . '</label></div>';
		echo '<div class="wdeb-form-control">';
		echo $this->_create_checkbox('wizard_enabled', 'wdeb_wizard');
		echo '<p>' . __('Aktiviere den Einrichtungsassistenten für neue Benutzer.', 'wdeb') . '</p>';
		echo '</div></div>';
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