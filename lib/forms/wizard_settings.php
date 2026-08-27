<div class="wrap">
	<h2><?php _e('Easy Wizard settings','wdeb'); ?></h2>

<?php if (defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN) { ?>
	<form action="settings.php" method="post" enctype="multipart/form-data">
<?php } else { ?>
	<form action="options.php" method="post" enctype="multipart/form-data">
<?php } ?>

	<?php settings_fields('wdeb_wizard'); ?>
	<?php do_settings_sections('wdeb_wizard'); ?>
	<p class="submit">
		<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Änderungen speichern', 'wdeb'); ?>" />
	</p>
	</form>

</div>

<dialog id="wdeb_step_edit_dialog">
	<p>
		<label><?php _e("Titel", 'wdeb'); ?></label>
			<input class="widefat" id="wdeb_step_edit_dialog_title" />
	</p>
	<p>
		<label><?php _e("URL", 'wdeb'); ?></label>
			<input class="widefat" id="wdeb_step_edit_dialog_url" />
	</p>
	<p>
		<label><?php _e("Hilfe", 'wdeb'); ?></label>
			<textarea class="widefat" id="wdeb_step_edit_dialog_help"></textarea>
	</p>
	<p class="submit">
	<button type="button" class="button button-primary" id="wdeb_step_edit_save">
		<?php esc_html_e( 'Speichern', 'wdeb' ); ?>
	</button>
	<button type="button" class="button" id="wdeb_step_edit_cancel">
		<?php esc_html_e( 'Abbrechen', 'wdeb' ); ?>
	</button>
</p>
</dialog>

<style type="text/css">
.wdeb_step {
	width: 400px;
	background: #fff;
	margin-bottom: 1em;
	cursor: move;
	padding: .5em;
	padding-left: 2em;
	border: 2px solid #E1E1E1;
	position: relative;
	overflow: hidden;
}
.wdeb_step:before {
	content: "";
	position: absolute;
	top: .7em;
	left: .5em;
	width: 1em;
	height: 2px;
	border-top: 6px double #E1E1E1;
	border-bottom: 2px solid #E1E1E1;
}
.wdeb_step h4 {
	margin: 0;
	float: left;
}
.wdeb_step .wdeb_step_actions {
	float: right;
}
#wdeb_step_edit_dialog {
	width: 600px;
	max-width: calc(100vw - 40px);
	padding: 10px 20px;
	border: 0;
	border-radius: 4px;
	box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

#wdeb_step_edit_dialog::backdrop {
	background: rgba(0, 0, 0, 0.5);
}
</style>
<script type="text/javascript">
(function ($) {
$(function () {

function updateUrlPreview () {
	var type = "<?php echo site_url(); ?>" + $("#wdeb_last_wizard_step_url_type").val();
	var url = $("#wdeb_last_wizard_step_url").val();

	var preview = type + url;

	$("#wdeb_url_preview code").text(preview);

	return true;
}

const steps = document.getElementById('wdeb_steps');

if (steps) {
	let draggedItem = null;

	steps.querySelectorAll(':scope > li').forEach(function (item) {
		item.draggable = true;

		item.addEventListener('dragstart', function () {
			draggedItem = item;
			item.classList.add('wdeb-dragging');
		});

		item.addEventListener('dragend', function () {
			item.classList.remove('wdeb-dragging');
			draggedItem = null;
			updateStepNumbers();
		});

		item.addEventListener('dragover', function (event) {
			event.preventDefault();

			if (!draggedItem || draggedItem === item) {
				return;
			}

			const rect = item.getBoundingClientRect();
			const insertAfter = event.clientY > rect.top + rect.height / 2;

			steps.insertBefore(
				draggedItem,
				insertAfter ? item.nextSibling : item
			);
		});
	});

	function updateStepNumbers() {
		steps.querySelectorAll(':scope > li').forEach(function (item, index) {
			const count = item.querySelector('h4 .wdeb_step_count');

			if (count) {
				count.textContent = index + 1;
			}
		});
	}
}

$(".wdeb_step_delete").on('click', function () {
	$(this).parents('li.wdeb_step').remove();
	return false;
});

$("#wdeb_last_wizard_step_url_type").on("change", updateUrlPreview);
$("#wdeb_last_wizard_step_url").on("input", updateUrlPreview);

$(".wdeb_step_edit").on('click', function () {
	const $parent = $(this).parents('li.wdeb_step');
	const $url = $parent.find('input:hidden.wdeb_step_url');
	const $title = $parent.find('input:hidden.wdeb_step_title');
	const $help = $parent.find('input:hidden.wdeb_step_help');
	const $titleSpan = $parent.find('h4 .wdeb_step_title');
	const dialog = document.getElementById('wdeb_step_edit_dialog');

	if (!dialog) {
		return false;
	}

	$("#wdeb_step_edit_dialog_title").val($title.val());
	$("#wdeb_step_edit_dialog_url").val($url.val());
	$("#wdeb_step_edit_dialog_help").val($help.val());

	dialog.showModal();

	$("#wdeb_step_edit_save").off('click').on('click', function () {
		$title.val($("#wdeb_step_edit_dialog_title").val());
		$titleSpan.text($("#wdeb_step_edit_dialog_title").val());
		$url.val($("#wdeb_step_edit_dialog_url").val());
		$help.val($("#wdeb_step_edit_dialog_help").val());

		dialog.close();
	});

	$("#wdeb_step_edit_cancel").off('click').on('click', function () {
		dialog.close();
	});

	return false;
});

updateUrlPreview();

});
})(jQuery);
</script>