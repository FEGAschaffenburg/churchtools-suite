/**
 * ChurchTools Suite Gutenberg Blocks
 * 
 * Unified Block with View Type Selection
 * Sprint 2: Layout-Parameter (columns) + Live Preview
 * 
 * @package ChurchTools_Suite
 * @since   0.6.3.10
 */

(function() {
	'use strict';
	
	console.log('✅ ChurchTools Suite Blocks JS geladen! Version 0.6.5.7 - Collapsible Panels!');
	
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
	const { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el, useState } = wp.element;
	const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
	
	/**
	 * View Options by Type (Standard + Presets)
	 */
	const standardViewOptions = {
		list: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' },
			{ label: 'Classic Services', value: 'classic-services' },
			{ label: 'Modern', value: 'modern' },
			{ label: 'Medium', value: 'medium' },
			{ label: 'Fluent', value: 'fluent' },
			{ label: 'Compact', value: 'compact' }
		],
		calendar: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Monthly Modern', value: 'monthly-modern' }
		],
		grid: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Simple', value: 'simple' },
			{ label: 'Colorful', value: 'colorful' },
			{ label: 'Modern', value: 'modern' }
		],
		search: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		widget: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Upcoming', value: 'upcoming' }
		],
		slider: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		masonry: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		agenda: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		timetable: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		carousel: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		countdown: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		cover: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		map: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		]
	};
	
	/**
	 * Hook to fetch presets from REST API
	 */
	function usePresets() {
		const [presets, setPresets] = wp.element.useState({});
		const [loading, setLoading] = wp.element.useState(true);
		
		wp.element.useEffect(function() {
			wp.apiFetch({ path: '/churchtools-suite/v1/presets' })
				.then(function(data) {
					setPresets(data);
					setLoading(false);
				})
				.catch(function(error) {
					console.error('Fehler beim Laden der Presets:', error);
					setLoading(false);
				});
		}, []);
		
		return { presets: presets, loading: loading };
	}
	
	/**
	 * Get combined view options (standard + presets)
	 */
	function getViewOptions(viewType, presets) {
		const standard = standardViewOptions[viewType] || [];
		const presetList = presets[viewType] || [];
		
		if (presetList.length === 0) {
			return standard;
		}
		
		// Add separator and presets
		return [
			...standard,
			{ label: '--- Meine Presets ---', value: '', disabled: true },
			...presetList
		];
	}
	
	/**
	 * Hook to fetch calendars from REST API
	 */
	function useCalendars() {
		const [calendars, setCalendars] = wp.element.useState([]);
		const [loading, setLoading] = wp.element.useState(true);
		
		wp.element.useEffect(function() {
			wp.apiFetch({ path: '/churchtools-suite/v1/calendars' })
				.then(function(data) {
					const calendarList = data
						.filter(function(item) { return item.value !== ''; })
						.map(function(item) {
							return { id: item.value, name: item.label };
						});
					setCalendars(calendarList);
					setLoading(false);
				})
				.catch(function(error) {
					console.error('Fehler beim Laden der Kalender:', error);
					setLoading(false);
				});
		}, []);
		
		return { calendars: calendars, loading: loading };
	}
	
	/**
	 * Get default view for view type
	 */
	function getDefaultView(viewType) {
		const defaults = {
			list: 'classic',
			calendar: 'monthly-modern',
			grid: 'simple',
			search: 'classic',
			widget: 'upcoming',
			slider: 'classic',
			masonry: 'classic',
			agenda: 'classic',
			timetable: 'classic',
			carousel: 'classic',
			countdown: 'classic',
			cover: 'classic',
			map: 'classic'
		};
		return defaults[viewType] || 'classic';
	}
	
	/**
	 * Get icon for view type
	 */
	function getIcon(viewType) {
		const icons = {
			list: '📋',
			calendar: '📅',
			grid: '▦',
			search: '🔍',
			widget: '📱',
			slider: '🎬',
			masonry: '🧱',
			agenda: '📒',
			timetable: '🏢',
			carousel: '🎠',
			countdown: '⏱️',
			cover: '🏞️',
			map: '🗺️'
		};
		return icons[viewType] || '📋';
	}
	
	/**
	 * Register ChurchTools Events Block
	 */
	registerBlockType('churchtools-suite/events', {
		title: __('ChurchTools Events', 'churchtools-suite'),
		description: __('Zeigt ChurchTools Events in verschiedenen Ansichten', 'churchtools-suite'),
		icon: 'calendar-alt',
		category: 'churchtools-suite',
		keywords: ['calendar', 'kalender', 'events', 'termine'],
		
		attributes: {
			viewType: { type: 'string', default: 'list' },
			view: { type: 'string', default: 'classic' },
			calendar: { type: 'string', default: '' },
			limit: { type: 'number', default: 5 },
			columns: { type: 'number', default: 3 },
			show_description: { type: 'boolean', default: true },
			show_location: { type: 'boolean', default: true },
			show_services: { type: 'boolean', default: true },
			show_calendar_name: { type: 'boolean', default: false },
			show_time: { type: 'boolean', default: true },
			order: { type: 'string', default: 'asc' },
			date_from: { type: 'string', default: '' },
			date_to: { type: 'string', default: '' },
			enableLivePreview: { type: 'boolean', default: false }
		},
		
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			
			// Fetch presets and calendars
			const { presets, loading: presetsLoading } = usePresets();
			const { calendars, loading: calendarsLoading } = useCalendars();
			const loading = calendarsLoading;
			
			// Parse selected calendar IDs
			const selectedIds = attributes.calendar ? attributes.calendar.split(',').filter(function(id) { return id; }) : [];
			
			// Check if current view is a STANDARD view (not a preset)
			const isStandardView = standardViewOptions[attributes.viewType] && standardViewOptions[attributes.viewType].some(function(option) {
				return option.value === attributes.view;
			});
			
			// Check if current view is a preset (nur wenn Presets geladen sind UND kein Standard-View)
			const isPresetView = !isStandardView && !presetsLoading && presets[attributes.viewType] && presets[attributes.viewType].some(function(preset) {
				return preset.value === attributes.view;
			});
			
			// Handle calendar toggle
			function handleCalendarToggle(calendarId) {
				var newSelectedIds = selectedIds.slice();
				var index = newSelectedIds.indexOf(calendarId);
				
				if (index > -1) {
					newSelectedIds.splice(index, 1);
				} else {
					newSelectedIds.push(calendarId);
				}
				
				setAttributes({ calendar: newSelectedIds.join(',') });
			}
			
			// Handle view type change
			function handleViewTypeChange(newViewType) {
				setAttributes({ 
					viewType: newViewType,
					view: getDefaultView(newViewType)
				});
			}
			
			return el('div', blockProps,
				// Inspector Controls (Sidebar)
				el(InspectorControls, {},
					// === PANEL 1: Ansicht & Kalender ===
					el(PanelBody, { 
						title: __('📋 Ansicht & Kalender', 'churchtools-suite'), 
						initialOpen: true 
					},
						// View Type Selector
						el(SelectControl, {
							label: __('Ansichtstyp', 'churchtools-suite'),
							value: attributes.viewType,
							options: [
								{ label: '📋 Liste', value: 'list' },
								{ label: '📅 Kalender', value: 'calendar' },
							{ label: '▦ Raster', value: 'grid' },
							{ label: '🔍 Suche', value: 'search' },
							{ label: '📱 Widget', value: 'widget' },
							{ label: '🎬 Slider', value: 'slider' },
							{ label: '🧱 Masonry', value: 'masonry' },
							{ label: '📒 Agenda', value: 'agenda' },
							{ label: '🏢 Timetable', value: 'timetable' },
							{ label: '🎠 Carousel', value: 'carousel' },
							{ label: '⏱️ Countdown', value: 'countdown' },
							{ label: '🏞️ Cover', value: 'cover' },
							{ label: '🗺️ Karte', value: 'map' }
							],
							onChange: handleViewTypeChange
						}),
						// View Variant Selector
						el(SelectControl, {
							label: __('Variante', 'churchtools-suite'),
							value: attributes.view,
							options: getViewOptions(attributes.viewType, presets),
							onChange: function(value) { setAttributes({ view: value }); }
						}),
						// Hinweis bei Presets
						isPresetView && el('div', { 
							style: { 
								padding: '12px', 
								background: '#e0f2fe', 
								border: '1px solid #0284c7', 
								borderRadius: '4px', 
								marginTop: '12px',
								fontSize: '13px',
								color: '#0c4a6e'
							}
						},
							el('strong', {}, '⚙️ Preset-Modus'),
							el('p', { style: { margin: '4px 0 0' } },
								__('Alle Einstellungen über Shortcode-Manager ändern.', 'churchtools-suite')
							)
						),
						// Kalender-Auswahl
						!isPresetView && el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
						!isPresetView && el('h4', { style: { fontSize: '13px', fontWeight: '600', marginBottom: '8px' } }, __('Kalender-Auswahl', 'churchtools-suite')),
						!isPresetView && (
							loading ? el('p', {}, __('Lade Kalender...', 'churchtools-suite')) :
							calendars.length === 0 ? el('p', {}, __('Keine Kalender verfügbar', 'churchtools-suite')) :
							el('div', {},
								calendars.map(function(calendar) {
									return el('div', { key: calendar.id, style: { marginBottom: '8px' } },
										el('label', { style: { display: 'flex', alignItems: 'center', cursor: 'pointer' } },
											el('input', {
												type: 'checkbox',
												checked: selectedIds.indexOf(calendar.id) > -1,
												onChange: function() { handleCalendarToggle(calendar.id); },
												style: { marginRight: '8px' }
											}),
											el('span', {}, calendar.name)
										)
									);
								}),
								el('p', { style: { fontSize: '11px', color: '#757575', marginTop: '8px' } },
									__('Keine Auswahl = alle Kalender', 'churchtools-suite')
								)
							)
						)
					),
					// === PANEL 2: Basis-Einstellungen ===
					!isPresetView && el(PanelBody, { 
						title: __('⚙️ Basis-Einstellungen', 'churchtools-suite'), 
						initialOpen: false 
					},
						// Limit (nur List & Grid)
						(attributes.viewType === 'list' || attributes.viewType === 'grid') && el(RangeControl, {
							label: __('Anzahl Termine', 'churchtools-suite'),
							value: attributes.limit,
							onChange: function(value) { setAttributes({ limit: value }); },
							min: 1,
							max: 100,
							step: 1,
							help: __('Maximale Anzahl der angezeigten Termine', 'churchtools-suite')
						}),
						// Columns (nur Grid)
						attributes.viewType === 'grid' && el(RangeControl, {
							label: __('Spalten', 'churchtools-suite'),
							value: attributes.columns,
							onChange: function(value) { setAttributes({ columns: value }); },
							min: 1,
							max: 4,
							step: 1,
							help: __('Anzahl der Spalten im Raster (1-4)', 'churchtools-suite')
						})
					),
					// === PANEL 3: Anzeige-Optionen ===
					!isPresetView && el(PanelBody, { 
						title: __('👁️ Anzeige-Optionen', 'churchtools-suite'), 
						initialOpen: false 
					},
						el(ToggleControl, {
							label: __('Beschreibung anzeigen', 'churchtools-suite'),
							checked: attributes.show_description,
							onChange: function(value) { setAttributes({ show_description: value }); },
							help: __('Event-Beschreibung unter dem Titel anzeigen', 'churchtools-suite')
						}),
						el(ToggleControl, {
							label: __('Ort anzeigen', 'churchtools-suite'),
							checked: attributes.show_location,
							onChange: function(value) { setAttributes({ show_location: value }); },
							help: __('Veranstaltungsort unter dem Event anzeigen', 'churchtools-suite')
						}),
						el(ToggleControl, {
							label: __('Services anzeigen', 'churchtools-suite'),
							checked: attributes.show_services,
							onChange: function(value) { setAttributes({ show_services: value }); },
							help: __('Event-Services und Zuordnungen anzeigen', 'churchtools-suite')
						}),
						el(ToggleControl, {
							label: __('Kalender-Name anzeigen', 'churchtools-suite'),
							checked: attributes.show_calendar_name,
							onChange: function(value) { setAttributes({ show_calendar_name: value }); },
							help: __('Name des Kalenders anzeigen', 'churchtools-suite')
						}),
						el(ToggleControl, {
							label: __('Uhrzeit anzeigen', 'churchtools-suite'),
							checked: attributes.show_time,
							onChange: function(value) { setAttributes({ show_time: value }); },
							help: __('Start- und Endzeit des Termins anzeigen', 'churchtools-suite')
						})
					),
					// === PANEL 4: Filter & Sortierung ===
					!isPresetView && el(PanelBody, { 
						title: __('🔍 Filter & Sortierung', 'churchtools-suite'), 
						initialOpen: false 
					},
						el(SelectControl, {
							label: __('Sortierung', 'churchtools-suite'),
							value: attributes.order,
							options: [
								{ label: 'Aufsteigend (älteste zuerst)', value: 'asc' },
								{ label: 'Absteigend (neueste zuerst)', value: 'desc' }
							],
							onChange: function(value) { setAttributes({ order: value }); },
							help: __('Reihenfolge der Events nach Datum', 'churchtools-suite')
						}),
						el(TextControl, {
							label: __('Datum von', 'churchtools-suite'),
							value: attributes.date_from,
							onChange: function(value) { setAttributes({ date_from: value }); },
							placeholder: 'YYYY-MM-DD',
							help: __('Start-Datum für Filter (z.B. 2025-01-01)', 'churchtools-suite')
						}),
						el(TextControl, {
							label: __('Datum bis', 'churchtools-suite'),
							value: attributes.date_to,
							onChange: function(value) { setAttributes({ date_to: value }); },
							placeholder: 'YYYY-MM-DD',
							help: __('End-Datum für Filter (z.B. 2025-12-31)', 'churchtools-suite')
						})
					),
					// Live Preview Toggle
					el(PanelBody, { 
						title: __('Vorschau-Einstellungen', 'churchtools-suite'),
						initialOpen: false
					},
						el(ToggleControl, {
							label: __('Live-Vorschau aktivieren', 'churchtools-suite'),
							help: __('Zeigt echte Events im Editor (kann langsamer sein)', 'churchtools-suite'),
							checked: attributes.enableLivePreview,
							onChange: function(value) {
								setAttributes({ enableLivePreview: value });
							}
						})
					)
				),
				// Preview Block
				attributes.enableLivePreview ? 
					// LIVE PREVIEW
					el('div', {
						className: 'cts-block-live-preview',
						style: {
							padding: '20px',
							background: '#f5f5f5',
							borderRadius: '4px',
							minHeight: '200px'
						}
					},
						el(ServerSideRender, {
							block: 'churchtools-suite/events',
							attributes: attributes
						})
					)
				:
					// STATIC PREVIEW
					el('div', { 
						style: { 
							padding: '32px',
							textAlign: 'center',
							background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
							borderRadius: '8px',
							color: 'white'
						} 
					},
						el('span', { 
							style: { fontSize: '48px', display: 'block', marginBottom: '16px' } 
						}, getIcon(attributes.viewType)),
						el('h3', { 
							style: { margin: '0 0 8px', fontSize: '18px', fontWeight: '600' } 
						}, __('ChurchTools Events', 'churchtools-suite')),
						el('p', { 
							style: { margin: '0', fontSize: '14px', opacity: '0.9' } 
						}, __('Typ: ', 'churchtools-suite') + attributes.viewType + ' | ' + __('Ansicht: ', 'churchtools-suite') + attributes.view),
						attributes.calendar && el('p', { 
							style: { margin: '4px 0 0', fontSize: '13px', opacity: '0.8' } 
						}, __('Kalender: ', 'churchtools-suite') + (selectedIds.length + ' ausgewählt')),
						!isPresetView && el('p', { 
							style: { margin: '8px 0 0', fontSize: '12px', opacity: '0.75', borderTop: '1px solid rgba(255,255,255,0.2)', paddingTop: '8px' } 
						}, 
							(attributes.viewType !== 'calendar' ? ('Limit: ' + attributes.limit + ' | ') : '') +
							'Beschreibung: ' + (attributes.show_description ? '✓' : '✗') + ' | ' +
							'Ort: ' + (attributes.show_location ? '✓' : '✗')
						),
						!isPresetView && attributes.viewType === 'grid' && el('p', { 
							style: { margin: '4px 0 0', fontSize: '11px', opacity: '0.65' } 
						}, 
							'Spalten: ' + attributes.columns
						)
					)
			);
		},
		
		save: function() {
			return null; // Server-side rendering
		}
	});
	
	console.log('✅ Block erfolgreich registriert!');
	
})();
