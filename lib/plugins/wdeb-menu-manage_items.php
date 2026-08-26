<?php
/*
Plugin Name: Menüeinträge verwalten
Description: Verwalte Menüeinträge in deinem PS Easy Blogging Menü einfach.
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0.1
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
		if ( ! is_admin() && ! is_network_admin() ) {
			return true;
		}

		if ( empty( $_GET['wdeb_source'] ) ) {
			return true;
		}

		if ( 'easy_blogging-new_menu_item' !== trim( $_GET['wdeb_source'] ) ) {
			return false;
		}

		add_filter(
			'pre_option_image_default_link_type',
			static function () {
				return 'file';
			}
		);
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
		$new_items = is_array($new_items) && !empty($new_items['new_items']) ? $new_items['new_items'] : array();
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
			$my_menu = is_array($my_menu) && !empty($my_menu['my_menu']) ? $my_menu['my_menu'] : array();
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
	 * Removes a custom menu item.
	 */
	function json_remove_my_item() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );

		$id = isset( $_POST['url_id'] ) ? sanitize_text_field( wp_unslash( $_POST['url_id'] ) ) : '';

		if ( '' === $id ) {
			wp_send_json_error(
				array( 'message' => __( 'Es wurde kein Menüeintrag angegeben.', 'wdeb' ) ),
				400
			);
		}

		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$opts = is_array( $opts ) ? $opts : array();

		$new_items = isset( $opts['new_items'] ) && is_array( $opts['new_items'] )
			? $opts['new_items']
			: array();

		foreach ( $new_items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$item['_added'] = true;

			if ( $id === $this->_item_to_id( $item ) ) {
				unset( $new_items[ $index ] );
				break;
			}
		}

		$opts['new_items'] = array_values( $new_items );

		$this->_data->set_options( $opts, 'wdeb_menu_items' );

		wp_send_json_success(
			array(
				'status' => true,
			)
		);
	}


/* ---------- JSON handlers ---------- */


	/**
	 * Resets custom menu order.
	 */
	function json_reset_order() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );

		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$opts = is_array( $opts ) ? $opts : array();

		$opts['order'] = array();

		$this->_data->set_options( $opts, 'wdeb_menu_items' );

		wp_send_json_success(
			array(
				'status' => true,
			)
		);
	}

	/**
	 * Removes all custom menu items.
	 */
	function json_reset_items() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );

		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$opts = is_array( $opts ) ? $opts : array();

		$opts['new_items'] = array();

		$this->_data->set_options( $opts, 'wdeb_menu_items' );

		wp_send_json_success(
			array(
				'status' => true,
			)
		);
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
		add_submenu_page('wdeb', __('Menüeinträge', 'wdeb'), __('Menüeinträge', 'wdeb'), $perms, 'wdeb_menu_items', array($this, 'render_page'));
	}

	function render_page () {
		echo '<div class="wrap"><h2>Easy Blogging Menü</h2>';
		echo (defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN
			? '<form action="settings.php" method="post" enctype="multipart/form-data">'
			: '<form action="options.php" method="post" enctype="multipart/form-data">'
		);
		settings_fields('wdeb_menu_items');
		do_settings_sections('wdeb_menu_items');
		echo '<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . __('Änderungen speichern', 'wdeb') . '" /></p>';
		echo '</form></div>';
	}

	function add_settings () {
		register_setting( 'wdeb', 'wdeb_menu_items' );

		add_settings_section(
			'wdeb_menu_items',
			__( 'Assistent Einstellungen', 'wdeb' ),
			static function () {},
			'wdeb_menu_items'
		);

		add_settings_field(
			'wdeb_show_items',
			__( 'Anzeigen oder Ausblenden von Menüeinträgen<br/><small>(Per Drag-and-Drop neu anordnen)</small>', 'wdeb' ),
			array( $this, 'create_show_hide_box' ),
			'wdeb_menu_items',
			'wdeb_menu_items'
		);

		add_settings_field(
			'wdeb_add_item',
			__( 'Menüpunkt hinzufügen', 'wdeb' ),
			array( $this, 'create_add_item_box' ),
			'wdeb_menu_items',
			'wdeb_menu_items'
		);

		add_settings_field(
			'wdeb_resets',
			__( 'Zurücksetzen', 'wdeb' ),
			array( $this, 'create_resets_box' ),
			'wdeb_menu_items',
			'wdeb_menu_items'
		);
	}

	/**
	 * Saves menu item settings.
	 *
	 * @param bool $changed Whether settings have already changed.
	 * @return bool
	 */
	function save_settings( $changed ) {
		if ( ! isset( $_POST['option_page'] ) || 'wdeb_menu_items' !== $_POST['option_page'] ) {
			return $changed;
		}

		$posted = isset( $_POST['wdeb_menu_items'] ) && is_array( $_POST['wdeb_menu_items'] )
			? wp_unslash( $_POST['wdeb_menu_items'] )
			: array();

		$new_items = isset( $posted['new_items'] ) && is_array( $posted['new_items'] )
			? $posted['new_items']
			: array();

		// Process a newly added menu item.
		if ( isset( $new_items['new'] ) && is_array( $new_items['new'] ) ) {
			$new_item = $new_items['new'];
			unset( $new_items['new'] );

			$title = isset( $new_item['title'] ) ? sanitize_text_field( $new_item['title'] ) : '';
			$url   = isset( $new_item['url'] ) ? esc_url_raw( $new_item['url'] ) : '';

			if ( '' !== $title && '' !== $url ) {
				$item = array(
					'title'      => $title,
					'url'        => $url,
					'help'       => isset( $new_item['help'] )
						? sanitize_text_field( $new_item['help'] )
						: '',
					'icon'       => isset( $new_item['icon'] )
						? esc_url_raw( $new_item['icon'] )
						: '',
					'capability' => isset( $new_item['capability'] )
						? sanitize_text_field( $new_item['capability'] )
						: '',
				);

				if ( $this->_is_unique_item( $item, $new_items ) ) {
					$new_items[] = $item;
				}
			}
		}

		// Remove empty entries and normalize indexes.
		$new_items = array_values(
			array_filter(
				$new_items,
				'is_array'
			)
		);

		$posted['new_items'] = $new_items;

		$this->_data->set_options( $posted, 'wdeb_menu_items' );

		return true;
	}

	/**
	 * Displays the menu item visibility and ordering controls.
	 */
	function create_show_hide_box() {
		if ( ! defined( 'WDEB_PLUGIN_THEME_URL' ) ) {
			$theme = $this->_data->get_option( 'plugin_theme' );
			$theme = $theme ? $theme : 'default';

			define(
				'WDEB_PLUGIN_THEME_URL',
				WDEB_PLUGIN_URL . '/themes/' . $theme
			);
		}

		$menu_items = apply_filters( 'wdeb_initialize_menu', array() );
		$menu_items = apply_filters( 'wdeb_menu_items', $menu_items );

		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$opts = is_array( $opts ) ? $opts : array();

		$my_menu = isset( $opts['my_menu'] ) && is_array( $opts['my_menu'] )
			? $opts['my_menu']
			: array();

		echo '<p>';
		echo '<a href="#check_all" class="wdeb_check_all_items">' . esc_html__( 'Alle wählen', 'wdeb' ) . '</a>';
		echo ' &nbsp;|&nbsp; ';
		echo '<a href="#uncheck_all" class="wdeb_uncheck_all_items">' . esc_html__( 'Alle abwählen', 'wdeb' ) . '</a>';
		echo '</p>';

		echo '<table id="wdeb_show_hide_root" class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col" style="width: 3%;">&nbsp;</th>';
		echo '<th scope="col" style="width: 5%;">' . esc_html__( 'Zeigen', 'wdeb' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Element', 'wdeb' ) . '</th>';
		echo '<th scope="col" style="width: 25%;">' . esc_html__( 'URL', 'wdeb' ) . '</th>';
		echo '<th scope="col" style="width: 15%;">' . esc_html__( 'Berechtigung', 'wdeb' ) . '</th>';
		echo '<th scope="col" style="width: 10%;">' . esc_html__( 'Typ', 'wdeb' ) . '</th>';
		echo '<th scope="col" style="width: 5%;">' . esc_html__( 'Entfernen', 'wdeb' ) . '</th>';
		echo '</tr>';
		echo '</thead>';

		echo '<tbody>';

		foreach ( $menu_items as $item ) {
			$url_id = $this->_item_to_id( $item );

			$checked = empty( $my_menu ) || array_key_exists( $url_id, $my_menu );

			$title      = isset( $item['title'] ) ? $item['title'] : '';
			$help       = isset( $item['help'] ) ? $item['help'] : '';
			$icon       = isset( $item['icon'] ) ? $item['icon'] : '';
			$url        = isset( $item['url'] ) ? $item['url'] : '';
			$capability = isset( $item['capability'] ) ? $item['capability'] : '';

			echo '<tr data-id="' . esc_attr( $url_id ) . '">';

			// Drag handle.
			echo '<td class="wdeb-sort-handle" style="text-align: center;">';
			echo '<span class="wdeb-drag-handle" title="' . esc_attr__( 'Zum Neuanordnen ziehen', 'wdeb' ) . '" aria-hidden="true">☰</span>';
			echo '</td>';

			// Visibility.
			echo '<td>';
			echo '<input type="checkbox" name="wdeb_menu_items[my_menu][' . esc_attr( $url_id ) . ']" value="1"' . checked( $checked, true, false ) . '>';
			echo '<input type="hidden" class="wdeb_menu_items-url_id" name="wdeb_menu_items[order][]" value="' . esc_attr( $url_id ) . '">';
			echo '</td>';

			// Item.
			echo '<td>';
			echo '<div style="display:flex; align-items:center; gap:10px;">';

			if ( $icon ) {
				echo '<img src="' . esc_url( $icon ) . '" alt="" style="width:32px;height:32px;">';
			}

			echo '<div>';
			echo '<strong>' . esc_html( $title ) . '</strong>';

			if ( $help ) {
				echo '<div class="description">' . esc_html( $help ) . '</div>';
			}

			echo '</div>';
			echo '</div>';
			echo '</td>';

			// URL.
			echo '<td>';
			echo esc_html( $url );
			echo '</td>';

			// Capability.
			echo '<td>';
			echo $capability ? esc_html( $capability ) : '&mdash;';
			echo '</td>';

			// Type.
			echo '<td>';

			if ( isset( $item['_builtin'] ) ) {
				echo esc_html__( 'Eingebaut', 'wdeb' );
			} elseif ( isset( $item['_added'] ) ) {
				echo esc_html__( 'Mein Element', 'wdeb' );
			} else {
				echo esc_html__( 'Vom Plugin hinzugefügt', 'wdeb' );
			}

			echo '</td>';

			// Remove.
			echo '<td>';

			if ( isset( $item['_added'] ) ) {
				echo '<a href="#remove_item" class="wdeb_remove_menu_item">';
				echo esc_html__( 'Entfernen', 'wdeb' );
				echo '</a>';
			}

			echo '</td>';

			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		echo '<p>';
		echo '<a href="#check_all" class="wdeb_check_all_items">' . esc_html__( 'Alle wählen', 'wdeb' ) . '</a>';
		echo ' &nbsp;|&nbsp; ';
		echo '<a href="#uncheck_all" class="wdeb_uncheck_all_items">' . esc_html__( 'Alle abwählen', 'wdeb' ) . '</a>';
		echo '</p>';
	}

	/**
	 * Displays the form for adding a custom menu item.
	 */
	function create_add_item_box() {
		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$opts = is_array( $opts ) ? $opts : array();

		$new_items = isset( $opts['new_items'] ) && is_array( $opts['new_items'] )
			? $opts['new_items']
			: array();

		// Preserve existing custom menu items in the settings form.
		foreach ( $new_items as $key => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			echo '<input type="hidden" name="wdeb_menu_items[new_items][' . esc_attr( $key ) . '][title]" value="' . esc_attr( $item['title'] ?? '' ) . '">';
			echo '<input type="hidden" name="wdeb_menu_items[new_items][' . esc_attr( $key ) . '][url]" value="' . esc_attr( $item['url'] ?? '' ) . '">';
			echo '<input type="hidden" name="wdeb_menu_items[new_items][' . esc_attr( $key ) . '][icon]" value="' . esc_attr( $item['icon'] ?? '' ) . '">';
			echo '<input type="hidden" name="wdeb_menu_items[new_items][' . esc_attr( $key ) . '][help]" value="' . esc_attr( $item['help'] ?? '' ) . '">';
			echo '<input type="hidden" name="wdeb_menu_items[new_items][' . esc_attr( $key ) . '][capability]" value="' . esc_attr( $item['capability'] ?? '' ) . '">';
		}

		?>

		<p>
			<label for="wdeb_menu_items-new-title">
				<?php esc_html_e( 'Titel', 'wdeb' ); ?>
			</label>
			<input
				type="text"
				class="widefat"
				id="wdeb_menu_items-new-title"
				name="wdeb_menu_items[new_items][new][title]"
				value=""
			>
		</p>

		<p>
			<label for="wdeb_menu_items-new-url">
				<?php esc_html_e( 'URL', 'wdeb' ); ?>
			</label>
			<input
				type="url"
				class="widefat"
				id="wdeb_menu_items-new-url"
				name="wdeb_menu_items[new_items][new][url]"
				value=""
			>
		</p>

		<p>
			<label for="wdeb_menu_items-new-icon">
				<?php esc_html_e( 'Icon', 'wdeb' ); ?>
			</label>

			<input
				type="hidden"
				id="wdeb_menu_items-new-icon"
				name="wdeb_menu_items[new_items][new][icon]"
				value=""
			>

			<a href="#choose_icon" id="wdeb_menu_items-new-icon-trigger">
				<?php esc_html_e( 'Icon wählen', 'wdeb' ); ?>
			</a>

			<div id="wdeb_menu_items-new-icon-target"></div>
		</p>

		<p>
			<label for="wdeb_menu_items-new-help">
				<?php esc_html_e( 'Hilfe', 'wdeb' ); ?>
			</label>
			<input
				type="text"
				class="widefat"
				id="wdeb_menu_items-new-help"
				name="wdeb_menu_items[new_items][new][help]"
				value=""
			>
		</p>

		<?php

		global $wp_roles;

		$role_capabilities = array(
			'administrator' => 'manage_options',
			'editor'        => 'edit_others_posts',
			'author'        => 'upload_files',
			'contributor'   => 'edit_posts',
			'subscriber'    => 'read',
		);

		?>

		<p>
			<label for="wdeb_menu_items-new-capability">
				<?php esc_html_e( 'Dieses Menüelement anzeigen für:', 'wdeb' ); ?>
			</label>

			<select
				id="wdeb_menu_items-new-capability"
				name="wdeb_menu_items[new_items][new][capability]"
			>
				<?php foreach ( $wp_roles->roles as $key => $role ) : ?>

					<?php
					$title      = sprintf( __( 'Nur %s', 'wdeb' ), $role['name'] );
					$capability = $key;

					if ( isset( $role_capabilities[ $key ] ) ) {
						$title      = sprintf( __( '%s und höher', 'wdeb' ), $role['name'] );
						$capability = $role_capabilities[ $key ];
					}
					?>

					<option value="<?php echo esc_attr( $capability ); ?>">
						<?php echo esc_html( $title ); ?>
					</option>

				<?php endforeach; ?>
			</select>

			<a href="#enter_capability" id="wdeb_menu_items-manual_capability">
				<?php esc_html_e( '... oder die Berechtigung manuell eingeben', 'wdeb' ); ?>
			</a>
		</p>

		<p>
			<input
				type="submit"
				class="button"
				value="<?php echo esc_attr__( 'Neues Element hinzufügen', 'wdeb' ); ?>"
			>
		</p>

		<div>
			<p>
				<?php esc_html_e( 'Du kannst diese Makros in Deinen URLs verwenden:', 'wdeb' ); ?>
			</p>

			<dl>
				<dt><code>BLOG_PATH</code></dt>
				<dd><?php esc_html_e( 'Dein aktueller Blog-Pfad', 'wdeb' ); ?></dd>

				<dt><code>LOGOUT_URL</code></dt>
				<dd><?php esc_html_e( 'Eine saubere Logout-URL', 'wdeb' ); ?></dd>
			</dl>
		</div>

		<?php
	}

	function create_resets_box () {
		echo '<p>' . __('Verwende die untenstehenden Schaltflächen, um einige Aspekte Deiner Anpassungen auf die Standardeinstellungen zurückzusetzen.', 'wdeb') . '</p>';
		echo '<input type="button" id="wdeb_menu_items-reset_order" value="' . esc_attr(__('Menüreihenfolge zurücksetzen', 'wdeb')) . '" />';
		echo '&nbsp;';
		echo '<input type="button" id="wdeb_menu_items-reset_items" value="' . esc_attr(__('Neue Menüelemente zurücksetzen', 'wdeb')) . '" />';
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
			"reset_order_confirmation" => __('Warnung: Dies wird alle deine benutzerdefinierten Reihenfolgen entfernen und auf die Standardeinstellungen zurücksetzen. Fortfahren?', 'wdeb'),
			"reset_items_confirmation" => __('Warnung: Dies wird alle neuen Menüelemente entfernen, die du hinzugefügt hast. Fortfahren?', 'wdeb'),
			"reset_all_confirmation" => __('Warnung: Dies wird alle deine Anpassungen entfernen. Fortfahren?', 'wdeb'),
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
	 * Generates an item's unique ID used for internal checks.
	 *
	 * @param array $item Menu item.
	 * @return string
	 */
	private function _item_to_id( $item ) {
		$data = array(
			isset( $item['title'] ) ? $item['title'] : '',
			isset( $item['url'] ) ? $item['url'] : '',
			isset( $item['help'] ) ? $item['help'] : '',
			isset( $item['capability'] ) ? $item['capability'] : '',
			isset( $item['check_callback'] ) ? $item['check_callback'] : '',
			isset( $item['_builtin'] ),
			isset( $item['_added'] ),
		);

		return md5( serialize( $data ) );
	}

	/**
	 * Reorders menu items.
	 */
	private function _reorder_items( $items ) {
		$items = array_values( $items );

		$opts = $this->_data->get_options( 'wdeb_menu_items' );
		$order = is_array( $opts ) && !empty( $opts['order'] )
			? $opts['order']
			: array();

		if ( empty( $order ) ) {
			return $items;
		}

		$ordered = array();

		foreach ( $order as $oid => $ord ) {
			foreach ( $items as $item ) {
				$item_id = $this->_item_to_id( $item );

				if ( $ord == $item_id ) {
					$ordered[] = $item;
					break;
				}
			}
		}

		foreach ( $items as $item ) {
			if ( !in_array( $item, $ordered, true ) ) {
				$ordered[] = $item;
			}
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