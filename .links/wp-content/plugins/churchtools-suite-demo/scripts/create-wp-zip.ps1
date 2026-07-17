param(
    [Parameter(Mandatory = $true)]
    [string]$Version
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$RepoRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path
$ArchiveDir = "C:\privat\archiv"
$TempDir = Join-Path $env:TEMP "churchtools-suite-demo-$Version"
$PluginDir = Join-Path $TempDir "churchtools-suite-demo"
$OutputZip = Join-Path "C:\privat" "churchtools-suite-demo-$Version.zip"

Write-Host "=== ChurchTools Suite Demo ZIP Creator ===" -ForegroundColor Cyan
Write-Host "Version: $Version"
Write-Host ""

# Archive ALL old ZIPs
$oldZips = Get-ChildItem -Path "C:\privat" -Filter "churchtools-suite-demo-*.zip" -ErrorAction SilentlyContinue
if ($oldZips) {
    if (-not (Test-Path $ArchiveDir)) {
        New-Item -ItemType Directory -Path $ArchiveDir -Force | Out-Null
    }
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    foreach ($zip in $oldZips) {
        $archiveFile = Join-Path $ArchiveDir ($zip.Name -replace '\.zip$', "-$timestamp.zip")
        Move-Item -Path $zip.FullName -Destination $archiveFile -Force
        Write-Host "Archived: $($zip.Name) -> archiv\$($archiveFile | Split-Path -Leaf)" -ForegroundColor Yellow
    }
}

# Cleanup temp
if (Test-Path $TempDir) { Remove-Item -Recurse -Force $TempDir }

# Create temp dir structure
New-Item -ItemType Directory -Path $PluginDir -Force | Out-Null

# Copy files - Exclude development files
$ExcludeItems = @(
    '.git',
    '.github',
    '.gitignore',
    '.editorconfig',
    '.gitattributes',
    'scripts',
    'tests',
    'node_modules',
    '*.zip',
    '*.log',
    '.vscode',
    '.idea',
    'phpunit.xml',
    'phpcs.xml',
    '.phpcs.xml.dist',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    '*.backup-*',
    'content207.txt',
    # Exclude ALL test PHP files in root
    'add-churchtools-access.php',
    'add-cpt-caps-to-role.php',
    'check-current-status.php',
    'check-hooks.php',
    'check-menu-visibility.php',
    'check-method.php',
    'check-post-3.php',
    'check-status.php',
    'check-user.php',
    'create-demo-user-role.php',
    'create-new-demo-role.php',
    'find-redirect-cause.php',
    'fix-demo-users-role.php',
    'remove-standard-caps.php',
    'reset-to-editor-role.php',
    'show-logs.php',
    'test-*.php',
    'update-demo-role.php',
    'verify-*.php',
    'watch-debug.php',
    'refresh-*.php'
)

Write-Host "Copying files..."
Get-ChildItem -Path $RepoRoot -Force | Where-Object {
    $item = $_
    $exclude = $false
    foreach ($pattern in $ExcludeItems) {
        if ($item.Name -like $pattern) {
            $exclude = $true
            break
        }
    }
    -not $exclude
} | ForEach-Object {
    $dest = Join-Path $PluginDir $_.Name
    if ($_.PSIsContainer) {
        Copy-Item -Path $_.FullName -Destination $dest -Recurse -Force
    } else {
        Copy-Item -Path $_.FullName -Destination $dest -Force
    }
}

Write-Host "Files copied: " -NoNewline
$fileCount = (Get-ChildItem -Path $PluginDir -Recurse -File).Count
Write-Host $fileCount -ForegroundColor Green

# Normalize paths to forward slashes (required for WordPress)
Write-Host "Normalizing path separators..."
Get-ChildItem -Path $PluginDir -Recurse -File | ForEach-Object {
    $content = Get-Content $_.FullName -Raw -ErrorAction SilentlyContinue
    if ($content) {
        $hasBackslash = $content -match '\\'
        if ($hasBackslash) {
            # Only normalize plugin-specific paths
            $newContent = $content -replace '([/\\])includes([/\\])', '/includes/'
            $newContent = $newContent -replace '([/\\])admin([/\\])', '/admin/'
            $newContent = $newContent -replace '([/\\])templates([/\\])', '/templates/'
            $newContent = $newContent -replace '([/\\])assets([/\\])', '/assets/'
            
            if ($newContent -ne $content) {
                Set-Content -Path $_.FullName -Value $newContent -NoNewline
            }
        }
    }
}

# Create ZIP with proper internal structure (WordPress requires forward slashes)
Write-Host "Creating ZIP archive with WordPress-compatible paths..."
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }

Add-Type -Assembly System.IO.Compression.FileSystem
$compressionLevel = [System.IO.Compression.CompressionLevel]::Optimal
$zipArchive = [System.IO.Compression.ZipFile]::Open($OutputZip, 'Create')

try {
    Get-ChildItem -Path $PluginDir -Recurse -File | ForEach-Object {
        $relativePath = $_.FullName.Substring($PluginDir.Length + 1)
        # WordPress requires forward slashes
        $entryName = "churchtools-suite-demo/" + ($relativePath -replace '\\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zipArchive, $_.FullName, $entryName, $compressionLevel) | Out-Null
    }
} finally {
    $zipArchive.Dispose()
}

# Validate ZIP structure
Write-Host "Validating ZIP structure..."
$zip = [System.IO.Compression.ZipFile]::OpenRead($OutputZip)
$entries = $zip.Entries
Write-Host "  Total entries: $($entries.Count)"

# Check for backslashes
$hasBackslashes = $entries | Where-Object { $_.FullName -like '*\*' }
if ($hasBackslashes) {
    Write-Host "  ERROR: Found backslashes in ZIP paths!" -ForegroundColor Red
    $hasBackslashes | Select-Object -First 3 | ForEach-Object { Write-Host "    $($_.FullName)" -ForegroundColor Red }
    $zip.Dispose()
    Remove-Item $OutputZip -Force
    Remove-Item -Recurse -Force $TempDir
    throw "ZIP validation failed: Contains backslashes"
} else {
    Write-Host "  SUCCESS: All paths use forward slashes (WordPress compatible)" -ForegroundColor Green
}
$zip.Dispose()

# Cleanup
Remove-Item -Recurse -Force $TempDir

Write-Host ""
Write-Host "=== SUCCESS ===" -ForegroundColor Green
Write-Host "ZIP created: $OutputZip"
$zipSize = (Get-Item $OutputZip).Length
Write-Host "Size: $([math]::Round($zipSize / 1MB, 2)) MB"
Write-Host ""
Write-Host "Next steps:"
Write-Host "1. Test the ZIP by installing it in WordPress"
Write-Host "2. Create Git tag: git tag v$Version"
Write-Host "3. Push tag: git push origin v$Version"
Write-Host "4. Create GitHub Release with this ZIP"
