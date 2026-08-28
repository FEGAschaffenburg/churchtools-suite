param(
    [string]$MainVersion,
    [string]$ElementorVersion,
    [string]$PostsSyncVersion,
    [string]$PresentationsVersion
)

$ErrorActionPreference = 'Stop'
$repoPath = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$workspacePath = (Resolve-Path (Join-Path $repoPath '..')).Path
Set-Location $repoPath

function Get-PluginVersion {
    param(
        [Parameter(Mandatory = $true)][string]$FilePath,
        [Parameter(Mandatory = $true)][string]$Pattern
    )

    if (-not (Test-Path $FilePath)) {
        throw "Datei nicht gefunden: $FilePath"
    }

    $match = [regex]::Match((Get-Content $FilePath -Raw -Encoding UTF8), $Pattern)
    if (-not $match.Success) {
        throw "Version nicht lesbar: $FilePath"
    }

    return $match.Groups[1].Value
}

if (-not $MainVersion) {
    $MainVersion = Get-PluginVersion (Join-Path $repoPath 'churchtools-suite.php') "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $ElementorVersion) {
    $ElementorVersion = Get-PluginVersion (Join-Path $workspacePath 'churchtools-suite-elementor\churchtools-suite-elementor.php') "define\(\s*'CTS_ELEMENTOR_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $PostsSyncVersion) {
    $PostsSyncVersion = Get-PluginVersion (Join-Path $workspacePath 'churchtools-suite-posts-sync\churchtools-suite-posts-sync.php') "define\(\s*'CTS_POSTS_SYNC_VERSION'\s*,\s*'([^']+)'\s*\);"
}
if (-not $PresentationsVersion) {
    $PresentationsVersion = Get-PluginVersion (Join-Path $workspacePath 'churchtools-suite-presentations\churchtools-suite-presentations.php') "define\(\s*'CTS_PRESENTATIONS_VERSION'\s*,\s*'([^']+)'\s*\);"
}

Write-Host "=== Monorepo: Automatische GitHub Release Erstellung ===" -ForegroundColor Cyan

Write-Host "[1/2] Erstelle ZIP-Artefakte für alle Plugins..." -ForegroundColor Yellow

function Invoke-ZipBuild {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Plugin,
        [Parameter(Mandatory = $true)]
        [string]$Version
    )

    & .\scripts\create-wp-zip.ps1 -Version $Version -Plugin $Plugin
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ ZIP-Erstellung fehlgeschlagen ($Plugin $Version)" -ForegroundColor Red
        exit 1
    }
}

Invoke-ZipBuild -Plugin 'main' -Version $MainVersion
Invoke-ZipBuild -Plugin 'elementor' -Version $ElementorVersion
Invoke-ZipBuild -Plugin 'posts-sync' -Version $PostsSyncVersion
Invoke-ZipBuild -Plugin 'presentations' -Version $PresentationsVersion

$mainZip = "C:\privat\churchtools-suite-$MainVersion.zip"
$elementorZip = "C:\privat\churchtools-suite-elementor-$ElementorVersion.zip"
$postsSyncZip = "C:\privat\churchtools-suite-posts-sync-$PostsSyncVersion.zip"
$presentationsZip = "C:\privat\churchtools-suite-presentations-$PresentationsVersion.zip"

foreach ($zip in @($mainZip, $elementorZip, $postsSyncZip, $presentationsZip)) {
    if (-not (Test-Path $zip)) {
        Write-Host "❌ ZIP-Datei nicht gefunden: $zip" -ForegroundColor Red
        exit 1
    }
}

Write-Host "[2/2] Erstelle GitHub-Release im Monorepo..." -ForegroundColor Yellow

$tag = "v$MainVersion"
$title = "Monorepo Release $tag"
$notes = @"
## ChurchTools Suite Monorepo Release

Dieses Release enthält ZIP-Artefakte für alle Plugins:

- `churchtools-suite-$MainVersion.zip`
- `churchtools-suite-elementor-$ElementorVersion.zip`
- `churchtools-suite-posts-sync-$PostsSyncVersion.zip`
- `churchtools-suite-presentations-$PresentationsVersion.zip`

### Hinweise
- Hauptplugin und Addons werden jetzt zentral im Monorepo verwaltet.
- Auto-Update der Addons nutzt Releases aus `FEGAschaffenburg/churchtools-suite`.
"@

& gh release create $tag `
    --repo "FEGAschaffenburg/churchtools-suite" `
    --title $title `
    --notes $notes `
    $mainZip $elementorZip $postsSyncZip $presentationsZip

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Monorepo-Release erfolgreich erstellt" -ForegroundColor Green
} else {
    Write-Host "❌ Fehler beim Erstellen des Monorepo-Releases" -ForegroundColor Red
    exit 1
}
