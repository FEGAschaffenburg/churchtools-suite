param(
    [Parameter(Mandatory = $true)]
    [string]$Version,
    [Parameter(Mandatory = $false)]
    [ValidateSet('main', 'elementor', 'posts-sync')]
    [string]$Plugin = 'main'
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$RepoRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path
$ArchiveDir = "C:\privat\archiv"

switch ($Plugin) {
    'main' {
        $PluginSlug = 'churchtools-suite'
        $SourceRoot = $RepoRoot
        $ExcludeItems = @(
            '.git', '.github', '.gitignore', '.editorconfig', '.gitattributes',
            'scripts', 'tests', 'node_modules', 'addons',
            '*.zip', '*.log', '.vscode', '.idea',
            'phpunit.xml', 'phpcs.xml', '.phpcs.xml.dist',
            'composer.json', 'composer.lock', 'package.json', 'package-lock.json',
            'clear-cache.php', 'clear-opcache.php',
            'RELEASE-NOTES*.md', 'release-notes*.md'
        )
    }
    'elementor' {
        $PluginSlug = 'churchtools-suite-elementor'
        $SourceRoot = Join-Path $RepoRoot 'addons\churchtools-suite-elementor'
        $ExcludeItems = @(
            '.git', '.github', '.gitignore', '.editorconfig', '.gitattributes',
            'scripts', 'tests', 'node_modules', '*.zip', '*.log', '.vscode', '.idea',
            '*.backup-*', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json'
        )
    }
    'posts-sync' {
        $PluginSlug = 'churchtools-suite-posts-sync'
        $SourceRoot = Join-Path $RepoRoot 'addons\churchtools-suite-posts-sync'
        $ExcludeItems = @(
            '.git', '.github', '.gitignore', '.editorconfig', '.gitattributes',
            'scripts', 'tests', 'node_modules', '*.zip', '*.log', '.vscode', '.idea',
            'composer.json', 'composer.lock', 'package.json', 'package-lock.json'
        )
    }
}

if ( -not (Test-Path $SourceRoot) ) {
    throw "Source path not found: $SourceRoot"
}

$TempDir = Join-Path $env:TEMP "$PluginSlug-wp-$Version"
$PluginDir = Join-Path $TempDir $PluginSlug
$OutputZip = Join-Path "C:\privat" "$PluginSlug-$Version.zip"

Write-Host "=== ChurchTools Suite Monorepo ZIP Creator ===" -ForegroundColor Cyan
Write-Host "Plugin: $PluginSlug"
Write-Host "Version: $Version"
Write-Host ""

# Archive old ZIPs for selected plugin
$oldZips = Get-ChildItem -Path "C:\privat" -Filter "$PluginSlug-*.zip" -ErrorAction SilentlyContinue
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

Write-Host "Copying files..."
Get-ChildItem -Path $SourceRoot -Force | Where-Object {
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
    Write-Host "  Copied: $($_.Name)" -ForegroundColor Gray
}

# Create ZIP with proper forward slashes for WordPress compatibility
Write-Host ""
Write-Host "Creating ZIP with WordPress-compatible paths..."

# Create initial ZIP
if (Test-Path $OutputZip) { Remove-Item $OutputZip -Force }
Compress-Archive -Path $PluginDir -DestinationPath $OutputZip -CompressionLevel Optimal -Force

# Fix ZIP entries to use forward slashes (WordPress requirement)
Write-Host "Normalizing paths to forward slashes..."
$tempFixedZip = Join-Path $env:TEMP "churchtools-suite-fixed.zip"
if (Test-Path $tempFixedZip) { Remove-Item $tempFixedZip -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$sourceZip = [System.IO.Compression.ZipFile]::OpenRead($OutputZip)
$targetZip = [System.IO.Compression.ZipFile]::Open($tempFixedZip, [System.IO.Compression.ZipArchiveMode]::Create)

foreach ($entry in $sourceZip.Entries) {
    # Convert backslashes to forward slashes
    $normalizedPath = $entry.FullName -replace '\\', '/'
    
    if ([string]::IsNullOrEmpty($normalizedPath)) { continue }
    
    if ($normalizedPath.EndsWith('/')) {
        # Directory entry
        $targetZip.CreateEntry($normalizedPath) | Out-Null
    } else {
        # File entry
        $sourceStream = $entry.Open()
        $targetEntry = $targetZip.CreateEntry($normalizedPath, [System.IO.Compression.CompressionLevel]::Optimal)
        $targetStream = $targetEntry.Open()
        $sourceStream.CopyTo($targetStream)
        $targetStream.Close()
        $sourceStream.Close()
    }
}

$sourceZip.Dispose()
$targetZip.Dispose()

# Replace original ZIP
Move-Item -Path $tempFixedZip -Destination $OutputZip -Force

# Validate
Write-Host "Validating..."
Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::OpenRead($OutputZip)

Write-Host ""
Write-Host "First 5 entries:"
$zip.Entries | Select-Object -First 5 | ForEach-Object {
    Write-Host "  $($_.FullName)"
}

# Look for plugin main file with forward slashes (WordPress standard)
$expectedMainFile = "$PluginSlug/$PluginSlug.php"
$mainFile = $zip.Entries | Where-Object { $_.FullName -eq $expectedMainFile }
$mainFileAlt = $zip.Entries | Where-Object { $_.Name -eq "$PluginSlug.php" }

Write-Host ""
Write-Host "Validating WordPress structure..."
Write-Host "Found with path '$expectedMainFile': $($null -ne $mainFile)"
Write-Host "Found with name '$PluginSlug.php': $($null -ne $mainFileAlt)"

if ($mainFile) {
    Write-Host "SUCCESS: WordPress structure OK (forward slashes)" -ForegroundColor Green
} else {
    Write-Host "ERROR: $expectedMainFile not found!" -ForegroundColor Red
}

$totalEntries = $zip.Entries.Count
$zip.Dispose()

# Cleanup
Remove-Item -Recurse -Force $TempDir

# Result
$zipSizeMB = [math]::Round((Get-Item $OutputZip).Length / 1MB, 2)
Write-Host ""
Write-Host "DONE!" -ForegroundColor Green
Write-Host "File: $OutputZip"
Write-Host "Size: $zipSizeMB MB"
Write-Host "Entries: $totalEntries"
