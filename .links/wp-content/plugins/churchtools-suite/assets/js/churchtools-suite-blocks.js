/**
 * ChurchTools Suite - Gutenberg Block
 * 
 * CLEAN SLATE v1.0.0 - Complete Rewrite
 * 
 * @package ChurchTools_Suite
 * @since   1.0.0
 */

(function() {
	'use strict';
	
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
	const { PanelBody, SelectControl, RangeControl, ToggleControl, CheckboxControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el } = wp.element;
	const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
	
	/**
	 * Available View Types (from PHP, with fallback)
	 */
	const viewTypes = (window.churchtoolsSuiteBlocks && window.churchtoolsSuiteBlocks.viewTypes) || [
		{ label: __('Liste', 'churchtools-suite'), value: 'list' },
		{ label: __('Grid', 'churchtools-suite'), value: 'grid' },
		{ label: __('Kalender', 'churchtools-suite'), value: 'calendar' }
	];
	
	/**
	 * Available Views per Type
	 */
	const views = (window.churchtoolsSuiteBlocks && window.churchtoolsSuiteBlocks.views) || {
		list: [
			{ label: __('Classic', 'churchtools-suite'), value: 'classic' },
			{ label: __('Classic mit Bildern', 'churchtools-suite'), value: 'classic-with-images' },
			{ label: __('Minimal', 'churchtools-suite'), value: 'minimal' },
			{ label: __('Modern', 'churchtools-suite'), value: 'modern' },
			{ label: __('Tabelle', 'churchtools-suite'), value: 'table' }
		],
		grid: [
			{ label: __('Simple', 'churchtools-suite'), value: 'simple' },
			{ label: __('Modern', 'churchtools-suite'), value: 'modern' }
		],
		calendar: [
			{ label: __('Monat (Simple)', 'churchtools-suite'), value: 'monthly-simple' }
		]
	};
	
	/**
	 * Register ChurchTools Events Block
	 */

	registerBlockType('churchtools-suite/events', {
		title: __('ChurchTools Events', 'churchtools-suite'),
		description: __('Zeigt Events aus ChurchTools', 'churchtools-suite'),
		icon: 'calendar-alt',
		category: 'churchtools-suite',
		keywords: ['churchtools', 'events', 'kalender', 'termine'],
		
		attributes: {
			// View Configuration
			viewType: { type: 'string', default: 'list' },
			view: { type: 'string', default: 'classic' },
			
			// Event Settings
		limit: { type: 'number', default: 5 },		event_id: { type: 'number', default: 0 },			columns: { type: 'number', default: 3 },
			calendars: { type: 'string', default: '' },
			tags: { type: 'string', default: '' },
			show_event_description: { type: 'boolean', default: true },
			show_appointment_description: { type: 'boolean', default: true },
			show_location: { type: 'boolean', default: true },
			show_services: { type: 'boolean', default: false },
			show_time: { type: 'boolean', default: true },
			show_tags: { type: 'boolean', default: true },
			show_calendar_name: { type: 'boolean', default: true },
			show_filter: { type: 'boolean', default: false },
			show_images: { type: 'boolean', default: true },
			image_fit: { type: 'string', default: 'cover' },
			hero_title_font_size: { type: 'number', default: 0 },
			hero_layout_preset: { type: 'string', default: 'standard' },
			hero_mobile_optimize: { type: 'boolean', default: true },
			show_month_separator: { type: 'boolean', default: true },
			show_past_events: { type: 'boolean', default: false },
			
			// Style Management
			style_mode: { type: 'string', default: 'theme' },
			use_calendar_colors: { type: 'boolean', default: false },
			custom_primary_color: { type: 'string', default: '#2563eb' },
			custom_text_color: { type: 'string', default: '#1e293b' },
			custom_background_color: { type: 'string', default: '#ffffff' },
			custom_border_radius: { type: 'number', default: 6 },
			custom_font_size: { type: 'number', default: 14 },
			custom_padding: { type: 'number', default: 12 },
			custom_spacing: { type: 'number', default: 8 },
			
			// Features
			event_action: { type: 'string', default: 'modal' }
		},
		
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			
			// Get available views for current viewType
			const availableViews = views[attributes.viewType] || [];

			// Get feature matrix from localized data
			const viewFeatures = window.churchtoolsSuiteBlocks?.viewFeatures || {};
			const currentViewFeatures = viewFeatures[attributes.view] || {};
			
			// Helper: Check if feature is supported by current view
			const isFeatureSupported = function(featureName) {
				return currentViewFeatures[featureName] !== false;
			};
			
			// Helper: Get disabled help text
			const getDisabledHelpText = function(featureName, defaultHelp) {
				if (!isFeatureSupported(featureName)) {
					return __('Diese Option wird von der aktuellen View nicht unterstützt', 'churchtools-suite');
				}
				return defaultHelp || '';
			};

			// View helpers
			const isImagesSupportedByView = (function() {
				if (attributes.viewType === 'list') {
					return attributes.view === 'classic-with-images';
				}

				// Grid/Calendar keep existing feature-matrix behavior.
				return isFeatureSupported('show_images');
			})();

			// Minimal-View soll standardmäßig keinen Kalendernamen anzeigen
			if (attributes.view === 'minimal' && attributes.show_calendar_name) {
				setAttributes({ show_calendar_name: false });
			}
			
			// v0.9.6.25: Disable click events in editor
			const editorStyles = {
				pointerEvents: 'none', // Disable all click events
				position: 'relative'
			};
			
			const editorOverlay = el(
				'div',
				{
					style: {
						position: 'absolute',
						top: 0,
						left: 0,
						right: 0,
						bottom: 0,
						zIndex: 10,
						cursor: 'default'
					}
				}
			);
			
			return el(
				'div',
				blockProps,
				[
					// Inspector Controls (Sidebar)
					el(
						InspectorControls,
						{},
						[
							// Allgemein (v0.9.6.25: Moved to first position)
							el(
								PanelBody,
								{
									title: __('Allgemein', 'churchtools-suite'),
									initialOpen: true
								},
								[
									el(SelectControl, {
										label: __('Bei Event-Klick', 'churchtools-suite'),
										value: attributes.event_action,
										options: [
											{ label: __('Modal öffnen', 'churchtools-suite'), value: 'modal' },
											{ label: __('Event-Seite öffnen', 'churchtools-suite'), value: 'page' },
											{ label: __('Nicht anklickbar', 'churchtools-suite'), value: 'none' }
										],
										help: __('Modal = Popup-Fenster, Event-Seite = Eigene Seite mit URL-Parameter', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ event_action: value });
										}
									}),
									el(ToggleControl, {
										label: __('Vergangene Termine', 'churchtools-suite'),
										checked: attributes.show_past_events,
										help: __('Zeigt auch Termine in der Vergangenheit an', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ show_past_events: value });
										}
									})
								]
							),
							
							// View Settings
							el(
								PanelBody,
								{
									title: __('Ansicht', 'churchtools-suite'),
									initialOpen: false
								},
								[
									el(SelectControl, {
										label: __('Ansichtstyp', 'churchtools-suite'),
										value: attributes.viewType,
										options: viewTypes,
										onChange: function(value) {
											// v0.9.8.7: Auto-reset view to first available when viewType changes
											const newAvailableViews = views[value] || [];
											const newView = newAvailableViews.length > 0 ? newAvailableViews[0].value : 'classic';
											setAttributes({ 
												viewType: value,
												view: newView
											});
										}
									}),
									el(SelectControl, {
										label: __('View-Variante', 'churchtools-suite'),
										value: attributes.view,
										options: availableViews,
										onChange: function(value) {
											setAttributes({ view: value });
										}
									}),
								// v0.9.9.0: Grid-Spalten (Limit verschoben nach Filter & Anzahl)
									attributes.viewType === 'grid' ? el(RangeControl, {
										label: __('Anzahl Spalten', 'churchtools-suite'),
										value: attributes.columns,
										onChange: function(value) {
											setAttributes({ columns: value });
										},
										min: 1,
										max: 6,
										help: __('Anzahl der Spalten im Grid (1-6)', 'churchtools-suite')
									}) : null,

									// v0.9.6.26: Monats-Trennlinien nur bei Listen-Ansichten
									attributes.viewType === 'list' ? el(ToggleControl, {
										label: __('Monats-Trennlinien', 'churchtools-suite'),
										checked: attributes.show_month_separator,
										help: __('Zeigt Monatsüberschriften zwischen Events', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ show_month_separator: value });
										}
									}) : null
								]
							),
					
						// Display Options - ALWAYS visible, toggles disabled if not supported
						el(
				PanelBody,
				{
					title: __('Anzeige-Optionen', 'churchtools-suite'),
					initialOpen: false
				},
				[
									el(ToggleControl, {
										label: __('Event-Beschreibung', 'churchtools-suite'),
										checked: attributes.show_event_description,
										disabled: !isFeatureSupported('show_event_description'),
										help: getDisabledHelpText('show_event_description'),
										onChange: function(value) {
											setAttributes({ show_event_description: value });
										}
									}),
									el(ToggleControl, {
										label: __('Termin-Beschreibung', 'churchtools-suite'),
										checked: attributes.show_appointment_description,
										disabled: !isFeatureSupported('show_appointment_description'),
										help: getDisabledHelpText('show_appointment_description'),
										onChange: function(value) {
											setAttributes({ show_appointment_description: value });
										}
									}),
									el(ToggleControl, {
										label: __('Ort', 'churchtools-suite'),
										checked: attributes.show_location,
										disabled: !isFeatureSupported('show_location'),
										help: getDisabledHelpText('show_location'),
										onChange: function(value) {
											setAttributes({ show_location: value });
										}
									}),
									el(ToggleControl, {
										label: __('Services', 'churchtools-suite'),
										checked: attributes.show_services,
										disabled: !isFeatureSupported('show_services'),
										help: getDisabledHelpText('show_services'),
										onChange: function(value) {
											setAttributes({ show_services: value });
										}
									}),
									el(ToggleControl, {
										label: __('Uhrzeit', 'churchtools-suite'),
										checked: attributes.show_time,
										disabled: !isFeatureSupported('show_time'),
										help: getDisabledHelpText('show_time'),
										onChange: function(value) {
											setAttributes({ show_time: value });
										}
									}),
									el(ToggleControl, {
										label: __('Tags', 'churchtools-suite'),
										checked: attributes.show_tags,
										disabled: !isFeatureSupported('show_tags'),
										help: getDisabledHelpText('show_tags'),
										onChange: function(value) {
											setAttributes({ show_tags: value });
										}
									}),
									el(ToggleControl, {
										label: __('Bilder', 'churchtools-suite'),
										checked: attributes.show_images,
										disabled: !isImagesSupportedByView,
										help: isImagesSupportedByView
											? ''
											: __('Diese Option wird von der aktuellen View nicht unterstützt', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ show_images: value });
										}
									}),
									(attributes.show_images && isImagesSupportedByView) ? el(SelectControl, {
										label: __('Bilddarstellung', 'churchtools-suite'),
										value: attributes.image_fit || 'cover',
										options: [
											{ label: __('Zuschneiden (Cover)', 'churchtools-suite'), value: 'cover' },
											{ label: __('Ganzes Bild (Contain)', 'churchtools-suite'), value: 'contain' }
										],
										help: __('Contain zeigt das komplette Bild mit ggf. freien Flächen.', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ image_fit: value || 'cover' });
										}
									}) : null,
									(attributes.viewType === 'carousel' && attributes.view === 'carousel-einzel-event') ? el(RangeControl, {
										label: __('Titel-Schriftgröße (Hero)', 'churchtools-suite'),
										value: attributes.hero_title_font_size || 0,
										min: 0,
										max: 120,
										help: __('0 = automatisch (responsive). Sonst feste Pixelgröße.', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ hero_title_font_size: value || 0 });
										}
									}) : null,
									(attributes.viewType === 'carousel' && attributes.view === 'carousel-einzel-event') ? el(SelectControl, {
										label: __('Bild/Höhen-Preset (Hero)', 'churchtools-suite'),
										value: attributes.hero_layout_preset || 'standard',
										options: [
											{ label: __('Kompakt', 'churchtools-suite'), value: 'compact' },
											{ label: __('Standard', 'churchtools-suite'), value: 'standard' },
											{ label: __('Hero', 'churchtools-suite'), value: 'hero' }
										],
										onChange: function(value) {
											setAttributes({ hero_layout_preset: value || 'standard' });
										}
									}) : null,
									(attributes.viewType === 'carousel' && attributes.view === 'carousel-einzel-event') ? el(ToggleControl, {
										label: __('Mobile-Optimierung (Hero)', 'churchtools-suite'),
										checked: attributes.hero_mobile_optimize !== false,
										help: __('Optimiert Höhe, Titel und Abstände für kleine Bildschirme.', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ hero_mobile_optimize: value });
										}
									}) : null,
									el(ToggleControl, {
										label: __('Kalendername', 'churchtools-suite'),
										checked: attributes.show_calendar_name,
										disabled: !isFeatureSupported('show_calendar_name'),
										help: getDisabledHelpText('show_calendar_name'),
										onChange: function(value) {
											setAttributes({ show_calendar_name: value });
										}
									})
								]
							), // Close Display Options panel
							
// Filter & Anzahl Panel (v1.0.6.0: Reihenfolge: Tags, Kalender, Limit)
						el(
							PanelBody,
							{
								title: __('Filter & Anzahl', 'churchtools-suite'),
								initialOpen: false
							},
							[
								// Helper functions for checkbox state
								function() {
									const calendarsArray = attributes.calendars ? attributes.calendars.split(',').filter(Boolean) : [];
									const tagsArray = attributes.tags ? attributes.tags.split(',').filter(Boolean) : [];
									
									const availableCalendars = window.churchtoolsSuiteBlocks?.calendars || [];
									const availableTags = window.churchtoolsSuiteBlocks?.tags || [];
									
									// Tags FIRST (v1.0.6.0)
									const tagCheckboxes = availableTags.length > 0 ? [
										el('h4', { style: { margin: '0 0 8px 0', fontSize: '12px', fontWeight: '600' } }, 
											__('Tags', 'churchtools-suite')
										),
										el('p', { style: { fontSize: '11px', color: '#666', marginBottom: '8px' } }, 
											__('Events müssen ALLE ausgewählten Tags haben (UND-Verknüpfung)', 'churchtools-suite')
										),
										...availableTags.map(function(tag) {
											return el(CheckboxControl, {
												label: tag.label,
												checked: tagsArray.includes(tag.value),
												onChange: function(checked) {
													let newTags = [...tagsArray];
													if (checked) {
														if (!newTags.includes(tag.value)) {
															newTags.push(tag.value);
														}
													} else {
														newTags = newTags.filter(id => id !== tag.value);
													}
													setAttributes({
														tags: newTags.join(','),
														event_id: 0
													});
												}
											});
										})
									] : [];
									
									// Kalender SECOND (v1.0.6.0)
									const calendarCheckboxes = availableCalendars.length > 0 ? [
										el('h4', { style: { margin: '16px 0 8px 0', fontSize: '12px', fontWeight: '600' } }, 
											__('Kalender', 'churchtools-suite')
										),
										el('p', { style: { fontSize: '11px', color: '#666', marginBottom: '8px' } }, 
											__('Wählen Sie einen oder mehrere Kalender aus', 'churchtools-suite')
										),
										...availableCalendars.map(function(cal) {
											return el(CheckboxControl, {
												label: cal.label,
												checked: calendarsArray.includes(cal.value),
												onChange: function(checked) {
													let newCalendars = [...calendarsArray];
													if (checked) {
														if (!newCalendars.includes(cal.value)) {
															newCalendars.push(cal.value);
														}
													} else {
														newCalendars = newCalendars.filter(id => id !== cal.value);
													}
													setAttributes({
														calendars: newCalendars.join(','),
														event_id: 0
													});
												}
											});
										})
									] : [
										el('p', { style: { fontSize: '12px', color: '#999', fontStyle: 'italic' } }, 
											__('Keine Kalender verfügbar. Bitte synchronisieren Sie zuerst Kalender.', 'churchtools-suite')
										)
									];
									
									return el('div', {}, [
										attributes.viewType === 'list' ? el(ToggleControl, {
											label: __('Filter im Frontend anzeigen', 'churchtools-suite'),
											checked: attributes.show_filter,
											help: __('Zeigt über der Liste einen Kalender/Tag-Filter für Besucher', 'churchtools-suite'),
											onChange: function(value) {
												setAttributes({ show_filter: value });
											}
										}) : null,

										...tagCheckboxes,
										...calendarCheckboxes
									]);
								}(),
								
							// Event-ID Selection (nur für Countdown)
							attributes.viewType === 'countdown' ? el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }) : null,
							attributes.viewType === 'countdown' ? el(SelectControl, {
								label: __('Event-Auswahl', 'churchtools-suite'),
								value: attributes.event_id || 0,
								onChange: function(value) {
									setAttributes({ event_id: parseInt(value, 10) });
								},
								options: (function() {
									// Get all events from localized data
									const allEvents = (window.churchtoolsSuiteBlocks && window.churchtoolsSuiteBlocks.events) || [];
									
									// First option: Automatic next event
									const options = [
										{ label: __('Nächstes Event (automatisch)', 'churchtools-suite'), value: 0 }
									];
									
									// Filter events based on selected calendars and tags
									const calendarsArray = attributes.calendars ? attributes.calendars.split(',').filter(Boolean) : [];
									const tagsArray = attributes.tags ? attributes.tags.split(',').filter(Boolean) : [];
									
									const filteredEvents = allEvents.filter(function(event) {
										// Check calendar filter
										const calendarMatch = calendarsArray.length === 0 || calendarsArray.includes(String(event.calendar_id));
										
										// Check tags filter (must have ALL selected tags)
										let tagsMatch = true;
										if (tagsArray.length > 0 && event.tags) {
											const eventTags = event.tags.map(function(t) { return String(t.id || t); });
											tagsMatch = tagsArray.every(function(tagId) {
												return eventTags.includes(tagId);
											});
										}
										
										return calendarMatch && tagsMatch;
									});
									
									// Add filtered events to options
									filteredEvents.forEach(function(event) {
										options.push({
											label: event.label || event.title,
											value: event.value || event.id
										});
									});
									
									return options;
								})(),
								help: (function() {
									const calendarsArray = attributes.calendars ? attributes.calendars.split(',').filter(Boolean) : [];
									const tagsArray = attributes.tags ? attributes.tags.split(',').filter(Boolean) : [];
									const allEvents = (window.churchtoolsSuiteBlocks && window.churchtoolsSuiteBlocks.events) || [];
									
									const filteredCount = allEvents.filter(function(event) {
										const calendarMatch = calendarsArray.length === 0 || calendarsArray.includes(String(event.calendar_id));
										let tagsMatch = true;
										if (tagsArray.length > 0 && event.tags) {
											const eventTags = event.tags.map(function(t) { return String(t.id || t); });
											tagsMatch = tagsArray.every(function(tagId) {
												return eventTags.includes(tagId);
											});
										}
										return calendarMatch && tagsMatch;
									}).length;
									
									if (calendarsArray.length > 0 || tagsArray.length > 0) {
										return __('Gefiltert nach ausgewählten Kalendern/Tags', 'churchtools-suite') + ' (' + filteredCount + ' Events)';
									}
									return __('Wähle ein spezifisches Event oder lass es bei "automatisch"', 'churchtools-suite');
								})()
							}) : null,
							
							// LIMIT LAST (v1.0.6.0)
							el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
							attributes.viewType !== 'calendar' && attributes.viewType !== 'countdown' ? el(RangeControl, {
									label: __('Anzahl Events', 'churchtools-suite'),
									value: attributes.limit,
									onChange: function(value) {
										setAttributes({ limit: value });
									},
									min: 1,
									max: 50,
									help: __('Maximale Anzahl anzuzeigender Events', 'churchtools-suite')
								}) : null
							]
						),
							
							// Farbschema
							el(
								PanelBody,
								{
									title: __('Farbschema', 'churchtools-suite'),
									initialOpen: false
								},
								[
									el(SelectControl, {
										label: __('Style-Modus', 'churchtools-suite'),
										value: attributes.style_mode,
										options: [
											{ label: __('Plugin-Styles', 'churchtools-suite'), value: 'plugin' },
											{ label: __('Theme-Styles', 'churchtools-suite'), value: 'theme' },
											{ label: __('Individuelle Styles', 'churchtools-suite'), value: 'custom' }
										],
										help: attributes.style_mode === 'plugin' ? __('Plugin verwendet eigene Farbpalette', 'churchtools-suite') :
										      attributes.style_mode === 'theme' ? __('Plugin nutzt Theme-Farben (inherit)', 'churchtools-suite') :
										      __('Definieren Sie eigene Farben', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ style_mode: value });
										}
									}),
									// v0.9.9.2: Kalenderfarben verwenden
									el(ToggleControl, {
										label: __('Kalenderfarben verwenden', 'churchtools-suite'),
										checked: attributes.use_calendar_colors,
										help: __('Verwendet die Farbe des jeweiligen Kalenders als Akzentfarbe', 'churchtools-suite'),
										onChange: function(value) {
											setAttributes({ use_calendar_colors: value });
										}
									}),
									// Show color pickers only for custom mode
									attributes.style_mode === 'custom' && el(
										'div',
										{ style: { marginTop: '16px' } },
										[
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Primärfarbe', 'churchtools-suite')),
													el('input', {
														type: 'color',
														value: attributes.custom_primary_color,
														onChange: function(e) { setAttributes({ custom_primary_color: e.target.value }); },
														style: { width: '100%', height: '32px', cursor: 'pointer' }
													})
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Textfarbe', 'churchtools-suite')),
													el('input', {
														type: 'color',
														value: attributes.custom_text_color,
														onChange: function(e) { setAttributes({ custom_text_color: e.target.value }); },
														style: { width: '100%', height: '32px', cursor: 'pointer' }
													})
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Hintergrundfarbe', 'churchtools-suite')),
													el('input', {
														type: 'color',
														value: attributes.custom_background_color,
														onChange: function(e) { setAttributes({ custom_background_color: e.target.value }); },
														style: { width: '100%', height: '32px', cursor: 'pointer' }
													})
												]
											),
											el(
												'div',
												{ style: { marginTop: '20px', paddingTop: '16px', borderTop: '1px solid #ddd' } },
												[
													el('h4', { style: { margin: '0 0 12px 0', fontSize: '12px', fontWeight: '600' } }, __('Schriftgröße & Abstände', 'churchtools-suite'))
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Schriftgröße (px)', 'churchtools-suite')),
													el('input', {
														type: 'number',
														value: attributes.custom_font_size,
														onChange: function(e) { setAttributes({ custom_font_size: parseInt(e.target.value) }); },
														min: 10,
														max: 24,
														style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }
													})
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Innenabstand (px)', 'churchtools-suite')),
													el('input', {
														type: 'number',
														value: attributes.custom_padding,
														onChange: function(e) { setAttributes({ custom_padding: parseInt(e.target.value) }); },
														min: 0,
														max: 40,
														style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }
													})
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Abstände (px)', 'churchtools-suite')),
													el('input', {
														type: 'number',
														value: attributes.custom_spacing,
														onChange: function(e) { setAttributes({ custom_spacing: parseInt(e.target.value) }); },
														min: 0,
														max: 30,
														style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }
													})
												]
											),
											el(
												'div',
												{ style: { marginBottom: '12px' } },
												[
													el('label', { style: { display: 'block', marginBottom: '4px', fontSize: '11px', fontWeight: '500' } }, __('Ecken-Radius (px)', 'churchtools-suite')),
													el('input', {
														type: 'number',
														value: attributes.custom_border_radius,
														onChange: function(e) { setAttributes({ custom_border_radius: parseInt(e.target.value) }); },
														min: 0,
														max: 20,
														style: { width: '100%', padding: '6px', border: '1px solid #ddd', borderRadius: '4px' }
													})
												]
											)
										]
									)
								]
							)
						]
					),
					
					// v0.9.6.25: Editor overlay to disable clicks
					editorOverlay,
					
					// Block Preview (with click protection)
					el(
						'div',
						{ style: editorStyles },
						el(ServerSideRender, {
							block: 'churchtools-suite/events',
							attributes: attributes
						})
					)
				]
			);
		},
		
		save: function() {
			return null; // Server-side rendering
		}
	});
	
})(); // Close IIFE

