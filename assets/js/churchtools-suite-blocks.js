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
	const viewTypes = (window.churchtoolsSuiteBlocks && churchtoolsSuiteBlocks.viewTypes) || [
		{ label: __('Liste', 'churchtools-suite'), value: 'list' },
		{ label: __('Grid', 'churchtools-suite'), value: 'grid' },
		{ label: __('Kalender', 'churchtools-suite'), value: 'calendar' }
	];
	
	/**
	 * Available Views per Type
	 */
	const views = (window.churchtoolsSuiteBlocks && churchtoolsSuiteBlocks.views) || {
		list: [
			{ label: __('Classic', 'churchtools-suite'), value: 'classic' },
			{ label: __('Classic mit Bildern', 'churchtools-suite'), value: 'classic-with-images' },
			{ label: __('Minimal', 'churchtools-suite'), value: 'minimal' },
			{ label: __('Modern', 'churchtools-suite'), value: 'modern' }
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
		limit: { type: 'number', default: 5 },
			columns: { type: 'number', default: 3 },
			calendars: { type: 'string', default: '' },
			tags: { type: 'string', default: '' },
			show_event_description: { type: 'boolean', default: true },
			show_appointment_description: { type: 'boolean', default: true },
			show_location: { type: 'boolean', default: true },
			show_services: { type: 'boolean', default: false },
			show_time: { type: 'boolean', default: true },
			show_tags: { type: 'boolean', default: true },
			show_calendar_name: { type: 'boolean', default: true },
			show_images: { type: 'boolean', default: true },
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

			// View helpers
			const isListWithImages = attributes.viewType === 'list' && (attributes.view === 'classic-with-images' || attributes.view === 'modern');

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
									// v0.9.8.9: Anzahl Events nur bei Listen-Ansichten
									attributes.viewType === 'list' ? el(RangeControl, {
										label: __('Anzahl Events', 'churchtools-suite'),
										value: attributes.limit,
										onChange: function(value) {
											setAttributes({ limit: value });
										},
										min: 1,
										max: 50
									}) : null,
									// v0.9.9.0: Grid-Einstellungen
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
									attributes.viewType === 'grid' ? el(RangeControl, {
										label: __('Anzahl Events', 'churchtools-suite'),
										value: attributes.limit,
										onChange: function(value) {
											setAttributes({ limit: value });
										},
										min: 1,
										max: 50
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
					
						// Display Options (v0.9.7.3: Show for classic and modern views, v0.9.9.0: + grid simple, v0.9.9.66: + grid modern, v0.9.9.96: + classic-with-images)
						(attributes.view === 'classic' || attributes.view === 'classic-with-images' || attributes.view === 'modern' || (attributes.viewType === 'grid' && (attributes.view === 'simple' || attributes.view === 'modern'))) ? el(
				PanelBody,
				{
					title: __('Anzeige-Optionen', 'churchtools-suite'),
					initialOpen: false
				},
				[
									el(ToggleControl, {
										label: __('Event-Beschreibung', 'churchtools-suite'),
										checked: attributes.show_event_description,
										onChange: function(value) {
											setAttributes({ show_event_description: value });
										}
									}),
									el(ToggleControl, {
										label: __('Termin-Beschreibung', 'churchtools-suite'),
										checked: attributes.show_appointment_description,
										onChange: function(value) {
											setAttributes({ show_appointment_description: value });
										}
									}),
									el(ToggleControl, {
										label: __('Ort', 'churchtools-suite'),
										checked: attributes.show_location,
										onChange: function(value) {
											setAttributes({ show_location: value });
										}
									}),
									el(ToggleControl, {
										label: __('Services', 'churchtools-suite'),
										checked: attributes.show_services,
										onChange: function(value) {
											setAttributes({ show_services: value });
										}
									}),
									el(ToggleControl, {
										label: __('Uhrzeit', 'churchtools-suite'),
										checked: attributes.show_time,
										onChange: function(value) {
											setAttributes({ show_time: value });
										}
									}),
									el(ToggleControl, {
										label: __('Tags', 'churchtools-suite'),
										checked: attributes.show_tags,
										onChange: function(value) {
											setAttributes({ show_tags: value });
										}
									}),
									el(ToggleControl, {
										label: __('Kalendername', 'churchtools-suite'),
										checked: attributes.show_calendar_name,
										onChange: function(value) {
											setAttributes({ show_calendar_name: value });
										}
									})
								]
							) : null, // Close conditional Display Options panel
							
// Filter Panel (v0.9.9.94: Multi-Select mit Klarnamen)
						el(
							PanelBody,
							{
								title: __('Filter', 'churchtools-suite'),
								initialOpen: false
							},
							[
								// Helper functions for checkbox state
								function() {
									const calendarsArray = attributes.calendars ? attributes.calendars.split(',').filter(Boolean) : [];
									const tagsArray = attributes.tags ? attributes.tags.split(',').filter(Boolean) : [];
									
									const availableCalendars = window.churchtoolsSuiteBlocks?.calendars || [];
									const availableTags = window.churchtoolsSuiteBlocks?.tags || [];
									
									const calendarCheckboxes = availableCalendars.length > 0 ? [
										el('h4', { style: { margin: '0 0 8px 0', fontSize: '12px', fontWeight: '600' } }, 
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
													setAttributes({ calendars: newCalendars.join(',') });
												}
											});
										})
									] : [
										el('p', { style: { fontSize: '12px', color: '#999', fontStyle: 'italic' } }, 
											__('Keine Kalender verfügbar. Bitte synchronisieren Sie zuerst Kalender.', 'churchtools-suite')
										)
									];
									
									const tagCheckboxes = availableTags.length > 0 ? [
										el('h4', { style: { margin: '16px 0 8px 0', fontSize: '12px', fontWeight: '600' } }, 
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
													setAttributes({ tags: newTags.join(',') });
												}
											});
										})
									] : [];
									
									return el('div', {}, [
										...calendarCheckboxes,
										...tagCheckboxes
									]);
								}()
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

