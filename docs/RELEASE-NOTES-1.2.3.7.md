# ChurchTools Integration Suite 1.2.3.7

## Enthaltene Plugins

- `churchtools-suite-1.2.3.7.zip`
- `churchtools-suite-posts-sync-0.1.10.zip`
- `churchtools-suite-elementor-0.6.29.zip`

## Änderungen

- ChurchTools-Berichte als öffentlich erreichbarer `ct_post` mit eigener Einzelansicht.
- Eigenes `single-ct_post.php`-Template mit Beitragsbild und Galerie-Unterstützung.
- Markdown-Konvertierung für Überschriften, Listen, Zitate, Tabellen und horizontale Linien.
- Erstes Berichtbild wird als Beitragsbild importiert; weitere Bilder werden als WordPress-Galerie angelegt.
- Duplikaterkennung für importierte Medien verbessert.
- Alte synchronisierte Berichte werden beim Addon-Update in den `ct_post`-CPT migriert.
- Bearbeiten- und Löschen-Aktionen in den Berichte- und Terminübersichten.
- Stabilere Token-Authentifizierung und Update-/Rewrite-Behandlung.

## Update-Reihenfolge

1. Core `churchtools-suite` aktualisieren.
2. Posts-Sync-Addon aktualisieren.
3. Elementor-Addon aktualisieren.

Die ZIP-Dateien enthalten jeweils genau einen Plugin-Ordner im Root und können direkt über WordPress installiert werden.
