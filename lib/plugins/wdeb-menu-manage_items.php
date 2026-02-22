<?php
/*
Plugin Name: Menüelemente verwalten
Description: Verwalte ganz einfach Menüelemente in Deinem Easy Blogging-Menü.
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.2
Author: PSOURCE
*/

class Wdeb_Menu_ManageMenuItems {

	private $_data;

	private function __construct () {
		$this->_data = new Wdeb_Options;
	}

	public static function serve () {
		$me = new Wdeb_Menu_ManageMenuItems;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		// Add ressources
		add_action('admin_print_scripts', array($this, 'js_add_scripts'));
		add_action('admin_print_styles', array($this, 'css_add_styles'));

		// Add page
		add_action('wdeb_admin-add_pages', array($this, 'register_page'));

		// Add settings
		add_action('wdeb_admin-register_settings-settings', array($this, 'add_settings'));
		add_filter('wdeb_admin-options_changed', array($this, 'save_settings'));

		// Actual filtering
		add_filter('wdeb_menu_items', array($this, 'filter_menu_builtins'), 0);
		add_filter('wdeb_menu_items', array($this, 'filter_menu_items'), 999);

		// AJAX handlers
		add_action('wp_ajax_wdeb_menu_items_remove_my_item', array($this, 'json_remove_my_item'));
		add_action('wp_ajax_wdeb_menu_items_reset_order', array($this, 'json_reset_order'));
		add_action('wp_ajax_wdeb_menu_items_reset_items', array($this, 'json_reset_items'));
		add_action('wp_ajax_wdeb_menu_items_reset_all', array($this, 'json_reset_all'));

		add_action('admin_init', array($this, 'dispatch_default_type'));
	}

	function dispatch_default_type () {
		if (!is_admin() && !is_network_admin()) return true;
		if (empty($_GET['wdeb_source'])) return true;
		if ('easy_blogging-new_menu_item' !== trim($_GET['wdeb_source'])) return false;

		add_filter('pre_option_image_default_link_type', function() {return "file";});
	}


/* ---------- Filtering ---------- */


	/**
	 * Mark builtins.
	 */
	function filter_menu_builtins ($items) {
		foreach ($items as $idx => $item) {
			$item['_builtin'] = true;
			$items[$idx] = $item;
		}
		return $items;
	}

	/**
	 * Applies menu ordering, adding, showing and hiding.
	 */
	function filter_menu_items ($items) {
		// Add new items
		$new_items = $this->_data->get_options('wdeb_menu_items');
		$new_items = isset($new_items['new_items']) ? $new_items['new_items'] : array();
		foreach ($new_items as $item) {
			$item['check_callback'] = false;
			$item['_added'] = true;
			$items[] = $item;
		}

		// Reorder items
		$items = $this->_reorder_items($items);

		// Filter items
		if (
			!isset($_GET['page']) ||
			(isset($_GET['page']) && 'wdeb_menu_items' != $_GET['page']) // but not on settings page
		) {
			$my_menu = $this->_data->get_options('wdeb_menu_items');
			$my_menu = isset($my_menu['my_menu']) ? $my_menu['my_menu'] : array();
			if (!$my_menu) return $items;

			$filtered = array();
			foreach ($items as $item) {
				$url_id = $this->_item_to_id($item);
				if (!in_array($url_id, array_keys($my_menu))) continue;
				$filtered[] = $item;
			}
			$items = $filtered;
		}
		return $items;
	}

	/**
	 * Removes new menu item.
	 */
	function json_remove_my_item () {
		$status = false;
		$id = $_POST['url_id'] ?? null;
		if ($id) {
			$opts = $this->_data->get_options('wdeb_menu_items');
			$new_items = isset($opts['new_items']) ? $opts['new_items'] : array();
			foreach ($new_items as $idx => $item) {
				$item['_added'] = true;
				if ($id == $this->_item_to_id($item)) unset($new_items[$idx]);
			}
			$opts['new_items'] = array_filter($new_items);
			$this->_data->set_options($opts, 'wdeb_menu_items');
			$status = true;
		}
		header('Content-type: application/json');
		echo json_encode(array(
			'status' => (int)$status,
		));
		exit();
	}


/* ---------- JSON handlers ---------- */


	/**
	 * Resets items custom order.
	 */
	function json_reset_order () {
		$opts = $this->_data->get_options('wdeb_menu_items');
		$opts['order'] = array();
		$this->_data->set_options($opts, 'wdeb_menu_items');

		header('Content-type: application/json');
		echo json_encode(array(
			'status' => 1,
		));
		exit();
	}

	/**
	 * Resets any new items.
	 */
	function json_reset_items () {
		$opts = $this->_data->get_options('wdeb_menu_items');
		$opts['new_items'] = array();
		$this->_data->set_options($opts, 'wdeb_menu_items');

		header('Content-type: application/json');
		echo json_encode(array(
			'status' => 1,
		));
		exit();
	}

	/**
	 * Resets everything.
	 */
	function json_reset_all () {
		$this->_data->set_options(array(), 'wdeb_menu_items');

		header('Content-type: application/json');
		echo json_encode(array(
			'status' => 1,
		));
		exit();
	}


/* ---------- User Interface ---------- */


	function register_page ($perms) {
		add_submenu_page('wdeb', __('Menüpunkte', 'wdeb'), __('Menüpunkte', 'wdeb'), $perms, 'wdeb_menu_items', array($this, 'render_page'));
	}

	function render_page () {
		echo '<div class="wrap"><h2>Easy Blogging Menü</h2>';
		echo (defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN
			? '<form action="settings.php" method="post" enctype="multipart/form-data">'
			: '<form action="options.php" method="post" enctype="multipart/form-data">'
		);
		settings_fields('wdeb_menu_items');
		do_settings_sections('wdeb_menu_items');
		echo '<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . __('Änderungen speichern') . '" /></p>';
		echo '</form></div>';
	}

	function add_settings () {
		register_setting('wdeb', 'wdeb_menu_items');
		add_settings_section('wdeb_menu_items', __('Assistenteneinstellungen', 'wdeb'), function() {return;}, 'wdeb_menu_items');
		add_settings_field('wdeb_show_items', __('Menüelemente ein- oder ausblenden<br/><small>(Zum Neuordnen per Drag & Drop verschieben)</small>', 'wdeb'), array($this, 'create_show_hide_box'), 'wdeb_menu_items', 'wdeb_menu_items');
		add_settings_field('wdeb_add_item', __('Menüpunkt hinzufügen', 'wdeb'), array($this, 'create_add_item_box'), 'wdeb_menu_items', 'wdeb_menu_items');
		add_settings_field('wdeb_resets', __('Zurücksetzen', 'wdeb'), array($this, 'create_resets_box'), 'wdeb_menu_items', 'wdeb_menu_items');
	}

	function save_settings ($changed) {
		if ('wdeb_menu_items' == ($_POST['option_page'] ?? '')) {
			if (isset($_POST['wdeb_menu_items']['new_items']['new'])) {
				$last = $_POST['wdeb_menu_items']['new_items']['new'];
				unset($_POST['wdeb_menu_items']['new_items']['new']);
				if (trim($last['url'] ?? '') && trim($last['title'] ?? '')) {
					$last['title'] = stripslashes(htmlspecialchars($last['title'], ENT_QUOTES));
					$last['help'] = stripslashes(htmlspecialchars($last['help'] ?? '', ENT_QUOTES));
					$last['icon'] = stripslashes(htmlspecialchars($last['icon'] ?? '', ENT_QUOTES));
					$last['url'] = esc_url($last['url']);
					$last['capability'] = trim(stripslashes(htmlspecialchars($last['capability'] ?? '', ENT_QUOTES)));
					if ($this->_is_unique_item($last, $_POST['wdeb_menu_items']['new_items'])) {
						// Item is unique. Yay.
						$_POST['wdeb_menu_items']['new_items'][] = $last;
					}
				}
			}
			if (isset($_POST['wdeb_menu_items']['new_items'])) {
				$_POST['wdeb_menu_items']['new_items'] = array_filter($_POST['wdeb_menu_items']['new_items']);
				$_POST['wdeb_menu_items']['new_items'] = array_map('stripslashes_deep', $_POST['wdeb_menu_items']['new_items']);
			}
			$this->_data->set_options($_POST['wdeb_menu_items'], 'wdeb_menu_items');
			$changed = true;
		}
		return $changed;
	}

	function create_show_hide_box () {
		if (!defined('WDEB_PLUGIN_THEME_URL')) {
			$theme = $this->_data->get_option('plugin_theme');
			$theme = $theme ? $theme : 'default';
			define('WDEB_PLUGIN_THEME_URL', WDEB_PLUGIN_URL . '/themes/' . $theme);
		}

		$menu_items = apply_filters('wdeb_initialize_menu', array());
		$menu_items = apply_filters('wdeb_menu_items', $menu_items);

		$opts = $this->_data->get_options('wdeb_menu_items');
			$my_menu = isset($opts['my_menu']) ? $opts['my_menu'] : array();

		echo "<p>";
		echo "	<a href='#check_all' class='wdeb_check_all_items'>" . __('Alle auswählen', 'wdeb') . '</a>';
		echo "	&nbsp;|&nbsp;";
		echo "	<a href='#uncheck_all' class='wdeb_uncheck_all_items'>" . __('Alle abwählen', 'wdeb') . '</a>';
		echo "</p>";
		echo "<table id='wdeb_show_hide_root' class='widefat'>";
		foreach (array('thead', 'tfoot') as $part) {
			echo "<{$part}>";
			echo '<th width="5%">' . __('Zeige', 'wdeb') . '</th>';
			echo '<th>' . __('Element', 'wdeb') . '</th>';
			echo '<th width="25%">' . __('URL', 'wdeb') . '</th>';
			echo '<th width="20%">' . __('Berechtigung', 'wdeb') . '</th>';
			echo '<th width="10%">' . __('Typ', 'wdeb') . '</th>';
			echo '<th width="5%">' . __('Entfernen', 'wdeb') . '</th>';
			echo "</{$part}>\n";
		}
		echo "<tbody>\n";
		foreach ($menu_items as $item) {
			$url_id = $this->_item_to_id($item);
			if ($my_menu) {
				$checked = in_array($url_id, array_keys($my_menu)) ? 'checked="checked"' : '';
			} else $checked = 'checked="checked"';
			echo "<tr >";
			echo "<td width='5%'>";
			echo "	<input type='checkbox' name='wdeb_menu_items[my_menu][{$url_id}]' value='1' {$checked} />";
			echo "	<input type='hidden' class='wdeb_menu_items-url_id' name='wdeb_menu_items[order][]' value='{$url_id}' />";
			echo "</td>";
			echo '<td>';
			echo '	<img style="float:left; margin-right:10px; width:32px; height: 32px;" src="' . $item['icon'] . '">';
			echo '	<div><b>' . $item['title'] . '</b></div>';
			echo '	<div>' . $item['help'] . '</div>';
			echo '	<div style="clear:both"></div>';
			echo '</td>';
			echo "<td width='25%'>" . esc_url($item['url']) . "</td>";
			echo "<td width='10%'>" . ($item['capability'] ? $item['capability'] : '-') . "</td>";
			echo "<td width='10%'>";
			echo (isset($item['_builtin'])
				? __('Built-in', 'wdeb')
				: (isset($item['_added']) ? __('Mein Element', 'wdeb') : __('Plugin hinzugefügt', 'wdeb'))
			);
			echo "</td>";
			echo '<td width="5%">';
			if (isset($item['_added'])) {
				echo '<a href="#remove_item" class="wdeb_remove_menu_item">' . __('Entfernen', 'wdeb') . '</a>';
			}
			echo '</td>';
			echo "</tr>\n";
		}
		echo "</tbody>";
		echo "</table>";
		echo "<p>";
		echo "	<a href='#check_all' class='wdeb_check_all_items'>" . __('Alle auswählen', 'wdeb') . '</a>';
		echo "	&nbsp;|&nbsp;";
		echo "	<a href='#uncheck_all' class='wdeb_uncheck_all_items'>" . __('Alle abwählen', 'wdeb') . '</a>';
		echo "</p>";
	}

	function create_add_item_box () {
		$new_items = $this->_data->get_options('wdeb_menu_items');
		$new_items = isset($new_items['new_items']) ? $new_items['new_items'] : array();
		foreach ($new_items as $key=>$item) {
			echo "<input type='hidden' name='wdeb_menu_items[new_items][{$key}][title]' value='" . esc_attr($item['title']) . "' />";
			echo "<input type='hidden' name='wdeb_menu_items[new_items][{$key}][url]' value='" . esc_url($item['url']) . "' />";
			echo "<input type='hidden' name='wdeb_menu_items[new_items][{$key}][icon]' value='" . esc_url($item['icon']) . "' />";
			echo "<input type='hidden' name='wdeb_menu_items[new_items][{$key}][help]' value='" . esc_attr($item['help']) . "' />";
			echo "<input type='hidden' name='wdeb_menu_items[new_items][{$key}][capability]' value='" . esc_attr($item['capability']) . "' />";
		}
		echo '' .
			'<label for="wdeb_menu_items-new-title">' . __('Titel', 'wdeb') . '</label> ' .
			"<input type='text' class='widefat' id='wdeb_menu_items-new-title' name='wdeb_menu_items[new_items][new][title]' value='' />" .
		"<br />";
		echo '' .
			'<label for="wdeb_menu_items-new-url">' . __('URL', 'wdeb') . '</label> ' .
			"<input type='text' class='widefat' id='wdeb_menu_items-new-url' name='wdeb_menu_items[new_items][new][url]' value='' />" .
		"<br />";
		echo '' .
			'<label for="wdeb_menu_items-new-icon">' . __('Symbol', 'wdeb') . '</label> ' .
			"<input type='hidden' class='widefat' id='wdeb_menu_items-new-icon' name='wdeb_menu_items[new_items][new][icon]' value='' />" .
			"<div><a href='#choose_icon' id='wdeb_menu_items-new-icon-trigger'>" . __('Symbol auswählen', 'wdeb') . '</a></div>' .
			'<div id="wdeb_menu_items-new-icon-target"></div>' .
		"<br />";
		echo '' .
			'<label for="wdeb_menu_items-new-help">' . __('Hilfe', 'wdeb') . '</label> ' .
			"<input type='text' class='widefat' id='wdeb_menu_items-new-help' name='wdeb_menu_items[new_items][new][help]' value='' />" .
		"<br />";
		/*
		echo '' .
			'<label for="wdeb_menu_items-new-capability">' . __('Capability', 'wdeb') . '</label> ' .
			"<input type='text' class='widefat' id='wdeb_menu_items-new-capability' name='wdeb_menu_items[new_items][new][capability]' value='' />" .
		"<br />";
		*/
		global $wp_roles;
		$_roles = array (
			'administrator' => 'manage_options',
			'editor' => 'edit_others_posts',
			'author' => 'upload_files',
			'contributor' => 'edit_posts',
			'subscriber' => 'read',
		);
		echo '<label for="wdeb_menu_items-new-capability">' . __('Diesen Menüeintrag anzeigen für:', 'wdeb') . '</label> ';
		echo "<select id='wdeb_menu_items-new-capability' name='wdeb_menu_items[new_items][new][capability]'>";
		foreach ($wp_roles->roles as $key => $role) {
			$title = sprintf(__('Nur %s', 'wdeb'), $role['name']);
			$capability = $key;
			if (isset($_roles[$key])) {
				$title = sprintf(__('%s und höher'), $role['name']);
				$capability = $_roles[$key];
			}
			echo "<option value='{$capability}'>{$title}&nbsp;</option>";
		}
		echo "</select> ";
		echo "<a href='#enter_capability' id='wdeb_menu_items-manual_capability'>" . __('... oder gib die Funktion manuell ein', 'wdeb') . '</a>';
		echo "<br />";
		
		echo '<input type="submit" class="button" value="' . esc_attr(__('Neues Element hinzufügen', 'wdeb')) . '" />';
		
		echo '<div>' .
			'<p>' . __('Du kannst diese Makros in Deinen URLs verwenden:', 'wdeb') . '</p>' .
			'<dl>' .
				'<dt>BLOG_PATH</dt>' .
				'<dd>' . __('Dein aktueller Blog-Pfad', 'wdeb') . '</dd>' .
				'<dt>LOGOUT_URL</dt>' .
				'<dd>' . __('Eine saubere Abmelde-URL', 'wdeb') . '</dd>' .
			'</dl>' .
			'<p>' . __('Beispiele für "Mediathek":', 'wdeb') . '</p>' .
			'<dl>' .
				'<dt>https://BLOG_PATH/MEINE-DOMAIN/wp-admin/upload.php</dt>' .
				'<dd>' . __('Pfad zur Mediathek bei Subdomains', 'wdeb') . '</dd>' .
				'<dt>https://MEINE-DOMAIN/BLOG_PATH/wp-admin/upload.php</dt>' .
				'<dd>' . __('Pfad zur Mediathek bei Subverzeichnis', 'wdeb') . '</dd>' .
			'</dl>' .
		'</div>';
	}

	function create_resets_box () {
		echo '<p>' . __('Verwende die folgenden Schaltflächen, um einige Aspekte Deiner Anpassung auf ihre Standardeinstellungen zurückzusetzen', 'wdeb') . '</p>';
		echo '<input type="button" id="wdeb_menu_items-reset_order" value="' . esc_attr(__('Menüreihenfolge zurücksetzen', 'wdeb')) . '" />';
		echo '&nbsp;';
		echo '<input type="button" id="wdeb_menu_items-reset_items" value="' . esc_attr(__('Neue Menüpunkte zurücksetzen', 'wdeb')) . '" />';
		echo '&nbsp;';
		echo '<input type="button" id="wdeb_menu_items-reset_all" value="' . esc_attr(__('Alles zurücksetzen', 'wdeb')) . '" />';
	}

	function js_add_scripts () {
		if (!isset($_GET['page']) || 'wdeb_menu_items' != $_GET['page']) return false;
		wp_enqueue_script( array("jquery", "jquery-ui-core", "jquery-ui-sortable", 'jquery-ui-dialog') );
		wp_enqueue_script('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script("wdeb_menu_items", WDEB_PLUGIN_URL . '/js/wdeb-menu-items.js', array('jquery'));
		wp_localize_script('wdeb_menu_items', 'l10nMenuItems', array(
			"reset_order_confirmation" => __('Warnung: Dadurch werden alle Deine benutzerdefinierten Bestellungen entfernt und auf die Standardeinstellungen zurückgesetzt. Fortsetzen?', 'wdeb'),
			"reset_items_confirmation" => __('Warnung: Dadurch werden alle neuen Menüelemente entfernt, die Du hinzugefügt hast. Fortsetzen?', 'wdeb'),
			"reset_all_confirmation" => __('Warnung: Dadurch werden alle Anpassungen entfernt. Fortsetzen?', 'wdeb'),
		));
		printf(
			'<script type="text/javascript">
				var _wdeb_menu_items = {
					"admin_base": "%s",
					"ajax_url": "%s",
				};
			</script>',
			admin_url(), admin_url('admin-ajax.php')
		);
	}

	function css_add_styles () {
		if (!isset($_GET['page']) || 'wdeb_menu_items' != $_GET['page']) return false;
		wp_enqueue_style('thickbox');
	}


/* ---------- Private API ---------- */


	/**
	 * Generates items unique ID used in most checks.
	 */
	private function _item_to_id ($item) {
		$builtin = isset($item['_builtin']) ? 1 : 0;
		$added = isset($item['_added']) ? 1 : 0;
		return md5(
			($item['title'] ?? '') .
			($item['url'] ?? '') .
			($item['help'] ?? '') .
			($item['capability'] ?? '') .
			($item['check_callback'] ?? '') .
			$builtin . $added
		);
	}

	/**
	 * Reorders menu items.
	 */
	private function _reorder_items ($items) {
		$items = array_values($items);
		$opts = $this->_data->get_options('wdeb_menu_items');
		$order = @$opts['order'] ? $opts['order'] : array();
		if (!$order) return $items;

		$ordered = array();
		foreach ($order as $oid=>$ord) {
			foreach ($items as $item) {
				$item_id = $this->_item_to_id($item);
				if ($ord == $item_id) {
					$ordered[] = $item;
					break;
				}
			}
		}

		//return $ordered + $items;
		$leftover = array();
		foreach ($items as $item) {
			if (!in_array($item, $ordered)) $ordered[] = $item;
		}
		return $ordered;
	}

	/**
	 * Checks if an item is unique in a collection
	 */
	private function _is_unique_item ($new, $items) {
		$uid = $this->_item_to_id($new);
		foreach ($items as $item) {
			if ($uid == $this->_item_to_id($item)) return false;
		}
		return true;
	}
}

if (is_admin()) Wdeb_Menu_ManageMenuItems::serve();