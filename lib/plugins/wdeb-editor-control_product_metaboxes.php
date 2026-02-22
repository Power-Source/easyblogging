<?php
/*
Plugin Name: Control "Product" metaboxes
Description: Ermöglicht die Kontrolle darüber, welche Metaboxen im benutzerdefinierten Beitragstyp "Produkt" angezeigt werden. <b>Benötigt PSeCommerce</b>
Plugin URI: https://n3rds.work/piestingtal_source/easy-blogging-plugin/
Version: 1.1
Author: WPMS N@W
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

	function remove_metaboxes () {
		global $wp_meta_boxes;
		$opts = $this->_data->get_options('wdeb_ecpm');
		$post_boxes = @$opts['hide_boxes'];
		$post_boxes = is_array($post_boxes) ? $post_boxes : array();

		if (isset($wp_meta_boxes['product']['side']['core']) && is_array(@$wp_meta_boxes['product']['side']['core'])) foreach ($wp_meta_boxes['product']['side']['core'] as $name => $box) if (in_array($name, $post_boxes)) unset($wp_meta_boxes['product']['side']['core'][$name]);
		if (isset($wp_meta_boxes['product']['side']['low']) && is_array(@$wp_meta_boxes['product']['side']['low'])) foreach ($wp_meta_boxes['product']['side']['low'] as $name => $box) if (in_array($name, $post_boxes)) unset($wp_meta_boxes['product']['side']['low'][$name]);
		if (isset($wp_meta_boxes['product']['normal']['core']) && is_array(@$wp_meta_boxes['product']['normal']['core'])) foreach ($wp_meta_boxes['product']['normal']['core'] as $name => $box) if (in_array($name, $post_boxes)) unset($wp_meta_boxes['product']['normal']['core'][$name]);
		if (isset($wp_meta_boxes['product']['normal']['high']) && is_array(@$wp_meta_boxes['product']['normal']['high'])) foreach ($wp_meta_boxes['product']['normal']['high'] as $name => $box) if (in_array($name, $post_boxes)) unset($wp_meta_boxes['product']['normal']['high'][$name]);
	}

	function add_settings () {
		add_settings_field('wdeb_ecpm_boxes', __('Blende diese Produktmetaboxen aus', 'wdeb'), array($this, 'render_settings'), 'wdeb_options_page', 'wdeb_settings');
	}

	function render_settings () {
		$pfx = 'wdeb_ecpm';
		$name = 'hide_boxes';
		$opts = $this->_data->get_options($pfx);
		$hides = @$opts[$name];
		$hides = is_array($hides) ? $hides : array();
		
		$_boxes = array (
			'authordiv' => __('Autor'),
			'postexcerpt' => __('Auszug'),
			'product_categorydiv' => __('Produktkategorien', 'wdeb'),
			'tagsdiv-product_tag' => __('Produkt Tags', 'wdeb'),
			'mp-meta-download' => __('Produkt Download', 'wdeb'),
		);
		foreach ($_boxes as $bid => $label) {
			$checked = in_array($bid, $hides) ? 'checked="checked"' : '';
			echo "<input type='hidden' name='{$pfx}[{$name}][{$bid}]' value='0' />" .
				"<input {$checked} type='checkbox' name='{$pfx}[{$name}][{$bid}]' value='{$bid}' id='wdeb_product_post_boxes_{$bid}' /> " .
				"<label for='wdeb_product_post_boxes_{$bid}'>{$label}</label><br />\n";
		}
		_e(
			'<p><b>Warnung:</b> Alle anderen Felder werden entsprechend ihren Bildschirmeinstellungen ein- oder ausgeblendet</p>',
		'wdeb');
	}

	function save_settings ($changed) {
		if ('wdeb' == @$_POST['option_page']) {
			$this->_data->set_options($_POST['wdeb_ecpm'], 'wdeb_ecpm');
			$changed = true;
		}
		return $changed;
	}
}

if (is_admin()) Wdeb_Editor_ControlProductMetaboxes::serve();