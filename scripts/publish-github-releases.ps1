param(
    [string]$ElementorVersion = '1.0.0.1',
    [string]$PostsSyncVersion = '0.2.0.1',
    [string]$PresentationsVersion = '0.1.1'
)

$ErrorActionPreference = 'Stop'

$repos = @(
    @{ repo = 'FEGAschaffenburg/churchtools-suite-elementor'; version = $ElementorVersion; zip = "C:\privat\churchtools-suite-elementor-$ElementorVersion.zip" },
    @{ repo = 'FEGAschaffenburg/churchtools-suite-posts-sync'; version = $PostsSyncVersion; zip = "C:\privat\churchtools-suite-posts-sync-$PostsSyncVersion.zip" },
    @{ repo = 'FEGAschaffenburg/churchtools-suite-presentations'; version = $PresentationsVersion; zip = "C:\privat\churchtools-suite-presentations-$PresentationsVersion.zip" }
)

foreach ($item in $repos) {
    $tag = "v$($item.version)"
    Write-Host "=== $($item.repo) $tag ===" -ForegroundColor Cyan
    
    # Check if release exists
    $existing = gh release view $tag --repo $item.repo 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Release existiert bereits, wird übersprungen" -ForegroundColor Yellow
        continue
    }
    
    # Verify ZIP exists
    if (-not (Test-Path $item.zip)) {
        Write-Host "ERROR: ZIP nicht gefunden: $($item.zip)" -ForegroundColor Red
        exit 1
    }
    
    # Create release with ZIP asset
    Write-Host "Erstelle Release $tag..."
    gh release create $tag `
        --repo $item.repo `
        --title "Release $tag" `
        --notes "Version $($item.version) des Addons." `
        $item.zip
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Release erstellt und ZIP hochgeladen" -ForegroundColor Green
    } else {
        Write-Host "✗ Release-Erstellung fehlgeschlagen" -ForegroundColor Red
        exit 1
    }
    Write-Host ""
}

Write-Host "Alle GitHub-Releases erstellt!" -ForegroundColor Green
