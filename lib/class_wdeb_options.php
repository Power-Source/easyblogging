<?php
class Wdeb_Options {

	static function is_network_activated() {
		if ( ! is_multisite() ) {
			return false;
		}

		if ( defined( 'WDEB_PLUGIN_LOCATION' ) && 'mu-plugins' === WDEB_PLUGIN_LOCATION ) {
			return true;
		}

		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		return is_array( $active_plugins )
			&& defined( 'WDEB_PLUGIN_BASENAME' )
			&& isset( $active_plugins[ WDEB_PLUGIN_BASENAME ] );
	}

	/**
	 * Gets a single option from options storage.
	 */
	function get_option( $key, $pfx = 'wdeb' ) {
		$opts = self::is_network_activated() ? get_site_option( $pfx ) : get_option( $pfx );
		$opts = is_array( $opts ) ? $opts : array();

		return apply_filters(
			"wdeb_get_option-{$pfx}-{$key}",
			$opts[ $key ] ?? null
		);
	}

	/** Gets a full set of options from options storage. */
	function get_options( $pfx = 'wdeb' ) {
		return self::is_network_activated() ? get_site_option( $pfx ) : get_option( $pfx );
	}

	/**
	 * Sets all stored options.
	 */
	function set_options( $opts, $key = 'wdeb' ) {
		return self::is_network_activated()
			? update_site_option( $key, $opts )
			: update_option( $key, $opts );
	}

	function get_logo() {
		$logo = get_option( 'wdeb_logo' );
		$logo = $logo ? $logo : $this->get_option( 'wdeb_logo' );
		$logo = $logo ? $logo : WDEB_LOGO_URL;

		return is_ssl()
			? str_replace( 'http://', 'https://', $logo )
			: $logo;
	}

	/**
	 * Populates options key for storage.
	 *
	 * @static
	 */
	public static function populate() {
	}
}