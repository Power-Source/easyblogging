<style>
/* Essential modern styles - inline with !important to override d defaults */
.wrap.wdeb-modern { background: #f8f9fa !important; padding: 20px !important; display: block !important; }
.wrap.wdeb-modern::before { content: "✓ Modern Styles Active" !important; display: block !important; background: #4CAF50 !important; color: white !important; padding: 10px !important; margin: -20px -20px 20px -20px !important; text-align: center !important; font-weight: bold !important; }
.wrap.wdeb-modern h1, .wrap.wdeb-modern h2 { color: #1a1a1a !important; font-weight: 600 !important; margin: 20px 0 30px 0 !important; border-bottom: 2px solid #007cba !important; padding-bottom: 15px !important; font-size: 24px !important; }
.wdeb-section { background: white !important; margin: 0 0 20px 0 !important; padding: 30px !important; border-radius: 8px !important; border-left: 4px solid #007cba !important; box-shadow: 0 1px 3px rgba(0,0,0,.08) !important; }
.wdeb-form-group { margin: 25px 0 !important; display: flex !important; gap: 20px !important; align-items: flex-start !important; }
.wdeb-form-label { flex: 0 0 300px !important; padding-top: 8px !important; }
.wdeb-form-label label { font-weight: 600 !important; color: #1a1a1a !important; margin: 0 0 5px 0 !important; display: block !important; font-size: 14px !important; }
.wdeb-form-control { flex: 1 !important; min-width: 0 !important; }
.wdeb-form-control p { margin: 10px 0 0 0 !important; color: #666 !important; font-size: 13px !important; }
.wdeb-toggle-switch { position: relative !important; display: inline-block !important; width: 56px !important; height: 32px !important; }
.wdeb-toggle-switch input { opacity: 0 !important; width: 0 !important; height: 0 !important; }
.wdeb-toggle-slider { position: absolute !important; cursor: pointer !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; background: #ccc !important; border-radius: 32px !important; transition: .3s !important; border: 2px solid transparent !important; }
.wdeb-toggle-slider:before { position: absolute !important; content: "" !important; height: 24px !important; width: 24px !important; left: 3px !important; bottom: 3px !important; background: white !important; border-radius: 50% !important; transition: .3s !important; }
.wdeb-toggle-switch input:checked + .wdeb-toggle-slider { background: #007cba !important; border-color: #007cba !important; }
.wdeb-toggle-switch input:checked + .wdeb-toggle-slider:before { transform: translateX(24px) !important; }
.wdeb-checkbox-list { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important; gap: 15px !important; }
.wdeb-checkbox-item { display: flex !important; align-items: center !important; gap: 10px !important; }
.wdeb-checkbox-item input[type="checkbox"] { width: 18px !important; height: 18px !important; cursor: pointer !important; accent-color: #007cba !important; }
.wdeb-checkbox-item label { flex: 1 !important; cursor: pointer !important; margin: 0 !important; color: #1a1a1a !important; font-weight: 500 !important; }
.wdeb-alert { padding: 15px 20px !important; border-radius: 4px !important; border-left: 4px solid !important; margin: 15px 0 !important; }
.wdeb-alert-info { background: #d6ebf7 !important; border-color: #0071a1 !important; color: #003d5c !important; }
.wdeb-theme-gallery { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important; gap: 20px !important; }
.wdeb-theme-item { position: relative !important; border: 3px solid #e5e7eb !important; border-radius: 8px !important; cursor: pointer !important; overflow: hidden !important; transition: all .2s !important; }
.wdeb-theme-item:hover { border-color: #007cba !important; transform: translateY(-2px) !important; box-shadow: 0 4px 12px rgba(0,0,0,.12) !important; }
.wdeb-theme-item input[type="radio"] { position: absolute !important; top: 10px !important; left: 10px !important; cursor: pointer !important; z-index: 2 !important; accent-color: #007cba !important; }
.wdeb-theme-item.selected { border-color: #007cba !important; }
.wdeb-theme-item.selected::after { content: "✓" !important; position: absolute !important; top: 10px !important; right: 10px !important; width: 30px !important; height: 30px !important; background: #007cba !important; color: white !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 3 !important; font-weight: bold !important; font-size: 16px !important; }
.wdeb-theme-screenshot { width: 100% !important; height: 200px !important; background: #f5f5f5 !important; }
.wdeb-theme-screenshot img { width: 100% !important; height: 100% !important; object-fit: cover !important; }
.wdeb-theme-name { padding: 12px !important; background: white !important; font-weight: 500 !important; color: #1a1a1a !important; font-size: 14px !important; }
.wdeb-logo-preview { margin: 15px 0 !important; max-width: 200px !important; }
.wdeb-logo-preview img { max-width: 100% !important; height: auto !important; border: 1px solid #ddd !important; border-radius: 4px !important; }
.wdeb-file-input-wrapper { position: relative !important; display: inline-block !important; cursor: pointer !important; }
.wdeb-file-input-wrapper input[type="file"] { position: absolute !important; left: -9999px !important; }
.wdeb-file-input-label { display: inline-block !important; padding: 10px 16px !important; background: #007cba !important; color: white !important; border-radius: 4px !important; font-weight: 500 !important; cursor: pointer !important; transition: .2s !important; }
.wdeb-file-input-label:hover { background: #005a87 !important; }
.submit { margin: 30px 0 !important; }
.submit input[type="submit"] { padding: 12px 32px !important; background: #007cba !important; color: white !important; border: none !important; border-radius: 4px !important; font-weight: 600 !important; cursor: pointer !important; font-size: 16px !important; height: auto !important; }
.submit input[type="submit"]:hover { background: #005a87 !important; }
</style>

<div class="wrap wdeb-modern">
	<h1>Easy Blogging Einstellungen</h1>

<?php if (defined('WP_NETWORK_ADMIN') && WP_NETWORK_ADMIN) { ?>
	<form action="settings.php" method="post" enctype="multipart/form-data">
<?php } else { ?>
	<form action="options.php" method="post" enctype="multipart/form-data">
<?php } ?>

	<?php settings_fields('wdeb'); ?>
	
	<div class="wdeb-section">
		<?php do_settings_sections('wdeb_options_page'); ?>
	</div>
	
	<div class="submit">
		<input name="Submit" type="submit" class="button button-primary" value="<?php esc_attr_e('Änderungen speichern'); ?>" />
	</div>
	</form>

</div>
<script type="text/javascript">
(function ($) {
	
$(function () {
	// Theme selection
	$('.wdeb-theme-item').on('click', function(e) {
		var $radio = $(this).find('input[type="radio"]');
		if (e.target.tagName !== 'INPUT') {
			$radio.prop('checked', true);
		}
		$('.wdeb-theme-item').removeClass('selected');
		$(this).addClass('selected');
	});
	
	// Initialize selected theme
	$('.wdeb-theme-item input[type="radio"]:checked').closest('.wdeb-theme-item').addClass('selected');

	// Logo removal functionality
	$("#wdeb-logo-remove_logo").on('click', function () {
		$("#wdeb-logo-custom_logo").val('');
		$(".wdeb-logo-preview, .wdeb-logo-actions").fadeOut(300, function() { $(this).remove(); });
		return false;
	});
	
	// File input styling
	$('.wdeb-file-input-wrapper input[type="file"]').on('change', function() {
		var fileName = this.files[0]?.name || 'Datei auswählen';
		$(this).siblings('.wdeb-file-input-label').text(fileName);
	});
});
})(jQuery);
</script>
