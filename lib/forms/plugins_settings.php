<div class="wrap">
	<h2><?php _e("Easy Blogging Erweiterungen", 'wdeb'); ?></h2>

<?php
	$all = Wdeb_PluginsHandler::get_all_plugins();
	$active = Wdeb_PluginsHandler::get_active_plugins();
	$sections = array('thead', 'tfoot');
	echo "<table class='widefat'>";
	foreach ($sections as $section) {
		echo "<{$section}>";
		echo '<tr>';
		echo '<th width="30%">' . __('Erweiterungsname', 'wdeb') . '</th>';
		echo '<th>' . __('Erweiterungsbeschreibung', 'wdeb') . '</th>';
		echo '</tr>';
		echo "</{$section}>";
	}
	echo "<tbody>";
	foreach ( $all as $plugin ) {
		$plugin_data = Wdeb_PluginsHandler::get_plugin_info( $plugin );

		if ( ! is_array( $plugin_data ) || empty( $plugin_data['Name'] ) ) {
			continue;
		}

		$is_active = in_array( $plugin, $active, true );

		echo '<tr>';
		echo "<td width='30%'>";
		echo '<b>' . esc_html( $plugin_data['Name'] ) . '</b>';
		echo '<br />';
		echo (
			$is_active
				? '<a href="#deactivate" class="wdeb_deactivate_plugin" wdeb:plugin_id="' . esc_attr( $plugin ) . '">' . __( 'Deaktivieren', 'wdeb' ) . '</a>'
				: '<a href="#activate" class="wdeb_activate_plugin" wdeb:plugin_id="' . esc_attr( $plugin ) . '">' . __( 'Aktivieren', 'wdeb' ) . '</a>'
		);
		echo '</td>';

		echo '<td>';
		echo wp_kses_post( $plugin_data['Description'] );
		echo '<br />';
		echo sprintf(
			__( 'Version %s', 'wdeb' ),
			esc_html( $plugin_data['Version'] ?? '' )
		);
		echo '&nbsp;|&nbsp;';
		echo sprintf(
			__( 'von %s', 'wdeb' ),
			'<a href="' . esc_url( $plugin_data['Plugin URI'] ?? '' ) . '">' .
			esc_html( $plugin_data['Author'] ?? '' ) .
			'</a>'
		);
		echo '</td>';

		echo '</tr>';
	}
	echo "</tbody>";
	echo "</table>";
?>

<script type="text/javascript">
(function ($) {
$(function () {
	$(".wdeb_activate_plugin").on('click', function () {
		var me = $(this);
		var plugin_id = me.attr("wdeb:plugin_id");
		$.post(ajaxurl, {"action": "wdeb_activate_plugin", "plugin": plugin_id}, function (data) {
			window.location = window.location;
		});
		return false;
	});
	$(".wdeb_deactivate_plugin").on('click', function () {
		var me = $(this);
		var plugin_id = me.attr("wdeb:plugin_id");
		$.post(ajaxurl, {"action": "wdeb_deactivate_plugin", "plugin": plugin_id}, function (data) {
			window.location = window.location;
		});
		return false;
	});
});
})(jQuery);
</script>

</div>