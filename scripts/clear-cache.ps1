# ChurchTools Suite - Clear Cache Script
# Löscht WordPress Cache über WP-CLI (lokal oder remote)

param(
    [switch]$Remote,
    [switch]$All
)

Write-Host "ChurchTools Suite - Cache leeren" -ForegroundColor Cyan
Write-Host "=================================" -ForegroundColor Cyan
Write-Host ""

if ($Remote) {
    # Remote Server (FEG Live-Server)
    Write-Host "Verbinde mit Live-Server..." -ForegroundColor Yellow
    
    $keyPath = "C:\Users\knaumann\.ssh\feg_plugin"
    $sshHost = "aschaffesshadmin@web73.feg.de"
    $wpPath = "/var/www/clients/client436/web2975/web"
    
    # Transients löschen
    Write-Host "Lösche Transients..." -ForegroundColor White
    ssh -i $keyPath -p 22073 $sshHost "wp transient delete --all --path=$wpPath"
    
    # Object Cache löschen
    Write-Host "Lösche Object Cache..." -ForegroundColor White
    ssh -i $keyPath -p 22073 $sshHost "wp cache flush --path=$wpPath"
    
    # Plugin Cache löschen (falls vorhanden)
    if ($All) {
        Write-Host "Lösche Plugin-spezifische Optionen..." -ForegroundColor White
        ssh -i $keyPath -p 22073 $sshHost "wp option delete churchtools_suite_events_cache --path=$wpPath"
    }
    
    Write-Host ""
    Write-Host "✅ Remote Cache gelöscht!" -ForegroundColor Green
    
} else {
    # Lokaler WordPress Server
    Write-Host "Lokaler Cache wird gelöscht..." -ForegroundColor Yellow
    
    # Suche wp-cli.phar in Common Locations
    $wpCliPaths = @(
        "C:\xampp\htdocs\wp-cli.phar",
        "C:\laragon\www\wp-cli.phar",
        "wp" # System PATH
    )
    
    $wpCli = $null
    foreach ($path in $wpCliPaths) {
        if (Test-Path $path) {
            $wpCli = $path
            break
        }
    }
    
    if (-not $wpCli) {
        Write-Host "❌ WP-CLI nicht gefunden!" -ForegroundColor Red
        Write-Host "Installiere WP-CLI: https://wp-cli.org/#installing" -ForegroundColor Yellow
        exit 1
    }
    
    # Finde WordPress Root
    $wpRoot = Get-Location
    if (-not (Test-Path "$wpRoot\wp-config.php")) {
        Write-Host "❌ wp-config.php nicht gefunden!" -ForegroundColor Red
        Write-Host "Führe das Script im WordPress Root-Verzeichnis aus." -ForegroundColor Yellow
        exit 1
    }
    
    # Transients löschen
    Write-Host "Lösche Transients..." -ForegroundColor White
    & $wpCli transient delete --all --path=$wpRoot
    
    # Object Cache löschen
    Write-Host "Lösche Object Cache..." -ForegroundColor White
    & $wpCli cache flush --path=$wpRoot
    
    # Plugin Cache löschen
    if ($All) {
        Write-Host "Lösche Plugin-spezifische Optionen..." -ForegroundColor White
        & $wpCli option delete churchtools_suite_events_cache --path=$wpRoot
    }
    
    Write-Host ""
    Write-Host "✅ Lokaler Cache gelöscht!" -ForegroundColor Green
}

Write-Host ""
Write-Host "💡 Tipp: Drücke im Browser Strg+Shift+R für Hard Refresh" -ForegroundColor Cyan
Write-Host ""
