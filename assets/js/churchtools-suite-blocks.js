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
	
	console.log('✅ ChurchTools Suite Blocks JS geladen! Version 0.10.4.13 - List Views Cleanup!');
	
	const { registerBlockType } = wp.blocks;
	const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
	const { PanelBody, SelectControl, RangeControl, ToggleControl, TextControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el, useState } = wp.element;
	const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
	
	/**
	 * View Options by Type (nur implementierte Views)
	 * TODO: slider, masonry, agenda, timetable, carousel, countdown, cover, map später hinzufügen
	 */
	const standardViewOptions = {
		list: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' },
			{ label: 'Medium', value: 'medium' }
		],
		calendar: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Monthly Modern', value: 'monthly-modern' }
		],
		grid: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Simple', value: 'simple' },
			{ label: 'Modern', value: 'modern' },
			{ label: 'Colorful', value: 'colorful' }
		],
		search: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Classic', value: 'classic' }
		],
		widget: [
			{ label: '--- Standard Views ---', value: '', disabled: true },
			{ label: 'Upcoming', value: 'upcoming' }
		]
	};
	
	/**
	 * Get combined view options (standard only - v0.10.4.15: Presets deaktiviert)
	 */
	function getViewOptions(viewType, presets) {
		const standard = standardViewOptions[viewType] || [];
		// v0.10.4.15: Shortcode Manager deaktiviert, daher keine Presets mehr
		return standard;
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
			widget: 'upcoming'
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
			widget: '📱'
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
			enable_modal: { type: 'boolean', default: true },
			// v0.10.3.28: Tooltip options - defaults MUST match PHP (only show_time=true)
			show_event_description: { type: 'boolean', default: false },
			show_appointment_description: { type: 'boolean', default: false },
			show_location: { type: 'boolean', default: false },
			show_services: { type: 'boolean', default: false },
			show_calendar_name: { type: 'boolean', default: false },
			show_time: { type: 'boolean', default: true },
			show_tags: { type: 'boolean', default: true },
			order: { type: 'string', default: 'asc' },
			date_from: { type: 'string', default: '' },
			date_to: { type: 'string', default: '' },
			enableLivePreview: { type: 'boolean', default: false }
		},
		
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			
			// v0.10.4.15: Nur noch Calendars, keine Presets mehr
			const { calendars, loading } = useCalendars();
			
			// Parse selected calendar IDs
			const selectedIds = attributes.calendar ? attributes.calendar.split(',').filter(function(id) { return id; }) : [];
			
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
							{ label: '📱 Widget', value: 'widget' }
							],
							onChange: handleViewTypeChange
						}),
						// View Variant Selector
						el(SelectControl, {
							label: __('Variante', 'churchtools-suite'),
							value: attributes.view,
							options: getViewOptions(attributes.viewType),
							onChange: function(value) { setAttributes({ view: value }); }
						}),
						// Kalender-Auswahl
					el('hr', { style: { margin: '16px 0', border: 'none', borderTop: '1px solid #ddd' } }),
					el('h4', { style: { fontSize: '13px', fontWeight: '600', marginBottom: '8px' } }, __('Kalender-Auswahl', 'churchtools-suite')),
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
					),
					// === PANEL 2: Basis-Einstellungen ===
					el(PanelBody, {
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
					// Click-to-Details Toggle
					el(ToggleControl, {
						label: __('👆 Click-to-Details', 'churchtools-suite'),
						checked: attributes.enable_modal,
						onChange: function(value) { setAttributes({ enable_modal: value }); },
						help: __('Öffnet Event-Details in einem Modal beim Klick auf einen Termin', 'churchtools-suite')
					}),
					// === PANEL 3: Anzeige-Optionen (NICHT für Calendar!) ===
				attributes.viewType !== 'calendar' && el(PanelBody, {
						title: __('👁️ Anzeige-Optionen', 'churchtools-suite'), 
						initialOpen: false 
					},
						el(ToggleControl, {
							label: __('Event-Beschreibung anzeigen', 'churchtools-suite'),
							checked: attributes.show_event_description,
							onChange: function(value) { setAttributes({ show_event_description: value }); },
							help: __('Beschreibung der Event-Serie anzeigen', 'churchtools-suite')
						}),
						el(ToggleControl, {
							label: __('Termin-Beschreibung anzeigen', 'churchtools-suite'),
							checked: attributes.show_appointment_description,
							onChange: function(value) { setAttributes({ show_appointment_description: value }); },
							help: __('Beschreibung des einzelnen Termins anzeigen', 'churchtools-suite')
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
						}),
						el(ToggleControl, {
							label: __('Tags anzeigen', 'churchtools-suite'),
							checked: attributes.show_tags,
							onChange: function(value) { setAttributes({ show_tags: value }); },
							help: __('ChurchTools-Tags als farbige Badges anzeigen', 'churchtools-suite')
						})
					),
					// === PANEL 4: Filter & Sortierung ===
					el(PanelBody, { 
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
					el('p', { 
						style: { margin: '8px 0 0', fontSize: '12px', opacity: '0.75', borderTop: '1px solid rgba(255,255,255,0.2)', paddingTop: '8px' } 
					}, 
						(attributes.viewType !== 'calendar' ? ('Limit: ' + attributes.limit + ' | ') : '') +
						'Ort: ' + (attributes.show_location ? '✓' : '✗')
					),
					attributes.viewType === 'grid' && el('p', {
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
