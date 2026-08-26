<?php
class Wdeb_AdminFormRenderer {

	function _get_option ($key = false, $pfx = 'wdeb') {
		$opt = defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN ? get_site_option($pfx) : get_option($pfx);
		if (!$key) return $opt;
		return is_array($opt) ? $opt[$key] : null;
	}

	function _create_checkbox ($name, $pfx = 'wdeb') {
		$opt = $this->_get_option($name, $pfx);
		$value = $opt ?? null; // Use the null coalescing operator for PHP 7+
		return
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-yes' value='1' " . ((int)$value ? 'checked="checked" ' : '') . " /> " .
			"<label for='{$name}-yes'>" . __('JA', 'wdeb') . "</label>" .
			'&nbsp;' .
			"<input type='radio' name='{$pfx}[{$name}]' id='{$name}-no' value='0' " . (!(int)$value ? 'checked="checked" ' : '') . " /> " .
			"<label for='{$name}-no'>" . __('NEIN', 'wdeb') . "</label>" .
			"";
	}

	function _create_radiobox( $name, $value ) {
		$opt = $this->_get_option( $name );
		$checked = ( $opt == $value );

		return "<input type='radio' name='wdeb[{$name}]' id='{$name}-{$value}' value='{$value}' " . ( $checked ? 'checked="checked" ' : '' ) . ' /> ';
	}

	function create_metaboxes_posts_box () {
		$boxes = array (
			'postexcerpt' => __('Auszug', 'wdeb'),
			'postimagediv' => __('Beitragsbild', 'wdeb'),
			'trackbacksdiv' => __('Trackbacks senden', 'wdeb'),
			'postcustom' => __('Benutzerdefinierte Felder', 'wdeb'),
			'commentstatusdiv' => __('Diskussion', 'wdeb'),
			'slugdiv' => __('Slug', 'wdeb'),
			'authordiv' => __('Autor', 'wdeb'),
			'formatdiv' => __('Format', 'wdeb'),
			'categorydiv' => __('Kategorien', 'wdeb'),
			'tagsdiv-post_tag' => __('Beitragstags', 'wdeb'),
			'revisionsdiv' => __('Revisionen', 'wdeb'),
		);
		$opt = $this->_get_option('post_boxes');
		$opt = is_array($opt) ? $opt : array();
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo "<input type='hidden' name='wdeb[post_boxes][{$bid}]' value='0' />" .
				"<input {$checked} type='checkbox' name='wdeb[post_boxes][{$bid}]' value='{$bid}' id='wdeb_post_boxes_{$bid}' /> " .
				"<label for='wdeb_post_boxes_{$bid}'>{$label}</label><br />\n";
		}
		_e('<p><b>Achtung:</b> Alle anderen Boxen werden entsprechend ihren Bildschirmeinstellungen angezeigt oder ausgeblendet.</p>', 'wdeb');
	}

	function create_metaboxes_pages_box () {
		$boxes = array (
			'postcustom' => __('Benutzerdefinierte Felder', 'wdeb'),
			'postimagediv' => __('Beitragsbild', 'wdeb'),
			'commentstatusdiv' => __('Diskussion', 'wdeb'),
			'slugdiv' => __('Slug', 'wdeb'),
			'authordiv' => __('Autor', 'wdeb'),
			'pageparentdiv' => __('Seitenattribute', 'wdeb'),
		);
		$opt = $this->_get_option('page_boxes');
		$opt = is_array($opt) ? $opt : array();
		foreach ($boxes as $bid => $label) {
			$checked = in_array($bid, $opt) ? 'checked="checked"' : '';
			echo "<input type='hidden' name='wdeb[page_boxes][{$bid}]' value='0' />" .
				"<input type='checkbox' {$checked} name='wdeb[page_boxes][{$bid}]' value='{$bid}' id='wdeb_page_boxes_{$bid}' /> " .
				"<label for='wdeb_page_boxes_{$bid}'>{$label}</label><br />\n";
		}
		_e('<p><b>Achtung:</b> Alle anderen Boxen werden entsprechend ihren Bildschirmeinstellungen angezeigt oder ausgeblendet.</p>', 'wdeb');
	}

	function create_admin_bar_box () {
		echo $this->_create_checkbox('admin_bar');
		_e('<p>Zeige die WordPress-Admin-Leiste im Easy-Modus an.</p>', 'wdeb');
	}

	function create_screen_options_box () {
		echo $this->_create_checkbox('screen_options');
		_e('<p>Zeige kontextbezogene Hilfe und Bildschirmeinstellungen im Easy-Modus an.</p>', 'wdeb');
	}

	function create_easy_bar_box () {
		echo $this->_create_checkbox('easy_bar');
		_e('<p>Zeige die persistent oben rechts Easy Bar im Easy-Modus an.</p>', 'wdeb');
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
		_e('<p>Benutzer mit den ausgewählten Rollen werden gezwungen, den Easy-Modus zu verwenden.</p>', 'wdeb');
	}

	function create_plugin_theme_box() {
		$previews_url = WDEB_PLUGIN_URL . '/themes/default/previews/';

		$colors = array(
			'default' => __( 'Classic Blue', 'wdeb' ),
			'red'     => __( 'Red', 'wdeb' ),
			'orange'  => __( 'Orange', 'wdeb' ),
			'green'   => __( 'Green', 'wdeb' ),
		);

		$selected = $this->_get_option( 'plugin_theme' );

		if ( ! isset( $colors[ $selected ] ) ) {
			$selected = 'default';
		}

		echo '<div class="wdeb-theme-selector" style="display:flex; flex-direction:row; flex-wrap:nowrap; gap:20px; align-items:flex-start;">';

		foreach ( $colors as $color => $label ) {
			echo '<div class="wdeb-theme-choice" style="display:block; flex:0 0 233px; width:233px;">';

			echo '<div class="wdeb-theme-choice-header">';
			echo '<input type="radio" name="wdeb[plugin_theme]" id="plugin_theme-' . esc_attr( $color ) . '" value="' . esc_attr( $color ) . '" ' . checked( $selected, $color, false ) . ' />';
			echo '<label for="plugin_theme-' . esc_attr( $color ) . '">' . esc_html( $label ) . '</label>';
			echo '</div>';

			echo '<img src="' . esc_url( $previews_url . $color . '.png' ) . '" alt="' . esc_attr( $label ) . '" />';

			echo '</div>';
		}

		echo '</div>';
	}

	function create_hijack_start_page_box () {
		echo $this->_create_checkbox('hijack_start_page');
		_e(
			'<p>Wenn auf "Ja" gesetzt, erlaubt diese Option neuen Benutzern, beim ersten Anmelden zwischen Easy- und Advanced-Modus zu wählen.</p>' .
			'<p>Deine Wahl wird gespeichert und ab diesem Zeitpunkt verwendet, solange diese Option aktiviert ist.</p>',
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
		_e('Eigenes Logo hochladen:<br /><em>*geeignete Logodimension: Breite=150px Höhe=80px oder mehr</em><br />', 'wdeb');
		echo " <input type='file' name='wdeb_logo' />";

	}

	function create_dashboard_widget_box () {
		echo
			'<labeld for="show_dashboard_widget-yes">' . __('Zeige Dashboard-Widget', 'wdeb') . '</label> ',
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
			_e('<p>Ziehe die Schritte per Drag & Drop, um sie in der gewünschten Reihenfolge zu sortieren.</p>', 'wdeb');
		} else {
			_e('<p>Aktiviere den Assistenten, um die Schritte per Drag & Drop in der gewünschten Reihenfolge zu sortieren.</p>', 'wdeb');
		}
	}

	function create_wizard_add_step_box () {
		// URL
		echo '<label for="wdeb_last_wizard_step_url">' . __('URL:', 'wdeb') . '</label><br />';
		echo '<select id="wdeb_last_wizard_step_url_type" name="wdeb_wizard[wizard_steps][_last_][url_type]">';
		echo '<option value="/wp-admin">' . __('Dashboard-Seite (z. B. "/post-new.php" oder "/themes.php")', 'wdeb') . '&nbsp;</option>';
		echo '<option value="">' . __('Seite der Webseite (z. B. "/" oder "/2007-06-05/ein-alter-beitrag")', 'wdeb') . '&nbsp;</option>';
		echo '</select> <span id="wdeb_url_preview">' . __('Vorschau:', 'wdeb') . ' <code></code></span><br />';
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
