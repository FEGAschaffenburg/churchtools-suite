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
	const { PanelBody, SelectControl, RangeControl, ToggleControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement: el } = wp.element;
	const ServerSideRender = wp.serverSideRender || wp.components.ServerSideRender;
	
	/**
	 * Available View Types
	 * Start minimal - activate more views as needed
	 */
	const viewTypes = [
		{ label: __('Liste', 'churchtools-suite'), value: 'list' }
		// Deaktiviert für Clean Start:
		// { label: __('Kalender', 'churchtools-suite'), value: 'calendar' },
		// { label: __('Raster', 'churchtools-suite'), value: 'grid' },
		// { label: __('Suche', 'churchtools-suite'), value: 'search' },
		// { label: __('Widget', 'churchtools-suite'), value: 'widget' }
	];
	
	/**
	 * Available Views per Type
	 */
	const views = {
		list: [
			{ label: __('Classic', 'churchtools-suite'), value: 'classic' }
			// Deaktiviert für Clean Start:
			// { label: __('Medium', 'churchtools-suite'), value: 'medium' },
			// { label: __('Modern', 'churchtools-suite'), value: 'modern' },
			// { label: __('Fluent', 'churchtools-suite'), value: 'fluent' },
			// { label: __('Compact', 'churchtools-suite'), value: 'compact' }
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
			limit: { type: 'number', default: 10 },
			calendar: { type: 'string', default: '' },
			
			// Display Options
			show_event_description: { type: 'boolean', default: true },
			show_appointment_description: { type: 'boolean', default: true },
			show_location: { type: 'boolean', default: true },
			show_services: { type: 'boolean', default: false },
			show_time: { type: 'boolean', default: true },
			show_tags: { type: 'boolean', default: true },
			
			// Features
			enable_modal: { type: 'boolean', default: true }
		},
		
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const blockProps = useBlockProps();
			
			// Get available views for current viewType
			const availableViews = views[attributes.viewType] || [];
			
			return el(
				'div',
				blockProps,
				[
					// Inspector Controls (Sidebar)
					el(
						InspectorControls,
						{},
						[
							// View Settings
							el(
								PanelBody,
								{
									title: __('Ansicht', 'churchtools-suite'),
									initialOpen: true
								},
								[
									el(SelectControl, {
										label: __('Ansichtstyp', 'churchtools-suite'),
										value: attributes.viewType,
										options: viewTypes,
										onChange: function(value) {
											setAttributes({ viewType: value });
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
									el(RangeControl, {
										label: __('Anzahl Events', 'churchtools-suite'),
										value: attributes.limit,
										onChange: function(value) {
											setAttributes({ limit: value });
										},
										min: 1,
										max: 50
									})
								]
							),
							
							// Display Options
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
									})
								]
							),
							
							// Features
							el(
								PanelBody,
								{
									title: __('Features', 'churchtools-suite'),
									initialOpen: false
								},
								[
									el(ToggleControl, {
										label: __('Event-Details Modal', 'churchtools-suite'),
										checked: attributes.enable_modal,
										onChange: function(value) {
											setAttributes({ enable_modal: value });
										}
									})
								]
							)
						]
					),
					
					// Block Preview
					el(ServerSideRender, {
						block: 'churchtools-suite/events',
						attributes: attributes
					})
				]
			);
		},
		
		save: function() {
			return null; // Server-side rendering
		}
	});
	
})();
