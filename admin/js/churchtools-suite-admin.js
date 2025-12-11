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

