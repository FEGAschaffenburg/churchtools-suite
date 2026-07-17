#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Erstellt Release-Hinweise für das ChurchTools Suite Monorepo

.DESCRIPTION
    Bereitet einen einzigen GitHub-Release-Eintrag mit drei ZIP-Artefakten vor.
#>

param(
    [Parameter(Mandatory = $true)]
    [string]$MainVersion,
    [Parameter(Mandatory = $true)]
    [string]$ElementorVersion,
    [Parameter(Mandatory = $true)]
    [string]$PostsSyncVersion
)

$repo = "FEGAschaffenburg/churchtools-suite"
$tag = "v$MainVersion"
$url = "https://github.com/$repo/releases/new?tag=$tag"

$notes = @"
## ChurchTools Suite Monorepo Release

Dieses Release enthält alle Plugin-Artefakte:

- churchtools-suite-$MainVersion.zip
- churchtools-suite-elementor-$ElementorVersion.zip
- churchtools-suite-posts-sync-$PostsSyncVersion.zip

### Monorepo-Änderung
- Hauptplugin und Addons werden zentral in einem Repository verwaltet.
- Build-/Release-Prozess erfolgt über `scripts/create-wp-zip.ps1` (Plugin-Parameter).
- Addon-Updates referenzieren Releases aus `FEGAschaffenburg/churchtools-suite`.
"@

Write-Host "`n=== GitHub Release Creator (Monorepo) ===`n" -ForegroundColor Cyan
Write-Host "Repository: $repo" -ForegroundColor Gray
Write-Host "Tag: $tag" -ForegroundColor Yellow
Write-Host "URL: $url" -ForegroundColor Blue
Write-Host ""
Write-Host "Release Notes:" -ForegroundColor Magenta
Write-Host "─────────────────────────────────────────────────────" -ForegroundColor DarkGray
Write-Host $notes -ForegroundColor White
Write-Host "─────────────────────────────────────────────────────" -ForegroundColor DarkGray
Write-Host ""

$notes | Set-Clipboard
Write-Host "✅ Release Notes in Zwischenablage kopiert" -ForegroundColor Green

$open = Read-Host "Browser öffnen? (j/n)"
if ($open -match '^(j|J|y|Y)$') {
    Start-Process $url
}
