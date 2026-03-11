(function () {
	'use strict';

	let sortableInstance = null;
	const menuData = (typeof window.wdebMenuData !== 'undefined')
		? window.wdebMenuData
		: {
			ajax_url: (typeof window.ajaxurl !== 'undefined') ? window.ajaxurl : '',
			admin_base: '',
			nonce: '',
			l10n: {
				reset_order_confirmation: 'Warnung: Dadurch werden alle Deine benutzerdefinierten Bestellungen entfernt und auf die Standardeinstellungen zurueckgesetzt. Fortsetzen?',
				reset_items_confirmation: 'Warnung: Dadurch werden alle neuen Menueelemente entfernt, die Du hinzugefuegt hast. Fortsetzen?',
				reset_all_confirmation: 'Warnung: Dadurch werden alle Anpassungen entfernt. Fortsetzen?'
			}
		};

	function initMenuManager() {
		if (typeof window.wdebMenuData === 'undefined') {
			console.warn('wdebMenuData not found, running with fallback data');
		}

		console.log('✓ Menu Manager initializing...');
		console.log('✓ Sortable available:', typeof Sortable !== 'undefined');

		// Initialize the sortable table
		const tableBody = document.querySelector('table#wdeb_show_hide_root tbody');
		if (!tableBody) {
			console.error('✗ Table body not found');
			return;
		}

		console.log('✓ Table body found with', tableBody.querySelectorAll('tr').length, 'rows');

		// Wait a tick to ensure Sortable is fully loaded
		if (typeof Sortable === 'undefined') {
			console.error('✗ Sortable.js library not loaded!');
			console.log('Waiting for Sortable.js to load...');
			setTimeout(initMenuManager, 500);
			return;
		}

		try {
			console.log('Creating Sortable instance...');
			
			// Try first with handle - if no handles found, fall back to entire row
			const handles = tableBody.querySelectorAll('.wdeb-drag-handle');
			const useHandle = handles.length > 0;
			const handleSelector = useHandle ? '.wdeb-drag-handle' : undefined;
			
			console.log('✓ Found', handles.length, 'drag handles in table');
			console.log('Using handle selector:', useHandle ? 'yes (.wdeb-drag-handle)' : 'no (entire row)');

			if (sortableInstance) {
				sortableInstance.destroy();
				sortableInstance = null;
			}
			
			sortableInstance = Sortable.create(tableBody, {
				animation: 150,
				ghostClass: 'wdeb-sortable-ghost',
				dragClass: 'wdeb-sortable-drag',
				handle: handleSelector,
				draggable: 'tr',
				direction: 'vertical',
				forceFallback: true,
				fallbackOnBody: true,
				fallbackTolerance: 3,
				invertSwap: false,
				scrollSensitivity: 30,
				scrollSpeed: 10,
				delay: 0,
				delayOnTouchOnly: true,
				
				onStart: function(evt) {
					console.log('✓ Drag started on row:', evt.item);
					tableBody.classList.add('sorting');
				},
				
				onEnd: function(evt) {
					console.log('✓ Drag ended. Old index:', evt.oldIndex, 'New index:', evt.newIndex);
					tableBody.classList.remove('sorting');
					
					// Only update if position changed
					if (evt.oldIndex !== evt.newIndex) {
						updateMenuOrder();
					}
				},
				
				onMove: function(evt) {
					return true; // Allow movement
				}
			});
			
			console.log('✓ Sortable instance created successfully');
			
			if (handles.length === 0) {
				console.warn('⚠ No drag handles found, using entire row for dragging');
			}
			
		} catch (error) {
			console.error('✗ Error creating Sortable instance:', error);
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

		// Icon selection via input
		const iconPreviewBtn = document.getElementById('wdeb_menu_items-new-icon-preview-btn');
		if (iconPreviewBtn) {
			iconPreviewBtn.addEventListener('click', function(e) {
				e.preventDefault();
				const iconUrl = document.getElementById('wdeb_menu_items-new-icon').value.trim();
				const target = document.getElementById('wdeb_menu_items-new-icon-target');
				
				if (!iconUrl) {
					target.innerHTML = '<small style="color: #999;">Bitte geben Sie eine URL ein</small>';
					return;
				}
				
				// Validate it's a URL
				try {
					new URL(iconUrl);
				} catch (e) {
					target.innerHTML = '<small style="color: #dc3545;">Ungültige URL</small>';
					return;
				}
				
				// Create image to test if it loads
				const img = document.createElement('img');
				img.style.maxHeight = '80px';
				img.style.borderRadius = '3px';
				img.style.boxShadow = '0 1px 3px rgba(0,0,0,0.2)';
				
				img.onload = function() {
					target.innerHTML = '';
					target.appendChild(img);
				};
				
				img.onerror = function() {
					target.innerHTML = '<small style="color: #dc3545;">Bild konnte nicht geladen werden</small>';
				};
				
				img.src = iconUrl;
			});
		}

		// Auto-preview on input change
		const iconInput = document.getElementById('wdeb_menu_items-new-icon');
		if (iconInput) {
			iconInput.addEventListener('change', function() {
				document.getElementById('wdeb_menu_items-new-icon-target').innerHTML = '';
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
				if (!confirm(menuData.l10n.reset_order_confirmation)) return;
				sendMenuAction('wdeb_menu_items_reset_order', {}, function() {
					window.location.reload();
				});
			});
		}

		const resetItemsBtn = document.getElementById('wdeb_menu_items-reset_items');
		if (resetItemsBtn) {
			resetItemsBtn.addEventListener('click', function() {
				if (!confirm(menuData.l10n.reset_items_confirmation)) return;
				sendMenuAction('wdeb_menu_items_reset_items', {}, function() {
					window.location.reload();
				});
			});
		}

		const resetAllBtn = document.getElementById('wdeb_menu_items-reset_all');
		if (resetAllBtn) {
			resetAllBtn.addEventListener('click', function() {
				if (!confirm(menuData.l10n.reset_all_confirmation)) return;
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
		
		// Update the hidden order inputs in the table (important for form submission)
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
		if (!menuData.ajax_url) {
			alert('Fehler: ajax_url fehlt (wdebMenuData/ajaxurl nicht verfuegbar).');
			return;
		}

		const formData = new FormData();
		formData.append('action', action);
		formData.append('nonce', menuData.nonce);
		
		for (const key in data) {
			if (data.hasOwnProperty(key)) {
				formData.append(key, data[key]);
			}
		}

		fetch(menuData.ajax_url, {
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

	// Also try after a short delay for async-loaded content
	setTimeout(function() {
		if (!sortableInstance) {
			console.warn('⚠ Sortable not initialized after delay, trying again...');
			initMenuManager();
		}
	}, 1000);
})();