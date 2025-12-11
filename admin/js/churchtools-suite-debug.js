/**
 * Repro CT-Suite Debug JavaScript
 * 
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/js
 * @author     Ralph Stöhr
 */

(function( $ ) {
	'use strict';

	console.log('=== REPRO-CT-SUITE-DEBUG.JS WIRD GELADEN ===');
	console.log('jQuery verfügbar:', typeof $ !== 'undefined');
	console.log('ajaxurl verfügbar:', typeof ajaxurl !== 'undefined');

	/**
	 * Debug-Funktionen
	 */
	const ReproCTSuiteDebug = {

		/**
		 * AJAX-Endpoint URL
		 */
		ajaxUrl: (typeof reproCTSuiteDebugData !== 'undefined' && reproCTSuiteDebugData.ajax_url) ? reproCTSuiteDebugData.ajax_url : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php'),

		/**
		 * Nonce
		 */
		nonce: (typeof reproCTSuiteDebugData !== 'undefined' && reproCTSuiteDebugData.nonce) ? reproCTSuiteDebugData.nonce : '',

		/**
		 * Initialisierung
		 */
		init: function() {
			console.log('=== Repro CT-Suite Debug loaded ===');
			console.log('URL:', window.location.href);
			console.log('Admin Wrapper gefunden:', $('.repro-ct-suite-admin-wrapper').length);
			
			this.initClearTableHandlers();
			this.initMigrationHandler();
			this.initLogHandlers();
		},

		/**
		 * Handler für Tabellen-Reset
		 */
		initClearTableHandlers: function() {
			const self = this;
			const $buttons = $('.repro-ct-suite-clear-single-table');
			
			console.log('=== initClearTableHandlers() aufgerufen ===');
			console.log('Gefundene Buttons:', $buttons.length);
			console.log('Button-Elemente:', $buttons);
			console.log('jQuery Version:', $.fn.jquery);

			// Einzelne Tabelle leeren
			$('.repro-ct-suite-clear-single-table').on('click', function(e) {
				e.preventDefault();
				console.log('=== BUTTON GEKLICKT ===', $(this).data('table'));
				
				const $button = $(this);
				const table = $button.data('table');
				const tableName = $button.data('table-name');
				const label = $button.data('label');
				const nonce = $button.data('nonce');
				
				console.log('Button Daten:', { table, tableName, label, nonce });
				
				if (!confirm('WARNUNG: Alle Daten in der Tabelle "' + label + '" werden unwiderruflich gelöscht!\n\nMöchten Sie fortfahren?')) {
					console.log('Benutzer hat abgebrochen');
					return;
				}
				
				console.log('Benutzer hat bestätigt, starte AJAX...');
				self.setButtonLoading($button, true);
				
				$.ajax({
					url: self.ajaxUrl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_clear_single_table',
						table: table,
						nonce: nonce
					},
					success: function(response) {
						console.log('AJAX Success:', response);
						self.setButtonLoading($button, false);
						
						if (response.success) {
							self.showNotification('Tabelle "' + label + '" wurde erfolgreich geleert.', 'success');
							
							// Badge aktualisieren
							const $badge = $button.closest('tr').find('.repro-ct-suite-badge');
							if ($badge.length) {
								$badge.text('0');
								$badge.removeClass('repro-ct-suite-badge-info').addClass('repro-ct-suite-badge-success');
							}
							
							// Button deaktivieren und HTML mit Icon beibehalten (bereits durch setButtonLoading wiederhergestellt)
							$button.prop('disabled', true);
						} else {
							const errorMsg = response.data && response.data.message ? response.data.message : 'Unbekannter Fehler';
							self.showNotification('Fehler beim Leeren der Tabelle: ' + errorMsg, 'error');
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', { xhr, status, error });
						self.setButtonLoading($button, false);
						self.showNotification('Fehler beim Leeren der Tabelle: ' + error, 'error');
					}
				});
			});
		},

		/**
		 * Handler für Migrations-Button
		 */
		initMigrationHandler: function() {
			const self = this;

			$('#repro-ct-suite-run-migration').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const migrationVersion = $button.data('migration-version');
				const nonce = $button.data('nonce');
				
				if (!confirm('Migration v' + migrationVersion + ' ausführen?\n\nDieser Vorgang kann nicht rückgängig gemacht werden!')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				
				// Fortschrittsanzeige einblenden
				const $progress = $('#repro-ct-suite-migration-progress');
				const $result = $('#repro-ct-suite-migration-result');
				$progress.show();
				$result.hide().empty();
				
				$.ajax({
					url: self.ajaxUrl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_run_migration',
						migration_version: migrationVersion,
						nonce: nonce
					},
					success: function(response) {
						self.setButtonLoading($button, false);
						$progress.hide();
						
						if (response.success) {
							$result
								.removeClass('notice-error')
								.addClass('notice notice-success')
								.html('<p><strong>Migration erfolgreich!</strong></p>' + (response.data.message ? '<p>' + response.data.message + '</p>' : ''))
								.show();
							
							// Button deaktivieren nach erfolgreicher Migration
							$button.prop('disabled', true).text('Migration bereits ausgeführt');
						} else {
							const errorMsg = response.data && response.data.message ? response.data.message : 'Unbekannter Fehler';
							$result
								.removeClass('notice-success')
								.addClass('notice notice-error')
								.html('<p><strong>Fehler bei der Migration:</strong></p><p>' + errorMsg + '</p>')
								.show();
						}
					},
					error: function(xhr, status, error) {
						self.setButtonLoading($button, false);
						$progress.hide();
						
						// Erweiterte Fehlerbehandlung
						let errorMessage = 'AJAX-Fehler: ' + error;
						
						if (xhr.responseText) {
							try {
								const errorResponse = JSON.parse(xhr.responseText);
								if (errorResponse.data && errorResponse.data.message) {
									errorMessage = errorResponse.data.message;
								}
							} catch (e) {
								// Falls Response kein JSON ist
								errorMessage += ' (HTTP ' + xhr.status + ': ' + xhr.statusText + ')';
								if (xhr.responseText && xhr.responseText.length < 300) {
									errorMessage += ' - Response: ' + xhr.responseText.substring(0, 200);
								}
							}
						}
						
						console.error('Migration AJAX Error:', {
							status: xhr.status,
							statusText: xhr.statusText,
							responseText: xhr.responseText,
							error: error
						});
						
						$result
							.removeClass('notice-success')
							.addClass('notice notice-error')
							.html('<p><strong>Fehler bei der Migration:</strong></p><p>' + errorMessage + '</p>')
							.show();
					}
				});
			});
		},

		/**
		 * Handler für Log-Tab
		 */
		initLogHandlers: function() {
			const self = this;

			// Log-Level Tabs
			$('.repro-ct-suite-log-level-tabs .repro-ct-suite-tab').on('click', function(e) {
				e.preventDefault();
				
				const $tab = $(this);
				const level = $tab.data('level');
				
				// Tab-Status aktualisieren
				$('.repro-ct-suite-log-level-tabs .repro-ct-suite-tab').removeClass('active');
				$tab.addClass('active');
				
				// Log-Content anzeigen/verstecken
				$('.repro-ct-suite-log-content').hide();
				$('#repro-ct-suite-log-' + level).show();
			});

			// Logs löschen
			$('.repro-ct-suite-clear-logs').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const level = $button.data('level');
				const nonce = $button.data('nonce');
				
				if (!confirm('Alle ' + level.toUpperCase() + '-Logs löschen?')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				
				$.ajax({
					url: self.ajaxUrl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_clear_logs',
						level: level,
						nonce: nonce
					},
					success: function(response) {
						self.setButtonLoading($button, false);
						
						if (response.success) {
							// Log-Content leeren
							const $content = $('#repro-ct-suite-log-' + level);
							$content.find('.repro-ct-suite-log-entries').empty();
							$content.find('.repro-ct-suite-log-empty').show();
							
							self.showNotification(level.toUpperCase() + '-Logs wurden gelöscht.', 'success');
						} else {
							const errorMsg = response.data && response.data.message ? response.data.message : 'Unbekannter Fehler';
							self.showNotification('Fehler beim Löschen der Logs: ' + errorMsg, 'error');
						}
					},
					error: function(xhr, status, error) {
						self.setButtonLoading($button, false);
						self.showNotification('Fehler beim Löschen der Logs: ' + error, 'error');
					}
				});
			});

			// Log-Einträge expandieren/kollabieren
			$(document).on('click', '.repro-ct-suite-log-entry-header', function() {
				const $entry = $(this).closest('.repro-ct-suite-log-entry');
				$entry.toggleClass('expanded');
			});
		},

		/**
		 * Button-Loading-Status setzen
		 */
		setButtonLoading: function($button, loading) {
			if (loading) {
				$button.prop('disabled', true).addClass('loading');
				$button.data('original-html', $button.html());
				$button.html('<span class="spinner is-active" style="float:none;"></span> ' + $button.text());
			} else {
				$button.prop('disabled', false).removeClass('loading');
				const originalHtml = $button.data('original-html');
				if (originalHtml) {
					$button.html(originalHtml);
				}
			}
		},

		/**
		 * Notification anzeigen
		 */
		showNotification: function(message, type) {
			const $notification = $('<div>')
				.addClass('notice notice-' + type + ' is-dismissible')
				.html('<p>' + message + '</p>')
				.hide();
			
			$('.repro-ct-suite-admin-wrapper').prepend($notification);
			$notification.fadeIn();
			
			// Auto-Dismiss nach 5 Sekunden
			setTimeout(function() {
				$notification.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
			
			// Dismiss-Button aktivieren
			$(document).trigger('wp-updates-notice-added');
		}

	};

	// Objekt SOFORT verfügbar machen, BEVOR Document Ready
	window.ReproCTSuiteDebug = ReproCTSuiteDebug;
	console.log('=== ReproCTSuiteDebug Objekt registriert ===', window.ReproCTSuiteDebug);

	/**
	 * Initialisierung beim Document Ready
	 */
	$(function() {
		console.log('=== DEBUG SCRIPT INITIALISIERUNG ===');
		console.log('URL:', window.location.href);
		console.log('Wrapper:', $('.repro-ct-suite-admin-wrapper').length);
		console.log('jQuery geladen:', typeof $ !== 'undefined');
		console.log('URL enthält tab=debug:', window.location.href.indexOf('tab=debug') !== -1);
		console.log('URL enthält repro-ct-suite-debug:', window.location.href.indexOf('repro-ct-suite-debug') !== -1);
		
		// Nur auf der Debug-Seite ausführen
		if ($('.repro-ct-suite-admin-wrapper').length && (window.location.href.indexOf('repro-ct-suite-debug') !== -1 || window.location.href.indexOf('tab=debug') !== -1)) {
			console.log('=== BEDINGUNG ERFÜLLT - STARTE INIT() ===');
			ReproCTSuiteDebug.init();
		} else {
			console.log('=== BEDINGUNG NICHT ERFÜLLT ===');
		}
	});

})( jQuery );
