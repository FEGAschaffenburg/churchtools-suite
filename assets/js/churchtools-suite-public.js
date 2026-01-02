/**
 * ChurchTools Suite Public JS
 * Frontend-JavaScript für interaktive Features
 *
 * @package ChurchTools_Suite
 * @since   0.5.1.0
 */

(function($) {
	'use strict';

	// DOM Ready
	$(function() {
		console.log('[ChurchTools Suite] Public JS loaded');
		
		// Check if we're in editor mode (Gutenberg or Elementor)
		const isEditor = $('body').hasClass('block-editor-page') || 
		                 typeof elementor !== 'undefined' ||
		                 $('body').hasClass('elementor-editor-active') ||
		                 $('body').hasClass('wp-admin') ||
		                 window.location.href.indexOf('/wp-admin/') !== -1;
		
		if (isEditor) {
			console.log('[ChurchTools Suite] Editor mode detected - skipping ALL click handlers');
			// Only initialize modal close handlers (for cleanup)
			initModalCloseHandlers();
		} else {
			console.log('[ChurchTools Suite] Frontend mode - initializing ALL handlers');
			initClickableEvents(); // v0.10.3.0: Click-to-details
			initGridButtons(); // Grid/List detail buttons
			initModalViews(); // Modal open/close
		}
		
		// Always initialize these (needed everywhere)
		initCalendarViews();
		
		console.log('[ChurchTools Suite] Init complete');
	});

	/**
	 * Initialize Calendar Views
	 */
	function initCalendarViews() {
		// Initialize monthly modern calendar
		$('.cts-calendar-monthly').each(function() {
			setupCalendarNavigation($(this));
		});
		
		// Legacy calendar view support
		$('.cts-calendar-view').each(function() {
			const $calendar = $(this);
			const eventsData = $calendar.find('.cts-calendar-grid').data('events');
			
			if (!eventsData || !eventsData.length) {
				return;
			}
			
			renderCalendarGrid($calendar, eventsData);
			setupCalendarNavigation($calendar);
		});
	}

	/**
	 * Render calendar grid with events
	 */
	function renderCalendarGrid($calendar, events) {
		const $grid = $calendar.find('.cts-calendar-grid');
		
		// Get current month/year from header
		const headerText = $calendar.find('.cts-calendar-title').text();
		// Parse month/year (format: "December 2025")
		
		// Group events by date
		const eventsByDate = {};
		events.forEach(event => {
			const date = event.start_date; // Format: Y-m-d
			if (!eventsByDate[date]) {
				eventsByDate[date] = [];
			}
			eventsByDate[date].push(event);
		});
		
		// Add events to calendar days
		$grid.find('.cts-calendar-day').each(function() {
			const $day = $(this);
			const date = $day.data('date');
			
			if (eventsByDate[date]) {
				eventsByDate[date].forEach(event => {
					const $eventEl = $('<div>')
						.addClass('cts-calendar-event')
						.css('background', event.calendar_color || '#3498db')
						.text(event.title)
						.attr('data-event-id', event.id);
					
					$day.append($eventEl);
				});
			}
		});
	}

	/**
	 * Setup calendar navigation
	 */
	function setupCalendarNavigation($calendar) {
		// Event listener für Monatswechsel-Buttons
		$calendar.find('.cts-prev-month, .cts-next-month').on('click', function(e) {
			e.preventDefault();
			const isPrev = $(this).hasClass('cts-prev-month');
			const direction = isPrev ? -1 : 1;
			
			// Aktuellen Monat aus dem Titel extrahieren
			const $title = $calendar.find('.cts-calendar-title');
			const titleText = $title.text().trim(); // z.B. "Januar 2026"
			
			// Parse aktuellen Monat
			let currentDate = new Date();
			try {
				// Versuche verschiedene Formate zu parsen
				const parsedDate = parseMonthYear(titleText);
				if (parsedDate) {
					currentDate = parsedDate;
				}
			} catch (err) {
				console.warn('Could not parse month/year, using current date:', err);
			}
			
			// Neuen Monat berechnen
			const newDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + direction, 1);
			const year = newDate.getFullYear();
			const month = newDate.getMonth() + 1; // JavaScript months are 0-based
			
			// Lade neuen Monat via AJAX
			loadCalendarMonth($calendar, year, month);
		});
	}
	
	/**
	 * Parse month/year from calendar title
	 */
	function parseMonthYear(titleText) {
		// Monatsnamen auf Deutsch
		const monthNames = [
			'januar', 'februar', 'märz', 'april', 'mai', 'juni',
			'juli', 'august', 'september', 'oktober', 'november', 'dezember'
		];
		
		const parts = titleText.toLowerCase().split(' ');
		if (parts.length !== 2) return null;
		
		const monthName = parts[0];
		const year = parseInt(parts[1]);
		
		const monthIndex = monthNames.indexOf(monthName);
		if (monthIndex === -1 || isNaN(year)) return null;
		
		return new Date(year, monthIndex, 1);
	}
	
	/**
	 * Load calendar for specific month via AJAX
	 */
	function loadCalendarMonth($calendar, year, month) {
		// Show loading state
		$calendar.addClass('cts-loading');
		
		// Extract shortcode attributes from calendar element
		const calendarIds = $calendar.data('calendar-ids') || '';
		const limit = $calendar.data('limit') || 100;
		const enableModalAttr = $calendar.data('enable-modal');
		// Convert string "true"/"false" to boolean
		const enableModal = enableModalAttr === 'false' ? false : (enableModalAttr === 'true' || enableModalAttr === true || enableModalAttr === undefined);
		
		console.log('[Calendar] Loading month:', year, month, 'enableModal:', enableModal, 'raw:', enableModalAttr);
		
		$.ajax({
			url: churchtoolsSuitePublic.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cts_load_calendar_month',
				nonce: churchtoolsSuitePublic.nonce,
				year: year,
				month: month,
				calendar_ids: calendarIds,
				limit: limit,
				enable_modal: enableModal
			},
			success: function(response) {
				console.log('[Calendar] AJAX success:', response);
				if (response.success && response.data.html) {
					// Replace calendar content
					$calendar.replaceWith(response.data.html);
					
					// Re-initialize navigation for new calendar
					const $newCalendar = $('.cts-calendar-monthly').last();
					setupCalendarNavigation($newCalendar);
				} else {
					console.error('Failed to load calendar month:', response);
					alert('Fehler beim Laden des Kalenders');
				}
			},
			error: function(xhr, status, error) {
				console.error('AJAX error loading calendar:', xhr, status, error);
				console.error('Response:', xhr.responseText);
				alert('Netzwerkfehler beim Laden des Kalenders: ' + error);
			},
			complete: function() {
				$calendar.removeClass('cts-loading');
			}
		});
	}

	/**
	 * Initialize grid view detail buttons
	 */
	function initGridButtons() {
		// All detail/action buttons (grid, list, calendar)
		$(document).on('click', '[data-event-id]:not(.cts-weekly-event)', function(e) {
			// Only handle buttons, not entire cards
			if ($(this).is('button') || $(this).hasClass('cts-grid-item-more')) {
				e.preventDefault();
				e.stopPropagation();
				const eventId = $(this).data('event-id');
				showEventModal(eventId);
			}
		});
	}

	/**
	 * Initialize modal views
	 */
	function initModalViews() {
		// Close handlers
		initModalCloseHandlers();
	}
	
	/**
	 * Initialize modal close handlers only (safe for editor)
	 */
	function initModalCloseHandlers() {
		// Close modal on background click
		$(document).on('click', '#cts-modal-overlay', function(e) {
			if (e.target === this || $(e.target).hasClass('cts-modal-overlay')) {
				closeModal();
			}
		});
		
		// Close modal on close button
		$(document).on('click', '#cts-modal-close, #cts-modal-close-btn', function() {
			closeModal();
		});
		
		// Close modal on ESC key
		$(document).on('keydown', function(e) {
			if (e.keyCode === 27 && $('#cts-modal-overlay').hasClass('active')) {
				closeModal();
			}
		});
	}

	/**
	 * Initialize clickable events (v0.10.3.0)
	 * Macht alle Events mit .cts-event-clickable klickbar
	 */
	function initClickableEvents() {
		console.log('[ChurchTools Suite] initClickableEvents() called');
		console.log('[ChurchTools Suite] Found clickable events:', $('.cts-event-clickable').length);
		
		// Event-Delegation für dynamisch geladene Events
		$(document).on('click', '.cts-event-clickable', function(e) {
			e.preventDefault();
			const eventId = $(this).data('event-id');
			console.log('[ChurchTools Suite] Event clicked, ID:', eventId);
			if (eventId) {
				showEventModal(eventId);
			} else {
				console.error('[ChurchTools Suite] No event-id attribute found!');
			}
		});
		
		// Keyboard accessibility (Enter/Space)
		$(document).on('keydown', '.cts-event-clickable', function(e) {
			if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
				e.preventDefault();
				const eventId = $(this).data('event-id');
				console.log('[ChurchTools Suite] Keyboard event, ID:', eventId);
				if (eventId) {
					showEventModal(eventId);
				}
			}
		});
	}

	/**
	 * Show event detail modal
	 */
	function showEventModal(eventId) {
		console.log('[ChurchTools Suite] showEventModal() called with ID:', eventId);
		
		// Show modal overlay
		let $overlay = $('#cts-modal-overlay');
		console.log('[ChurchTools Suite] Modal overlay found:', $overlay.length > 0);
		
		// Create modal if not exists
		if ($overlay.length === 0) {
			console.log('[ChurchTools Suite] Loading modal template via AJAX...');
			console.log('[ChurchTools Suite] AJAX URL:', churchtoolsSuitePublic.ajaxUrl);
			
			// Load modal template via AJAX
			$.ajax({
				url: churchtoolsSuitePublic.ajaxUrl,
				type: 'POST',
				data: {
					action: 'cts_get_modal_template',
					nonce: churchtoolsSuitePublic.nonce
				},
				success: function(response) {
					console.log('[ChurchTools Suite] Modal template response:', response);
					console.log('[ChurchTools Suite] response.success:', response.success);
					console.log('[ChurchTools Suite] response.data:', response.data);
					console.log('[ChurchTools Suite] response.data.html exists:', !!response.data?.html);
					console.log('[ChurchTools Suite] response.data.html length:', response.data?.html?.length);
					
					if (response.success && response.data && response.data.html) {
						console.log('[ChurchTools Suite] Appending modal HTML to body...');
						$('body').append(response.data.html);
						$overlay = $('#cts-modal-overlay');
						console.log('[ChurchTools Suite] Modal template appended to body');
						loadEventData(eventId, $overlay);
					} else {
						console.error('[ChurchTools Suite] Invalid modal template response');
						console.error('[ChurchTools Suite] Missing html in response.data!');
					}
				},
				error: function(xhr, status, error) {
					console.error('[ChurchTools Suite] AJAX error loading modal template:', error);
					alert('Modal konnte nicht geladen werden.');
				}
			});
		} else {
			loadEventData(eventId, $overlay);
		}
	}

	/**
	 * Load event data into modal
	 */
	function loadEventData(eventId, $overlay) {
		// Show modal
		$overlay.addClass('active');
		$('body').css('overflow', 'hidden');
		
		// Show loading
		$('#cts-modal-loading').show();
		$('#cts-modal-details').hide();
		$('#cts-modal-error').hide();
		
		// Load event data
		$.ajax({
			url: churchtoolsSuitePublic.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cts_get_event_details',
				nonce: churchtoolsSuitePublic.nonce,
				event_id: eventId
			},
			success: function(response) {
				$('#cts-modal-loading').hide();
				
				if (response.success && response.data) {
					displayEventData(response.data);
				} else {
					$('#cts-modal-error').show();
				}
			},
			error: function() {
				$('#cts-modal-loading').hide();
				$('#cts-modal-error').show();
			}
		});
	}

	/**
	 * Display event data in modal
	 */
	function displayEventData(event) {
		// Title
		$('#cts-modal-title').text(event.title);
		
		// Calendar Badge
		$('#cts-modal-calendar').text(event.calendar_name)
			.css('background', event.calendar_color || '#3498db');
		
		// Date & Time (use pre-formatted time_display from backend)
		$('#cts-modal-date').text(event.start_date);
		$('#cts-modal-time').text(event.time_display || event.start_time);
		
		// Location (prefer structured address fields)
		var loc = event.address_name || event.location_name || '';
		if (!loc && event.address_street) {
			loc = event.address_street;
		}
		if (!loc && event.address) {
			loc = event.address;
		}

		// Build fallback info string
		var infoParts = [];
		if (event.address_street) infoParts.push(event.address_street);
		if (event.address_zip) infoParts.push(event.address_zip);
		if (event.address_city) infoParts.push(event.address_city);
		var infoStr = infoParts.join(', ');

		if (loc) {
			$('#cts-modal-location').text(loc);
			$('#cts-modal-location-wrapper').show();
			if (infoStr) {
				$('#cts-modal-location').find('.cts-info-popup').remove();
				$('#cts-modal-location').append(' <span class="cts-info-popup" title="'+infoStr+'"> ⓘ</span>');
			}
		} else {
			$('#cts-modal-location-wrapper').hide();
		}

		if (loc) {
			$('#cts-modal-location').text(loc);
			$('#cts-modal-location-wrapper').show();
		} else {
			$('#cts-modal-location-wrapper').hide();
		}
		
		// Description
		if (event.description) {
			$('#cts-modal-description').html(event.description);
			$('#cts-modal-description-wrapper').show();
		} else {
			$('#cts-modal-description-wrapper').hide();
		}
		
		// Services
		if (event.services && event.services.length > 0) {
			const $servicesList = $('#cts-modal-services');
			$servicesList.empty();
			
			event.services.forEach(function(service) {
				const $item = $('<div>').addClass('cts-modal-service-item');
				
				const $name = $('<span>').addClass('cts-modal-service-name')
					.text(service.service_name);
				$item.append($name);
				
				if (service.person_name) {
					const $person = $('<span>').addClass('cts-modal-service-person')
						.text('- ' + service.person_name);
					$item.append($person);
				}
				
				$servicesList.append($item);
			});
			
			$('#cts-modal-services-wrapper').show();
		} else {
			$('#cts-modal-services-wrapper').hide();
		}
		
		// Show details
		$('#cts-modal-details').show();
	}

	/**
	 * Close modal
	 */
	function closeModal() {
		$('#cts-modal-overlay').removeClass('active');
		$('body').css('overflow', '');
	}

	/**
	 * Show loading spinner
	 */
	function showLoadingSpinner() {
		if ($('.cts-loading-overlay').length) {
			return;
		}
		
		const $spinner = $('<div>')
			.addClass('cts-loading-overlay')
			.html('<div class="cts-spinner"></div>')
			.hide()
			.appendTo('body')
			.fadeIn(200);
	}

	/**
	 * Hide loading spinner
	 */
	function hideLoadingSpinner() {
		$('.cts-loading-overlay').fadeOut(200, function() {
			$(this).remove();
		});
	}

	/**
	 * Calendar day click handler
	 */
	$(document).on('click', '.cts-calendar-day', function() {
		const date = $(this).data('date');
		const events = $(this).find('.cts-calendar-event');
		
		if (events.length === 0) {
			return;
		}
		
		if (events.length === 1) {
			// Single event - show modal
			const eventId = events.first().data('event-id');
			showEventModal(eventId);
		} else {
			// Multiple events - show day view
			showDayEventsModal(date, events);
		}
	});

	/**
	 * Show day events modal (multiple events)
	 */
	function showDayEventsModal(date, $events) {
		const eventIds = [];
		$events.each(function() {
			eventIds.push($(this).data('event-id'));
		});
		
		// AJAX call to load day events
		$.ajax({
			url: churchtoolsSuitePublic.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cts_get_day_events',
				nonce: churchtoolsSuitePublic.nonce,
				date: date,
				event_ids: eventIds
			},
			beforeSend: function() {
				showLoadingSpinner();
			},
			success: function(response) {
				hideLoadingSpinner();
				
				if (response.success && response.data.html) {
					displayModal(response.data.html);
				}
			},
			error: function() {
				hideLoadingSpinner();
			}
		});
	}

	/**
	 * Event click handler (in list/grid views)
	 */
	$(document).on('click', '[data-event-id]', function(e) {
		// Only handle if not a button/link
		if ($(e.target).is('a, button')) {
			return;
		}
		
		const eventId = $(this).data('event-id');
		if (eventId) {
			showEventModal(eventId);
		}
	});

})(jQuery);
