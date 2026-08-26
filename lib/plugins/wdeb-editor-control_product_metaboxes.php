<?php
/*
Plugin Name: Kontrolle der "Produkt"-Metaboxen
Description: Ermöglicht die Kontrolle darüber, welche Metaboxen im benutzerdefinierten Beitragstyp "Produkt" angezeigt werden. <b>Erfordert das PS MarketPress-Plugin</b>
Plugin URI: https://psource.eimen.net/wiki/easy-blogging-dokumentation/
Version: 1.0
Author: PSOURCE
*/

class Wdeb_Editor_ControlProductMetaboxes {
	
	private $_data;

	private function __construct () {
		$this->_data = new Wdeb_Options;
	}

	public static function serve () {
		$me = new Wdeb_Editor_ControlProductMetaboxes;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		// Add settings
		add_action('wdeb_admin-register_settings-settings', array($this, 'add_settings'));
		add_filter('wdeb_admin-options_changed', array($this, 'save_settings'));

		// Actual removing
		add_action('wdeb_admin-editor_metaboxes_cleanup', array($this, 'remove_metaboxes'));
	}

	function remove_metaboxes() {
		global $wp_meta_boxes;

		$opts = $this->_data->get_options( 'wdeb_ecpm' );
		$post_boxes = is_array( $opts ) && ! empty( $opts['hide_boxes'] )
			? $opts['hide_boxes']
			: array();

		$locations = array(
			array( 'side', 'core' ),
			array( 'side', 'low' ),
			array( 'normal', 'core' ),
			array( 'normal', 'high' ),
		);

		foreach ( $locations as $location ) {
			list( $context, $priority ) = $location;

			if (
				isset( $wp_meta_boxes['product'][ $context ][ $priority ] ) &&
				is_array( $wp_meta_boxes['product'][ $context ][ $priority ] )
			) {
				foreach ( $wp_meta_boxes['product'][ $context ][ $priority ] as $name => $box ) {
					if ( in_array( $name, $post_boxes, true ) ) {
						unset( $wp_meta_boxes['product'][ $context ][ $priority ][ $name ] );
					}
				}
			}
		}
	}

	function add_settings () {
		add_settings_field('wdeb_ecpm_boxes', __('Diese Produkt-Metaboxen ausblenden', 'wdeb'), array($this, 'render_settings'), 'wdeb_options_page', 'wdeb_settings');
	}

	function render_settings() {
		$pfx = 'wdeb_ecpm';
		$name = 'hide_boxes';

		$opts = $this->_data->get_options( $pfx );
		$hides = is_array( $opts ) && isset( $opts[ $name ] ) && is_array( $opts[ $name ] )
			? $opts[ $name ]
			: array();

		$boxes = array(
			'authordiv'           => __( 'Autor', 'wdeb' ),
			'postexcerpt'         => __( 'Auszug', 'wdeb' ),
			'product_categorydiv' => __( 'Produktkategorien', 'wdeb' ),
			'tagsdiv-product_tag' => __( 'Produkt-Tags', 'wdeb' ),
			'mp-meta-download'    => __( 'Produkt-Download', 'wdeb' ),
		);

		foreach ( $boxes as $bid => $label ) {
			$checked = in_array( $bid, $hides, true ) ? ' checked="checked"' : '';

			echo '<label>';
			echo '<input type="checkbox" name="' . esc_attr( $pfx . '[' . $name . '][]' ) . '" value="' . esc_attr( $bid ) . '"' . $checked . '>';
			echo ' ' . esc_html( $label );
			echo '</label><br>' . "\n";
		}

		echo '<p><strong>' . esc_html__( 'Warnung:', 'wdeb' ) . '</strong> ';
		echo esc_html__( 'alle anderen Boxen werden entsprechend ihren Anzeigeeinstellungen angezeigt oder ausgeblendet', 'wdeb' );
		echo '</p>';
	}

	function save_settings( $changed ) {
		if ( isset( $_POST['option_page'] ) && 'wdeb_ecpm' === $_POST['option_page'] ) {
			$options = isset( $_POST['wdeb_ecpm'] ) && is_array( $_POST['wdeb_ecpm'] )
				? $_POST['wdeb_ecpm']
				: array();

			$this->_data->set_options( $options, 'wdeb_ecpm' );
			$changed = true;
		}

		return $changed;
	}
}

if (is_admin()) Wdeb_Editor_ControlProductMetaboxes::serve();