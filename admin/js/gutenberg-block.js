/**
 * Gutenberg Block für Repro CT Suite Events
 * Ermöglicht die Auswahl gespeicherter Shortcodes oder manuelle Konfiguration
 */

(function (blocks, element, blockEditor, components, data) {
	const el = element.createElement;
	const { registerBlockType } = blocks;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, SelectControl, TextControl, ToggleControl, RangeControl, Button, Notice } = components;
	const { useState, useEffect } = element;

	registerBlockType('repro-ct-suite/events', {
		apiVersion: 3,
		title: 'ChurchTools Termine',
		icon: 'calendar-alt',
		category: 'widgets',
		attributes: {
			mode: {
				type: 'string',
				default: 'manual' // 'manual' oder 'preset'
			},
			presetId: {
				type: 'number',
				default: 0
			},
			view: {
				type: 'string',
				default: 'list'
			},
			limit: {
				type: 'number',
				default: 10
			},
			calendarIds: {
				type: 'string',
				default: ''
			},
			fromDays: {
				type: 'number',
				default: 0
			},
			toDays: {
				type: 'number',
				default: 90
			},
			showPast: {
				type: 'boolean',
				default: false
			},
			showFields: {
				type: 'string',
				default: 'date,time,location,description'
			}
		},

		edit: function (props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps({
				className: 'rcts-gutenberg-block-wrapper'
			});
			
			const [presets, setPresets] = useState([]);
			const [loading, setLoading] = useState(true);

			// Lade gespeicherte Shortcodes
			useEffect(() => {
				if (window.shortcodeManager && window.shortcodeManager.ajaxUrl) {
					fetch(window.shortcodeManager.ajaxUrl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'sm_get_all_presets',
							nonce: window.shortcodeManager.nonce
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success && data.data) {
							const presetOptions = data.data.map(preset => ({
								label: preset.name,
								value: preset.id
							}));
							presetOptions.unshift({ label: '-- Manuell konfigurieren --', value: 0 });
							setPresets(presetOptions);
						}
						setLoading(false);
					})
					.catch(() => {
						setLoading(false);
					});
				} else {
					setLoading(false);
				}
			}, []);

			const viewOptions = [
				{ label: '📌 Kompakt', value: 'compact' },
				{ label: '� Liste', value: 'list' },
				{ label: '� Medium', value: 'medium' },
				{ label: '� Timeline', value: 'list-grouped' },
				{ label: '🎴 Karten', value: 'cards' },
				{ label: '� Sidebar', value: 'sidebar' }
			];

			const viewDescriptions = {
				compact: 'Ultra kompakt: Eine Zeile pro Termin',
				list: 'Standard: Große Datums-Box mit Details',
				medium: 'Ausgewogen: Zweispaltig mit Icon',
				'list-grouped': 'Timeline: Nach Datum gruppiert',
				cards: 'Grid: Karten-Layout responsive',
				sidebar: 'Widget: Für schmale Bereiche'
			};

			const isManualMode = attributes.mode === 'manual' || attributes.presetId === 0;

			return el(
				'div',
				blockProps,
				[
					el(
						InspectorControls,
						null,
						el(
							PanelBody,
							{ title: 'Shortcode-Quelle', initialOpen: true },
							[
								loading ? el(Notice, {
									status: 'info',
									isDismissible: false
								}, 'Lade gespeicherte Shortcodes...') : null,
								
								presets.length > 1 ? el(SelectControl, {
									label: 'Gespeicherten Shortcode verwenden',
									value: attributes.presetId,
									options: presets,
									onChange: (value) => {
										const numValue = parseInt(value);
										setAttributes({ 
											presetId: numValue,
											mode: numValue === 0 ? 'manual' : 'preset'
										});
									},
									help: 'Wähle einen gespeicherten Shortcode oder konfiguriere manuell'
								}) : el(Notice, {
									status: 'warning',
									isDismissible: false
								}, 'Keine gespeicherten Shortcodes gefunden. Gehe zu ChurchTools Suite → Shortcodes um einen zu erstellen.')
							]
						),
						
						isManualMode ? [
							el(
								PanelBody,
								{ title: 'Ansicht & Layout', initialOpen: true },
								[
									el(SelectControl, {
										label: 'Template wählen',
										value: attributes.view,
										options: viewOptions,
										onChange: (value) => setAttributes({ view: value }),
										help: viewDescriptions[attributes.view]
									}),
									el(RangeControl, {
										label: 'Anzahl Termine',
										value: attributes.limit,
										onChange: (value) => setAttributes({ limit: value }),
										min: 1,
										max: 50
									})
								]
							),
							el(
								PanelBody,
								{ title: 'Filter-Einstellungen', initialOpen: false },
								[
									el(TextControl, {
										label: 'Kalender-IDs',
										value: attributes.calendarIds,
										onChange: (value) => setAttributes({ calendarIds: value }),
										help: 'Komma-getrennt, z.B. "1,2,3" (leer = alle)'
									}),
									el(RangeControl, {
										label: 'Von (Tage ab heute)',
										value: attributes.fromDays,
										onChange: (value) => setAttributes({ fromDays: value }),
										min: -365,
										max: 365
									}),
									el(RangeControl, {
										label: 'Bis (Tage ab heute)',
										value: attributes.toDays,
										onChange: (value) => setAttributes({ toDays: value }),
										min: 0,
										max: 365
									}),
									el(ToggleControl, {
										label: 'Vergangene Termine anzeigen',
										checked: attributes.showPast,
										onChange: (value) => setAttributes({ showPast: value })
									})
								]
							),
							el(
								PanelBody,
								{ title: 'Anzuzeigende Felder', initialOpen: false },
								el(TextControl, {
									label: 'Felder',
									value: attributes.showFields,
									onChange: (value) => setAttributes({ showFields: value }),
									help: 'Komma-getrennt: date, time, location, description'
								})
							)
						] : null
					),
					el(
						'div',
						{
							style: {
								padding: '30px 20px',
								background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
								borderRadius: '12px',
								color: '#ffffff',
								textAlign: 'center',
								minHeight: '200px',
								display: 'flex',
								flexDirection: 'column',
								alignItems: 'center',
								justifyContent: 'center'
							}
						},
						[
							el('div', {
								style: {
									fontSize: '48px',
									marginBottom: '16px'
								}
							}, '📅'),
							el('h3', {
								style: {
									margin: '0 0 8px 0',
									fontSize: 'clamp(18px, 3vw, 24px)',
									fontWeight: '600'
								}
							}, 'ChurchTools Termine'),
							!isManualMode && attributes.presetId > 0 ? el('div', {
								style: {
									background: 'rgba(255,255,255,0.2)',
									padding: '8px 16px',
									borderRadius: '6px',
									margin: '8px 0',
									fontSize: '14px'
								}
							}, `📌 Gespeicherter Shortcode ID: ${attributes.presetId}`) : null,
							el('p', {
								style: {
									margin: '0',
									opacity: '0.9',
									fontSize: 'clamp(13px, 2.5vw, 15px)'
								}
							}, isManualMode ? 
								`Template: ${viewOptions.find(opt => opt.value === attributes.view)?.label || 'Standard'}` :
								'Verwendet gespeicherten Shortcode'
							),
							isManualMode ? el('p', {
								style: {
									margin: '8px 0 0 0',
									opacity: '0.8',
									fontSize: 'clamp(12px, 2vw, 14px)'
								}
							}, `${attributes.limit} Termine · ${attributes.showPast ? 'inkl. vergangene' : 'nur zukünftige'}`) : null,
							el('div', {
								style: {
									marginTop: '16px',
									fontSize: '12px',
									opacity: '0.7'
								}
							}, '🎨 Passt sich automatisch der Container-Breite an')
						]
					)
				]
			);
		},

		save: function (props) {
			const { attributes } = props;
			
			// Wenn Preset-Modus, nutze das gespeicherte Shortcode-Tag
			if (attributes.mode === 'preset' && attributes.presetId > 0) {
				// Wird vom Server über render_callback verarbeitet
				return null;
			}
			
			// Manuelle Konfiguration: Shortcode-String erstellen
			let shortcode = '[repro_ct_suite_events';
			shortcode += ` view="${attributes.view}"`;
			shortcode += ` limit="${attributes.limit}"`;
			
			if (attributes.calendarIds) {
				shortcode += ` calendar_ids="${attributes.calendarIds}"`;
			}
			
			if (attributes.fromDays !== 0) {
				shortcode += ` from_days="${attributes.fromDays}"`;
			}
			
			if (attributes.toDays !== 90) {
				shortcode += ` to_days="${attributes.toDays}"`;
			}
			
			if (attributes.showPast) {
				shortcode += ' show_past="true"';
			}
			
			if (attributes.showFields !== 'date,time,location,description') {
				shortcode += ` show_fields="${attributes.showFields}"`;
			}
			
			shortcode += ']';
			
			return el('div', null, shortcode);
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components
);
