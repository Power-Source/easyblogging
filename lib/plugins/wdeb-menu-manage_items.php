<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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

		// AJAX handlers with nonce for client-side use
		add_action('admin_enqueue_scripts', array($this, 'enqueue_nonce'));
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
		// Security: Verify nonce and capabilities
		check_ajax_referer('wdeb_menu_action', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'wdeb')));
		}
		
		$status = false;
		$id = sanitize_text_field($_POST['url_id'] ?? '');
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
		wp_send_json_success(array('status' => (int)$status));
	}

	/* ---------- JSON handlers ---------- */


	/**
	 * Resets items custom order.
	 */
	function json_reset_order () {
		// Security: Verify nonce and capabilities
		check_ajax_referer('wdeb_menu_action', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'wdeb')));
		}
		
		$opts = $this->_data->get_options('wdeb_menu_items');
		$opts['order'] = array();
		$this->_data->set_options($opts, 'wdeb_menu_items');

		header('Content-type: application/json');
		wp_send_json_success(array('status' => 1));
	}

	/**
	 * Resets any new items.
	 */
	function json_reset_items () {
		// Security: Verify nonce and capabilities
		check_ajax_referer('wdeb_menu_action', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'wdeb')));
		}
		
		$opts = $this->_data->get_options('wdeb_menu_items');
		$opts['new_items'] = array();
		$this->_data->set_options($opts, 'wdeb_menu_items');

		header('Content-type: application/json');
		wp_send_json_success(array('status' => 1));
	}

	/**
	 * Resets everything.
	 */
	function json_reset_all () {
		// Security: Verify nonce and capabilities
		check_ajax_referer('wdeb_menu_action', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'wdeb')));
		}
		
		$this->_data->set_options(array(), 'wdeb_menu_items');

		header('Content-type: application/json');
		wp_send_json_success(array('status' => 1));
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

		echo "<div style='margin-bottom: 15px;'>";
		echo "	<a href='#check_all' class='wdeb_check_all_items'>" . __('Alle auswählen', 'wdeb') . '</a>';
		echo "	&nbsp;|&nbsp;";
		echo "	<a href='#uncheck_all' class='wdeb_uncheck_all_items'>" . __('Alle abwählen', 'wdeb') . '</a>';
		echo "	<small style='color: #999; margin-left: 15px;'>" . __('💡 Zum Sortieren hier klicken und ziehen', 'wdeb') . '</small>';
		echo "</div>";
		echo "<table id='wdeb_show_hide_root' class='widefat sortable'>";
		foreach (array('thead', 'tfoot') as $part) {
			echo "<{$part}>";
			echo '<th width="2%">&nbsp;</th>';
			echo '<th width="3%">' . __('Zeige', 'wdeb') . '</th>';
			echo '<th>' . __('Element', 'wdeb') . '</th>';
			echo '<th width="25%">' . __('URL', 'wdeb') . '</th>';
			echo '<th width="20%">' . __('Berechtigung', 'wdeb') . '</th>';
			echo '<th width="10%">' . __('Typ', 'wdeb') . '</th>';
			echo '<th width="5%">' . __('Aktion', 'wdeb') . '</th>';
			echo "</{$part}>\n";
		}
		echo "<tbody>\n";
		foreach ($menu_items as $item) {
			$url_id = $this->_item_to_id($item);
			if ($my_menu) {
				$checked = in_array($url_id, array_keys($my_menu)) ? 'checked="checked"' : '';
			} else $checked = 'checked="checked"';
			echo "<tr data-id='{$url_id}'>";
			echo "<td width='2%' style='text-align: center; color: #ccc;'>";
			echo "	<span class='wdeb-drag-handle'></span>";
			echo "</td>";
			echo "<td width='3%'>";
			echo "	<input type='checkbox' name='wdeb_menu_items[my_menu][{$url_id}]' value='1' {$checked} />";
			echo "	<input type='hidden' class='wdeb_menu_items-url_id' name='wdeb_menu_items[order][]' value='{$url_id}' />";
			echo "</td>";
			echo '<td style="vertical-align: middle;">';
			echo '	<div style="display: flex; align-items: center; gap: 10px;">';
			echo '		<img src="' . esc_attr($item['icon']) . '" style="width: 32px; height: 32px; border-radius: 3px;">';
			echo '		<div>';
			echo '			<div style="font-weight: 600; color: #222; margin-bottom: 2px;">' . esc_html($item['title']) . '</div>';
			if (!empty($item['help'])) {
				echo '			<div style="font-size: 12px; color: #999;">' . esc_html($item['help']) . '</div>';
			}
			echo '		</div>';
			echo '	</div>';
			echo '</td>';
			echo "<td width='25%' style='font-family: monospace; font-size: 12px; color: #666;'>" . esc_url($item['url']) . "</td>";
			echo "<td width='20%'><small>" . ($item['capability'] ? esc_html($item['capability']) : '—') . "</small></td>";
			echo "<td width='10%'><small>";
			echo (isset($item['_builtin'])
				? '<span style="background: #e7f3ff; color: #0073aa; padding: 2px 6px; border-radius: 2px;">' . __('Built-in', 'wdeb') . '</span>'
				: (isset($item['_added']) 
					? '<span style="background: #fff8e5; color: #856404; padding: 2px 6px; border-radius: 2px;">' . __('Benutzerdefiniert', 'wdeb') . '</span>'
					: '<span style="background: #f0f0f0; color: #666; padding: 2px 6px; border-radius: 2px;">' . __('Plugin', 'wdeb') . '</span>')
			);
			echo "</small></td>";
			echo '<td width="5%" style="text-align: center;">';
			if (isset($item['_added'])) {
				echo '<a href="#remove_item" class="wdeb_remove_menu_item" style="color: #dc3545; font-size: 12px; text-decoration: none;">' . __('✕', 'wdeb') . '</a>';
			}
			echo '</td>';
			echo "</tr>\n";
		}
		echo "</tbody>";
		echo "</table>";
		echo "<div style='margin-top: 15px; text-align: right;'>";
		echo "	<small style='color: #999;'>" . __('ℹ️ Drag & Drop zum Sortieren verwenden oder Check/Uncheck oben', 'wdeb') . '</small>';
		echo "</div>";
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
		
		echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">';
		
		// Left column
		echo '<div>';
		echo '<div style="margin-bottom: 15px;">';
		echo '	<label for="wdeb_menu_items-new-title" style="display: block; font-weight: 600; margin-bottom: 6px; color: #555;">' . __('Titel *', 'wdeb') . '</label>';
		echo "	<input type='text' class='widefat' id='wdeb_menu_items-new-title' name='wdeb_menu_items[new_items][new][title]' placeholder='" . __('z.B. Meine Custom Seite', 'wdeb') . "' value='' style='padding: 10px; border: 1px solid #ddd; border-radius: 3px;' />";
		echo '</div>';
		
		echo '<div style="margin-bottom: 15px;">';
		echo '	<label for="wdeb_menu_items-new-url" style="display: block; font-weight: 600; margin-bottom: 6px; color: #555;">' . __('URL *', 'wdeb') . '</label>';
		echo "	<input type='text' class='widefat' id='wdeb_menu_items-new-url' name='wdeb_menu_items[new_items][new][url]' placeholder='" . __('https://example.com oder admin.php?page=custom', 'wdeb') . "' value='' style='padding: 10px; border: 1px solid #ddd; border-radius: 3px;' />";
		echo '</div>';
		
		echo '<div style="margin-bottom: 15px;">';
		echo '	<label for="wdeb_menu_items-new-help" style="display: block; font-weight: 600; margin-bottom: 6px; color: #555;">' . __('Beschreibung', 'wdeb') . '</label>';
		echo "	<input type='text' class='widefat' id='wdeb_menu_items-new-help' name='wdeb_menu_items[new_items][new][help]' placeholder='" . __('Kurze Hilfe für diesen Menüpunkt', 'wdeb') . "' value='' style='padding: 10px; border: 1px solid #ddd; border-radius: 3px;' />";
		echo '</div>';
		echo '</div>';
		
		// Right column
		echo '<div>';
		echo '<div style="margin-bottom: 15px;">';
		echo '	<label for="wdeb_menu_items-new-icon" style="display: block; font-weight: 600; margin-bottom: 6px; color: #555;">' . __('Symbol auswählen *', 'wdeb') . '</label>';
		echo "	<input type='hidden' class='widefat' id='wdeb_menu_items-new-icon' name='wdeb_menu_items[new_items][new][icon]' value='' />";
		echo "	<a href='#choose_icon' id='wdeb_menu_items-new-icon-trigger' class='button' style='display: inline-block;'>" . __('🖼️ Symbol auswählen', 'wdeb') . '</a>';
		echo '	<div id="wdeb_menu_items-new-icon-target" style="margin-top: 10px;"></div>';
		echo '</div>';
		
		global $wp_roles;
		$_roles = array (
			'administrator' => 'manage_options',
			'editor' => 'edit_others_posts',
			'author' => 'upload_files',
			'contributor' => 'edit_posts',
			'subscriber' => 'read',
		);
		
		echo '<div style="margin-bottom: 15px;">';
		echo '<label for="wdeb_menu_items-new-capability" style="display: block; font-weight: 600; margin-bottom: 6px; color: #555;">' . __('Sichtbar für:', 'wdeb') . '</label>';
		echo "<select id='wdeb_menu_items-new-capability' name='wdeb_menu_items[new_items][new][capability]' style='width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px;'>";
		foreach ($wp_roles->roles as $key => $role) {
			$title = sprintf(__('Nur %s', 'wdeb'), $role['name']);
			$capability = $key;
			if (isset($_roles[$key])) {
				$title = sprintf(__('%s und höher'), $role['name']);
				$capability = $_roles[$key];
			}
			echo "<option value='{$capability}'>{$title}</option>";
		}
		echo "</select>";
		echo '<small style="display: block; margin-top: 6px; color: #999;">';
		echo '	<a href="#enter_capability" id="wdeb_menu_items-manual_capability" style="color: #0073aa; text-decoration: none;">' . __('Benutzerdefinierte Fähigkeit (Capability) eingeben', 'wdeb') . '</a>';
		echo '</small>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 3px; margin-bottom: 20px; border-left: 4px solid #0073aa;">';
		echo '<p style="margin: 0 0 10px 0; color: #555;"><strong>' . __('💡 Verfügbare Makros für URLs:', 'wdeb') . '</strong></p>';
		echo '<ul style="margin: 0; padding-left: 20px; color: #999; font-size: 12px;">';
		echo '<li><code>BLOG_PATH</code> — ' . __('Dein aktueller Blog-Pfad', 'wdeb') . '</li>';
		echo '<li><code>LOGOUT_URL</code> — ' . __('Sichere Abmelde-URL', 'wdeb') . '</li>';
		echo '</ul>';
		echo '</div>';
		
		echo '<input type="submit" class="button button-primary" value="' . esc_attr(__('✚ Neuen Menüpunkt hinzufügen', 'wdeb')) . '" />';
	}

	function create_resets_box () {
		echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 3px; padding: 12px; margin-bottom: 15px;">';
		echo '<strong style="color: #856404;">⚠️ ' . __('Achtung:', 'wdeb') . '</strong>';
		echo '<p style="margin: 6px 0 0 0; font-size: 13px; color: #856404;">' . __('Diese Aktionen können nicht rückgängig gemacht werden. Verwende sie mit Bedacht.', 'wdeb') . '</p>';
		echo '</div>';
		
		echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">';
		
		echo '<button type="button" id="wdeb_menu_items-reset_order" class="button" style="background: #ffc107; border-color: #ffc107; color: #333; font-weight: 600;">' . __('↺ Menüreihenfolge zurücksetzen', 'wdeb') . '</button>';
		
		echo '<button type="button" id="wdeb_menu_items-reset_items" class="button" style="background: #dc3545; border-color: #dc3545; color: white; font-weight: 600;">' . __('✕ Benutzerdefinierte Menüpunkte löschen', 'wdeb') . '</button>';
		
		echo '<button type="button" id="wdeb_menu_items-reset_all" class="button" style="background: #6c757d; border-color: #6c757d; color: white; font-weight: 600;">' . __('↻ Alles zurücksetzen', 'wdeb') . '</button>';
		
		echo '</div>';
	}

	function js_add_scripts () {
		if (!isset($_GET['page']) || 'wdeb_menu_items' != $_GET['page']) return false;
		wp_enqueue_script('thickbox');
		wp_enqueue_script('media-upload');
		// Load SortableJS library via footer for compatibility with Easy Mode
		add_action('wp_footer', array($this, 'load_sortablejs'));
	}

	function load_sortablejs () {
		wp_enqueue_script('sortablejs', 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', array(), '1.15.0', true);
		wp_enqueue_script('wdeb_menu_items', WDEB_PLUGIN_URL . '/js/wdeb-menu-items.js', array('sortablejs'), '2.0.0-modern', true);
		wp_localize_script('wdeb_menu_items', 'wdebMenuData', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'admin_base' => admin_url(),
			'nonce' => wp_create_nonce('wdeb_menu_action'),
			'l10n' => array(
				'reset_order_confirmation' => __('Warnung: Dadurch werden alle Deine benutzerdefinierten Bestellungen entfernt und auf die Standardeinstellungen zurückgesetzt. Fortsetzen?', 'wdeb'),
				'reset_items_confirmation' => __('Warnung: Dadurch werden alle neuen Menüelemente entfernt, die Du hinzugefügt hast. Fortsetzen?', 'wdeb'),
				'reset_all_confirmation' => __('Warnung: Dadurch werden alle Anpassungen entfernt. Fortsetzen?', 'wdeb'),
			)
		));
	}

	function css_add_styles () {
		if (!isset($_GET['page']) || 'wdeb_menu_items' != $_GET['page']) return false;
		wp_enqueue_style('thickbox');
		wp_enqueue_style('wdeb_menu_items_styles', WDEB_PLUGIN_URL . '/css/wdeb-menu-items.css');
	}

	function enqueue_nonce () {
		// Nonce is now localized in load_sortablejs()
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