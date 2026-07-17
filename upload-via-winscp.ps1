# WinSCP Upload Script
# Download WinSCP: https://winscp.net/download/WinSCP-6.1.2-Setup.exe
# oder: choco install winscp

param(
    [switch]$SkipCheck
)

# Prüfe ob WinSCP installiert ist
$winscp = Get-Command winscp -ErrorAction SilentlyContinue
if (-not $winscp -and -not $SkipCheck) {
    Write-Host "WinSCP nicht gefunden. Installiere mit:" -ForegroundColor Yellow
    Write-Host "  choco install winscp" -ForegroundColor Cyan
    Write-Host "Oder manuell von: https://winscp.net/download/" -ForegroundColor Cyan
    exit 1
}

$localBase = "C:\Users\KasseFeg\wp-local\plugin.feg-aschaffenburg.de\.links\wp-content\plugins\churchtools-suite"
$remoteBase = "/home/ascaffessh_plugin/web/wp-content/plugins/churchtools-suite"

# Dateien zum hochladen
$files = @(
    @{
        local = "$localBase\churchtools-suite.php"
        remote = "$remoteBase/churchtools-suite.php"
        name = "Main Plugin (v1.2.2.0)"
    },
    @{
        local = "$localBase\addons\churchtools-suite-elementor\churchtools-suite-elementor.php"
        remote = "$remoteBase/addons/churchtools-suite-elementor/churchtools-suite-elementor.php"
        name = "Elementor Addon (v0.6.29)"
    },
    @{
        local = "$localBase\addons\churchtools-suite-posts-sync\churchtools-suite-posts-sync.php"
        remote = "$remoteBase/addons/churchtools-suite-posts-sync/churchtools-suite-posts-sync.php"
        name = "Posts Sync Addon (v0.1.8)"
    }
)

Write-Host "=" * 60 -ForegroundColor Cyan
Write-Host "ChurchTools Suite - Remote Upload via SFTP" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan
Write-Host ""

# Überprüfe lokale Dateien
$allExist = $true
foreach ($file in $files) {
    if (Test-Path $file.local) {
        $size = (Get-Item $file.local).Length
        Write-Host "✓ $($file.name)" -ForegroundColor Green
        Write-Host "  Lokal: $($file.local)" -ForegroundColor Gray
        Write-Host "  Größe: $('{0:N0}' -f $size) bytes" -ForegroundColor Gray
    } else {
        Write-Host "✗ $($file.name) - NICHT GEFUNDEN!" -ForegroundColor Red
        Write-Host "  Erwartet: $($file.local)" -ForegroundColor Red
        $allExist = $false
    }
}

if (-not $allExist) {
    Write-Host ""
    Write-Host "Fehler: Nicht alle Dateien gefunden!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Starte Upload..." -ForegroundColor Yellow
Write-Host ""

# Winscp Kommands vorbereiten
$session = New-WinSCPSessionOption -HostName "web73.feg.de" -Port 22073 -UserName "ascaffessh_plugin" -SshPrivateKeyPath "C:\Users\KasseFeg\.ssh\id_rsa" -SshHostKeyFingerprint "ssh-rsa 2048 (wird automatisch akzeptiert)"

try {
    $session = New-WinSCPSessionOption -HostName "web73.feg.de" -Port 22073 -UserName "ascaffessh_plugin" -KeyString (Get-Content "C:\Users\KasseFeg\.ssh\id_rsa" -Raw) 
} catch {
    Write-Host "WinSCP-Module nicht verfügbar. Nutze OpenSSH stattdessen..." -ForegroundColor Yellow
}

Write-Host "Hinweis: Nutze FileZilla für manuellen Upload:" -ForegroundColor Cyan
Write-Host "  Host: web73.feg.de" -ForegroundColor Cyan
Write-Host "  Port: 22073" -ForegroundColor Cyan
Write-Host "  User: ascaffessh_plugin" -ForegroundColor Cyan
Write-Host "  Protokoll: SFTP" -ForegroundColor Cyan
Write-Host "  Auth: SSH Key (C:\Users\KasseFeg\.ssh\id_rsa)" -ForegroundColor Cyan
