param(
    [Parameter(Mandatory=$true)]
    [string]$Version
)

$ThemeName = "cts-demo-theme"
$TempDir = "C:\temp\$ThemeName"
$DestinationZip = "C:\privat\$ThemeName-$Version.zip"
$ArchiveDir = "C:\privat\archiv"

Write-Host "=== ChurchTools Suite Demo Theme ZIP Creator ===" -ForegroundColor Cyan
Write-Host "Version: $Version`n" -ForegroundColor Yellow

# Create archive directory if needed
if (-not (Test-Path $ArchiveDir)) {
    New-Item -ItemType Directory -Path $ArchiveDir | Out-Null
}

# Archive old ZIP if exists
if (Test-Path $DestinationZip) {
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $archiveName = "$ThemeName-$(Split-Path $DestinationZip -LeafBase)-$timestamp.zip"
    $archivePath = Join-Path $ArchiveDir $archiveName
    Move-Item -Path $DestinationZip -Destination $archivePath -Force
    Write-Host "Archived old ZIP to: $archivePath`n" -ForegroundColor DarkYellow
}

# Clean temp directory
if (Test-Path $TempDir) {
    Remove-Item -Path $TempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $TempDir | Out-Null

Write-Host "Copying theme files..." -ForegroundColor Cyan

# Copy theme files
$filesToCopy = @(
    @{ Path = "*.php"; Dest = "" },
    @{ Path = "*.css"; Dest = "" },
    @{ Path = "assets"; Dest = "assets" },
    @{ Path = "inc"; Dest = "inc" },
    @{ Path = "gutenberg-templates"; Dest = "gutenberg-templates" },
    @{ Path = "page-templates"; Dest = "page-templates" },
    @{ Path = "screenshot.png"; Dest = "" }
)

$fileCount = 0
foreach ($item in $filesToCopy) {
    $sourcePath = $item.Path
    $destPath = if ($item.Dest) { Join-Path $TempDir $item.Dest } else { $TempDir }
    
    if (Test-Path $sourcePath) {
        if ((Get-Item $sourcePath) -is [System.IO.DirectoryInfo]) {
            # Directory
            Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
            Write-Host "  Copied: $sourcePath" -ForegroundColor Green
            $fileCount++
        } else {
            # Single file or wildcard
            $files = Get-ChildItem -Path $sourcePath -File -ErrorAction SilentlyContinue
            foreach ($file in $files) {
                Copy-Item -Path $file.FullName -Destination $destPath -Force
                $fileCount++
            }
            if ($files) {
                Write-Host "  Copied: $sourcePath ($($files.Count) files)" -ForegroundColor Green
            }
        }
    } else {
        Write-Host "  Warning: $sourcePath not found" -ForegroundColor Yellow
    }
}

Write-Host "`nCreating ZIP with WordPress-compatible paths..." -ForegroundColor Cyan

# Create ZIP with proper WordPress structure
Add-Type -Assembly System.IO.Compression.FileSystem
$compressionLevel = [System.IO.Compression.CompressionLevel]::Optimal
$zipArchive = [System.IO.Compression.ZipFile]::Open($DestinationZip, 'Create')

try {
    Get-ChildItem -Path $TempDir -Recurse -File | ForEach-Object {
        $relativePath = $_.FullName.Substring($TempDir.Length + 1)
        # WordPress requires forward slashes
        $entryName = "$ThemeName/" + ($relativePath -replace '\\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zipArchive, $_.FullName, $entryName, $compressionLevel) | Out-Null
    }
} finally {
    $zipArchive.Dispose()
}

# Validate ZIP structure
Write-Host "Validating..." -ForegroundColor Cyan
$zip = [System.IO.Compression.ZipFile]::OpenRead($DestinationZip)
$entries = $zip.Entries
Write-Host "`nFirst 5 entries:"
$entries | Select-Object -First 5 | ForEach-Object { Write-Host "  $($_.FullName)" -ForegroundColor DarkGray }

# Check WordPress structure
$hasStyleCss = $entries | Where-Object { $_.FullName -eq "$ThemeName/style.css" }
Write-Host "`nValidating WordPress structure..."
Write-Host "Found with path '$ThemeName/style.css': $($null -ne $hasStyleCss)" -ForegroundColor $(if ($hasStyleCss) { "Green" } else { "Red" })

$hasBackslashes = $entries | Where-Object { $_.FullName -like '*\*' }
if ($hasBackslashes) {
    Write-Host "ERROR: Found backslashes in ZIP paths!" -ForegroundColor Red
    $hasBackslashes | Select-Object -First 3 | ForEach-Object { Write-Host "  $($_.FullName)" -ForegroundColor Red }
} else {
    Write-Host "SUCCESS: WordPress structure OK (forward slashes)" -ForegroundColor Green
}

$zip.Dispose()

# Cleanup
Remove-Item -Path $TempDir -Recurse -Force

# Summary
$zipInfo = Get-Item $DestinationZip
Write-Host "`nDONE!" -ForegroundColor Green
Write-Host "File: $DestinationZip" -ForegroundColor Cyan
Write-Host "Size: $([math]::Round($zipInfo.Length / 1MB, 2)) MB" -ForegroundColor Cyan
Write-Host "Entries: $($entries.Count)" -ForegroundColor Cyan
Write-Host ""
