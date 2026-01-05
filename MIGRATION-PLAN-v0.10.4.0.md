# Template Migration Plan v0.10.4.0
## Alle Templates: Vollständige Toggle + Tags Unterstützung

### Ziel:
1. **Shortcode Manager deaktiviert** ✅ (bereits erledigt)
2. Alle Templates unterstützen ALLE Toggles
3. Alle Templates zeigen Tags an

---

## Phase 1: Fehlende Toggles hinzufügen

### list/compact.php
**Fehlend:** show_description, show_services, show_calendar_name

**Änderung (Zeile 19-20):**
```php
// ALT:
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : false;

// NEU:
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : false;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : false;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
```

**Template-Ergänzung (nach Zeile 53):**
```php
<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
	<div class="cts-list-item-description">
		<?php echo wp_kses_post( wpautop( $event['description'] ) ); ?>
	</div>
<?php endif; ?>

<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
	<div class="cts-list-item-services">
		<?php foreach ( $event['services'] as $service ) : ?>
			<span class="cts-service"><?php echo esc_html( $service['service_name'] ); ?>: <?php echo esc_html( $service['person_name'] ); ?></span>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
	<div class="cts-list-item-calendar">
		<?php echo esc_html( $event['calendar_name'] ); ?>
	</div>
<?php endif; ?>
```

---

### list/fluent.php
**Fehlend:** show_calendar_name

**Änderung:** Zeile ~23 hinzufügen:
```php
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
```

**Template-Ergänzung:** Nach Services-Block (Zeile ~88):
```php
<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
	<div class="cts-event-calendar" style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
		📅 <?php echo esc_html( $event['calendar_name'] ); ?>
	</div>
<?php endif; ?>
```

---

### list/medium.php
**Fehlend:** show_calendar_name

**Änderung:** Zeile ~24 hinzufügen:
```php
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
```

**Template-Ergänzung:** In Event-Card (nach Description):
```php
<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
	<div class="cts-event-calendar">
		<?php echo esc_html( $event['calendar_name'] ); ?>
	</div>
<?php endif; ?>
```

---

### widget/upcoming.php
**Fehlend:** show_description, show_services, show_calendar_name

**Änderung:** Zeile 19-20 erweitern:
```php
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : false;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : false;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : false;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : false;
```

**Template-Ergänzung:** In Widget-Item:
```php
<?php if ( $show_description && ! empty( $event['description'] ) ) : ?>
	<div class="cts-widget-description">
		<?php echo esc_html( wp_trim_words( $event['description'], 20 ) ); ?>
	</div>
<?php endif; ?>

<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
	<div class="cts-widget-services">
		<?php echo esc_html( implode( ', ', array_column( $event['services'], 'service_name' ) ) ); ?>
	</div>
<?php endif; ?>

<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
	<div class="cts-widget-calendar">
		<?php echo esc_html( $event['calendar_name'] ); ?>
	</div>
<?php endif; ?>
```

---

### search/classic.php
**Fehlend:** show_time, show_services, show_calendar_name

**Änderung:** Zeile 19-20 erweitern:
```php
$show_location = isset( $args['show_location'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_description = isset( $args['show_description'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_description'] ) : true;
$show_time = isset( $args['show_time'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_services = isset( $args['show_services'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;
```

**Template-Ergänzung:** In Result-Item:
```php
<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
	<div class="cts-search-services">
		<?php foreach ( $event['services'] as $service ) : ?>
			<span><?php echo esc_html( $service['service_name'] ); ?>: <?php echo esc_html( $service['person_name'] ); ?></span>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : ?>
	<div class="cts-search-calendar">
		📅 <?php echo esc_html( $event['calendar_name'] ); ?>
	</div>
<?php endif; ?>
```

---

## Phase 2: Tags-Unterstützung überall hinzufügen

**Standard Tags-Block** (für alle Templates):
```php
<?php if ( ! empty( $event['tags'] ) ) : ?>
	<?php
	$tags = is_string( $event['tags'] ) ? json_decode( $event['tags'], true ) : $event['tags'];
	if ( is_array( $tags ) && ! empty( $tags ) ) :
	?>
	<div class="cts-event-tags" style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
		<?php foreach ( $tags as $tag ) : ?>
			<span class="cts-tag" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: <?php echo esc_attr( $tag['color'] ?? '#e5e7eb' ); ?>; color: #fff;">
				<?php echo esc_html( $tag['name'] ?? '' ); ?>
			</span>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
<?php endif; ?>
```

**Position in Templates:**
- **List-Templates:** Nach Services/Description
- **Grid-Templates:** Nach Description
- **Calendar:** In Modal/Details
- **Widget:** Optional (platzsparend)
- **Search:** Nach Description

---

## CSS-Ergänzung (assets/css/churchtools-suite-public.css)

```css
/* Tags Styling (v0.10.4.0) */
.cts-event-tags {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
	margin-top: 0.75rem;
}

.cts-tag {
	display: inline-block;
	padding: 0.25rem 0.75rem;
	border-radius: 9999px;
	font-size: 0.75rem;
	font-weight: 500;
	color: #fff;
	background-color: #6b7280;
}

/* Compact List Ergänzungen */
.cts-list-compact .cts-list-item-description {
	grid-column: 2 / -1;
	font-size: 0.875rem;
	color: #6b7280;
	margin-top: 0.25rem;
}

.cts-list-compact .cts-list-item-services,
.cts-list-compact .cts-list-item-calendar {
	grid-column: 2 / -1;
	font-size: 0.75rem;
	color: #9ca3af;
	margin-top: 0.25rem;
}

/* Widget Ergänzungen */
.cts-widget-description,
.cts-widget-services,
.cts-widget-calendar {
	font-size: 0.75rem;
	color: #6b7280;
	margin-top: 0.25rem;
}

/* Search Ergänzungen */
.cts-search-services,
.cts-search-calendar {
	font-size: 0.875rem;
	color: #6b7280;
	margin-top: 0.5rem;
}
```

---

## Deployment-Plan

1. ✅ Shortcode Manager deaktiviert
2. ⏳ Templates updaten (5 Dateien)
3. ⏳ CSS ergänzen
4. ⏳ Testen: Alle Views mit allen Toggles
5. ⏳ Git Commit + Tag v0.10.4.0
6. ⏳ ZIP + Release

---

## Risiken & Mitigation

**Risiko:** Breaking Changes für existierende Shortcodes
**Mitigation:** Alle Toggles haben Default-Werte (backward compatible)

**Risiko:** Tags-Format aus DB inkorrekt
**Mitigation:** JSON-Decode mit Fallback auf Array

**Risiko:** Template-Layouts brechen
**Mitigation:** CSS ist additiv, keine Überschreibungen

---

**Review-Status:** READY FOR IMPLEMENTATION
**Estimated Time:** 30-45 Minuten
**Breaking Changes:** KEINE (nur Additions)
