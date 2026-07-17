(function(wp) {
	if (!wp || !wp.blocks || !wp.element || !wp.i18n || !wp.components || !wp.blockEditor) {
		return;
	}

	if (typeof console !== 'undefined' && typeof console.info === 'function') {
		console.info('[CTS Posts Sync] Block editor script loaded');
	}

	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var Placeholder = wp.components.Placeholder;

	var data = window.ctsPostsSyncBlockData || {};
	var blockName = data.blockName || 'churchtools-suite-posts-sync/posts-list';
	var postTypeOptions = Array.isArray(data.postTypeOptions) ? data.postTypeOptions.slice() : [];
	postTypeOptions.unshift({ value: '', label: __('Standard aus Sync-Einstellungen', 'churchtools-suite-posts-sync') });

	if (wp.blocks.getBlockType && wp.blocks.getBlockType(blockName) && wp.blocks.unregisterBlockType) {
		wp.blocks.unregisterBlockType(blockName);
	}

	registerBlockType(blockName, {
		title: __('ChurchTools Berichte', 'churchtools-suite-posts-sync'),
		description: __('Zeigt synchronisierte ChurchTools-Berichte als Liste an.', 'churchtools-suite-posts-sync'),
		icon: 'media-document',
		category: 'churchtools-suite',
		keywords: ['churchtools', 'berichte', 'posts'],
		attributes: {
			limit: { type: 'number', default: 10 },
			postType: { type: 'string', default: '' },
			showDate: { type: 'boolean', default: true },
			showExcerpt: { type: 'boolean', default: true },
			excerptWords: { type: 'number', default: 28 },
			onlyNew: { type: 'boolean', default: false },
			onlySynced: { type: 'boolean', default: true }
		},
		edit: function(props) {
			var attrs = props.attributes;
			return el(
				wp.element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __('Berichte-Liste', 'churchtools-suite-posts-sync'), initialOpen: true },
						el(RangeControl, {
							label: __('Anzahl', 'churchtools-suite-posts-sync'),
							value: attrs.limit,
							onChange: function(value) { props.setAttributes({ limit: value || 10 }); },
							min: 1,
							max: 100
						}),
						el(SelectControl, {
							label: __('Post-Typ', 'churchtools-suite-posts-sync'),
							value: attrs.postType,
							options: postTypeOptions,
							onChange: function(value) { props.setAttributes({ postType: value || '' }); }
						}),
						el(ToggleControl, {
							label: __('Nur synchronisierte Inhalte', 'churchtools-suite-posts-sync'),
							checked: !!attrs.onlySynced,
							onChange: function(value) { props.setAttributes({ onlySynced: !!value }); }
						}),
						el(ToggleControl, {
							label: __('Nur neue', 'churchtools-suite-posts-sync'),
							checked: !!attrs.onlyNew,
							onChange: function(value) { props.setAttributes({ onlyNew: !!value }); }
						}),
						el(ToggleControl, {
							label: __('Datum anzeigen', 'churchtools-suite-posts-sync'),
							checked: !!attrs.showDate,
							onChange: function(value) { props.setAttributes({ showDate: !!value }); }
						}),
						el(ToggleControl, {
							label: __('Auszug anzeigen', 'churchtools-suite-posts-sync'),
							checked: !!attrs.showExcerpt,
							onChange: function(value) { props.setAttributes({ showExcerpt: !!value }); }
						}),
						el(RangeControl, {
							label: __('Auszug-Wörter', 'churchtools-suite-posts-sync'),
							value: attrs.excerptWords,
							onChange: function(value) { props.setAttributes({ excerptWords: value || 28 }); },
							min: 8,
							max: 80,
							disabled: !attrs.showExcerpt
						})
					)
				),
				el(
					Placeholder,
					{
						icon: 'media-document',
						label: __('ChurchTools Berichte', 'churchtools-suite-posts-sync'),
						instructions: __('Die Ausgabe wird im Frontend dynamisch gerendert.', 'churchtools-suite-posts-sync')
					},
					el('p', null, __('Anzahl: ', 'churchtools-suite-posts-sync') + String(attrs.limit || 10)),
					el('p', null, __('Post-Typ: ', 'churchtools-suite-posts-sync') + (attrs.postType || __('Standard', 'churchtools-suite-posts-sync'))),
					el('p', null, __('Nur synchronisiert: ', 'churchtools-suite-posts-sync') + (attrs.onlySynced ? __('Ja', 'churchtools-suite-posts-sync') : __('Nein', 'churchtools-suite-posts-sync'))),
					el('p', null, __('Nur neue: ', 'churchtools-suite-posts-sync') + (attrs.onlyNew ? __('Ja', 'churchtools-suite-posts-sync') : __('Nein', 'churchtools-suite-posts-sync')))
				)
			);
		},
		save: function() {
			return null;
		}
	});
})(window.wp);
