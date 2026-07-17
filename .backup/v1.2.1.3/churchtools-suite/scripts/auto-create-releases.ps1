param(
    [Parameter(Mandatory = $true)]
    [string]$MainVersion,
    [Parameter(Mandatory = $true)]
    [string]$ElementorVersion,
    [Parameter(Mandatory = $true)]
    [string]$PostsSyncVersion
)

Write-Host "=== Monorepo: Automatische GitHub Release Erstellung ===" -ForegroundColor Cyan

$repoPath = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
Set-Location $repoPath

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

$outputDir = if (Test-Path "C:\privat") { "C:\privat" } else { Join-Path $repoPath "dist" }
if (-not (Test-Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$mainZip = Join-Path $outputDir "churchtools-suite-$MainVersion.zip"
$elementorZip = Join-Path $outputDir "churchtools-suite-elementor-$ElementorVersion.zip"
$postsSyncZip = Join-Path $outputDir "churchtools-suite-posts-sync-$PostsSyncVersion.zip"

foreach ($zip in @($mainZip, $elementorZip, $postsSyncZip)) {
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

### Hinweise
- Hauptplugin und Addons werden jetzt zentral im Monorepo verwaltet.
- Auto-Update der Addons nutzt Releases aus `FEGAschaffenburg/churchtools-suite`.
"@

& gh release create $tag `
    --repo "FEGAschaffenburg/churchtools-suite" `
    --title $title `
    --notes $notes `
    $mainZip $elementorZip $postsSyncZip

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Monorepo-Release erfolgreich erstellt" -ForegroundColor Green
} else {
    Write-Host "❌ Fehler beim Erstellen des Monorepo-Releases" -ForegroundColor Red
    exit 1
}
