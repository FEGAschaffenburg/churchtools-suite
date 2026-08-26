# Erstellt WordPress-ZIPs für ChurchTools Suite (Monorepo-Skript)
#
# Aufruf vom Projektroot:
#   .\scripts\build-wp-zips.ps1
#   .\scripts\build-wp-zips.ps1 -Release   # inkl. GitHub-Release (gh)

param(
    [switch]$Release,
    [string]$MainVersion,
    [string]$ElementorVersion,
    [string]$PostsSyncVersion,
    [string]$PresentationsVersion
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Resolve-Path (Join-Path $PSScriptRoot "..") | Select-Object -ExpandProperty Path
$SuiteRoot = $ProjectRoot
$ZipScript = Join-Path $SuiteRoot "scripts\create-wp-zip.ps1"

if (-not (Test-Path $ZipScript)) {
    throw "ZIP-Skript nicht gefunden: $ZipScript"
}

function Get-PluginVersion {
    param(
        [Parameter(Mandatory = $true)][string]$FilePath,
        [Parameter(Mandatory = $true)][string]$Pattern
    )
    if (-not (Test-Path $FilePath)) {
        throw "Datei nicht gefunden: $FilePath"
    }
    $content = Get-Content $FilePath -Raw -Encoding UTF8
    $match = [regex]::Match($content, $Pattern)
    if (-not $match.Success) {
        throw "Version nicht lesbar in $FilePath (Pattern: $Pattern)"
    }
    return $match.Groups[1].Value
}

if (-not $MainVersion) {
    $MainVersion = Get-PluginVersion `
        -FilePath (Join-Path $SuiteRoot "churchtools-suite.php") `
        -Pattern "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $ElementorVersion) {
    $ElementorVersion = Get-PluginVersion `
        -FilePath (Join-Path $SuiteRoot "addons\churchtools-suite-elementor\churchtools-suite-elementor.php") `
        -Pattern "define\(\s*'CTS_ELEMENTOR_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $PostsSyncVersion) {
    $PostsSyncVersion = Get-PluginVersion `
        -FilePath (Join-Path $SuiteRoot "addons\churchtools-suite-posts-sync\churchtools-suite-posts-sync.php") `
        -Pattern "define\(\s*'CTS_POSTS_SYNC_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $PresentationsVersion) {
    $PresentationsVersion = Get-PluginVersion `
        -FilePath (Join-Path $SuiteRoot "addons\churchtools-suite-presentations\churchtools-suite-presentations.php") `
        -Pattern "define\(\s*'CTS_PRESENTATIONS_VERSION'\s*,\s*'([^']+)'\s*\);"
}

Write-Host "=== ChurchTools Suite — WP-ZIP Build ===" -ForegroundColor Cyan
Write-Host "Quelle:  $SuiteRoot"
Write-Host "Skript:  $ZipScript"
Write-Host ""
Write-Host "  main:          $MainVersion"
Write-Host "  elementor:     $ElementorVersion"
Write-Host "  posts-sync:    $PostsSyncVersion"
Write-Host "  presentations: $PresentationsVersion"
Write-Host ""

Set-Location $SuiteRoot

function Invoke-Zip {
    param([string]$Plugin, [string]$Version)
    Write-Host ">>> $Plugin $Version" -ForegroundColor Yellow
    & $ZipScript -Version $Version -Plugin $Plugin
    if ($LASTEXITCODE -ne 0) {
        throw "ZIP fehlgeschlagen: $Plugin $Version"
    }
}

Invoke-Zip -Plugin main -Version $MainVersion
Invoke-Zip -Plugin elementor -Version $ElementorVersion
Invoke-Zip -Plugin posts-sync -Version $PostsSyncVersion
Invoke-Zip -Plugin presentations -Version $PresentationsVersion

$outputBase = if (Test-Path "C:\privat") { "C:\privat" } else { Join-Path $SuiteRoot "dist" }
Write-Host ""
Write-Host "Fertig. ZIPs in: $outputBase" -ForegroundColor Green
Get-ChildItem -Path $outputBase -Filter "churchtools-suite*.zip" | Sort-Object LastWriteTime -Descending | Select-Object -First 5 | ForEach-Object {
    $sizeMb = [math]::Round($_.Length / 1MB, 2)
    Write-Host "  $($_.Name) ($sizeMb MB)"
}

if ($Release) {
    Write-Host ""
    Write-Host ">>> Git commit + GitHub-Release" -ForegroundColor Yellow
    & (Join-Path $SuiteRoot "scripts\release-and-deploy-github.ps1") `
        -MainVersion $MainVersion `
        -ElementorVersion $ElementorVersion `
        -PostsSyncVersion $PostsSyncVersion `
        -SkipGit:(-not (Test-Path (Join-Path $SuiteRoot ".git")))
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
