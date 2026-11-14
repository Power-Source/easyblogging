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
		add_action('admin_menu', array($this, 'register_main_site_network_menu_page'), 99);
		add_action('admin_menu', array($this, 'register_site_menu_page'), 99);

		// Add settings
		add_action('wdeb_admin-register_settings-settings', array($this, 'add_settings'));
		add_filter('wdeb_admin-options_changed', array($this, 'save_settings'));
		add_action('admin_post_wdeb_save_menu_items', array($this, 'handle_standalone_menu_save'));

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

	private function get_global_menu_options() {
		$options = $this->_data->get_options( 'wdeb_menu_items' );
		return is_array( $options ) ? $options : array();
	}

	private function site_menu_overrides_enabled() {
		if (
			! Wdeb_Options::is_network_activated()
			|| is_network_admin()
			|| ! is_multisite()
			|| is_main_site()
		) {
			return false;
		}

		$options = $this->get_global_menu_options();
		return ! empty( $options['allow_site_overrides'] );
	}

	private function get_stored_menu_options() {
		if ( $this->site_menu_overrides_enabled() ) {
			$options = get_option( 'wdeb_menu_items' );
			return is_array( $options ) ? $options : array();
		}

		return $this->get_global_menu_options();
	}

	private function get_effective_menu_options() {
		$global_options = $this->get_global_menu_options();

		if ( ! $this->site_menu_overrides_enabled() ) {
			return $global_options;
		}

		$local_options = get_option( 'wdeb_menu_items' );
		if ( ! is_array( $local_options ) ) {
			return $global_options;
		}

		$effective_options = $global_options;
		if ( array_key_exists( 'order', $local_options ) ) {
			$effective_options['order'] = $local_options['order'];
		}
		$effective_options['site_hidden_menu'] = isset( $local_options['hidden_menu'] ) && is_array( $local_options['hidden_menu'] )
			? $local_options['hidden_menu']
			: array();

		$global_items = isset( $global_options['new_items'] ) && is_array( $global_options['new_items'] )
			? $global_options['new_items']
			: array();
		$local_items = isset( $local_options['new_items'] ) && is_array( $local_options['new_items'] )
			? $local_options['new_items']
			: array();
		$effective_options['new_items'] = array_merge( $global_items, $local_items );

		return $effective_options;
	}

	private function save_menu_options( $options, $context = 'current' ) {
		if ( 'site' === $context || ( 'current' === $context && $this->site_menu_overrides_enabled() ) ) {
			return update_option( 'wdeb_menu_items', $options );
		}

		return $this->_data->set_options( $options, 'wdeb_menu_items' );
	}

	private function can_manage_menu_settings() {
		if (
			is_network_admin()
			|| (
				Wdeb_Options::is_network_activated()
				&& is_multisite()
				&& is_main_site()
			)
		) {
			return current_user_can( 'manage_network_options' );
		}

		if ( Wdeb_Options::is_network_activated() && ! $this->site_menu_overrides_enabled() ) {
			return false;
		}

		return current_user_can( 'manage_options' );
	}

	function register_main_site_network_menu_page() {
		if (
			! Wdeb_Options::is_network_activated()
			|| ! is_multisite()
			|| is_network_admin()
			|| ! is_main_site()
		) {
			return;
		}

		add_options_page(
			__( 'Easy Blogging Menü', 'wdeb' ),
			__( 'Easy Blogging Menü', 'wdeb' ),
			'manage_network_options',
			'wdeb_network_menu_items',
			array( $this, 'render_network_menu_page' )
		);
	}

	function register_site_menu_page() {
		if ( ! $this->site_menu_overrides_enabled() ) {
			return;
		}

		add_options_page(
			__( 'Easy Blogging Menü dieser Website', 'wdeb' ),
			__( 'Easy Blogging Menü', 'wdeb' ),
			'manage_options',
			'wdeb_site_menu_items',
			array( $this, 'render_site_menu_page' )
		);
	}

	function render_network_menu_page() {
		if (
			! Wdeb_Options::is_network_activated()
			|| ! is_multisite()
			|| ! is_main_site()
			|| ! current_user_can( 'manage_network_options' )
		) {
			wp_die( esc_html__( 'Du bist nicht berechtigt, diese Einstellungen zu bearbeiten.', 'wdeb' ), '', array( 'response' => 403 ) );
		}

		$this->render_standalone_page( 'network' );
	}

	function render_site_menu_page() {
		if ( ! $this->site_menu_overrides_enabled() || ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Du bist nicht berechtigt, diese Einstellungen zu bearbeiten.', 'wdeb' ), '', array( 'response' => 403 ) );
		}

		$this->render_standalone_page( 'site' );
	}

	private function render_standalone_page( $context ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Easy Blogging Menü', 'wdeb' ) . '</h1>';
		if ( 'site' === $context ) {
			echo '<p>' . esc_html__( 'Netzwerkvorgaben bleiben erhalten. Änderungen auf dieser Seite gelten nur für diese Subsite.', 'wdeb' ) . '</p>';
		} else {
			echo '<p>' . esc_html__( 'Diese Menüpunkte gelten netzwerkweit. Plugin-Menüs werden anhand des Mainblogs ausgewählt.', 'wdeb' ) . '</p>';
		}
		echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" enctype="multipart/form-data">';
		echo '<input type="hidden" name="action" value="wdeb_save_menu_items">';
		echo '<input type="hidden" name="wdeb_menu_context" value="' . esc_attr( $context ) . '">';
		wp_nonce_field( 'wdeb_save_menu_items_' . $context );
		$this->create_show_hide_box();
		$this->create_add_item_box();
		$this->create_resets_box();
		echo '<p class="submit"><input type="submit" class="button-primary" value="' . esc_attr__( 'Änderungen speichern', 'wdeb' ) . '"></p>';
		echo '</form></div>';
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
		$global_options = $this->get_global_menu_options();
		$local_options = $this->site_menu_overrides_enabled()
			? $this->get_stored_menu_options()
			: array();
		$options = $this->get_effective_menu_options();
		$global_items = isset( $global_options['new_items'] ) && is_array( $global_options['new_items'] )
			? $global_options['new_items']
			: array();

		foreach ($global_items as $item) {
			$item['check_callback'] = false;
			$item['_added'] = true;
			$item['_network_added'] = true;
			$items[] = $item;
		}

		$local_items = isset( $local_options['new_items'] ) && is_array( $local_options['new_items'] )
			? $local_options['new_items']
			: array();
		foreach ( $local_items as $item ) {
			$item['check_callback'] = false;
			$item['_added'] = true;
			$item['_site_added'] = true;
			$items[] = $item;
		}

		// Reorder items
		$items = $this->_reorder_items($items, $options);

		// Filter items
		if (
			!isset($_GET['page']) ||
			!in_array(
				$_GET['page'],
				array( 'wdeb_menu_items', 'wdeb_network_menu_items', 'wdeb_site_menu_items' ),
				true
			)
		) {
			$my_menu = isset( $global_options['my_menu'] ) && is_array( $global_options['my_menu'] )
				? $global_options['my_menu']
				: null;
			$site_hidden_menu = isset( $options['site_hidden_menu'] ) && is_array( $options['site_hidden_menu'] )
				? $options['site_hidden_menu']
				: array();
			$filtered = array();
			foreach ( $items as $item ) {
				$url_id = $this->_item_to_id( $item );
				$is_site_item = isset( $item['_site_added'] );
				if (
					! $is_site_item
					&& null !== $my_menu
					&& ! isset( $my_menu[ $url_id ] )
				) {
					continue;
				}
				if ( isset( $site_hidden_menu[ $url_id ] ) ) {
					continue;
				}
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
		if ( ! $this->can_manage_menu_settings() ) {
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

		$opts = $this->get_stored_menu_options();
		$opts = is_array( $opts ) ? $opts : array();

		$new_items = isset( $opts['new_items'] ) && is_array( $opts['new_items'] )
			? $opts['new_items']
			: array();

		foreach ( $new_items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$item['check_callback'] = false;
			$item['_added'] = true;

			if ( $id === $this->_item_to_id( $item ) ) {
				unset( $new_items[ $index ] );
				break;
			}
		}

		$opts['new_items'] = array_values( $new_items );

		$this->save_menu_options( $opts );

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
		if ( ! $this->can_manage_menu_settings() ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );

		$opts = $this->get_stored_menu_options();
		$opts = is_array( $opts ) ? $opts : array();

		if ( $this->site_menu_overrides_enabled() ) {
			unset( $opts['order'] );
		} else {
			$opts['order'] = array();
		}

		$this->save_menu_options( $opts );

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
		if ( ! $this->can_manage_menu_settings() ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );

		$opts = $this->get_stored_menu_options();
		$opts = is_array( $opts ) ? $opts : array();

		$opts['new_items'] = array();

		$this->save_menu_options( $opts );

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
		if ( ! $this->can_manage_menu_settings() ) {
			wp_send_json_error(
				array( 'message' => __( 'Du bist nicht berechtigt, dies zu tun.', 'wdeb' ) ),
				403
			);
		}

		check_ajax_referer( 'wdeb_menu_action', 'nonce' );
		$this->save_menu_options( array() );

		wp_send_json_success( array( 'status' => true ) );
	}


/* ---------- User Interface ---------- */


	function register_page ($perms) {
		add_submenu_page('wdeb', __('Menüeinträge', 'wdeb'), __('Menüeinträge', 'wdeb'), $perms, 'wdeb_menu_items', array($this, 'render_page'));
	}

	function render_page () {
		echo '<div class="wrap"><h2>Easy Blogging Menü</h2>';
		if ( is_network_admin() && Wdeb_Options::is_network_activated() ) {
			$main_site_menu_url = get_admin_url(
				get_main_site_id(),
				'options-general.php?page=wdeb_network_menu_items'
			);
			echo '<div class="notice notice-info"><p>';
			echo esc_html__( 'Plugin-Menüs für das Netzwerk werden im Adminbereich des Mainblogs ausgewählt.', 'wdeb' ) . ' ';
			echo '<a href="' . esc_url( $main_site_menu_url ) . '">';
			echo esc_html__( 'Menüeditor im Mainblog öffnen', 'wdeb' );
			echo '</a></p></div>';
		}
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

		if ( is_network_admin() && Wdeb_Options::is_network_activated() ) {
			add_settings_field(
				'wdeb_menu_items_allow_site_overrides',
				__( 'Menüverwaltung für Subsites', 'wdeb' ),
				array( $this, 'create_site_override_setting' ),
				'wdeb_menu_items',
				'wdeb_menu_items'
			);
		}

		add_settings_field(
			'wdeb_resets',
			__( 'Zurücksetzen', 'wdeb' ),
			array( $this, 'create_resets_box' ),
			'wdeb_menu_items',
			'wdeb_menu_items'
		);
	}

	function create_site_override_setting() {
		$options = $this->get_global_menu_options();
		$enabled = ! empty( $options['allow_site_overrides'] );

		echo '<input type="hidden" name="wdeb_menu_items[allow_site_overrides]" value="0">';
		echo '<label>';
		echo '<input type="checkbox" name="wdeb_menu_items[allow_site_overrides]" value="1" ' . checked( $enabled, true, false ) . '>';
		echo ' ' . esc_html__( 'Subsite-Admins dürfen eigene Menüpunkte hinzufügen sowie ihre lokale Sichtbarkeit und Reihenfolge festlegen.', 'wdeb' );
		echo '</label>';
		echo '<p class="description">';
		echo esc_html__( 'Netzwerkvorgaben bleiben unverändert. Lokale Ergänzungen und Anpassungen gelten nur für die jeweilige Subsite.', 'wdeb' );
		echo '</p>';
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
		$new_item = isset( $new_items['new'] ) && is_array( $new_items['new'] )
			? $new_items['new']
			: null;
		unset( $new_items['new'] );
		$added_item_id = '';

		foreach ( $new_items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				unset( $new_items[ $index ] );
				continue;
			}

			$new_items[ $index ] = $this->sanitize_menu_item( $item );
		}

		if ( is_array( $new_item ) ) {
			$item = $this->sanitize_menu_item( $new_item );
			$title = $item['title'];
			$url = $item['url'];
			if ( '' !== $title && '' !== $url ) {
				if ( $this->_is_unique_item( $item, $new_items ) ) {
					$new_items[] = $item;
					if ( ! $this->site_menu_overrides_enabled() ) {
						$item_for_id = $item;
						$item_for_id['check_callback'] = false;
						$item_for_id['_added'] = true;
						$added_item_id = $this->_item_to_id( $item_for_id );
					}
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

		$submitted_menu = isset( $posted['my_menu'] ) && is_array( $posted['my_menu'] )
			? $posted['my_menu']
			: array();
		$posted['my_menu'] = array();
		foreach ( $submitted_menu as $id => $value ) {
			if ( is_scalar( $value ) && (string) $value === '1' ) {
				$posted['my_menu'][ sanitize_key( $id ) ] = '1';
			}
		}
		if ( '' !== $added_item_id ) {
			$posted['my_menu'][ $added_item_id ] = '1';
		}
		$posted['order'] = isset( $posted['order'] ) && is_array( $posted['order'] )
			? array_values( array_filter( array_map( array( $this, 'sanitize_menu_id' ), $posted['order'] ) ) )
			: array();

		if ( $this->site_menu_overrides_enabled() ) {
			$editable_ids = isset( $posted['site_editable_ids'] ) && is_array( $posted['site_editable_ids'] )
				? array_values( array_filter( array_map( array( $this, 'sanitize_menu_id' ), $posted['site_editable_ids'] ) ) )
				: array();
			$selected_ids = array_keys( $posted['my_menu'] );
			$posted['hidden_menu'] = array_fill_keys(
				array_diff( array_unique( $editable_ids ), $selected_ids ),
				1
			);
			unset( $posted['my_menu'], $posted['site_editable_ids'], $posted['allow_site_overrides'] );
		}

		$current_options = $this->get_stored_menu_options();
		if ( ! array_key_exists( 'allow_site_overrides', $posted ) && isset( $current_options['allow_site_overrides'] ) ) {
			$posted['allow_site_overrides'] = $current_options['allow_site_overrides'];
		}
		if ( isset( $posted['allow_site_overrides'] ) ) {
			$posted['allow_site_overrides'] = is_scalar( $posted['allow_site_overrides'] )
				? (int) (bool) $posted['allow_site_overrides']
				: 0;
		}

		$this->save_menu_options( $posted );

		return true;
	}

	private function sanitize_menu_item( $item ) {
		return array(
			'title'      => isset( $item['title'] ) && is_scalar( $item['title'] )
				? sanitize_text_field( (string) $item['title'] )
				: '',
			'url'        => isset( $item['url'] ) && is_scalar( $item['url'] )
				? esc_url_raw( (string) $item['url'] )
				: '',
			'help'       => isset( $item['help'] ) && is_scalar( $item['help'] )
				? sanitize_text_field( (string) $item['help'] )
				: '',
			'icon'       => isset( $item['icon'] ) && is_scalar( $item['icon'] )
				? $this->sanitize_menu_icon( (string) $item['icon'] )
				: '',
			'capability' => isset( $item['capability'] ) && is_scalar( $item['capability'] )
				? sanitize_text_field( (string) $item['capability'] )
				: '',
		);
	}

	private function sanitize_menu_id( $id ) {
		return is_scalar( $id ) ? sanitize_key( (string) $id ) : '';
	}

	function handle_standalone_menu_save() {
		$context = isset( $_POST['wdeb_menu_context'] )
			? sanitize_key( wp_unslash( $_POST['wdeb_menu_context'] ) )
			: '';

		if ( ! $this->can_manage_menu_settings() ) {
			wp_die( esc_html__( 'Du bist nicht berechtigt, diese Einstellungen zu bearbeiten.', 'wdeb' ), '', array( 'response' => 403 ) );
		}

		if ( 'network' === $context ) {
			if ( ! Wdeb_Options::is_network_activated() || ! is_multisite() || ! is_main_site() ) {
				wp_die( esc_html__( 'Ungültiger Menübereich.', 'wdeb' ), '', array( 'response' => 403 ) );
			}
			check_admin_referer( 'wdeb_save_menu_items_network' );
			$redirect_url = get_admin_url( get_main_site_id(), 'options-general.php?page=wdeb_network_menu_items' );
		} elseif ( 'site' === $context ) {
			if ( ! $this->site_menu_overrides_enabled() ) {
				wp_die( esc_html__( 'Die lokale Menüverwaltung ist deaktiviert.', 'wdeb' ), '', array( 'response' => 403 ) );
			}
			check_admin_referer( 'wdeb_save_menu_items_site' );
			$redirect_url = admin_url( 'options-general.php?page=wdeb_site_menu_items' );
		} else {
			wp_die( esc_html__( 'Ungültiger Menübereich.', 'wdeb' ), '', array( 'response' => 400 ) );
		}

		$_POST['option_page'] = 'wdeb_menu_items';
		$this->save_settings( true );

		wp_safe_redirect( add_query_arg( 'settings-updated', 'true', $redirect_url ) );
		exit;
	}

	/**
	 * Displays the menu item visibility and ordering controls.
	 */
	function create_show_hide_box() {
		if ( ! defined( 'WDEB_PLUGIN_THEME_URL' ) ) {
			define(
				'WDEB_PLUGIN_THEME_URL',
				WDEB_PLUGIN_URL . '/themes/default'
			);
		}

		$menu_items = apply_filters( 'wdeb_initialize_menu', array() );
		$menu_items = apply_filters( 'wdeb_menu_items', $menu_items );

		$opts = $this->get_effective_menu_options();

		$local_context = $this->site_menu_overrides_enabled();
		$global_options = $this->get_global_menu_options();
		$my_menu = isset( $global_options['my_menu'] ) && is_array( $global_options['my_menu'] )
			? $global_options['my_menu']
			: null;
		$site_hidden_menu = isset( $opts['site_hidden_menu'] ) && is_array( $opts['site_hidden_menu'] )
			? $opts['site_hidden_menu']
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
			$is_site_item = isset( $item['_site_added'] );
			$global_visible = null === $my_menu || isset( $my_menu[ $url_id ] );
			$checked = $local_context
				? ( $is_site_item || $global_visible ) && ! isset( $site_hidden_menu[ $url_id ] )
				: ( null === $my_menu || isset( $my_menu[ $url_id ] ) );

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
			if ( $local_context && ( $is_site_item || $global_visible ) ) {
				echo '<input type="hidden" name="wdeb_menu_items[site_editable_ids][]" value="' . esc_attr( $url_id ) . '">';
			}
			echo '<input type="checkbox" name="wdeb_menu_items[my_menu][' . esc_attr( $url_id ) . ']" value="1"' . checked( $checked, true, false ) . disabled( $local_context && ! $is_site_item && ! $global_visible, true, false ) . '>';
			echo '<input type="hidden" class="wdeb_menu_items-url_id" name="wdeb_menu_items[order][]" value="' . esc_attr( $url_id ) . '">';
			echo '</td>';

			// Item.
			echo '<td>';
			echo '<div style="display:flex; align-items:center; gap:10px;">';

			$this->render_menu_icon( $icon );

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

			if ( isset( $item['_added'] ) && ( ! $this->site_menu_overrides_enabled() || isset( $item['_site_added'] ) ) ) {
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
		$opts = $this->get_stored_menu_options();

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

		<?php if ( ! is_network_admin() ) : ?>
			<p>
				<label for="wdeb_registered_menu_items">
					<?php esc_html_e( 'Vorhandenes Admin-Menü übernehmen', 'wdeb' ); ?>
				</label>
				<select id="wdeb_registered_menu_items" class="widefat">
					<option value=""><?php esc_html_e( 'Menüpunkt auswählen …', 'wdeb' ); ?></option>
					<?php foreach ( $this->get_registered_menu_items() as $registered_item ) : ?>
						<option
							value="<?php echo esc_attr( $registered_item['url'] ); ?>"
							data-title="<?php echo esc_attr( $registered_item['title'] ); ?>"
							data-help="<?php echo esc_attr( $registered_item['help'] ); ?>"
							data-icon="<?php echo esc_attr( $registered_item['icon'] ); ?>"
							data-capability="<?php echo esc_attr( $registered_item['capability'] ); ?>"
						><?php echo esc_html( $registered_item['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="description"><?php esc_html_e( 'Titel, URL, Icon und Berechtigung werden übernommen und können vor dem Hinzufügen angepasst werden.', 'wdeb' ); ?></span>
			</p>
		<?php elseif ( Wdeb_Options::is_network_activated() ) : ?>
			<p class="description">
				<?php esc_html_e( 'Plugin-Menüs werden im Menüeditor des Mainblogs ausgewählt.', 'wdeb' ); ?>
			</p>
		<?php endif; ?>

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
			type="text"
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
		if (
			! isset( $_GET['page'] )
			|| ! in_array(
				$_GET['page'],
				array( 'wdeb_menu_items', 'wdeb_network_menu_items', 'wdeb_site_menu_items' ),
				true
			)
		) {
			return false;
		}
		wp_enqueue_script( 'jquery' );
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
					"nonce": "%s",
				};
			</script>',
			esc_url( admin_url() ),
			esc_url( admin_url( 'admin-ajax.php' ) ),
			esc_js( wp_create_nonce( 'wdeb_menu_action' ) )
		);
	}

	function css_add_styles () {
		if (
			! isset( $_GET['page'] )
			|| ! in_array(
				$_GET['page'],
				array( 'wdeb_menu_items', 'wdeb_network_menu_items', 'wdeb_site_menu_items' ),
				true
			)
		) {
			return false;
		}
		wp_enqueue_style('thickbox');
	}


/* ---------- Private API ---------- */

	private function get_registered_menu_items() {
		global $menu, $submenu;

		$items = array();
		$seen = array();
		$parents = array();
		$existing_urls = array();
		$built_in_items = apply_filters( 'wdeb_initialize_menu', array() );
		foreach ( $built_in_items as $built_in_item ) {
			if ( isset( $built_in_item['url'] ) ) {
				$existing_urls[] = $built_in_item['url'];
			}
		}
		$existing_options = $this->get_effective_menu_options();
		if ( isset( $existing_options['new_items'] ) && is_array( $existing_options['new_items'] ) ) {
			foreach ( $existing_options['new_items'] as $existing_item ) {
				if ( isset( $existing_item['url'] ) ) {
					$existing_urls[] = $existing_item['url'];
				}
			}
		}

		$add_item = function ( $slug, $title, $capability, $icon, $parent_title = '', $help = '' ) use ( &$items, &$seen, $existing_urls ) {
			if ( ! is_string( $slug ) || '' === $slug || ! is_string( $title ) || '' === $title ) {
				return;
			}
			if (
				preg_match( '/^[a-z][a-z0-9+.-]*:/i', $slug )
				|| false !== strpos( $slug, 'separator' )
				|| 'wdeb' === $slug
				|| false !== strpos( $slug, 'wdeb_' )
			) {
				return;
			}
			if ( ! is_string( $capability ) || '' === $capability || ! current_user_can( $capability ) ) {
				return;
			}

			$url = $this->registered_menu_item_url( $slug );
			if ( in_array( $url, $existing_urls, true ) ) {
				return;
			}
			$key = $url . '|' . $capability;
			if ( isset( $seen[ $key ] ) ) {
				return;
			}

			$seen[ $key ] = true;
			$items[] = array(
				'label'      => $parent_title ? $parent_title . ' › ' . $title : $title,
				'title'      => $title,
				'help'       => $help === $title ? '' : $help,
				'url'        => $url,
				'icon'       => $this->normalize_menu_icon( $icon ),
				'capability' => $capability,
			);
		};

		foreach ( (array) $menu as $entry ) {
			if ( ! isset( $entry[0], $entry[1], $entry[2] ) ) {
				continue;
			}

			$title = sanitize_text_field( trim( wp_strip_all_tags( html_entity_decode( (string) $entry[0], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) ) );
			$slug = (string) $entry[2];
			$icon = isset( $entry[6] ) ? $entry[6] : '';
			$help = isset( $entry[3] )
				? sanitize_text_field( trim( wp_strip_all_tags( html_entity_decode( (string) $entry[3], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) ) )
				: '';
			$parents[ $slug ] = array(
				'title' => $title,
				'icon'  => $icon,
			);

			$add_item( $slug, $title, $entry[1], $icon, '', $help );
		}

		foreach ( (array) $submenu as $parent_slug => $entries ) {
			$parent = isset( $parents[ $parent_slug ] ) ? $parents[ $parent_slug ] : array( 'title' => '', 'icon' => '' );
			foreach ( (array) $entries as $entry ) {
				if ( ! isset( $entry[0], $entry[1], $entry[2] ) ) {
					continue;
				}

				$title = sanitize_text_field( trim( wp_strip_all_tags( html_entity_decode( (string) $entry[0], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) ) );
				$help = isset( $entry[3] )
					? sanitize_text_field( trim( wp_strip_all_tags( html_entity_decode( (string) $entry[3], ENT_QUOTES, get_bloginfo( 'charset' ) ) ) ) )
					: '';
				$icon = isset( $entry[6] ) && $entry[6] ? $entry[6] : $parent['icon'];
				$add_item( (string) $entry[2], $title, $entry[1], $icon, $parent['title'], $help );
			}
		}

		return $items;
	}

	private function registered_menu_item_url( $slug ) {
		if ( preg_match( '/\.php(?:\?|$)/', $slug ) ) {
			return $slug;
		}

		if ( function_exists( 'menu_page_url' ) ) {
			$url = menu_page_url( $slug, false );
			$admin_base = admin_url();
			if ( is_string( $url ) && 0 === strpos( $url, $admin_base ) ) {
				return substr( $url, strlen( $admin_base ) );
			}
		}

		return 'admin.php?page=' . rawurlencode( $slug );
	}

	private function normalize_menu_icon( $icon ) {
		return $this->sanitize_menu_icon( $icon );
	}

	private function sanitize_menu_icon( $icon ) {
		if ( is_string( $icon ) && preg_match( '/^dashicons-[a-z0-9-]+$/', $icon ) ) {
			return $icon;
		}

		if (
			is_string( $icon )
			&& preg_match( '~^data:image/svg\+xml;base64,([A-Za-z0-9+/=]+)$~', $icon, $matches )
			&& strlen( $matches[1] ) <= 44000
		) {
			$svg = base64_decode( $matches[1], true );
			if ( false !== $svg ) {
				$allowed_svg = array(
					'svg' => array(
						'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true,
						'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true,
					),
					'g' => array(
						'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true, 'transform' => true,
					),
					'path' => array(
						'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
						'fill-rule' => true, 'clip-rule' => true, 'opacity' => true, 'transform' => true,
					),
					'circle' => array(
						'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
					),
					'rect' => array(
						'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true,
						'fill' => true, 'stroke' => true, 'stroke-width' => true,
					),
					'line' => array(
						'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true,
					),
					'polygon' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
					'polyline' => array( 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
					'ellipse' => array(
						'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true,
					),
				);
				$safe_svg = wp_kses( $svg, $allowed_svg );
				if ( preg_match( '/<svg\b/i', $safe_svg ) ) {
					return 'data:image/svg+xml;base64,' . base64_encode( $safe_svg );
				}
			}
		}

		$icon = is_string( $icon ) ? esc_url_raw( $icon ) : '';
		if (
			$icon
			&& (
				preg_match( '~^https?://~i', $icon )
				|| ( isset( $icon[0] ) && '/' === $icon[0] )
			)
		) {
			return $icon;
		}

		return 'dashicons-admin-generic';
	}

	private function render_menu_icon( $icon ) {
		if ( is_string( $icon ) && preg_match( '/^dashicons-[a-z0-9-]+$/', $icon ) ) {
			echo '<span class="dashicons ' . esc_attr( $icon ) . '" aria-hidden="true" style="font-size:30px;width:32px;height:32px;"></span>';
		} elseif ( is_string( $icon ) && preg_match( '~^data:image/svg\+xml;base64,[A-Za-z0-9+/=]+$~', $icon ) ) {
			echo '<img src="' . esc_attr( $icon ) . '" alt="" style="width:32px;height:32px;">';
		} elseif ( $icon ) {
			echo '<img src="' . esc_url( $icon ) . '" alt="" style="width:32px;height:32px;">';
		}
	}


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
	private function _reorder_items( $items, $options = null ) {
		$items = array_values( $items );

		$opts = null === $options ? $this->get_effective_menu_options() : $options;
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