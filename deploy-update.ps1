#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Deploy ChurchTools Suite v0.10.4.37 via SSH
    
.DESCRIPTION
    Updates the plugin directly on the production server via SSH
    Skips WordPress admin UI and clears all caches
#>

param(
    [string]$Version = "0.10.4.37"
)

$ErrorActionPreference = "Stop"

# SSH Connection Details
$SSH_HOST = "s436.goserver.host"
$SSH_USER = "web2945"
$SSH_PATH = "/var/www/clients/client436/web2945/web/wp-content/plugins/churchtools-suite"
$LOCAL_ZIP = "C:\privat\churchtools-suite-$Version.zip"
$TEMP_DIR = "C:\Temp\cts-deploy-$Version"

Write-Host "`n=== ChurchTools Suite Deployment ===" -ForegroundColor Cyan
Write-Host "Version: $Version" -ForegroundColor Yellow
Write-Host "Target: $SSH_USER@$SSH_HOST" -ForegroundColor Yellow
Write-Host ""

# 1. Check local ZIP exists
if (-not (Test-Path $LOCAL_ZIP)) {
    Write-Host "❌ ZIP not found: $LOCAL_ZIP" -ForegroundColor Red
    Write-Host "   Run: .\create-wp-zip.ps1 -Version `"$Version`"" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ ZIP found: $LOCAL_ZIP" -ForegroundColor Green
$zipSize = [math]::Round((Get-Item $LOCAL_ZIP).Length / 1MB, 2)
Write-Host "   Size: $zipSize MB" -ForegroundColor Gray

# 2. Extract ZIP to temp directory
Write-Host "`n📦 Extracting ZIP..." -ForegroundColor Cyan
if (Test-Path $TEMP_DIR) {
    Remove-Item -Recurse -Force $TEMP_DIR
}
Expand-Archive -Path $LOCAL_ZIP -DestinationPath $TEMP_DIR -Force
Write-Host "✅ Extracted to: $TEMP_DIR" -ForegroundColor Green

# 3. Count files
$fileCount = (Get-ChildItem -Recurse -File $TEMP_DIR\churchtools-suite).Count
Write-Host "   Files: $fileCount" -ForegroundColor Gray

# 4. SSH Commands
Write-Host "`n🔧 Deploying via SSH..." -ForegroundColor Cyan

$sshCommands = @"
# Backup current version
cd /var/www/clients/client436/web2945/web/wp-content/plugins
if [ -d churchtools-suite ]; then
    echo '📦 Creating backup...'
    tar -czf churchtools-suite-backup-`$(date +%Y%m%d-%H%M%S).tar.gz churchtools-suite
    echo '✅ Backup created'
fi

# Remove old plugin
echo '🗑️ Removing old plugin...'
rm -rf churchtools-suite
echo '✅ Old plugin removed'

# Create plugin directory
echo '📁 Creating plugin directory...'
mkdir -p churchtools-suite
echo '✅ Directory created'

# Exit to allow file upload
echo '✅ Ready for upload'
"@

Write-Host "Executing SSH commands..." -ForegroundColor Gray
$sshCommands | ssh "$SSH_USER@$SSH_HOST" bash

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ SSH commands failed" -ForegroundColor Red
    exit 1
}

# 5. Upload files via SCP
Write-Host "`n📤 Uploading files via SCP..." -ForegroundColor Cyan
$uploadSource = "$TEMP_DIR\churchtools-suite\*"
$uploadTarget = "${SSH_USER}@${SSH_HOST}:${SSH_PATH}/"

scp -r $uploadSource $uploadTarget

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ SCP upload failed" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Files uploaded successfully" -ForegroundColor Green

# 6. Set permissions
Write-Host "`n🔐 Setting permissions..." -ForegroundColor Cyan

$permCommands = @"
cd $SSH_PATH
echo '🔧 Setting file permissions...'
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
echo '✅ Permissions set'

echo '📊 Deployment summary:'
echo '   Version: $Version'
echo '   Files: `$(find . -type f | wc -l)'
echo '   Size: `$(du -sh . | cut -f1)'
"@

$permCommands | ssh "$SSH_USER@$SSH_HOST" bash

# 7. Verify version
Write-Host "`n🔍 Verifying deployment..." -ForegroundColor Cyan

$verifyCmd = @"
cd $SSH_PATH
if [ -f churchtools-suite.php ]; then
    grep "Version:" churchtools-suite.php | head -n1
else
    echo '❌ Plugin file not found!'
    exit 1
fi
"@

$versionCheck = $verifyCmd | ssh "$SSH_USER@$SSH_HOST" bash

Write-Host "Server version: $versionCheck" -ForegroundColor Yellow

# 8. Clear WordPress cache
Write-Host "`n🧹 Clearing WordPress cache..." -ForegroundColor Cyan

$cacheCmd = @"
cd /var/www/clients/client436/web2945/web
php -r "
    define('WP_USE_THEMES', false);
    require 'wp-load.php';
    wp_cache_flush();
    echo 'WordPress cache cleared\n';
"
"@

$cacheCmd | ssh "$SSH_USER@$SSH_HOST" bash

# 9. Cleanup temp directory
Write-Host "`n🧹 Cleanup..." -ForegroundColor Cyan
Remove-Item -Recurse -Force $TEMP_DIR
Write-Host "✅ Temp directory removed" -ForegroundColor Green

# 10. Final instructions
Write-Host "`n=== DEPLOYMENT COMPLETE ===" -ForegroundColor Green
Write-Host ""
Write-Host "NÄCHSTE SCHRITTE:" -ForegroundColor Yellow
Write-Host "1. WordPress Admin → Plugins → ChurchTools Suite DEAKTIVIEREN" -ForegroundColor White
Write-Host "2. ChurchTools Suite AKTIVIEREN" -ForegroundColor White
Write-Host "3. Seite neu laden: https://test2-aschaffenburg.feg.de/guthenberg-test/" -ForegroundColor White
Write-Host "   (Ctrl+Shift+R für Hard Reload)" -ForegroundColor Gray
Write-Host ""
Write-Host "Debug Check:" -ForegroundColor Yellow
Write-Host "   Version sollte jetzt sein: $Version ✅" -ForegroundColor White
Write-Host "   show_event_description sollte 'true' zeigen (nicht 'NOT SET') ✅" -ForegroundColor White
Write-Host ""
