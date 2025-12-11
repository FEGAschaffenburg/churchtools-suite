# ChurchTools Suite - ZIP Creator with Archive Management
# Moves old ZIPs to archive folder before creating new one

param(
    [Parameter(Mandatory=$true)]
    [string]$Version
)

$ErrorActionPreference = "Stop"

Write-Host "=== ChurchTools Suite ZIP Creator ===" -ForegroundColor Cyan
Write-Host "Version: $Version"

# Paths
$sourceDir = "C:\privat\churchtools-suite"
$outputDir = "C:\privat"
$archiveDir = "$outputDir\archiv"
$zipName = "churchtools-suite-$Version.zip"
$zipPath = "$outputDir\$zipName"

# Create archive directory if it doesn't exist
if (-not (Test-Path $archiveDir)) {
    New-Item -ItemType Directory -Path $archiveDir | Out-Null
    Write-Host "Created archive directory: $archiveDir" -ForegroundColor Green
}

# Move existing ZIP to archive if it exists
if (Test-Path $zipPath) {
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $archiveName = "churchtools-suite-$Version-$timestamp.zip"
    Move-Item -Path $zipPath -Destination "$archiveDir\$archiveName" -Force
    Write-Host "Moved existing ZIP to: archiv\$archiveName" -ForegroundColor Yellow
}

# Move any other churchtools-suite ZIPs to archive
Get-ChildItem "$outputDir\churchtools-suite-*.zip" -ErrorAction SilentlyContinue | ForEach-Object {
    $timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $name = $_.BaseName
    $archiveName = "$name-$timestamp.zip"
    Move-Item -Path $_.FullName -Destination "$archiveDir\$archiveName" -Force
    Write-Host "Archived old ZIP: $($_.Name) -> archiv\$archiveName" -ForegroundColor Yellow
}

# Create new ZIP
Write-Host "`nCreating new ZIP..." -ForegroundColor Cyan
Compress-Archive -Path "$sourceDir\*" -DestinationPath $zipPath -Force

# Verify
if (Test-Path $zipPath) {
    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
    Write-Host "`nSUCCESS!" -ForegroundColor Green
    Write-Host "File: $zipPath"
    Write-Host "Size: $size MB"
} else {
    Write-Host "ERROR: ZIP creation failed!" -ForegroundColor Red
    exit 1
}
