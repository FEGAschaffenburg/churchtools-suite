/**
 * Modern Shortcode Manager JavaScript
 * 
 * Sauberes, modernes JavaScript für Shortcode-Management
 * 
 * @package Repro_CT_Suite
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Globale Shortcode Manager Klasse
    class ShortcodeManager {
        constructor() {
            this.modal = null;
            this.currentPresetId = null;
            this.isEditMode = false;
            
            this.init();
        }

        /**
         * Initialisierung
         */
        init() {
            this.setupElements();
            this.bindEvents();
            this.setupCalendarLogic();
            this.setupDisplayModeSelection();
            this.setupSearch();
            this.setupCollapsible();
            
            console.log('🚀 Modern Shortcode Manager initialized');
        }

        /**
         * Element-Referenzen einrichten
         */
        setupElements() {
            this.modal = $('#preset-modal');
            this.form = $('#preset-form');
            this.resultModal = $('#shortcode-result-modal');
            
            // Cache häufig verwendete Elemente
            this.elements = {
                createBtn: $('#create-shortcode-btn, #create-first-shortcode-btn'),
                closeBtn: $('#close-modal, #cancel-preset'),
                saveBtn: $('#save-preset'),
                saveDraftBtn: $('#save-draft'),
                modalTitle: $('#modal-title'),
                presetId: $('#preset-id'),
                presetName: $('#preset-name'),
                calendarMode: $('input[name="calendar_mode"]'),
                calendarList: $('#calendar-list'),
                calendarItems: $('.sm-calendar-item'),
                displayModeCards: $('.sm-option-card'),
                searchInput: $('#preset-search'),
                presetsGrid: $('#presets-grid')
            };
        }

        /**
         * Event-Handlers binden
         */
        bindEvents() {
            // Modal öffnen/schließen
            this.elements.createBtn.on('click', () => this.openCreateModal());
            this.elements.closeBtn.on('click', () => this.closeModal());
            $('.sm-modal').on('click', (e) => this.handleModalClick(e));

            // Form-Aktionen
            this.elements.saveBtn.on('click', () => this.savePreset(false));
            this.elements.saveDraftBtn.on('click', () => this.savePreset(true));

            // Preset-Aktionen
            $(document).on('click', '.sm-btn-edit, .edit-preset', (e) => this.editPreset(e));
            $(document).on('click', '.sm-btn-delete, .delete-preset', (e) => this.deletePreset(e));
            $(document).on('click', '.sm-btn-copy, .sm-copy-shortcode, .copy-shortcode', (e) => this.copyShortcode(e));

            // Preview-Buttons für Standard-Shortcodes
            $(document).on('click', '.preview-shortcode', (e) => this.openPreview(e));

            // Kalender-Auswahl
            this.elements.calendarMode.on('change', () => this.toggleCalendarSelection());
            $(document).on('click', '.sm-calendar-item', (e) => this.toggleCalendarItem(e));

            // Display-Modus
            $(document).on('click', '.sm-option-card', (e) => this.selectDisplayMode(e));

            // View-Toggle
            $('.sm-view-toggle button').on('click', (e) => this.toggleView(e));

            // Keyboard-Events
            $(document).on('keydown', (e) => this.handleKeyboard(e));
        }

        /**
         * Kalender-Auswahl Logik
         */
        setupCalendarLogic() {
            this.toggleCalendarSelection();
        }

        /**
         * Display-Modus Auswahl
         */
        setupDisplayModeSelection() {
            // Erste Option als Standard auswählen
            const firstCard = $('.sm-option-card').first();
            firstCard.addClass('active');
            firstCard.find('input[type="radio"]').prop('checked', true);
        }

        /**
         * Such-Funktionalität
         */
        setupSearch() {
            this.elements.searchInput.on('input', (e) => {
                const query = e.target.value.toLowerCase();
                this.filterPresets(query);
            });
        }

        /**
         * Modal zum Erstellen öffnen
         */
        openCreateModal() {
            this.isEditMode = false;
            this.currentPresetId = null;
            
            this.elements.modalTitle.text('Neuen Shortcode erstellen');
            this.elements.presetId.val('');
            this.elements.saveBtn.text('✨ Shortcode erstellen');
            
            this.resetForm();
            this.showModal();
        }

        /**
         * Modal zum Bearbeiten öffnen
         */
        openEditModal(presetId) {
            this.isEditMode = true;
            this.currentPresetId = presetId;
            
            this.elements.modalTitle.text('Shortcode bearbeiten');
            this.elements.presetId.val(presetId);
            this.elements.saveBtn.text('💾 Änderungen speichern');
            
            this.loadPresetData(presetId);
            this.showModal();
        }

        /**
         * Modal anzeigen
         */
        showModal() {
            this.modal.addClass('show');
            $('body').addClass('modal-open');
            
            // Fokus auf ersten Input
            setTimeout(() => {
                this.elements.presetName.focus();
            }, 300);
        }

        /**
         * Modal schließen
         */
        closeModal() {
            this.modal.removeClass('show');
            $('body').removeClass('modal-open');
            
            setTimeout(() => {
                this.resetForm();
            }, 300);
        }

        /**
         * Modal-Hintergrund Klick behandeln (DEAKTIVIERT - Modal soll nicht bei Außen-Klick schließen)
         */
        handleModalClick(e) {
            // Kommentiert aus, damit Modal nicht versehentlich geschlossen wird
            // if (e.target === e.currentTarget) {
            //     this.closeModal();
            // }
        }

        /**
         * Formular zurücksetzen
         */
        resetForm() {
            this.form[0].reset();
            
            // Preset-Modus zurücksetzen
            this.isEditMode = false;
            this.currentPresetId = null;
            this.elements.presetId.val('');
            this.elements.modalTitle.text('Neuen Shortcode erstellen');
            this.elements.saveBtn.text('💾 Speichern');
            
            // Kalender-Auswahl zurücksetzen
            $('.sm-calendar-item').removeClass('selected');
            $('.sm-calendar-checkbox').prop('checked', false);
            
            // Display-Modus zurücksetzen
            $('.sm-option-card').removeClass('active');
            $('.sm-option-card').first().addClass('active');
            $('.sm-option-card').first().find('input').prop('checked', true);
            
            // Kalender-Liste verstecken
            this.toggleCalendarSelection();
        }

        /**
         * Kalender-Auswahl umschalten
         */
        toggleCalendarSelection() {
            const mode = $('input[name="calendar_mode"]:checked').val();
            
            if (mode === 'specific') {
                this.elements.calendarList.addClass('show');
            } else {
                this.elements.calendarList.removeClass('show');
                // Alle Kalender abwählen
                $('.sm-calendar-checkbox').prop('checked', false);
                $('.sm-calendar-item').removeClass('selected');
            }
        }

        /**
         * Kalender-Element umschalten
         */
        toggleCalendarItem(e) {
            e.preventDefault();
            
            const item = $(e.currentTarget);
            const checkbox = item.find('.sm-calendar-checkbox');
            
            // Toggle-Status
            const isChecked = checkbox.prop('checked');
            checkbox.prop('checked', !isChecked);
            item.toggleClass('selected', !isChecked);
        }

        /**
         * Display-Modus auswählen
         */
        selectDisplayMode(e) {
            const card = $(e.currentTarget);
            const radio = card.find('input[type="radio"]');
            
            // Alle anderen deaktivieren
            $('.sm-option-card').removeClass('active');
            
            // Aktuellen aktivieren
            card.addClass('active');
            radio.prop('checked', true);
        }

        /**
         * Preset speichern
         */
        async savePreset(isDraft = false) {
            try {
                this.showLoading(true);
                
                const formData = this.collectFormData();
                formData.is_draft = isDraft;
                formData.preset_id = this.currentPresetId;
                
                // Validierung
                if (!this.validateFormData(formData)) {
                    this.showLoading(false);
                    return;
                }
                
                const response = await this.makeAjaxRequest('save_preset', formData);
                
                if (response.success) {
                    this.closeModal();
                    
                    // Erfolgs-Toast mit Shortcode anzeigen (statt separatem Modal)
                    if (!isDraft && response.data.shortcode) {
                        this.showToast('Shortcode erfolgreich erstellt: ' + response.data.shortcode, 'success');
                        // Shortcode in Zwischenablage kopieren
                        this.copyToClipboard(response.data.shortcode);
                    } else {
                        this.showToast('Shortcode erfolgreich gespeichert!', 'success');
                    }
                    
                    this.refreshPresetsList();
                } else {
                    this.showToast(response.data || 'Fehler beim Speichern', 'error');
                }
                
            } catch (error) {
                console.error('Save error:', error);
                this.showToast('Unerwarteter Fehler beim Speichern', 'error');
            } finally {
                this.showLoading(false);
            }
        }

        /**
         * Formulardaten sammeln
         */
        collectFormData() {
            const formData = {
                name: this.elements.presetName.val().trim(),
                calendar_mode: $('input[name="calendar_mode"]:checked').val(),
                calendar_ids: [],
                display_mode: $('input[name="display_mode"]:checked').val(),
                events_limit: parseInt($('#events-limit').val()) || 10,
                days_ahead: parseInt($('#days-ahead').val()) || 30,
                show_descriptions: $('input[name="show_descriptions"]').prop('checked'),
                show_locations: $('input[name="show_locations"]').prop('checked'),
                show_time: $('input[name="show_time"]').prop('checked'),
                show_organizer: $('input[name="show_organizer"]').prop('checked')
            };
            
            // Kalender-IDs sammeln
            if (formData.calendar_mode === 'specific') {
                formData.calendar_ids = $('.sm-calendar-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
            }
            
            return formData;
        }

        /**
         * Formulardaten validieren
         */
        validateFormData(data) {
            if (!data.name) {
                this.showToast('Bitte gib einen Namen für den Shortcode ein', 'error');
                this.elements.presetName.focus();
                return false;
            }
            
            if (data.calendar_mode === 'specific' && data.calendar_ids.length === 0) {
                this.showToast('Bitte wähle mindestens einen Kalender aus', 'error');
                return false;
            }
            
            return true;
        }

        /**
         * Preset bearbeiten
         */
        editPreset(e) {
            const presetId = $(e.currentTarget).data('preset-id');
            this.openEditModal(presetId);
        }

        /**
         * Preset-Daten laden
         */
        async loadPresetData(presetId) {
            try {
                const response = await this.makeAjaxRequest('get_preset', { preset_id: presetId });
                
                if (response.success) {
                    const preset = response.data;
                    this.populateForm(preset);
                } else {
                    this.showToast('Fehler beim Laden der Shortcode-Daten', 'error');
                }
            } catch (error) {
                console.error('Load error:', error);
                this.showToast('Fehler beim Laden der Shortcode-Daten', 'error');
            }
        }

        /**
         * Formular mit Preset-Daten füllen
         */
        populateForm(preset) {
            console.log('Populate form with preset:', preset);
            
            this.elements.presetName.val(preset.name);
            
            // Kalender-Modus setzen
            if (preset.calendar_ids && preset.calendar_ids !== 'all' && preset.calendar_ids !== null) {
                $('input[name="calendar_mode"][value="specific"]').prop('checked', true);
                this.toggleCalendarSelection();
                
                // Kalender auswählen
                const calendarIds = preset.calendar_ids.split(',');
                calendarIds.forEach(id => {
                    $(`.sm-calendar-checkbox[value="${id}"]`).prop('checked', true);
                    $(`.sm-calendar-item[data-calendar-id="${id}"]`).addClass('selected');
                });
            } else {
                $('input[name="calendar_mode"][value="all"]').prop('checked', true);
                this.toggleCalendarSelection();
            }
            
            // Display-Modus setzen
            $('.sm-option-card').removeClass('active');
            $(`.sm-option-card[data-mode="${preset.display_mode}"]`).addClass('active');
            $(`input[name="display_mode"][value="${preset.display_mode}"]`).prop('checked', true);
            
            // Weitere Optionen setzen - KORRIGIERT: limit_count statt events_limit
            $('#events-limit').val(preset.limit_count || preset.events_limit || 10);
            $('#days-ahead').val(preset.days_ahead || 30);
            
            // Features setzen - KORRIGIERT: show_location/show_description (singular)
            $('input[name="show_descriptions"]').prop('checked', (preset.show_description == 1 || preset.show_descriptions == 1));
            $('input[name="show_locations"]').prop('checked', (preset.show_location == 1 || preset.show_locations == 1));
            $('input[name="show_time"]').prop('checked', preset.show_time == 1);
            $('input[name="show_organizer"]').prop('checked', preset.show_organizer == 1);
        }

        /**
         * Preset löschen
         */
        async deletePreset(e) {
            const presetId = $(e.currentTarget).data('preset-id');
            
            if (!confirm('Möchtest du diesen Shortcode wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                return;
            }
            
            try {
                const response = await this.makeAjaxRequest('delete_preset', { preset_id: presetId });
                
                if (response.success) {
                    this.showToast('Shortcode erfolgreich gelöscht', 'success');
                    this.refreshPresetsList();
                } else {
                    this.showToast(response.data || 'Fehler beim Löschen', 'error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                this.showToast('Fehler beim Löschen des Shortcodes', 'error');
            }
        }

        /**
         * Shortcode kopieren
         */
        copyShortcode(e) {
            const shortcode = $(e.currentTarget).data('shortcode');
            this.copyToClipboard(shortcode);
        }

        /**
         * Text in Zwischenablage kopieren
         */
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.showToast('Shortcode in Zwischenablage kopiert!', 'success');
            } catch (err) {
                // Fallback für ältere Browser
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showToast('Shortcode kopiert!', 'success');
            }
        }

        /**
         * Vorschau für Standard-Shortcode öffnen
         */
        openPreview(e) {
            e.preventDefault();
            const viewType = $(e.currentTarget).data('shortcode');
            
            // Rufe die globale Funktion auf (die im inline-Script definiert ist)
            if (typeof window.openPreviewModal === 'function') {
                window.openPreviewModal(viewType);
            } else {
                console.warn('openPreviewModal function not found');
            }
        }

        /**
         * Ansicht umschalten (Grid/List)
         */
        toggleView(e) {
            const view = $(e.currentTarget).data('view');
            
            $('.sm-view-toggle button').removeClass('active');
            $(e.currentTarget).addClass('active');
            
            this.elements.presetsGrid.removeClass('grid-view list-view').addClass(`${view}-view`);
        }

        /**
         * Presets filtern
         */
        filterPresets(query) {
            $('.sm-preset-card').each(function() {
                const card = $(this);
                const name = card.find('.sm-preset-name').text().toLowerCase();
                const tags = card.find('.sm-preset-tag').map(function() {
                    return $(this).text().toLowerCase();
                }).get().join(' ');
                
                const matches = name.includes(query) || tags.includes(query);
                card.toggle(matches);
            });
        }

        /**
         * Keyboard-Events behandeln
         */
        handleKeyboard(e) {
            // ESC zum Schließen
            if (e.key === 'Escape' && this.modal.hasClass('show')) {
                this.closeModal();
            }
            
            // Enter zum Speichern (wenn im Modal)
            if (e.key === 'Enter' && this.modal.hasClass('show') && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                this.savePreset(false);
            }
        }

        /**
         * Shortcode-Ergebnis anzeigen
         */
        showShortcodeResult(shortcode) {
            $('#generated-shortcode').text(shortcode);
            this.resultModal.addClass('show');
        }

        /**
         * Shortcode-Ergebnis-Modal schließen
         */
        closeShortcodeResult() {
            this.resultModal.removeClass('show');
        }

        /**
         * Generierter Shortcode kopieren
         */
        copyGeneratedShortcode() {
            const shortcode = $('#generated-shortcode').text();
            this.copyToClipboard(shortcode);
        }

        /**
         * Kopieren und schließen
         */
        copyAndClose() {
            this.copyGeneratedShortcode();
            this.closeShortcodeResult();
        }

        /**
         * Loading-Status anzeigen
         */
        showLoading(isLoading) {
            if (isLoading) {
                this.elements.saveBtn.prop('disabled', true).text('💫 Speichere...');
                this.elements.saveDraftBtn.prop('disabled', true);
            } else {
                this.elements.saveBtn.prop('disabled', false);
                this.elements.saveDraftBtn.prop('disabled', false);
                
                if (this.isEditMode) {
                    this.elements.saveBtn.text('💾 Änderungen speichern');
                } else {
                    this.elements.saveBtn.text('✨ Shortcode erstellen');
                }
            }
        }

        /**
         * Toast-Nachricht anzeigen
         */
        showToast(message, type = 'success') {
            const toast = $(`
                <div class="sm-toast ${type}">
                    <span class="sm-toast-icon">${type === 'success' ? '✅' : '❌'}</span>
                    ${message}
                </div>
            `);
            
            $('body').append(toast);
            
            setTimeout(() => toast.addClass('show'), 100);
            
            setTimeout(() => {
                toast.removeClass('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        /**
         * Shortcode-Liste aktualisieren
         */
        refreshPresetsList() {
            location.reload(); // Einfacher Reload für jetzt
        }

        /**
         * AJAX-Request ausführen
         */
        async makeAjaxRequest(action, data = {}) {
            const requestData = {
                action: `sm_${action}`,
                nonce: window.shortcodeManager.nonce,
                ...data
            };
            
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: window.shortcodeManager.ajaxUrl,
                    type: 'POST',
                    data: requestData,
                    dataType: 'json',
                    success: resolve,
                    error: (xhr, status, error) => {
                        console.error('AJAX Error:', error, xhr.responseText);
                        reject(new Error(`AJAX Error: ${error}`));
                    }
                });
            });
        }
        
        /**
         * Setup Collapsible Sections
         */
        setupCollapsible() {
            $('.sm-collapsible-header').on('click', function() {
                const $header = $(this);
                const targetId = $header.data('target');
                const $content = $('#' + targetId);
                const $icon = $header.find('.sm-collapse-icon');
                
                // Toggle content
                $content.slideToggle(300);
                
                // Toggle icon
                if ($icon.text() === '▼') {
                    $icon.text('▶');
                } else {
                    $icon.text('▼');
                }
                
                // Toggle active class
                $header.toggleClass('collapsed');
            });
        }
    }

    // Globale Funktionen für Modal-Ereignisse
    window.closeShortcodeModal = function() {
        $('#shortcode-result-modal').removeClass('show');
    };

    window.copyGeneratedShortcode = function() {
        const shortcode = $('#generated-shortcode').text();
        window.smInstance.copyToClipboard(shortcode);
    };

    window.copyAndClose = function() {
        window.copyGeneratedShortcode();
        window.closeShortcodeModal();
    };

    // Initialisierung nach DOM-Ready
    $(document).ready(function() {
        window.smInstance = new ShortcodeManager();
    });

})(jQuery);