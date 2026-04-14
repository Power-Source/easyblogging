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

		// Plugin activation toggles (no reload)
		initPluginToggles();
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
			// Generic toggle hook point for future enhancements.
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
	 * Initialize plugin activation toggles.
	 */
	function initPluginToggles() {
		$(document).on('change', '.wdeb-plugin-toggle', function() {
			var $toggle = $(this);
			var pluginId = $toggle.data('plugin-id');
			var isActive = $toggle.is(':checked');
			var action = isActive ? 'wdeb_activate_plugin' : 'wdeb_deactivate_plugin';
			var nonce = (window.wdebSettings && window.wdebSettings.pluginNonce) ? window.wdebSettings.pluginNonce : '';
			var ajaxUrl = (window.wdebSettings && window.wdebSettings.ajaxUrl) ? window.wdebSettings.ajaxUrl : ((typeof window.ajaxurl !== 'undefined') ? window.ajaxurl : '');
			var $row = $toggle.closest('.wdeb-plugin-row');
			var $feedback = $row.find('.wdeb-plugin-feedback');
			var previousStatus = $row.find('.wdeb-plugin-status').text();

			if (!pluginId || !nonce || !ajaxUrl) {
				$toggle.prop('checked', !isActive);
				setPluginFeedback($feedback, 'AJAX-Konfiguration fehlt.', 'error');
				showNotice('AJAX-Konfiguration unvollstaendig. Bitte Seite neu laden.', 'error');
				return;
			}

			$row.addClass('is-loading');
			$toggle.prop('disabled', true);
			setPluginFeedback($feedback, 'Speichert...', 'pending');
			updatePluginRowState($row, isActive);

			$.ajax({
				url: ajaxUrl,
				type: 'POST',
				cache: false,
				timeout: 10000,
				data: {
					action: action,
					plugin: pluginId,
					nonce: nonce
				}
			})
			.done(function(response) {
				if (!response || !response.success) {
					$toggle.prop('checked', !isActive);
					$row.find('.wdeb-plugin-status').text(previousStatus || (!isActive ? 'Aktiv' : 'Inaktiv'));
					$row.toggleClass('is-active', !isActive);
					setPluginFeedback($feedback, 'Speichern fehlgeschlagen.', 'error');
					showNotice('Status konnte nicht gespeichert werden.', 'error');
					return;
				}

				setPluginFeedback($feedback, 'Gespeichert.', 'success');
				window.setTimeout(function() {
					if ($row.hasClass('is-loading')) {
						return;
					}
					setPluginFeedback($feedback, '', '');
				}, 2200);
			})
			.fail(function() {
				$toggle.prop('checked', !isActive);
				$row.find('.wdeb-plugin-status').text(previousStatus || (!isActive ? 'Aktiv' : 'Inaktiv'));
				$row.toggleClass('is-active', !isActive);
				setPluginFeedback($feedback, 'Fehler beim Speichern.', 'error');
				showNotice('Aktualisierung fehlgeschlagen. Bitte erneut versuchen.', 'error');
			})
			.always(function() {
				$row.removeClass('is-loading');
				$toggle.prop('disabled', false);
			});
		});
	}

	function updatePluginRowState($row, isActive) {
		$row.toggleClass('is-active', !!isActive);
		$row.find('.wdeb-plugin-status').text(isActive ? 'Aktiv' : 'Inaktiv');
	}

	function setPluginFeedback($feedback, message, state) {
		$feedback.removeClass('is-success is-error');
		if (state === 'success') {
			$feedback.addClass('is-success');
		}
		if (state === 'error') {
			$feedback.addClass('is-error');
		}
		$feedback.text(message || '');
	}

	function showNotice(message, type) {
		if (typeof window.wdebShowNotification === 'function') {
			window.wdebShowNotification(message, type || 'info');
			return;
		}
		window.alert(message);
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
