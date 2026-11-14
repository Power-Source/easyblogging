(function ($) {
$(function () {
	
var __oldSentToEditor;

/* --- Sortable table --- */
const menuTable = document.querySelector('#wdeb_show_hide_root tbody');

if (menuTable) {
	let draggedRow = null;

	menuTable.querySelectorAll('tr').forEach(function (row) {
		row.draggable = true;

		row.addEventListener('dragstart', function () {
			draggedRow = row;
			row.classList.add('wdeb-dragging');
		});

		row.addEventListener('dragend', function () {
			row.classList.remove('wdeb-dragging');
			draggedRow = null;
		});

		row.addEventListener('dragover', function (event) {
			event.preventDefault();

			if (!draggedRow || draggedRow === row) {
				return;
			}

			const rect = row.getBoundingClientRect();
			const after = event.clientY > rect.top + rect.height / 2;

			row.parentNode.insertBefore(
				draggedRow,
				after ? row.nextSibling : row
			);
		});
	});
}

/* --- (Un)Check all --- */
$(".wdeb_check_all_items").on('click', function () {
	$("table#wdeb_show_hide_root tbody input:checkbox:enabled").prop("checked", true);
	return false;
});
$(".wdeb_uncheck_all_items").on('click', function () {
	$("table#wdeb_show_hide_root tbody input:checkbox:enabled").prop("checked", false);
	return false;
});

/* --- Choosing icon --- */
$("#wdeb_menu_items-new-icon-trigger").on('click', function () {
	var height = jQuery(window).height()*0.35;
	tb_show("&nbsp;", _wdeb_menu_items.admin_base + "media-upload.php?wdeb_source=easy_blogging-new_menu_item&type=video&TB_iframe=1&width=640&height="+height);
	__oldSentToEditor = window.send_to_editor;
	window.send_to_editor = function (html) {
		var $el = $(html);
		var imageUrl = $el.attr("href");
		$("#wdeb_menu_items-new-icon").val(imageUrl);
		$("#wdeb_menu_items-new-icon-target").empty().append(
			$("<img>", { src: imageUrl, alt: "" })
		);
		tb_remove();
		window.send_to_editor = __oldSentToEditor;
	};
	return false;
});

$("#wdeb_registered_menu_items").on("change", function () {
	var option = this.options[this.selectedIndex];
	if (!option || !option.value) {
		return;
	}

	$("#wdeb_menu_items-new-title").val(option.dataset.title || "");
	$("#wdeb_menu_items-new-url").val(option.value);
	$("#wdeb_menu_items-new-help").val(option.dataset.help || "");
	$("#wdeb_menu_items-new-icon").val(option.dataset.icon || "");

	var $capability = $("#wdeb_menu_items-new-capability");
	var capability = option.dataset.capability || "";
	if ($capability.is("select") && !$capability.find("option").filter(function () {
		return this.value === capability;
	}).length) {
		$capability.replaceWith($("<input>", {
			type: "text",
			class: "widefat",
			id: $capability.attr("id"),
			name: $capability.attr("name"),
			value: capability
		}));
	} else {
		$capability.val(capability);
	}

	var $iconTarget = $("#wdeb_menu_items-new-icon-target").empty();
	if (/^dashicons-[a-z0-9-]+$/.test(option.dataset.icon || "")) {
		$iconTarget.append(
			$("<span>", {
				"class": "dashicons " + option.dataset.icon,
				"aria-hidden": "true"
			})
		);
	} else if (option.dataset.icon) {
		$iconTarget.append(
			$("<img>", { src: option.dataset.icon, alt: "" })
		);
	}
});

/* --- Remove menu item --- */
$(".wdeb_remove_menu_item").on('click', function () {
	$.post(_wdeb_menu_items.ajax_url, {
		"action": "wdeb_menu_items_remove_my_item",
		"url_id": $(this).closest("tr").find("input.wdeb_menu_items-url_id").val(),
		"nonce": _wdeb_menu_items.nonce
	}, function (data) {
		window.location.reload();
	});
	return false;
});

/* --- Resets --- */
$("#wdeb_menu_items-reset_order").on('click', function () {
	if (!confirm(l10nMenuItems.reset_order_confirmation)) return false;
	$.post(_wdeb_menu_items.ajax_url, {
		"action": "wdeb_menu_items_reset_order",
		"nonce": _wdeb_menu_items.nonce
	}, function (data) {
		window.location.reload();
	});
	return false;
});
$("#wdeb_menu_items-reset_items").on('click', function () {
	if (!confirm(l10nMenuItems.reset_items_confirmation)) return false;
	$.post(_wdeb_menu_items.ajax_url, {
		"action": "wdeb_menu_items_reset_items",
		"nonce": _wdeb_menu_items.nonce
	}, function (data) {
		window.location.reload();
	});
	return false;
});
$("#wdeb_menu_items-reset_all").on('click', function () {
	if (!confirm(l10nMenuItems.reset_all_confirmation)) return false;
	$.post(_wdeb_menu_items.ajax_url, {
		"action": "wdeb_menu_items_reset_all",
		"nonce": _wdeb_menu_items.nonce
	}, function (data) {
		window.location.reload();
	});
	return false;
});

$("#wdeb_menu_items-manual_capability").on('click', function () {
	var $me = $(this);
	var $select = $("#wdeb_menu_items-new-capability");
	if (!$select.length) return false;
	
	var $input = $('<input>', {
		type: 'text',
		class: 'widefat',
		id: $select.attr('id'),
		name: $select.attr('name'),
		value: $select.val()
	});

	$select.replaceWith($input);
	$me.remove();
	
	return false;
});

});
})(jQuery);