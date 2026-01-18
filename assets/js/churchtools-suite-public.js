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
		console.log('[ChurchTools Suite] jQuery version:', $.fn.jquery);
		console.log('[ChurchTools Suite] Body classes:', $('body').attr('class'));
		
		// Check if we're in editor mode (Gutenberg or Elementor)
		const isEditor = $('body').hasClass('block-editor-page') || 
		                 typeof elementor !== 'undefined' ||
		                 $('body').hasClass('elementor-editor-active') ||
		                 $('body').hasClass('wp-admin') ||
		                 window.location.href.indexOf('/wp-admin/') !== -1;
		
		console.log('[ChurchTools Suite] Editor mode check:', {
			'block-editor-page': $('body').hasClass('block-editor-page'),
			'elementor': typeof elementor !== 'undefined',
			'elementor-editor-active': $('body').hasClass('elementor-editor-active'),
			'wp-admin': $('body').hasClass('wp-admin'),
			'url-check': window.location.href.indexOf('/wp-admin/') !== -1,
			'isEditor': isEditor
		});
		
		if (isEditor) {
			console.log('[ChurchTools Suite] Editor mode detected - skipping ALL click handlers');
			// Only initialize modal close handlers (for cleanup)
			initModalCloseHandlers();
		} else {
			console.log('[ChurchTools Suite] Frontend mode - initializing ALL handlers');
			initClickableEvents(); // v0.10.3.0: Click-to-details (Modal)
			initPageLinkEvents(); // v0.9.6.16: Click-to-page navigation
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
		// Initialize monthly modern calendar (only NEW calendars, not already initialized)
		$('.cts-calendar-monthly').each(function() {
			const $calendar = $(this);
			// Skip if already initialized
			if ($calendar.data('calendar-initialized')) {
				console.log('[Calendar] Already initialized, skipping');
				return;
			}
			$calendar.data('calendar-initialized', true);
			setupCalendarNavigation($calendar);
		});
		
		// Legacy calendar view support
		$('.cts-calendar-view').each(function() {
			const $calendar = $(this);
			// Skip if already initialized
			if ($calendar.data('calendar-initialized')) {
				return;
			}
			const eventsData = $calendar.find('.cts-calendar-grid').data('events');
			
			if (!eventsData || !eventsData.length) {
				return;
			}
			
			$calendar.data('calendar-initialized', true);
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
		// Remove old handlers to prevent duplicates
		$calendar.find('.cts-prev-month, .cts-next-month').off('click');
		
		console.log('[Calendar] Setting up navigation for calendar');
		
		// Event listener für Monatswechsel-Buttons
		$calendar.find('.cts-prev-month, .cts-next-month').on('click', function(e) {
			e.preventDefault();
			const isPrev = $(this).hasClass('cts-prev-month');
			const direction = isPrev ? -1 : 1;
			
			// CRITICAL: Finde aktuellen Kalender im DOM (nicht alte $calendar Variable!)
			// Nach replaceWith() zeigt $calendar auf ein gelöschtes Element
			const $currentCalendar = $(this).closest('.cts-calendar-monthly');
			
			// Aktuellen Monat aus dem Titel extrahieren
			const $title = $currentCalendar.find('.cts-calendar-title');
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
			
			console.log('[Calendar] Navigation clicked:', direction > 0 ? 'next' : 'prev', 'New date:', year, month);
			
			// Lade neuen Monat via AJAX (nutze AKTUELLEN Kalender!)
			loadCalendarMonth($currentCalendar, year, month);
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
		
		// Extract display options
		const showTime = $calendar.data('show-time') !== false;
		const showDescription = $calendar.data('show-description') === true;
		const showLocation = $calendar.data('show-location') === true;
		const showServices = $calendar.data('show-services') === true;
		const showCalendarName = $calendar.data('show-calendar-name') === true;
		
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
				enable_modal: enableModal,
				show_time: showTime,
				show_description: showDescription,
				show_location: showLocation,
				show_services: showServices,
				show_calendar_name: showCalendarName
			},
			success: function(response) {
				console.log('[Calendar] AJAX success:', response);
				if (response.success && response.data.html) {
					// Update calendar title
					const monthName = response.data.month_name;
					$calendar.find('.cts-calendar-title').text(monthName);
					
					// Replace only the grid (not the whole calendar)
					const $grid = $calendar.find('.cts-calendar-grid');
					$grid.html(response.data.html);
					
					// CRITICAL: Re-attach navigation handlers (Buttons need event handlers!)
					console.log('[Calendar] Re-attaching navigation handlers');
					setupCalendarNavigation($calendar);
					
					// Re-initialize clickable events if modal enabled
					const enableModalCheck = $calendar.data('enable-modal');
					const isModalEnabled = enableModalCheck === 'false' ? false : (enableModalCheck === 'true' || enableModalCheck === true || enableModalCheck === undefined);
					
					console.log('[Calendar] Modal enabled check:', isModalEnabled, 'data attr:', enableModalCheck);
					
					if (isModalEnabled) {
						// Re-attach click handlers for events in new grid
						$grid.find('[data-event-id]').each(function() {
							const $event = $(this);
							if (!$event.data('click-handler-attached')) {
								$event.on('click', function(e) {
									e.preventDefault();
									const eventId = $(this).data('event-id');
									console.log('[Calendar] Event clicked:', eventId);
								showEventModal(eventId, $calendar, 'calendar');
								});
								$event.data('click-handler-attached', true);
							}
						});
					}
					
					console.log('[Calendar] Month loaded successfully');
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
				// Loading-State entfernen
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
				// Find parent container with settings
				const $container = $(this).closest('[data-show-description]');
				
				// v0.9.9.80: Detect current view type
				let currentView = null;
				if ($container.length > 0) {
					const classes = $container.attr('class') || '';
					if (classes.includes('cts-grid-')) currentView = 'grid';
					else if (classes.includes('cts-list')) currentView = 'list';
					else if (classes.includes('cts-calendar')) currentView = 'calendar';
					else if (classes.includes('cts-single')) currentView = 'single';
				}
				
				showEventModal(eventId, $container, currentView);
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
		const $clickableEvents = $('.cts-event-clickable');
		console.log('[ChurchTools Suite] initClickableEvents() called');
		console.log('[ChurchTools Suite] Found clickable events:', $clickableEvents.length);
		console.log('[ChurchTools Suite] Sample element:', $clickableEvents.first().length > 0 ? $clickableEvents.first()[0] : 'NONE');
		
		// Event-Delegation für dynamisch geladene Events
		$(document).on('click', '.cts-event-clickable', function(e) {
		// WICHTIG: Nur verarbeiten wenn NICHT auch page-link (verhindert Konflikt)
		if ($(this).hasClass('cts-event-page-link')) {
			console.log('[ChurchTools Suite] Skipping - has page-link class');
			return; // Lass den page-link handler übernehmen
		}
		
			const eventId = $(this).data('event-id');
			console.log('[ChurchTools Suite] Event clicked, ID:', eventId, 'Element:', this);
			if (eventId) {
				// Find parent container with settings (v0.9.9.66: Detect current view type)
				const $container = $(this).closest('[data-show-description]');
				
				// v0.9.9.66: Detect current view type from container classes
				let currentView = null;
				if ($container.length > 0) {
					// Check for view-specific class patterns
					const classes = $container.attr('class') || '';
					if (classes.includes('cts-grid-')) currentView = 'grid';
					else if (classes.includes('cts-list')) currentView = 'list';
					else if (classes.includes('cts-calendar')) currentView = 'calendar';
					else if (classes.includes('cts-single')) currentView = 'single';
					
					console.log('[ChurchTools Suite] Detected view type:', currentView, 'from classes:', classes);
				}
				
				showEventModal(eventId, $container, currentView);
			} else {
				console.error('[ChurchTools Suite] No event-id attribute found!', 'Element:', this, 'Attributes:', this.attributes);
			}
		});
		
		// Keyboard accessibility (Enter/Space)
		$(document).on('keydown', '.cts-event-clickable', function(e) {
			if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
				e.preventDefault();
				const eventId = $(this).data('event-id');
				console.log('[ChurchTools Suite] Keyboard event, ID:', eventId);
				if (eventId) {
					// Find parent container with settings
					const $container = $(this).closest('[data-show-description]');
					
					// v0.9.9.66: Detect current view type from container classes (keyboard access)
					let currentView = null;
					if ($container.length > 0) {
						const classes = $container.attr('class') || '';
						if (classes.includes('cts-grid-')) currentView = 'grid';
						else if (classes.includes('cts-list')) currentView = 'list';
						else if (classes.includes('cts-calendar')) currentView = 'calendar';
						else if (classes.includes('cts-single')) currentView = 'single';
					}
					
					showEventModal(eventId, $container, currentView);
				}
			}
		});
	}

	/**
	 * Initialize page link events (v0.9.9.3)
	 * DEPRECATED: Handler wurde nach unten verschoben (Zeile 746)
	 * Diese Funktion ist nur noch ein Platzhalter für Kompatibilität
	 */
	function initPageLinkEvents() {
		console.log('[ChurchTools Suite] initPageLinkEvents() called (deprecated - handler registered globally)');
		// Event-Handler wurde nach unten verschoben zu den anderen globalen Handlern (Zeile 746)
		// Dort wird er zusammen mit .cts-event-clickable registriert
	}

	/**
	 * Show event detail modal or navigate to page
	 * @param {string} eventId - Event ID to display
	 * @param {jQuery} $container - Optional container element with display settings (e.g., calendar)
	 * @param {string} currentView - Current view type ('list', 'grid', 'calendar', 'single') - v0.9.9.66
	 * @param {Object} options - Optional settings: { click_action: 'modal'|'page', template: 'name' } - v0.9.9.84
	 */
	function showEventModal(eventId, $container, currentView, options) {
		options = options || {};
		console.log('[ChurchTools Suite] showEventModal() called with ID:', eventId, 'view:', currentView, 'options:', options);
		
		// v0.9.9.84: Get click_action from options (passed from Block)
		var clickAction = options.click_action || 'modal';
		
		// v0.9.9.84: AJAX request with click_action and template
		$.ajax({
			url: churchtoolsSuitePublic.ajaxUrl,
			type: 'POST',
			data: {
				action: 'cts_get_modal_template',
				nonce: churchtoolsSuitePublic.nonce,
				event_id: eventId,
				current_view: currentView,
				click_action: clickAction
			},
			success: function(response) {
				console.log('[ChurchTools Suite] Click action response:', response);
				
				if (!response.success || !response.data) {
					console.error('[ChurchTools Suite] Invalid response');
					alert('Fehler beim Laden des Events.');
					return;
				}
				
				// v0.9.9.84: NEW - Check if action is 'page' or 'modal'
				if (response.data.action === 'page') {
					// Navigate to event page
					console.log('[ChurchTools Suite] Navigating to event page:', response.data.url);
					window.location.href = response.data.url;
					return;
				}
				
				// v0.9.9.84: action === 'modal' - show modal
				if (response.data.action !== 'modal' || !response.data.html) {
					console.error('[ChurchTools Suite] Invalid modal action or missing html');
					alert('Modal konnte nicht geladen werden.');
					return;
				}
				
				// Load modal overlay
				let $overlay = $('#cts-modal-overlay');
				console.log('[ChurchTools Suite] Modal overlay found:', $overlay.length > 0);
				
				// Create modal if not exists
				if ($overlay.length === 0) {
					console.log('[ChurchTools Suite] Appending modal HTML to body...');
					$('body').append(response.data.html);
					$overlay = $('#cts-modal-overlay');
					console.log('[ChurchTools Suite] Modal template appended to body');
				}
				
				// Load event data into modal
				loadEventData(eventId, $overlay, $container);
			},
			error: function(xhr, status, error) {
				console.error('[ChurchTools Suite] AJAX error:', error);
				alert('Event konnte nicht geladen werden.');
			}
		});
	}
	
	/**
	 * Load event data into modal
	 * @param {string} eventId - Event ID to load
	 * @param {jQuery} $overlay - Modal overlay element
	 * @param {jQuery} $container - Optional container element with display settings
	 */
	function loadEventData(eventId, $overlay, $container) {
		// Show modal
		$overlay.addClass('active');
		$('body').css('overflow', 'hidden');
		
		// Show loading
		$('#cts-modal-loading').show();
		$('#cts-modal-content').hide();
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
					displayEventData(response.data, $container);
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
	 * @param {object} event - Event data from backend
	 * @param {jQuery} $container - Optional container element with display settings
	 */
	function displayEventData(event, $container) {
		// Extract display options from container (calendar/grid/list view)
		const showDescription = $container ? ($container.data('show-description') === true) : true;
		const showLocation = $container ? ($container.data('show-location') === true) : true;
		const showServices = $container ? ($container.data('show-services') === true) : true;
		const showCalendarName = $container ? ($container.data('show-calendar-name') === true) : true;
		
		console.log('[Modal] Display options:', {showDescription, showLocation, showServices, showCalendarName});
		
		// Event Title (new: in main area)
		$('#cts-modal-event-title').text(event.title);
		
		// Event Image (new: hero image in main area) - v0.9.9.69
		if (event.image_url) {
			$('#cts-modal-image-img').attr('src', event.image_url);
			$('#cts-modal-image').show();
		} else {
			$('#cts-modal-image').hide();
		}
		
		// Calendar Badge (new: in main area)
		if (showCalendarName && event.calendar_name) {
			$('#cts-modal-calendar').text(event.calendar_name)
				.css('background-color', event.calendar_color || '#3498db').show();
		} else {
			$('#cts-modal-calendar').hide();
		}
		
		// Date (new: in sidebar) - v0.9.9.69
		if (event.start_date) {
			$('#cts-modal-date-value').text(event.start_date);
			$('#cts-modal-date').show();
		} else {
			$('#cts-modal-date').hide();
		}
		
		// Time (new: in sidebar) - v0.9.9.69
		if (event.time_display || event.start_time) {
			$('#cts-modal-time-value').text(event.time_display || event.start_time);
			$('#cts-modal-time').show();
		} else {
			$('#cts-modal-time').hide();
		}
		
		// Location (new: in sidebar with address details) - v0.9.9.69
		var loc = event.address_name || event.location_name || '';
		if (!loc && event.address_street) {
			loc = event.address_street;
		}
		if (!loc && event.address) {
			loc = event.address;
		}

		if (showLocation && loc) {
			var locationHtml = loc;
			if (event.address_street) locationHtml += '<br>' + event.address_street;
			if (event.address_zip || event.address_city) {
				locationHtml += '<br>' + [event.address_zip, event.address_city].filter(Boolean).join(' ');
			}
			$('#cts-modal-location-value').html(locationHtml);
			$('#cts-modal-location').show();
		} else {
			$('#cts-modal-location').hide();
		}
		
		// Debug: Log event data
		console.log('[Modal] Event data received:', event);
		console.log('[Modal] event_description:', event.event_description);
		console.log('[Modal] appointment_description:', event.appointment_description);
		
		// Event Description (in main area)
		if (event.event_description && event.event_description.trim() !== '') {
			console.log('[Modal] Showing event description');
			$('#cts-modal-event-description-content').html(event.event_description);
			$('#cts-modal-event-description').show();
		} else {
			console.log('[Modal] Hiding event description (empty)');
			$('#cts-modal-event-description').hide();
		}
		
		// Appointment Description (in main area)
		if (event.appointment_description && event.appointment_description.trim() !== '') {
			console.log('[Modal] Showing appointment description');
			$('#cts-modal-appointment-description-content').html(event.appointment_description);
			$('#cts-modal-appointment-description').show();
		} else {
			console.log('[Modal] Hiding appointment description (empty)');
			$('#cts-modal-appointment-description').hide();
		}
		
		// Tags (new: in sidebar) - v0.9.9.69
		if (event.tags && event.tags.length > 0) {
			const $tagsList = $('#cts-modal-tags-content');
			$tagsList.empty();
			event.tags.forEach(function(tag) {
				const $tag = $('<span>').addClass('cts-tag')
					.text(tag.name)
					.css('background-color', tag.color || '#6b7280');
				$tagsList.append($tag);
			});
			$('#cts-modal-tags').show();
		} else {
			$('#cts-modal-tags').hide();
		}
		
		// Services (in main area)
		if (showServices && event.services && event.services.length > 0) {
			const $servicesList = $('#cts-modal-services-list');
			$servicesList.empty();
			
			event.services.forEach(function(service) {
				const $item = $('<li>').addClass('cts-service-item');
				
				const $name = $('<strong>').addClass('cts-service-name')
					.text(service.service_name + ':');
				
				const $person = $('<span>').addClass('cts-service-person')
					.text(' ' + (service.person_name || 'Nicht zugewiesen'));
				
				$item.append($name).append($person);
				$servicesList.append($item);
			});
			
			$('#cts-modal-services').show();
		} else {
			$('#cts-modal-services').hide();
		}
		
		// Show content (hide loading)
		$('#cts-modal-content').show();
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
			// Find parent calendar with settings
			const $container = $(this).closest('.cts-calendar');
			showEventModal(eventId, $container, 'calendar');
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
	 * v0.9.3.1: Support for event_action modes (modal/page/none)
	 * v0.9.9.80: Always pass currentView for template selection
	 */
	$(document).on('click', '.cts-event-clickable', function(e) {
		// Modal action - open modal
		e.preventDefault();
		
		const eventId = $(this).data('event-id');
		if (eventId) {
			const $container = $(this).closest('[data-show-description]');
			
			// v0.9.9.80: Detect current view type
			let currentView = null;
			if ($container.length > 0) {
				const classes = $container.attr('class') || '';
				if (classes.includes('cts-grid-')) currentView = 'grid';
				else if (classes.includes('cts-list')) currentView = 'list';
				else if (classes.includes('cts-calendar')) currentView = 'calendar';
				else if (classes.includes('cts-single')) currentView = 'single';
			}
			
			showEventModal(eventId, $container, currentView);
		}
	});
	
	/**
	 * Page link click handler
	 * v0.9.9.3: Navigate to single event page with URL params + Display settings
	 */
	$(document).on('click', '.cts-event-page-link', function(e) {
		e.preventDefault();
		console.log('[ChurchTools Suite] .cts-event-page-link clicked');
		
		const eventId = $(this).data('event-id');
		const dataUrl = $(this).data('event-url');
		const baseUrl = (window.churchtoolsSuitePublic && churchtoolsSuitePublic.singleEventBaseUrl) ? churchtoolsSuitePublic.singleEventBaseUrl : null;
		const singleTemplate = (window.churchtoolsSuitePublic && churchtoolsSuitePublic.singleEventTemplate) ? churchtoolsSuitePublic.singleEventTemplate : null;
		console.log('[ChurchTools Suite] Event ID:', eventId);
		
		if (!eventId) {
			console.warn('[ChurchTools Suite] No event ID found on clicked element');
			return;
		}
		
		let targetUrl = null;
		try {
			if (dataUrl) {
				targetUrl = new URL(dataUrl, window.location.origin);
			} else if (baseUrl) {
				targetUrl = new URL(baseUrl, window.location.origin);
			}
		} catch (err) {
			console.warn('[ChurchTools Suite] Failed to build target URL, falling back to current page', err);
		}
		
		if (!targetUrl) {
			targetUrl = new URL(window.location.href);
		}
		
		targetUrl.searchParams.set('event_id', eventId);
		if (singleTemplate) {
			targetUrl.searchParams.set('template', singleTemplate);
		}
		
		console.log('[ChurchTools Suite] Navigating to:', targetUrl.toString());
		window.location.href = targetUrl.toString();
	});
	
	/**
	 * Keyboard accessibility for page links (Enter/Space)
	 */
	$(document).on('keydown', '.cts-event-page-link', function(e) {
		if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
			e.preventDefault();
			$(this).click();
		}
	});

	/**
	 * Elementor Frontend Support
	 * Re-initialize handlers when Elementor widgets are loaded/refreshed
	 */
	$(window).on('elementor/frontend/init', function() {
		console.log('[ChurchTools Suite] Elementor frontend initialized');
		
		elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope) {
			console.log('[ChurchTools Suite] Elementor widget ready, checking for CTS widgets');
			
			// Check if this is a ChurchTools Suite widget
			if ($scope.find('.cts-events-container, .cts-calendar-monthly, .cts-event-grid, .cts-event-list').length > 0) {
				console.log('[ChurchTools Suite] CTS widget detected, reinitializing handlers');
				
				// Re-initialize calendar views for this widget
				$scope.find('.cts-calendar-monthly').each(function() {
					const $calendar = $(this);
					if (!$calendar.data('calendar-initialized')) {
						$calendar.data('calendar-initialized', true);
						setupCalendarNavigation($calendar);
					}
				});
			}
		});
	});

})(jQuery);
