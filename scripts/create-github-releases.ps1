# Create GitHub Releases for all missing tags
# Requires GitHub CLI (gh) to be installed and authenticated

$ErrorActionPreference = "Stop"

Write-Host "🔍 Prüfe GitHub CLI..." -ForegroundColor Cyan

$existingReleases = gh release list --limit 100 2>$null | ForEach-Object { ($_ -split "`t")[0] }

$tags = git tag --sort=-version:refname

Write-Host "`n📋 Erstelle fehlende GitHub Releases...`n" -ForegroundColor Cyan

$created = 0
$skipped = 0

foreach ($tag in $tags) {
    if ($existingReleases -contains $tag) {
        Write-Host "⏭️  $tag existiert bereits" -ForegroundColor Gray
        $skipped++
        continue
    }
    
    $releaseNotesFile = "RELEASE-NOTES-$tag.md"
    
    if (Test-Path $releaseNotesFile) {
        Write-Host "📝 Erstelle $tag mit Release Notes..." -ForegroundColor Yellow
        & gh release create $tag --title "ChurchTools Suite $tag" --notes-file $releaseNotesFile --verify-tag
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ $tag erstellt!" -ForegroundColor Green
            $created++
        }
    }
    else {
        Write-Host "📝 Erstelle $tag ohne Release Notes..." -ForegroundColor Yellow
        & gh release create $tag --title "ChurchTools Suite $tag" --notes "Release $tag" --verify-tag
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ $tag erstellt!" -ForegroundColor Green
            $created++
        }
    }
    
    Start-Sleep -Seconds 1
}

Write-Host "`n✅ Fertig! Erstellt: $created, Übersprungen: $skipped" -ForegroundColor Green

