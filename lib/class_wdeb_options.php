<?php
class Wdeb_Options {

	/**
	 * Gets a single option from options storage.
	 */
	function get_option( $key, $pfx = 'wdeb' ) {
		$opts = is_multisite() ? get_site_option( $pfx ) : get_option( $pfx );
		$opts = is_array( $opts ) ? $opts : array();

		return apply_filters(
			"wdeb_get_option-{$pfx}-{$key}",
			$opts[ $key ] ?? null
		);
	}

	/**
	 * Gets a full set of options from options storage.
	 */
	function get_options( $pfx = 'wdeb' ) {
		return is_multisite() ? get_site_option( $pfx ) : get_option( $pfx );
	}

	/**
	 * Sets all stored options.
	 */
	function set_options( $opts, $key = 'wdeb' ) {
		return is_multisite() ? update_site_option( $key, $opts ) : update_option( $key, $opts );
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