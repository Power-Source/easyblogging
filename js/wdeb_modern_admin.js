/**
 * Easy Blogging - Modern Admin UI
 * Handles modern form interactions and theme selection
 */

(function($) {
	'use strict';

	$(function() {
		// Theme selection
		initThemeSelection();
		
		// File input labels
		initFileInputs();
		
		// Toggle switches
		initToggleSwitches();
		
		// Logo removal
		initLogoRemoval();
	});

	/**
	 * Initialize theme gallery selection
	 */
	function initThemeSelection() {
		$('.wdeb-theme-item').on('click', function() {
			var $item = $(this);
			var $radio = $item.find('input[type="radio"]');
			
			// Unselect all items
			$('.wdeb-theme-item').removeClass('selected');
			
			// Select clicked item
			$item.addClass('selected');
			$radio.prop('checked', true);
		});

		// Mark originally selected theme
		$('.wdeb-theme-item input[type="radio"]:checked').closest('.wdeb-theme-item').addClass('selected');
	}

	/**
	 * Initialize file input labels to show selected filename
	 */
	function initFileInputs() {
		$('.wdeb-file-input-wrapper').each(function() {
			var $wrapper = $(this);
			var $input = $wrapper.find('input[type="file"]');
			var $label = $wrapper.find('.wdeb-file-input-label');
			var originalText = $label.text();

			$input.on('change', function() {
				var fileName = this.files && this.files[0]?.name;
				if (fileName) {
					$label.text(fileName);
				} else {
					$label.text(originalText);
				}
			});
		});
	}

	/**
	 * Initialize toggle switches with keyboard support
	 */
	function initToggleSwitches() {
		$('.wdeb-toggle-switch input').on('change', function() {
			// Could add custom change handlers here if needed
			console.log('Toggle changed:', this.checked);
		});

		// Keyboard support for toggle switches
		$('.wdeb-toggle-switch input').on('keydown', function(e) {
			if (e.keyCode === 32 || e.keyCode === 13) { // spacebar or enter
				e.preventDefault();
				$(this).prop('checked', !$(this).prop('checked')).trigger('change');
			}
		});
	}

	/**
	 * Initialize logo removal functionality
	 */
	function initLogoRemoval() {
		$(document).on('click', '#wdeb-logo-remove_logo', function(e) {
			e.preventDefault();
			
			// Clear the hidden input
			$('#wdeb-logo-custom_logo').val('');
			
			// Remove preview and actions
			$('.wdeb-logo-preview').fadeOut(200, function() {
				$(this).remove();
			});
			
			$('.wdeb-logo-actions').fadeOut(200, function() {
				$(this).remove();
			});
		});
	}

	/**
	 * Utility function to show notifications
	 */
	window.wdebShowNotification = function(message, type) {
		type = type || 'info';
		var alertClass = 'wdeb-alert-' + type;
		var $notification = $('<div class="wdeb-alert ' + alertClass + '">' + message + '</div>');
		
		$('body').prepend($notification);
		
		setTimeout(function() {
			$notification.fadeOut(200, function() {
				$(this).remove();
			});
		}, 3000);
	};

})(jQuery);
