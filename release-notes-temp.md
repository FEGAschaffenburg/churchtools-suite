# v0.10.4.11 - Tag Filtering & Display

**Features:**
- Tag-Filterung mit AND-Logik (Event muss ALLE Tags haben)
- Tag-Anzeige als farbige Badges
- Parameter: filter_tags und show_tags

**Verwendung:**
```
[cts_events_list filter_tags="Gottesdienst,Alpha" show_tags="true"]
```

**AND-Logik:**
- filter_tags="Gottesdienst,Alpha" → Nur Events mit **BEIDEN** Tags
- Event mit nur "Gottesdienst" → nicht angezeigt
- Event mit "Gottesdienst" + "Alpha" → angezeigt

**Technische Änderungen:**
- Shortcodes: filter_tags + show_tags Parameter
- Template Data: filter_events_by_tags() mit AND-Logik  
- Templates: Tag-Badges Rendering
- CSS: .cts-tag-badge Styles

**Vorgängerversionen:**
- v0.10.4.9: Tags-Import (Root-Cause Fix)
- v0.10.4.10: Description-Felder getrennt

**Installation:**
1. Plugin-ZIP herunterladen
2. In WordPress hochladen
3. Tags werden automatisch synchronisiert
