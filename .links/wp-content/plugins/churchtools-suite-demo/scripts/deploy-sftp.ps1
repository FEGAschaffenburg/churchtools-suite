param(
    [Parameter(Mandatory = $false)]
    [string]$SshHost = "plugin-test",
    
    [Parameter(Mandatory = $false)]
    [string]$RemotePath = "web/wp-content/plugins/churchtools-suite-demo"
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$PluginRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path

Write-Host "=== ChurchTools Suite Demo - SFTP Deployment ===" -ForegroundColor Cyan
Write-Host "SSH Host: $SshHost (using local SSH config)"
Write-Host "Plugin: churchtools-suite-demo"
Write-Host ""

# Get all files to upload (excluding test files, etc.)
$excludePatterns = @(
    '.git', 'scripts', '*.zip', '*.log', '*.backup-*', 
    'add-*.php', 'check-*.php', 'create-*.php', 'find-*.php', 
    'fix-*.php', 'remove-*.php', 'reset-*.php', 'show-*.php', 
    'test-*.php', 'update-*.php', 'verify-*.php', 'watch-*.php', 
    'refresh-*.php', 'content207.txt'
)

$files = Get-ChildItem -Path $PluginRoot -Recurse -File | Where-Object {
    $relativePath = $_.FullName.Substring($PluginRoot.Length + 1)
    $shouldExclude = $false
    foreach ($pattern in $excludePatterns) {
        if ($relativePath -like "*\$pattern") {
            $shouldExclude = $true
            break
        }
        if ($relativePath -like $pattern) {
            $shouldExclude = $true
            break
        }
        $dirName = Split-Path $relativePath -Parent
        if ($dirName -and ($dirName.Split('\')[0] -eq $pattern -or $dirName -like "*\$pattern")) {
            $shouldExclude = $true
            break
        }
    }
    -not $shouldExclude
}

# Create SFTP batch file
$sftpCommands = @()
$sftpCommands += "cd $RemotePath"

$directories = @()
foreach ($file in $files) {
    $relativePath = $_.FullName.Substring($PluginRoot.Length + 1).Replace('\', '/')
    $remoteDir = Split-Path $relativePath -Parent
    if ($remoteDir -and $remoteDir -ne '') {
        $currentDir = ''
        foreach ($dirPart in $remoteDir.Split('/')) {
            if ($currentDir) {
                $currentDir += '/' + $dirPart
            } else {
                $currentDir = $dirPart
            }
            if ($directories -notcontains $currentDir) {
                $directories += $currentDir
            }
        }
    }
}

# Create directories
foreach ($dir in $directories | Sort-Object) {
    $sftpCommands += "mkdir $dir 2>/dev/null || true"
}

# Upload files
foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($PluginRoot.Length + 1).Replace('\', '/')
    $localPath = $file.FullName.Replace('\', '/')
    $sftpCommands += "put `"$localPath`" `"$relativePath`""
}

$sftpCommands += "bye"

$batchFile = Join-Path $env:TEMP "sftp-deploy-$(Get-Date -Format 'yyyyMMddHHmmss').txt"
$sftpCommands -join "`n" | Out-File -FilePath $batchFile -Encoding ASCII

Write-Host "Uploading $($files.Count) files via SFTP..." -ForegroundColor Gray
Write-Host ""

# Use SFTP with batch file
& sftp -b $batchFile $SshHost 2>&1 | ForEach-Object {
    if ($_ -match "Uploading|100%") {
        Write-Host $_ -ForegroundColor Gray
    }
}

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "[SUCCESS] Deployment successful!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "[ERROR] Deployment failed with exit code $LASTEXITCODE" -ForegroundColor Red
}

Remove-Item $batchFile -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "Plugin deployed to: https://plugin.feg-aschaffenburg.de/wp-admin/plugins.php"
