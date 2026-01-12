# Migration Script für Option B: Rollen-System aktivieren
# ChurchTools Suite - Roles & Capabilities Setup

Write-Host "🚀 ChurchTools Suite - Rollen-System Aktivierung" -ForegroundColor Cyan
Write-Host "=================================================" -ForegroundColor Cyan
Write-Host ""

# Konfig
$sshAlias = "feg-plugin"
$pluginPath = "/var/www/clients/client436/web2975/web"

Write-Host "🔧 Starte Rollen-Migration auf Live-Server..."
Write-Host ""

# Führe PHP-Skript via SSH aus
$result = ssh $sshAlias "cd $pluginPath && php" << 'EOF'
<?php
// Load WordPress
require_once 'wp-load.php';

// Load Roles Class
require_once 'wp-content/plugins/churchtools-suite/includes/class-churchtools-suite-roles.php';

// Register roles
ChurchTools_Suite_Roles::register_role();

echo "✅ Rollen registriert:\n";
echo "   - cts_manager (Custom Role)\n";
echo "   - Administrator erhielt neue Capabilities\n";
echo "\n";

// List capabilities
$role = get_role('cts_manager');
if ($role) {
    echo "✅ Capabilities für cts_manager:\n";
    foreach (ChurchTools_Suite_Roles::CAPABILITIES as $cap) {
        echo "   ✓ $cap\n";
    }
    echo "\n";
} else {
    echo "❌ Fehler: Rolle nicht gefunden!\n";
    exit(1);
}

// Verify migration
$managers = ChurchTools_Suite_Roles::get_cts_managers();
echo "✅ Aktuelle CTS Manager: " . count($managers) . "\n";

echo "\n✅ Migration abgeschlossen!\n";
?>
EOF

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "✅ Erfolgreich abgeschlossen!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Nächste Schritte:" -ForegroundColor Yellow
    Write-Host "1. Neue Benutzer mit Rolle 'cts_manager' erstellen" -ForegroundColor White
    Write-Host "2. AJAX-Checks in Admin-Klasse aktualisieren (manage_options → manage_churchtools_suite)" -ForegroundColor White
    Write-Host "3. Settings-Seite für Nutzer-Management anpassen" -ForegroundColor White
    Write-Host "4. Demo-Benutzer mit der neuen Rolle erstellen" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "❌ Migration fehlgeschlagen" -ForegroundColor Red
    exit 1
}
