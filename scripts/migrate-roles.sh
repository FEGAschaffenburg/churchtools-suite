#!/usr/bin/env bash
# Migration Script für Option B: Rollen-System aktivieren
# Führt auf der Live-Website aus, um die neuen Rollen zu registrieren

set -e

echo "🚀 ChurchTools Suite - Rollen-System Aktivierung"
echo "================================================="
echo ""

# Pfade
PLUGIN_DIR="/var/www/clients/client436/web2975/web/wp-content/plugins/churchtools-suite"

if [ ! -f "$PLUGIN_DIR/includes/class-churchtools-suite-roles.php" ]; then
    echo "❌ Fehler: class-churchtools-suite-roles.php nicht gefunden!"
    exit 1
fi

echo "✅ Klasse gefunden"
echo ""
echo "Führe PHP-Migration aus..."
echo ""

php << 'PHP_CODE'
<?php
// Load WordPress
require_once '/var/www/clients/client436/web2975/web/wp-load.php';

// Load Roles Class
require_once '/var/www/clients/client436/web2975/web/wp-content/plugins/churchtools-suite/includes/class-churchtools-suite-roles.php';

// Register roles
ChurchTools_Suite_Roles::register_role();

echo "✅ Rollen registriert:\n";
echo "   - cts_manager\n";
echo "   - Capabilities hinzugefügt\n";
echo "\n";

// List capabilities
$role = get_role('cts_manager');
if ($role) {
    echo "✅ Capabilities für cts_manager:\n";
    foreach (ChurchTools_Suite_Roles::CAPABILITIES as $cap) {
        echo "   ✓ $cap\n";
    }
} else {
    echo "❌ Fehler: Rolle nicht gefunden!\n";
    exit(1);
}

echo "\n✅ Migration abgeschlossen!\n";
?>
PHP_CODE

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Erfolgreich abgeschlossen!"
    echo ""
    echo "Nächste Schritte:"
    echo "1. Neue Benutzer mit Rolle 'cts_manager' erstellen"
    echo "2. AJAX-Checks in Admin-Klasse aktualisieren"
    echo "3. Settings-Seite für Nutzer-Management anpassen"
else
    echo ""
    echo "❌ Migration fehlgeschlagen"
    exit 1
fi
