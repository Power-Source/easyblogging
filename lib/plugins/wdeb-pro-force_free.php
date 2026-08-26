<?php
/*
Plugin Name: PS Bloghosting: Einfachen Modus auf kostenlosen Webseiten erzwingen
Description: Erzwingt den einfachen Modus auf kostenlosen Webseiten. <b>Erfordert das PS Bloghosting-Plugin.</b>
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0.1
Author: PSOURCE
*/

class Wdeb_Pro_ForceOnFreeSites {

	private $_data;

	private function __construct() {
		$this->_data = new Wdeb_Options();
	}

	public static function serve() {
		$me = new Wdeb_Pro_ForceOnFreeSites();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// Add settings.
		add_action(
			'wdeb_admin-register_settings-settings',
			array( $this, 'add_settings' )
		);

		add_filter(
			'wdeb_admin-options_changed',
			array( $this, 'save_settings' )
		);

		// Actual forcing.
		add_filter(
			'wdeb_get_option-wdeb-auto_enter_role',
			array( $this, 'force_roles_on_free_sites' )
		);
	}

	public function force_roles_on_free_sites( $roles ) {
		if ( ! class_exists( 'ProSites' ) ) {
			return $roles;
		}

		if ( current_user_can( 'manage_network_options' ) ) {
			return $roles;
		}

		if ( ! function_exists( 'is_pro_site' ) ) {
			return $roles;
		}

		$values = get_site_option( 'wdeb_pro', array() );

		if ( ! is_array( $values ) || empty( $values['force_on_free'] ) ) {
			return $roles;
		}

		if ( is_pro_site() ) {
			return $roles;
		}

		// Force Easy Mode on all roles.
		global $wp_roles;

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$all_roles = array_keys( $wp_roles->get_names() );

		return array_combine( $all_roles, $all_roles );
	}

	public function add_settings() {
		if ( ! class_exists( 'ProSites' ) ) {
			return false;
		}

		add_settings_field(
			'wdeb_pro_force_on_free',
			__( 'Einfachen Modus auf kostenlosen Webseiten erzwingen', 'wdeb' ),
			array( $this, 'render_settings' ),
			'wdeb_options_page',
			'wdeb_settings'
		);
	}

	public function render_settings() {
		if ( ! class_exists( 'ProSites' ) ) {
			return false;
		}

		$pfx    = 'wdeb_pro';
		$name   = 'force_on_free';
		$values = get_site_option( $pfx, array() );
		$value  = is_array( $values ) && ! empty( $values[ $name ] )
			? (int) $values[ $name ]
			: 0;

		echo '<input type="radio" name="' . esc_attr( $pfx . '[' . $name . ']' ) . '" id="' . esc_attr( $name . '-yes' ) . '" value="1" ' . checked( $value, 1, false ) . ' />';
		echo ' <label for="' . esc_attr( $name . '-yes' ) . '">' . esc_html__( 'JA', 'wdeb' ) . '</label>';

		echo '&nbsp;';

		echo '<input type="radio" name="' . esc_attr( $pfx . '[' . $name . ']' ) . '" id="' . esc_attr( $name . '-no' ) . '" value="0" ' . checked( $value, 0, false ) . ' />';
		echo ' <label for="' . esc_attr( $name . '-no' ) . '">' . esc_html__( 'NEIN', 'wdeb' ) . '</label>';
	}

	public function save_settings( $changed ) {
		if ( isset( $_POST['option_page'] ) && 'wdeb' === $_POST['option_page'] ) {
			$values = isset( $_POST['wdeb_pro'] ) && is_array( $_POST['wdeb_pro'] )
				? $_POST['wdeb_pro']
				: array();

			$values['force_on_free'] = ! empty( $values['force_on_free'] ) ? 1 : 0;

			update_site_option( 'wdeb_pro', $values );

			$changed = true;
		}

		return $changed;
	}
}

if ( is_admin() ) {
	Wdeb_Pro_ForceOnFreeSites::serve();
}