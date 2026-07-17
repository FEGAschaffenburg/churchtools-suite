param(
    [Parameter(Mandatory = $false)]
    [string]$SshHost = "plugin-test",
    
    [Parameter(Mandatory = $false)]
    [string]$RemotePath = "web/wp-content/plugins/churchtools-suite"
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$PluginRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path

Write-Host "=== ChurchTools Suite - SCP Recursive Deployment ===" -ForegroundColor Cyan
Write-Host "SSH Host: $SshHost (using local SSH config)"
Write-Host "Remote Path: $RemotePath"
Write-Host ""

# Clear remote directory first
Write-Host "Clearing remote directory..." -ForegroundColor Gray
& ssh $SshHost "cd $RemotePath && rm -rf * && echo 'Directory cleared'"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Failed to clear remote directory" -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Remote directory cleared" -ForegroundColor Green
Write-Host ""

# Upload each top-level folder/file separately
Write-Host "Uploading files..." -ForegroundColor Gray
Write-Host ""

$items = Get-ChildItem -Path $PluginRoot | Where-Object {
    $_.Name -notin @('.git', 'scripts', '.github', '.gitignore') -and
    $_.Name -notlike '*.zip' -and
    $_.Name -notlike '*.log' -and
    $_.Name -notlike '*.backup-*' -and
    $_.Name -notlike 'add-*.php' -and
    $_.Name -notlike 'check-*.php' -and
    $_.Name -notlike 'create-*.php' -and
    $_.Name -notlike 'find-*.php' -and
    $_.Name -notlike 'fix-*.php' -and
    $_.Name -notlike 'remove-*.php' -and
    $_.Name -notlike 'reset-*.php' -and
    $_.Name -notlike 'show-*.php' -and
    $_.Name -notlike 'test-*.php' -and
    $_.Name -notlike 'update-*.php' -and
    $_.Name -notlike 'verify-*.php' -and
    $_.Name -notlike 'watch-*.php' -and
    $_.Name -notlike 'refresh-*.php'
}

$totalItems = $items.Count
$uploadedItems = 0

foreach ($item in $items) {
    $itemPath = $item.FullName
    Write-Host "  [$($uploadedItems + 1)/$totalItems] Uploading: $($item.Name)" -NoNewline
    
    if ($item.PSIsContainer) {
        # It's a directory - use recursive SCP
        & scp -r $itemPath "${SshHost}:${RemotePath}/" 2>&1 | Out-Null
    } else {
        # It's a file
        & scp $itemPath "${SshHost}:${RemotePath}/" 2>&1 | Out-Null
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host " [OK]" -ForegroundColor Green
        $uploadedItems++
    } else {
        Write-Host " [FAILED]" -ForegroundColor Red
    }
}

Write-Host ""
if ($uploadedItems -eq $totalItems) {
    Write-Host "[SUCCESS] All $totalItems items uploaded successfully!" -ForegroundColor Green
} else {
    Write-Host "[WARNING] $uploadedItems/$totalItems items uploaded" -ForegroundColor Yellow
}

# Normalize permissions for web delivery (directories 755, files 644)
Write-Host ""
Write-Host "Normalizing remote permissions..." -ForegroundColor Gray
& ssh $SshHost "cd $RemotePath && find . -type d -exec chmod 755 {} + && find . -type f -exec chmod 644 {} + && echo 'Permissions normalized'"

if ($LASTEXITCODE -ne 0) {
    Write-Host "[WARNING] Could not normalize permissions automatically" -ForegroundColor Yellow
} else {
    Write-Host "[OK] Remote permissions normalized" -ForegroundColor Green
}

Write-Host ""
Write-Host "Plugin deployed to: https://plugin.feg-aschaffenburg.de/wp-admin/plugins.php"
