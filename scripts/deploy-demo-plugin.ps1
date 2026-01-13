# Demo Plugin SSH Deployment v1.0.5.19 (PowerShell)
# Deployment für churchtools-suite-demo auf Production

param(
    [Parameter(Mandatory=$true, HelpMessage="SSH Benutzername (z.B. 'user')")]
    [string]$RemoteUser,
    
    [Parameter(Mandatory=$true, HelpMessage="SSH Host (z.B. 'domain.de')")]
    [string]$RemoteHost,
    
    [Parameter(HelpMessage="SSH Port (default: 22)")]
    [int]$RemotePort = 22,
    
    [Parameter(HelpMessage="Remote WordPress Pfad (default: /var/www/html)")]
    [string]$RemotePath = "/var/www/html"
)

$ErrorActionPreference = "Stop"

# Konfiguration
$PluginName = "churchtools-suite-demo"
$Version = "1.0.5.19"
$ZipName = "$PluginName-$Version.zip"
$LocalZipPath = "C:\privat\$ZipName"
$RemoteZipPath = "/tmp/$ZipName"
$WpPluginsPath = "$RemotePath/wp-content/plugins"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "ChurchTools Suite Demo - SSH Deployment" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Überprüfungen
if (-not (Test-Path $LocalZipPath)) {
    Write-Host "❌ Fehler: ZIP nicht gefunden: $LocalZipPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Bitte zunächst das ZIP erstellen:" -ForegroundColor Yellow
    Write-Host "  powershell -File scripts/create-demo-zip.ps1" -ForegroundColor Yellow
    exit 1
}

Write-Host "📦 Plugin:    $PluginName" -ForegroundColor Green
Write-Host "📌 Version:   $Version" -ForegroundColor Green
Write-Host "🌐 Remote:    $RemoteUser@$RemoteHost (Port $RemotePort)" -ForegroundColor Green
Write-Host "📂 Pfad:      $WpPluginsPath" -ForegroundColor Green
Write-Host ""

$confirmation = Read-Host "Möchtest du fortfahren? (j/n)"
if ($confirmation -ne 'j' -and $confirmation -ne 'J') {
    Write-Host "Deployment abgebrochen." -ForegroundColor Yellow
    exit 0
}

# SSH-Befehle
function Invoke-SSH {
    param(
        [Parameter(Mandatory=$true)]
        [string]$Command,
        
        [Parameter(HelpMessage="Fehler erlaubt?")]
        [switch]$AllowError
    )
    
    try {
        $output = ssh -p $RemotePort "$RemoteUser@$RemoteHost" $Command 2>&1
        return $output
    } catch {
        if ($AllowError) {
            return $null
        } else {
            throw $_
        }
    }
}

# SCHRITT 1: Verbindung testen
Write-Host ""
Write-Host "=== SCHRITT 1: Verbindung testen ===" -ForegroundColor Cyan
try {
    $test = Invoke-SSH "echo 'SSH OK'"
    Write-Host "✅ SSH-Verbindung OK" -ForegroundColor Green
} catch {
    Write-Host "❌ SSH-Verbindung fehlgeschlagen: $_" -ForegroundColor Red
    exit 1
}

# SCHRITT 2: ZIP hochladen
Write-Host ""
Write-Host "=== SCHRITT 2: ZIP hochladen ===" -ForegroundColor Cyan
Write-Host "Kopiere $ZipName zu $RemoteHost..."
try {
    $scpCmd = "scp -P $RemotePort '$LocalZipPath' '${RemoteUser}@${RemoteHost}:$RemoteZipPath'"
    Invoke-Expression $scpCmd | Out-Null
    Write-Host "✅ ZIP hochgeladen" -ForegroundColor Green
} catch {
    Write-Host "❌ Fehler beim Hochladen: $_" -ForegroundColor Red
    exit 1
}

# SCHRITT 3: Plugin deaktivieren
Write-Host ""
Write-Host "=== SCHRITT 3: Plugin deaktivieren ===" -ForegroundColor Cyan
Invoke-SSH "cd $RemotePath && wp plugin deactivate $PluginName 2>/dev/null || true" -AllowError | Out-Null
Write-Host "✅ Plugin deaktiviert (oder war nicht aktiv)" -ForegroundColor Green

# SCHRITT 4: Alte Version löschen
Write-Host ""
Write-Host "=== SCHRITT 4: Alte Version löschen ===" -ForegroundColor Cyan
Invoke-SSH "rm -rf $WpPluginsPath/$PluginName" | Out-Null
Write-Host "✅ Alte Version gelöscht" -ForegroundColor Green

# SCHRITT 5: Neue Version installieren
Write-Host ""
Write-Host "=== SCHRITT 5: Neue Version installieren ===" -ForegroundColor Cyan
try {
    Invoke-SSH "cd $RemotePath && wp plugin install $RemoteZipPath --activate" | Out-Null
    Write-Host "✅ Neue Version installiert und aktiviert" -ForegroundColor Green
} catch {
    Write-Host "❌ Fehler bei der Installation: $_" -ForegroundColor Red
    exit 1
}

# SCHRITT 6: Version überprüfen
Write-Host ""
Write-Host "=== SCHRITT 6: Version überprüfen ===" -ForegroundColor Cyan
$installedVersion = Invoke-SSH "cd $RemotePath && wp plugin get $PluginName --field=version"
$installedVersion = $installedVersion.Trim()

Write-Host "Installierte Version: $installedVersion" -ForegroundColor White

if ($installedVersion -eq $Version) {
    Write-Host "✅ Version OK: $installedVersion" -ForegroundColor Green
} else {
    Write-Host "⚠️  Warnung: Erwartete Version $Version, aber $installedVersion gefunden" -ForegroundColor Yellow
}

# SCHRITT 7: Cleanup
Write-Host ""
Write-Host "=== SCHRITT 7: Cleanup ===" -ForegroundColor Cyan
Invoke-SSH "rm $RemoteZipPath" | Out-Null
Write-Host "✅ Temp-Dateien gelöscht" -ForegroundColor Green

# Erfolg
Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "✅ Deployment erfolgreich!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Plugin:  $PluginName" -ForegroundColor Green
Write-Host "Version: $installedVersion" -ForegroundColor Green
Write-Host "Status:  Aktiv" -ForegroundColor Green
Write-Host ""
Write-Host "Nächste Schritte:" -ForegroundColor Yellow
Write-Host "1. Admin-Panel überprüfen: https://domain.de/wp-admin/" -ForegroundColor Yellow
Write-Host "2. Plugin-Einstellungen testen" -ForegroundColor Yellow
Write-Host "3. Keine JavaScript-Fehler in Console?" -ForegroundColor Yellow
Write-Host "4. Funktionalität testen" -ForegroundColor Yellow
Write-Host ""
