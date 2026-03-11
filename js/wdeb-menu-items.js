(function () {
	'use strict';

	function initMenuManager() {
		// Only run if we have the required data
		if (typeof wdebMenuData === 'undefined') {
			console.error('wdebMenuData not found');
			return;
		}

		console.log('Menu Manager initializing...');
		console.log('Sortable available:', typeof Sortable !== 'undefined');

		// Initialize the sortable table
		const tableBody = document.querySelector('table#wdeb_show_hide_root tbody');
		if (!tableBody) {
			console.error('Table body not found');
			return;
		}

		console.log('Table body found:', tableBody);

		if (typeof Sortable !== 'undefined') {
			console.log('Initializing Sortable.js...');
			
			const sortableInstance = Sortable.create(tableBody, {
				animation: 150,
				ghostClass: 'wdeb-sortable-ghost',
				dragClass: 'wdeb-sortable-drag',
				handle: '.wdeb-drag-handle',
				forceFallback: false,
				invertSwap: false,
				direction: undefined, // auto-detect
				onStart: function(evt) {
					console.log('Drag started:', evt);
					tableBody.classList.add('sorting');
				},
				onEnd: function(evt) {
					console.log('Drag ended:', evt);
					tableBody.classList.remove('sorting');
					updateMenuOrder();
				},
				onMove: function(evt) {
					// Allow movement
					return true;
				}
			});
			
			console.log('Sortable instance created:', sortableInstance);
		} else {
			console.error('Sortable.js library not loaded!');
			// Fallback: show alert to user
			setTimeout(function() {
				console.error('Sortable is still not available');
			}, 2000);
		}

		// Check/Uncheck all items
		document.querySelectorAll('.wdeb_check_all_items').forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				document.querySelectorAll('table#wdeb_show_hide_root tbody input[type="checkbox"]').forEach(checkbox => {
					checkbox.checked = true;
				});
			});
		});

		document.querySelectorAll('.wdeb_uncheck_all_items').forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				document.querySelectorAll('table#wdeb_show_hide_root tbody input[type="checkbox"]').forEach(checkbox => {
					checkbox.checked = false;
				});
			});
		});

		// Icon selection
		const iconTrigger = document.getElementById('wdeb_menu_items-new-icon-trigger');
		if (iconTrigger) {
			iconTrigger.addEventListener('click', function(e) {
				e.preventDefault();
				const height = Math.round(window.innerHeight * 0.35);
				const tbUrl = wdebMenuData.admin_base + 'media-upload.php?wdeb_source=easy_blogging-new_menu_item&type=image&TB_iframe=1&width=640&height=' + height;
				
				if (typeof tb_show !== 'undefined') {
					const oldSendToEditor = window.send_to_editor;
					window.send_to_editor = function(html) {
						const parser = new DOMParser();
						const doc = parser.parseFromString(html, 'text/html');
						const img = doc.querySelector('img');
						const href = img ? img.src : html;
						
						document.getElementById('wdeb_menu_items-new-icon').value = href;
						const target = document.getElementById('wdeb_menu_items-new-icon-target');
						target.innerHTML = '<img src="' + escapeHtml(href) + '" style="max-width: 100px;" />';
						
						if (typeof tb_remove !== 'undefined') {
							tb_remove();
						}
						window.send_to_editor = oldSendToEditor;
					};
					tb_show('&nbsp;', tbUrl);
				}
			});
		}

		// Remove menu item
		document.querySelectorAll('.wdeb_remove_menu_item').forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				if (!confirm('Diesen Menüpunkt wirklich entfernen?')) return false;
				
				const urlId = this.closest('tr').querySelector('input.wdeb_menu_items-url_id').value;
				sendMenuAction('wdeb_menu_items_remove_my_item', { url_id: urlId }, function() {
					window.location.reload();
				});
			});
		});

		// Reset buttons
		const resetOrderBtn = document.getElementById('wdeb_menu_items-reset_order');
		if (resetOrderBtn) {
			resetOrderBtn.addEventListener('click', function() {
				if (!confirm(wdebMenuData.l10n.reset_order_confirmation)) return;
				sendMenuAction('wdeb_menu_items_reset_order', {}, function() {
					window.location.reload();
				});
			});
		}

		const resetItemsBtn = document.getElementById('wdeb_menu_items-reset_items');
		if (resetItemsBtn) {
			resetItemsBtn.addEventListener('click', function() {
				if (!confirm(wdebMenuData.l10n.reset_items_confirmation)) return;
				sendMenuAction('wdeb_menu_items_reset_items', {}, function() {
					window.location.reload();
				});
			});
		}

		const resetAllBtn = document.getElementById('wdeb_menu_items-reset_all');
		if (resetAllBtn) {
			resetAllBtn.addEventListener('click', function() {
				if (!confirm(wdebMenuData.l10n.reset_all_confirmation)) return;
				sendMenuAction('wdeb_menu_items_reset_all', {}, function() {
					window.location.reload();
				});
			});
		}

		// Manual capability toggle
		const manualCapabilityLink = document.getElementById('wdeb_menu_items-manual_capability');
		if (manualCapabilityLink) {
			manualCapabilityLink.addEventListener('click', function(e) {
				e.preventDefault();
				const select = document.getElementById('wdeb_menu_items-new-capability');
				if (!select) return;
				
				const input = document.createElement('input');
				input.type = 'text';
				input.className = 'widefat';
				input.id = select.id;
				input.name = select.name;
				input.value = select.value;
				
				select.replaceWith(input);
				this.remove();
			});
		}
	}

	function updateMenuOrder() {
		const inputs = document.querySelectorAll('input.wdeb_menu_items-url_id');
		const order = Array.from(inputs).map(input => input.value);
		
		console.log('Menu order updated:', order);
		
		// Update the hidden order inputs in the table
		const tbody = document.querySelector('table#wdeb_show_hide_root tbody');
		if (tbody) {
			const rows = tbody.querySelectorAll('tr');
			rows.forEach((row, index) => {
				const hiddenInput = row.querySelector('input.wdeb_menu_items-url_id');
				if (hiddenInput) {
					hiddenInput.value = order[index] || '';
				}
			});
		}
	}

	function sendMenuAction(action, data, callback) {
		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', wdebMenuData.nonce);
		
		for (const key in data) {
			if (data.hasOwnProperty(key)) {
				formData.append(key, data[key]);
			}
		}

		fetch(wdebMenuData.ajax_url, {
			method: 'POST',
			body: formData
		})
		.then(response => response.json())
		.then(json => {
			if (json.success) {
				if (typeof callback === 'function') {
					callback(json.data);
				}
			} else {
				const message = json.data && json.data.message ? json.data.message : 'Es ist ein Fehler aufgetreten';
				alert('Fehler: ' + message);
				console.error('Menu action failed:', json);
			}
		})
		.catch(error => {
			alert('Fehler beim Senden der Anfrage: ' + error.message);
			console.error('Fetch error:', error);
		});
	}

	function escapeHtml(text) {
		const map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(/[&<>"']/g, m => map[m]);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMenuManager);
	} else {
		initMenuManager();
	}
})();