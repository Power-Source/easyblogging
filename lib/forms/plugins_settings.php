<div class="wrap">
	<h2><?php _e("Easy Blogging Erweiterungen", 'wdeb'); ?></h2>
	<p class="description"><?php _e('Erweiterungen direkt per Schalter aktivieren oder deaktivieren.', 'wdeb'); ?></p>

	<style type="text/css">
	.wdeb-plugins-table.widefat td,
	.wdeb-plugins-table.widefat th {
		vertical-align: top;
	}
	.wdeb-plugins-table.widefat tbody td {
		padding: 12px 10px;
	}
	.wdeb-plugins-table .wdeb-plugin-title {
		font-weight: 600;
		display: block;
		margin-bottom: 8px;
		line-height: 1.25;
	}
	.wdeb-plugin-controls {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		min-height: 30px;
		white-space: nowrap;
	}
	.wdeb-plugin-controls .wdeb-toggle-switch {
		position: relative;
		display: inline-block;
		width: 52px;
		height: 30px;
		flex: 0 0 52px;
		vertical-align: middle;
	}
	.wdeb-plugin-controls .wdeb-toggle-switch input {
		opacity: 0;
		width: 1px;
		height: 1px;
		position: absolute;
	}
	.wdeb-plugin-controls .wdeb-toggle-slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: #c8d0d8;
		border-radius: 999px;
		transition: .2s ease;
	}
	.wdeb-plugin-controls .wdeb-toggle-slider:before {
		position: absolute;
		content: "";
		height: 24px;
		width: 24px;
		left: 3px;
		top: 3px;
		background: #fff;
		border-radius: 50%;
		transition: .2s ease;
		box-shadow: 0 1px 2px rgba(0,0,0,.25);
	}
	.wdeb-plugin-controls .wdeb-toggle-switch input:checked + .wdeb-toggle-slider {
		background: #0ea5a2;
	}
	.wdeb-plugin-controls .wdeb-toggle-switch input:checked + .wdeb-toggle-slider:before {
		transform: translateX(22px);
	}
	.wdeb-plugin-controls .wdeb-toggle-switch input:disabled + .wdeb-toggle-slider {
		cursor: not-allowed;
		opacity: .65;
	}
	.wdeb-plugin-status {
		font-size: 12px;
		color: #4b5563;
		font-weight: 600;
		line-height: 1;
	}
	.wdeb-plugin-feedback {
		font-size: 12px;
		color: #6b7280;
		min-height: 1em;
		min-width: 98px;
		line-height: 1;
	}
	.wdeb-plugin-row.is-loading .wdeb-plugin-feedback {
		color: #2563eb;
		font-weight: 600;
	}
	.wdeb-plugin-feedback.is-success {
		color: #0f766e;
	}
	.wdeb-plugin-feedback.is-error {
		color: #b91c1c;
	}
	.wdeb-plugin-row.is-active .wdeb-plugin-status {
		color: #0f766e;
		font-weight: 600;
	}
	.wdeb-plugin-row.is-loading {
		opacity: .82;
	}
	.wdeb-plugin-description {
		line-height: 1.35;
	}
	.wdeb-plugin-meta {
		display: block;
		margin-top: 3px;
		color: #6b7280;
	}

	@media screen and (max-width: 782px) {
		.wdeb-plugins-table thead,
		.wdeb-plugins-table tfoot {
			display: none;
		}

		.wdeb-plugins-table,
		.wdeb-plugins-table tbody,
		.wdeb-plugins-table tr,
		.wdeb-plugins-table td {
			display: block;
			width: 100% !important;
			box-sizing: border-box;
		}

		.wdeb-plugins-table.widefat tbody td {
			padding: 0;
			border: 0;
		}

		.wdeb-plugins-table .wdeb-plugin-row {
			margin: 0 0 12px 0;
			border: 1px solid #d0d7de;
			border-radius: 10px;
			background: #fff;
			overflow: hidden;
		}

		.wdeb-plugins-table .wdeb-plugin-row > td:first-child {
			padding: 12px;
			border-bottom: 1px solid #eef2f6;
		}

		.wdeb-plugins-table .wdeb-plugin-row > td:last-child {
			padding: 10px 12px 12px;
		}

		.wdeb-plugin-title {
			font-size: 14px;
			margin-bottom: 10px;
		}

		.wdeb-plugin-controls {
			display: flex;
			align-items: center;
			gap: 10px;
			white-space: normal;
			flex-wrap: wrap;
		}

		.wdeb-plugin-controls .wdeb-toggle-switch {
			width: 58px;
			height: 34px;
			flex: 0 0 58px;
		}

		.wdeb-plugin-controls .wdeb-toggle-slider:before {
			height: 26px;
			width: 26px;
		}

		.wdeb-plugin-controls .wdeb-toggle-switch input:checked + .wdeb-toggle-slider:before {
			transform: translateX(24px);
		}

		.wdeb-plugin-status {
			font-size: 13px;
		}

		.wdeb-plugin-feedback {
			min-width: 0;
			width: 100%;
			line-height: 1.2;
			padding-top: 2px;
		}

		.wdeb-plugin-description {
			font-size: 12px;
			line-height: 1.45;
		}

		.wdeb-plugin-meta {
			margin-top: 6px;
			font-size: 11px;
		}
	}
	</style>

<?php
	$all = Wdeb_PluginsHandler::get_all_plugins();
	$active = Wdeb_PluginsHandler::get_active_plugins();
	$sections = array('thead', 'tfoot');
	echo "<table class='widefat wdeb-plugins-table'>";
	foreach ($sections as $section) {
		echo "<{$section}>";
		echo '<tr>';
		echo '<th width="35%">' . __('Erweiterung-Name', 'wdeb') . '</th>';
		echo '<th>' . __('Erweiterung-Beschreibung', 'wdeb') . '</th>';
		echo '</tr>';
		echo "</{$section}>";
	}
	echo "<tbody>";
	foreach ($all as $plugin) {
		$plugin_data = Wdeb_PluginsHandler::get_plugin_info($plugin);
		if (!@$plugin_data['Name']) continue; // Require the name
		$is_active = in_array($plugin, $active);
		$plugin_name = esc_html($plugin_data['Name']);
		$description = wp_kses_post($plugin_data['Description']);
		$version = esc_html($plugin_data['Version']);
		$author = esc_html($plugin_data['Author']);
		$plugin_uri = esc_url($plugin_data['Plugin URI']);

		echo '<tr class="wdeb-plugin-row ' . ($is_active ? 'is-active' : '') . '" data-plugin-id="' . esc_attr($plugin) . '">';
		echo "<td width='35%'>";
		echo '<span class="wdeb-plugin-title">' . $plugin_name . '</span>';
		echo '<div class="wdeb-plugin-controls">';
		echo '<label class="wdeb-toggle-switch" for="wdeb-plugin-toggle-' . esc_attr($plugin) . '">';
		echo '<input type="checkbox" id="wdeb-plugin-toggle-' . esc_attr($plugin) . '" class="wdeb-plugin-toggle" data-plugin-id="' . esc_attr($plugin) . '" ' . checked($is_active, true, false) . ' />';
		echo '<span class="wdeb-toggle-slider"></span>';
		echo '</label>';
		echo '<span class="wdeb-plugin-status">' . ($is_active ? esc_html__('Aktiv', 'wdeb') : esc_html__('Inaktiv', 'wdeb')) . '</span>';
		echo '<span class="wdeb-plugin-feedback" aria-live="polite"></span>';
		echo '</div>';
		echo "</td>";
		echo '<td class="wdeb-plugin-description">';
		echo $description;
		echo '<span class="wdeb-plugin-meta">';
		echo sprintf(__('Version %s', 'wdeb'), $version);
		echo '&nbsp;|&nbsp;';
		echo sprintf(__('von %s', 'wdeb'), $plugin_uri ? '<a href="' . $plugin_uri . '">' . $author . '</a>' : $author);
		echo '</span>';
		echo '</td>';
		echo "</tr>";
	}
	echo "</tbody>";
	echo "</table>";
?>

</div>