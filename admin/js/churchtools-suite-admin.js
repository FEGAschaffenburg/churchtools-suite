/**
 * ChurchTools Suite Admin JS
 * Eigenständiges JavaScript ohne jQuery
 *
 * @package ChurchTools_Suite
 * @since   0.2.1.0
 */

(function() {
	'use strict';

	// DOM Ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	function init() {
		initTabs();
		initSyncButton();
		initForms();
		initTestConnection();
		initCalendarSync();
		initCalendarSelection();
		initEventSync();
	}

	/**
	 * Tab Navigation
	 */
	function initTabs() {
		const tabs = document.querySelectorAll('.cts-tab');
		
		tabs.forEach(tab => {
			tab.addEventListener('click', function(e) {
				// Let WordPress handle the URL change
				// This is just for visual feedback
			});
		});
	}

	/**
	 * Sync Button
	 */
	function initSyncButton() {
		const syncButton = document.getElementById('cts-sync-now');
		if (!syncButton) return;

		syncButton.addEventListener('click', function() {
			const progress = document.getElementById('cts-sync-progress');
			const result = document.getElementById('cts-sync-result');
			
			if (progress) progress.style.display = 'block';
			if (result) result.innerHTML = '';
			
			syncButton.disabled = true;
			syncButton.textContent = 'Synchronisiere...';

			// AJAX call (wird später implementiert)
			fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_sync_now',
					nonce: churchtoolsSuite.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (progress) progress.style.display = 'none';
				
				if (data.success) {
					if (result) {
						result.innerHTML = '<div class="cts-notice cts-notice-success"><p>' + 
							(data.data.message || 'Synchronisation erfolgreich!') + 
							'</p></div>';
					}
				} else {
					if (result) {
						result.innerHTML = '<div class="cts-notice cts-notice-error"><p>' + 
							(data.data.message || 'Synchronisation fehlgeschlagen!') + 
							'</p></div>';
					}
				}
			})
			.catch(error => {
				if (progress) progress.style.display = 'none';
				if (result) {
					result.innerHTML = '<div class="cts-notice cts-notice-error"><p>Fehler: ' + 
						error.message + 
						'</p></div>';
				}
			})
			.finally(() => {
				syncButton.disabled = false;
				syncButton.textContent = 'Jetzt synchronisieren';
			});
		});
	}

	/**
	 * Form Enhancements
	 */
	function initForms() {
		// Auto-dismiss notices
		const dismissButtons = document.querySelectorAll('.cts-notice-dismiss');
		dismissButtons.forEach(button => {
			button.addEventListener('click', function() {
				this.closest('.cts-notice').style.display = 'none';
			});
		});

		// Form validation
		const forms = document.querySelectorAll('.cts-form');
		forms.forEach(form => {
			form.addEventListener('submit', function(e) {
				const requiredFields = form.querySelectorAll('[required]');
				let valid = true;

				requiredFields.forEach(field => {
					if (!field.value.trim()) {
						valid = false;
						field.style.borderColor = '#dc3232';
					} else {
						field.style.borderColor = '';
					}
				});

				if (!valid) {
					e.preventDefault();
					alert('Bitte füllen Sie alle erforderlichen Felder aus.');
				}
			});
		});
	}

	/**
	 * Test Connection Button
	 */
	function initTestConnection() {
		const testButton = document.getElementById('cts-test-connection');
		if (!testButton) return;

		testButton.addEventListener('click', function() {
			const resultDiv = document.getElementById('cts-connection-result');
			
			if (resultDiv) {
				resultDiv.style.display = 'none';
				resultDiv.innerHTML = '';
			}
			
			testButton.disabled = true;
			const originalText = testButton.innerHTML;
			testButton.innerHTML = '<span>⏳</span> Teste Verbindung...';

			fetch(churchtoolsSuite.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_test_connection',
					nonce: churchtoolsSuite.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					
					if (data.success) {
						let message = data.data.message || 'Verbindung erfolgreich!';
						
						// User-Info anzeigen wenn verfügbar
						if (data.data.user_info) {
							const user = data.data.user_info;
							message += '<br><br><strong>Eingeloggt als:</strong><br>';
							if (user.firstName && user.lastName) {
								message += user.firstName + ' ' + user.lastName;
							}
							if (user.email) {
								message += ' (' + user.email + ')';
							}
						}
						
						resultDiv.innerHTML = '<div class="cts-notice cts-notice-success"><p>' + 
							message + 
							'</p></div>';
					} else {
						resultDiv.innerHTML = '<div class="cts-notice cts-notice-error"><p>' + 
							(data.data.message || 'Verbindung fehlgeschlagen!') + 
							'</p></div>';
					}
				}
			})
			.catch(error => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					resultDiv.innerHTML = '<div class="cts-notice cts-notice-error"><p>Fehler: ' + 
						error.message + 
						'</p></div>';
				}
			})
			.finally(() => {
				testButton.disabled = false;
				testButton.innerHTML = originalText;
			});
		});
	}

	/**
	 * Calendar Sync Button
	 */
	function initCalendarSync() {
		const syncButton = document.getElementById('cts-sync-calendars-btn');
		if (!syncButton) return;

		syncButton.addEventListener('click', function() {
			const resultDiv = document.getElementById('cts-sync-calendars-result');
			
			if (resultDiv) {
				resultDiv.style.display = 'none';
				resultDiv.innerHTML = '';
			}
			
			syncButton.disabled = true;
			const originalText = syncButton.innerHTML;
			syncButton.innerHTML = '<span class="dashicons dashicons-update"></span> Synchronisiere...';

			fetch(churchtoolsSuite.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_sync_calendars',
					nonce: churchtoolsSuite.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					
					if (data.success) {
						resultDiv.innerHTML = '<div class="notice notice-success inline"><p>' + 
							(data.data.message || 'Synchronisation erfolgreich!') + 
							'</p></div>';
						
						// Seite neu laden nach erfolgreicher Sync
						setTimeout(() => {
							location.reload();
						}, 1500);
					} else {
						resultDiv.innerHTML = '<div class="notice notice-error inline"><p>' + 
							(data.data.message || 'Synchronisation fehlgeschlagen!') + 
							'</p></div>';
					}
				}
			})
			.catch(error => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					resultDiv.innerHTML = '<div class="notice notice-error inline"><p>Fehler: ' + 
						error.message + 
						'</p></div>';
				}
			})
			.finally(() => {
				syncButton.disabled = false;
				syncButton.innerHTML = originalText;
			});
		});
	}

	/**
	 * Calendar Selection Form
	 */
	function initCalendarSelection() {
		const form = document.getElementById('cts-calendar-selection-form');
		if (!form) return;

		// Select all checkbox
		const selectAllCheckbox = document.getElementById('cts-select-all-calendars');
		const calendarCheckboxes = document.querySelectorAll('.cts-calendar-checkbox');

		if (selectAllCheckbox && calendarCheckboxes.length > 0) {
			selectAllCheckbox.addEventListener('change', function() {
				calendarCheckboxes.forEach(checkbox => {
					checkbox.checked = selectAllCheckbox.checked;
				});
			});

			calendarCheckboxes.forEach(checkbox => {
				checkbox.addEventListener('change', function() {
					const totalCheckboxes = calendarCheckboxes.length;
					const checkedCheckboxes = document.querySelectorAll('.cts-calendar-checkbox:checked').length;
					selectAllCheckbox.checked = totalCheckboxes === checkedCheckboxes;
				});
			});
		}

		// Form submission
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			
			const resultDiv = document.getElementById('cts-calendar-selection-result');
			const submitButton = form.querySelector('button[type="submit"]');
			
			if (resultDiv) {
				resultDiv.style.display = 'none';
				resultDiv.innerHTML = '';
			}
			
			if (submitButton) {
				submitButton.disabled = true;
				const originalText = submitButton.innerHTML;
				submitButton.innerHTML = '<span class="dashicons dashicons-update"></span> Speichere...';
			}

			// Collect selected calendar IDs
			const selectedIds = [];
			calendarCheckboxes.forEach(checkbox => {
				if (checkbox.checked) {
					selectedIds.push(checkbox.value);
				}
			});

			// Build form data with array support
			const formData = new URLSearchParams();
			formData.append('action', 'cts_save_calendar_selection');
			formData.append('nonce', churchtoolsSuite.nonce);
			selectedIds.forEach(id => {
				formData.append('selected_ids[]', id);
			});

			fetch(churchtoolsSuite.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					
					if (data.success) {
						resultDiv.innerHTML = '<div class="notice notice-success inline"><p>' + 
							(data.data.message || 'Auswahl gespeichert!') + 
							'</p></div>';
					} else {
						resultDiv.innerHTML = '<div class="notice notice-error inline"><p>' + 
							(data.data.message || 'Speichern fehlgeschlagen!') + 
							'</p></div>';
					}
				}
			})
			.catch(error => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					resultDiv.innerHTML = '<div class="notice notice-error inline"><p>Fehler: ' + 
						error.message + 
						'</p></div>';
				}
			})
			.finally(() => {
				if (submitButton) {
					submitButton.disabled = false;
					submitButton.innerHTML = originalText;
				}
			});
		});
	}

	/**
	 * Event Sync Button
	 */
	function initEventSync() {
		const syncButton = document.getElementById('cts-sync-events-btn');
		if (!syncButton) return;

		syncButton.addEventListener('click', function() {
			const resultDiv = document.getElementById('cts-sync-events-result');
			
			if (resultDiv) {
				resultDiv.style.display = 'none';
				resultDiv.innerHTML = '';
			}
			
			syncButton.disabled = true;
			const originalText = syncButton.innerHTML;
			syncButton.innerHTML = '<span class="dashicons dashicons-calendar"></span> Synchronisiere...';

			fetch(churchtoolsSuite.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'cts_sync_events',
					nonce: churchtoolsSuite.nonce
				})
			})
			.then(response => response.json())
			.then(data => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					
					if (data.success) {
						resultDiv.innerHTML = '<div class="notice notice-success inline"><p>' + 
							(data.data.message || 'Synchronisation erfolgreich!') + 
							'</p></div>';
						
						// Seite neu laden nach erfolgreicher Sync
						setTimeout(() => {
							location.reload();
						}, 1500);
					} else {
						resultDiv.innerHTML = '<div class="notice notice-error inline"><p>' + 
							(data.data.message || 'Synchronisation fehlgeschlagen!') + 
							'</p></div>';
					}
				}
			})
			.catch(error => {
				if (resultDiv) {
					resultDiv.style.display = 'block';
					resultDiv.innerHTML = '<div class="notice notice-error inline"><p>Fehler: ' + 
						error.message + 
						'</p></div>';
				}
			})
			.finally(() => {
				syncButton.disabled = false;
				syncButton.innerHTML = originalText;
			});
		});
	}

	/**
	 * Helper: Show Loading Spinner
	 */
	function showLoading(element) {
		if (!element) return;
		element.classList.add('cts-loading');
	}

	/**
	 * Helper: Hide Loading Spinner
	 */
	function hideLoading(element) {
		if (!element) return;
		element.classList.remove('cts-loading');
	}

})();

