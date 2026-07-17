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
		                 $('body').hasClass('wp-admin') ||
		                 $('body').hasClass('elementor-editor-active') ||
		                 window.location.href.indexOf('/wp-admin/') !== -1 ||
		                 window.location.search.indexOf('elementor-preview') !== -1;
		
		console.log('[ChurchTools Suite] Editor mode check:', {
			'block-editor-page': $('body').hasClass('block-editor-page'),
			'wp-admin': $('body').hasClass('wp-admin'),
			'elementor-editor-active': $('body').hasClass('elementor-editor-active'),
			'url-check': window.location.href.indexOf('/wp-admin/') !== -1,
			'elementor-preview': window.location.search.indexOf('elementor-preview') !== -1,
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
		initListFilters();
		initCalendarViews();
		initCountdownTimers(); // v1.1.1.0: Countdown Views
		initCarouselViews(); // v1.1.3.0: Carousel Views
		
		// Re-initialize views when DOM changes (for Gutenberg live preview)
		if (isEditor) {
			console.log('[ChurchTools Suite] Setting up MutationObserver for Views in Editor');
			const observer = new MutationObserver(function(mutations) {
				mutations.forEach(function(mutation) {
					if (mutation.addedNodes.length > 0) {
						mutation.addedNodes.forEach(function(node) {
							if (node.nodeType === 1) { // Element node
								const $node = $(node);
								// Check for Carousel
								if ($node.hasClass('cts-carousel-classic') || $node.find('.cts-carousel-classic').length > 0) {
									console.log('[ChurchTools Suite] Carousel detected in DOM, reinitializing...');
									setTimeout(initCarouselViews, 100);
								}
								// Check for Countdown
								if ($node.hasClass('cts-countdown-classic') || $node.find('.cts-countdown-classic').length > 0) {
									console.log('[ChurchTools Suite] Countdown detected in DOM, reinitializing...');
									setTimeout(initCountdownTimers, 100);
								}
								if ($node.hasClass('cts-fe-filter-bar') || $node.find('.cts-fe-filter-bar').length > 0) {
									setTimeout(initListFilters, 100);
								}
							}
						});
					}
				});
			});
			
			observer.observe(document.body, {
				childList: true,
				subtree: true
			});
		}
		
		console.log('[ChurchTools Suite] Init complete');
	});

	/**
	 * Frontend list filtering for list shortcodes/blocks/widgets.
	 */
	function initListFilters() {
		$('[data-cts-filter-bar]').each(function() {
			const $bar = $(this);

			if ($bar.data('cts-filter-init')) {
				return;
			}

			const $scope = $bar.nextAll('.churchtools-suite-wrapper').first();
			if ($scope.length === 0) {
				return;
			}

			const $items = $scope.find('[data-cts-calendar]');
			if ($items.length === 0) {
				return;
			}

			$bar.data('cts-filter-init', true);

			const $calendar = $bar.find('[data-cts-filter="calendar"]');
			const $tag = $bar.find('[data-cts-filter="tag"]');
			const $count = $bar.find('[data-cts-filter-count]');

			const updateCount = function(visibleCount, totalCount) {
				if ($count.length === 0) {
					return;
				}
				$count.text(visibleCount + ' / ' + totalCount + ' ' + (totalCount === 1 ? 'Event' : 'Events'));
			};

			const updateSeparators = function() {
				const separatorSelector = '.cts-month-separator, .cts-list__month-separator';
				const itemSelector = '.cts-event-classic, .cts-list--minimal__item, .cts-list__item';

				$scope.find(separatorSelector).each(function() {
					const $separator = $(this);
					const $between = $separator.nextUntil(separatorSelector, itemSelector);
					const hasVisibleItems = $between.filter(':visible').length > 0;
					$separator.toggle(hasVisibleItems);
				});
			};

			const applyFilters = function() {
				const calendarValue = String($calendar.val() || '').trim();
				const tagValue = String($tag.val() || '').trim();

				$items.each(function() {
					const $item = $(this);
					const itemCalendar = String($item.data('cts-calendar') || '');
					const itemTags = String($item.data('cts-tags') || '').split(',').map(function(v) {
						return v.trim();
					}).filter(Boolean);

					const calendarMatch = !calendarValue || itemCalendar === calendarValue;
					const tagMatch = !tagValue || itemTags.indexOf(tagValue) !== -1;

					$item.toggle(calendarMatch && tagMatch);
				});

				const visibleCount = $items.filter(':visible').length;
				updateSeparators();
				updateCount(visibleCount, $items.length);
			};

			$calendar.on('change', applyFilters);
			$tag.on('change', applyFilters);

			$bar.find('.cts-fe-filter-reset').on('click', function() {
				$calendar.val('');
				$tag.val('');
				applyFilters();
			});

			applyFilters();
		});
	}

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

		// Prevent accidental navigation when no valid image link is present.
		$(document).on('click', '#cts-modal-image-link', function(e) {
			const href = String($(this).attr('href') || '').trim();
			if (!href || href === '#') {
				e.preventDefault();
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
	 * Validate and normalize single-event template names.
	 */
	function normalizeSingleTemplate(template) {
		const value = String(template || '').trim().toLowerCase();
		if (value === 'minimal' || value === 'professional') {
			return value;
		}
		return 'professional';
	}

	/**
	 * Build a robust target URL for single-event navigation.
	 */
	function buildSingleEventTargetUrl(eventId, dataUrl, dataTemplate) {
		const configBaseUrl = (window.churchtoolsSuitePublic && churchtoolsSuitePublic.singleEventBaseUrl)
			? churchtoolsSuitePublic.singleEventBaseUrl
			: null;
		const configTemplate = (window.churchtoolsSuitePublic && churchtoolsSuitePublic.singleEventTemplate)
			? churchtoolsSuitePublic.singleEventTemplate
			: null;

		const isDefaultEventsPath = function(urlObj) {
			if (!urlObj || !urlObj.pathname) {
				return false;
			}
			const normalizedPath = urlObj.pathname.replace(/\/+$/, '').toLowerCase();
			return normalizedPath === '/events';
		};

		let targetUrl = null;
		try {
			if (dataUrl) {
				const dataTargetUrl = new URL(dataUrl, window.location.origin);
				if (!isDefaultEventsPath(dataTargetUrl)) {
					targetUrl = dataTargetUrl;
				}
			}

			if (!targetUrl && configBaseUrl) {
				const baseTargetUrl = new URL(configBaseUrl, window.location.origin);
				if (!isDefaultEventsPath(baseTargetUrl)) {
					targetUrl = baseTargetUrl;
				}
			}
		} catch (err) {
			console.warn('[ChurchTools Suite] Failed to build configured target URL, using current page', err);
		}

		if (!targetUrl) {
			targetUrl = new URL(window.location.href);
		}

		const template = normalizeSingleTemplate(dataTemplate || configTemplate);
		targetUrl.searchParams.set('event_id', String(eventId));
		targetUrl.searchParams.set('template', template);

		// Optional context marker for Elementor-related rendering logic.
		targetUrl.searchParams.set('ctse_context', 'elementor');

		return targetUrl.toString();
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
				click_action: clickAction,
				current_url: window.location.href
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
	 * Normalize data-* boolean-ish values from jQuery .data().
	 * Accepts true/false, 1/0, "1"/"0", "true"/"false", "yes"/"no", "on"/"off".
	 */
	function parseDataBool(value, defaultValue) {
		if (typeof value === 'boolean') {
			return value;
		}
		if (typeof value === 'number') {
			return value !== 0;
		}
		if (typeof value === 'string') {
			const normalized = value.trim().toLowerCase();
			if (['1', 'true', 'yes', 'on'].indexOf(normalized) !== -1) {
				return true;
			}
			if (['0', 'false', 'no', 'off'].indexOf(normalized) !== -1) {
				return false;
			}
		}
		return defaultValue;
	}

	/**
	 * Resolve a usable image URL from different payload variants.
	 * Accepts plain strings or objects with imageUrl/url/src fields.
	 */
	function resolveEventImageUrl(event) {
		const candidate = event && event.image_url;

		if (typeof candidate === 'string') {
			const trimmed = candidate.trim();
			return trimmed !== '' ? trimmed : '';
		}

		if (candidate && typeof candidate === 'object') {
			const objectUrl = candidate.imageUrl || candidate.url || candidate.src || '';
			if (typeof objectUrl === 'string') {
				const trimmed = objectUrl.trim();
				return trimmed !== '' ? trimmed : '';
			}
		}

		return '';
	}

	/**
	 * Display event data in modal
	 * @param {object} event - Event data from backend
	 * @param {jQuery} $container - Optional container element with display settings
	 */
	function displayEventData(event, $container) {
		// Extract display options from container (calendar/grid/list view)
		const showDescription = $container ? parseDataBool($container.data('show-description'), true) : true;
		const showLocation = $container ? parseDataBool($container.data('show-location'), true) : true;
		const showServices = $container ? parseDataBool($container.data('show-services'), true) : true;
		const showCalendarName = $container ? parseDataBool($container.data('show-calendar-name'), true) : true;
		
		console.log('[Modal] Display options:', {showDescription, showLocation, showServices, showCalendarName});
		
		// Set CSS Custom Properties for calendar colors (v1.1.4.4)
		const calendarColor = event.calendar_color || '#3498db';
		$('#cts-modal-overlay').css({
			'--cts-calendar-color': calendarColor,
			'--cts-primary-color': calendarColor
		});
		
		// Event Title (new: in main area)
		$('#cts-modal-event-title').text(event.title);
		
		// Event Image (new: hero image in main area) - v0.9.9.69
		const modalImageUrl = resolveEventImageUrl(event);
		if (modalImageUrl) {
			$('#cts-modal-image-img').attr('src', modalImageUrl);
			$('#cts-modal-image-link')
				.attr('href', modalImageUrl)
				.attr('aria-label', 'Bild in voller Groesse anzeigen: ' + (event.title || 'Event'));
			$('#cts-modal-image').show();
		} else {
			$('#cts-modal-image-link').attr('href', '#');
			$('#cts-modal-image-img').attr('src', '');
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
		if (showDescription && event.event_description && event.event_description.trim() !== '') {
			console.log('[Modal] Showing event description');
			$('#cts-modal-event-description-content').html(event.event_description);
			$('#cts-modal-event-description').show();
		} else {
			console.log('[Modal] Hiding event description (empty)');
			$('#cts-modal-event-description').hide();
		}
		
		// Appointment Description (in main area)
		if (showDescription && event.appointment_description && event.appointment_description.trim() !== '') {
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
				const $item = $('<li>').addClass('cts-modal-service-item');
				
				const $name = $('<strong>').addClass('cts-modal-service-name')
					.text(service.service_name + ':');
				
				const $person = $('<span>').addClass('cts-modal-service-person')
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
		const dataTemplate = $(this).data('template');
		console.log('[ChurchTools Suite] Event ID:', eventId);
		
		if (!eventId) {
			console.warn('[ChurchTools Suite] No event ID found on clicked element');
			return;
		}

		const targetUrl = buildSingleEventTargetUrl(eventId, dataUrl, dataTemplate);
		console.log('[ChurchTools Suite] Navigating to:', targetUrl);
		window.location.href = targetUrl;
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
	 * Initialize Countdown Timers (v1.1.1.0)
	 * Updates countdown displays every second
	 */
	function initCountdownTimers() {
		console.log('[ChurchTools Suite] Initializing countdown timers');
		
		$('.cts-countdown-classic').each(function() {
			const $countdown = $(this);
			const targetDate = $countdown.data('countdown-target');
			
			if (!targetDate) {
				console.warn('[ChurchTools Suite] Countdown target date missing');
				return;
			}
			
			console.log('[ChurchTools Suite] Countdown target:', targetDate);
			
			// Parse target date
			const target = new Date(targetDate);
			
			if (isNaN(target.getTime())) {
				console.error('[ChurchTools Suite] Invalid countdown target date:', targetDate);
				return;
			}
			
			// Update function
			const updateCountdown = function() {
				const now = new Date();
				const diff = target - now;
				
				if (diff <= 0) {
					// Event has passed
					$countdown.find('[data-unit="days"]').text('0');
					$countdown.find('[data-unit="hours"]').text('0');
					$countdown.find('[data-unit="minutes"]').text('0');
					$countdown.find('[data-unit="seconds"]').text('0');
					return;
				}
				
				// Calculate time units
				const days = Math.floor(diff / (1000 * 60 * 60 * 24));
				const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
				const seconds = Math.floor((diff % (1000 * 60)) / 1000);
				
				// Update display
				$countdown.find('[data-unit="days"]').text(days);
				$countdown.find('[data-unit="hours"]').text(hours);
				$countdown.find('[data-unit="minutes"]').text(minutes);
				$countdown.find('[data-unit="seconds"]').text(seconds);
			};
			
			// Initial update
			updateCountdown();
			
			// Update every second
			const intervalId = setInterval(updateCountdown, 1000);
			
			// Store interval ID for cleanup
			$countdown.data('countdown-interval', intervalId);
		});
		
		console.log('[ChurchTools Suite] Countdown timers initialized:', $('.cts-countdown-classic').length);
	}

	/**
	 * Initialize Carousel Views with horizontal navigation
	 * v1.1.3.0 - Basierend auf Grid Classic, konsistentes Design
	 */
	function initCarouselViews() {
		console.log('[Carousel] Searching for .cts-carousel-classic elements...');
		const $carousels = $('.cts-carousel-classic');
		console.log('[Carousel] Found', $carousels.length, 'carousel(s)');
		
		$carousels.each(function(index) {
			console.log('[Carousel] Initializing carousel #' + (index + 1));
			const $carousel = $(this);
			
			// Skip if already initialized
			if ($carousel.data('carousel-initialized')) {
				console.log('[Carousel] Carousel #' + (index + 1) + ' already initialized, skipping');
				return;
			}
			
			const $track = $carousel.find('.cts-carousel-track');
			const $slides = $carousel.find('.cts-carousel-slide');
			const $prevBtn = $carousel.find('.cts-carousel-nav-prev');
			const $nextBtn = $carousel.find('.cts-carousel-nav-next');
			const $pagination = $carousel.find('.cts-carousel-pagination');
			
			console.log('[Carousel] Elements found:', {
				track: $track.length,
				slides: $slides.length,
				prevBtn: $prevBtn.length,
				nextBtn: $nextBtn.length,
				pagination: $pagination.length
			});
			
			if ($slides.length === 0) return;
			
			// Carousel settings from data attributes
			const desktopSlidesPerView = parseInt($carousel.data('slides-per-view') || 3);
			const autoplay = $carousel.data('autoplay') == 1 || $carousel.data('autoplay') === true;
			const autoplayDelay = parseInt($carousel.data('autoplay-delay') || 5000);
			const loop = $carousel.data('loop') == 1 || $carousel.data('loop') === true;
			
			console.log('[Carousel] Settings:', {
				slidesPerView: desktopSlidesPerView,
				autoplay: autoplay,
				autoplayDelay: autoplayDelay,
				loop: loop,
				totalSlides: $slides.length
			});
			
			let slidesPerView = desktopSlidesPerView;
			let currentIndex = 0;
			let autoplayInterval = null;
			let isDragging = false;
			let startX = 0;
			let currentX = 0;
			let translateX = 0;
			
			// Responsive slides per view (respect desktop max)
			const updateSlidesPerView = () => {
				const width = $(window).width();
				if (width <= 640) {
					slidesPerView = 1;
				} else if (width <= 1024) {
					slidesPerView = Math.min(2, desktopSlidesPerView);
				} else {
					slidesPerView = desktopSlidesPerView;
				}
				// Set CSS custom property correctly
				$track.get(0).style.setProperty('--slides-per-view', slidesPerView);
				
				console.log('[Carousel] Updated slidesPerView:', slidesPerView, 'at width:', width);
			};
			updateSlidesPerView();
			
			// Calculate max index
			const getMaxIndex = () => Math.max(0, $slides.length - slidesPerView);

			const updateNavigationVisibility = () => {
				const hasNavigation = getMaxIndex() > 0;
				$prevBtn.toggle(hasNavigation);
				$nextBtn.toggle(hasNavigation);
				$pagination.toggle(hasNavigation);
			};
			
			// Update slide position
			const updateSlidePosition = (animated = true) => {
				const maxIndex = getMaxIndex();
				currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
				
				const slideWidth = $slides.first().outerWidth(true);
				const offset = -currentIndex * slideWidth;
				
				if (animated) {
					$track.css('transition', 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)');
				} else {
					$track.css('transition', 'none');
				}
				$track.css('transform', `translateX(${offset}px)`);
				
				// Update navigation buttons
				$prevBtn.prop('disabled', !loop && currentIndex === 0);
				$nextBtn.prop('disabled', !loop && currentIndex >= maxIndex);
				
				// Update pagination
				$pagination.find('.cts-carousel-dot').removeClass('active').eq(currentIndex).addClass('active');
			};
			
			// Navigation
			const goToSlide = (index) => {
				const maxIndex = getMaxIndex();
				if (loop) {
					if (index < 0) index = maxIndex;
					if (index > maxIndex) index = 0;
				}
				currentIndex = Math.max(0, Math.min(index, maxIndex));
				updateSlidePosition();
			};
			
			const nextSlide = () => goToSlide(currentIndex + 1);
			const prevSlide = () => goToSlide(currentIndex - 1);
			
			// Button events
			$prevBtn.on('click', prevSlide);
			$nextBtn.on('click', nextSlide);
			
			// Create pagination dots
			const createPagination = () => {
				$pagination.empty();
				const maxIndex = getMaxIndex();
				for (let i = 0; i <= maxIndex; i++) {
					const $dot = $('<button>')
						.addClass('cts-carousel-dot')
						.attr('aria-label', `Slide ${i + 1}`)
						.toggleClass('active', i === 0)
						.on('click', () => goToSlide(i));
					$pagination.append($dot);
				}
				updateNavigationVisibility();
			};
			createPagination();
			
			// Touch/Swipe support
			const getPositionX = (e) => {
				return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
			};
			
			const handleStart = (e) => {
				isDragging = true;
				startX = getPositionX(e);
				currentX = startX;
				$track.css('transition', 'none');
				stopAutoplay();
			};
			
			const handleMove = (e) => {
				if (!isDragging) return;
				e.preventDefault();
				currentX = getPositionX(e);
				const diff = currentX - startX;
				const slideWidth = $slides.first().outerWidth(true);
				const offset = -currentIndex * slideWidth + diff;
				$track.css('transform', `translateX(${offset}px)`);
			};
			
			const handleEnd = () => {
				if (!isDragging) return;
				isDragging = false;
				const diff = currentX - startX;
				const threshold = $slides.first().outerWidth() * 0.2;
				
				if (Math.abs(diff) > threshold) {
					if (diff > 0) {
						prevSlide();
					} else {
						nextSlide();
					}
				} else {
					updateSlidePosition();
				}
				startAutoplay();
			};
			
			$track.on('mousedown touchstart', handleStart);
			$(document).on('mousemove touchmove', handleMove);
			$(document).on('mouseup touchend', handleEnd);
			
			// Autoplay
			const stopAutoplay = () => {
				if (autoplayInterval) {
					clearInterval(autoplayInterval);
					autoplayInterval = null;
				}
			};
			
			const startAutoplay = () => {
				if (!autoplay) return;
				stopAutoplay();
				autoplayInterval = setInterval(nextSlide, autoplayDelay);
			};
			
			$carousel.on('mouseenter', stopAutoplay);
			$carousel.on('mouseleave', startAutoplay);
			
			// Resize handler (debounced)
			let resizeTimeout;
			$(window).on('resize', () => {
				clearTimeout(resizeTimeout);
				resizeTimeout = setTimeout(() => {
					updateSlidesPerView();
					createPagination();
					updateSlidePosition(false);
					updateNavigationVisibility();
				}, 150);
			});
			
			// Initialize
			updateSlidePosition(false);
			updateNavigationVisibility();
			startAutoplay();
			
			// Mark as initialized
			$carousel.data('carousel-initialized', true);
			
			// Store instance for cleanup
			$carousel.data('carousel-instance', {
				stop: stopAutoplay,
				currentIndex: () => currentIndex
			});
		});
		
		console.log('[ChurchTools Suite] Carousel views initialized:', $('.cts-carousel-classic').length);
	}

})(jQuery);
