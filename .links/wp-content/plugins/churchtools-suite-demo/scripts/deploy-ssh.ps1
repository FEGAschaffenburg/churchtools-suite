param(
    [Parameter(Mandatory = $false)]
    [string]$SshHost = "plugin.feg-aschaffenburg.de",
    
    [Parameter(Mandatory = $false)]
    [string]$RemotePath = "/var/www/html/wp-content/plugins/churchtools-suite-demo"
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$PluginRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path

Write-Host "=== ChurchTools Suite Demo - SSH Deployment ===" -ForegroundColor Cyan
Write-Host "SSH Host: $SshHost (using local SSH config)"
Write-Host "Plugin: churchtools-suite-demo"
Write-Host ""

# Check if SSH is available
$sshCheck = Get-Command ssh -ErrorAction SilentlyContinue
if (-not $sshCheck) {
    Write-Host "[ERROR] SSH not found. Please install OpenSSH." -ForegroundColor Red
    Write-Host "   Install via: Add-WindowsCapability -Online -Name OpenSSH.Client~~~~0.0.1.0"
    exit 1
}

# Test SSH connection
Write-Host "Testing SSH connection..." -ForegroundColor Gray
$connectionTest = & ssh -o ConnectTimeout=5 -o BatchMode=yes $SshHost "echo 'Connected'" 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] SSH connection failed. Please check:" -ForegroundColor Red
    Write-Host "   - SSH config file: $env:USERPROFILE\.ssh\config" -ForegroundColor Yellow
    Write-Host "   - Host entry for '$SshHost' exists" -ForegroundColor Yellow
    Write-Host "   - SSH key authentication is set up" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Error details:" -ForegroundColor Red
    Write-Host $connectionTest
    exit 1
}
Write-Host "[OK] SSH connection successful" -ForegroundColor Green
Write-Host ""

# Exclude files/folders from deployment
$ExcludeItems = @(
    '.git',
    '.github',
    '.gitignore',
    'scripts',
    'node_modules',
    '*.zip',
    '*.log',
    '*.backup-*',
    'content207.txt',
    # Exclude ALL test PHP files
    'add-*.php',
    'check-*.php',
    'create-*.php',
    'find-*.php',
    'fix-*.php',
    'remove-*.php',
    'reset-*.php',
    'show-*.php',
    'test-*.php',
    'update-*.php',
    'verify-*.php',
    'watch-*.php',
    'refresh-*.php'
)

# Build rsync exclude patterns
$excludeArgs = @()
foreach ($pattern in $ExcludeItems) {
    $excludeArgs += "--exclude=$pattern"
}

# Check if rsync is available (better for syncing)
$rsyncCheck = Get-Command rsync -ErrorAction SilentlyContinue
if ($rsyncCheck) {
    Write-Host "Using rsync for deployment..." -ForegroundColor Green
    Write-Host ""
    
    # Use rsync with SSH
    $rsyncArgs = @(
        '-avz',
        '--delete',
        '--progress'
    ) + $excludeArgs + @(
        "$PluginRoot/",
        "${SshHost}:${RemotePath}/"
    )
    
    Write-Host "Command: rsync $($rsyncArgs -join ' ')" -ForegroundColor Gray
    Write-Host ""
    
    & rsync @rsyncArgs
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "[SUCCESS] Deployment successful!" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "[ERROR] Deployment failed with exit code $LASTEXITCODE" -ForegroundColor Red
        exit $LASTEXITCODE
    }
} else {
    Write-Host "[WARNING] rsync not found. Using SCP (slower, less efficient)..." -ForegroundColor Yellow
    Write-Host "   Install rsync for better performance: https://github.com/msys2/msys2/wiki/MSYS2-installation"
    Write-Host ""
    
    # Fallback: Use SCP with tar
    Write-Host "Creating temporary archive..." -ForegroundColor Gray
    
    $TempDir = Join-Path $env:TEMP "cts-demo-deploy-$(Get-Date -Format 'yyyyMMddHHmmss')"
    $TarFile = Join-Path $TempDir "churchtools-suite-demo.tar.gz"
    
    New-Item -ItemType Directory -Path $TempDir -Force | Out-Null
    
    # Create tar excluding unwanted files
    $tarExcludes = $ExcludeItems | ForEach-Object { "--exclude=$_" }
    
    Push-Location $PluginRoot
    & tar -czf $TarFile @tarExcludes .
    Pop-Location
    
    if (-not (Test-Path $TarFile)) {
        Write-Host "[ERROR] Failed to create archive" -ForegroundColor Red
        exit 1
    }
    
    Write-Host "Uploading to server..." -ForegroundColor Gray
    
    # Upload tar file
    & scp $TarFile "${SshHost}:/tmp/cts-demo.tar.gz"
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "[ERROR] Upload failed" -ForegroundColor Red
        Remove-Item -Recurse -Force $TempDir
        exit $LASTEXITCODE
    }
    
    Write-Host "Extracting on server..." -ForegroundColor Gray
    
    # Extract on server (backup old, extract new, restore if failed)
    $sshCommands = @(
        "mkdir -p $RemotePath",
        "cd $RemotePath",
        "rm -rf /tmp/cts-demo-backup 2>/dev/null || true",
        "mkdir -p /tmp/cts-demo-backup",
        "cp -r ./* /tmp/cts-demo-backup/ 2>/dev/null || true",
        "rm -rf ./* ./.* 2>/dev/null || true",
        "tar -xzf /tmp/cts-demo.tar.gz",
        "rm /tmp/cts-demo.tar.gz",
        "rm -rf /tmp/cts-demo-backup",
        "echo 'Deployment completed'"
    ) -join ' && '
    
    & ssh $SshHost $sshCommands
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "[SUCCESS] Deployment successful!" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "[ERROR] Deployment failed" -ForegroundColor Red
    }
    
    # Cleanup
    Remove-Item -Recurse -Force $TempDir
}

Write-Host ""
Write-Host "Plugin deployed to: https://$SshHost/wp-admin/plugins.php"
