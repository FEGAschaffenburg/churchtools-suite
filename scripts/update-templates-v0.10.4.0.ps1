# Template Update Script v0.10.4.0
# Fügt fehlende Toggles und Tags-Unterstützung zu allen Templates hinzu

$ErrorActionPreference = "Stop"

Write-Host "=== ChurchTools Suite Template Updater v0.10.4.0 ===" -ForegroundColor Cyan
Write-Host ""

$templatesDir = "C:\privat\churchtools-suite\templates"
$updates = @()

# Template-Updates Definition
$templateUpdates = @{
    # LIST Templates
    "list\compact.php" = @{
        missing_toggles = @("show_description", "show_services", "show_calendar_name")
        add_tags = $true
    }
    "list\fluent.php" = @{
        missing_toggles = @("show_calendar_name")
        add_tags = $true
    }
    "list\medium.php" = @{
        missing_toggles = @("show_calendar_name")
        add_tags = $true
    }
    
    # WIDGET Templates  
    "widget\upcoming.php" = @{
        missing_toggles = @("show_description", "show_services", "show_calendar_name")
        add_tags = $true
    }
    
    # SEARCH Templates
    "search\classic.php" = @{
        missing_toggles = @("show_time", "show_services", "show_calendar_name")
        add_tags = $true
    }
}

Write-Host "Geplante Updates:" -ForegroundColor Yellow
foreach ($template in $templateUpdates.Keys) {
    $update = $templateUpdates[$template]
    Write-Host "  - $template" -ForegroundColor White
    Write-Host "    Fehlende Toggles: $($update.missing_toggles -join ', ')" -ForegroundColor Gray
    Write-Host "    Tags hinzufügen: $($update.add_tags)" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Hinweis: Dieses Script erstellt Backup-Dateien (.bak)" -ForegroundColor Cyan
Write-Host ""

$confirmation = Read-Host "Fortfahren? (j/n)"
if ($confirmation -ne "j") {
    Write-Host "Abgebrochen." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Starte Updates..." -ForegroundColor Green
Write-Host ""

# Backup-Funktion
function Backup-Template {
    param([string]$path)
    
    $backupPath = "$path.bak"
    Copy-Item $path $backupPath -Force
    Write-Host "  ✓ Backup erstellt: $backupPath" -ForegroundColor Gray
}

# Update-Funktion für Toggles
function Add-ToggleSupport {
    param(
        [string]$filePath,
        [string[]]$toggles
    )
    
    $content = Get-Content $filePath -Raw
    
    foreach ($toggle in $toggles) {
        $varName = $toggle -replace 'show_', ''
        
        # Toggle-Variable hinzufügen (nach anderen Toggle-Definitionen)
        $toggleDef = "`$$toggle = isset( `$args['$toggle'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( `$args['$toggle'] ) : true;"
        
        # Suche nach letzter Toggle-Definition
        if ($content -match '(\$show_\w+\s*=\s*isset.*?;)') {
            $lastToggle = $matches[1]
            $content = $content -replace [regex]::Escape($lastToggle), "$lastToggle`n$toggleDef"
        } else {
            # Fallback: Nach `$events = ...` einfügen
            $content = $content -replace '(\$events\s*=.*?;)', "`$1`n$toggleDef"
        }
    }
    
    Set-Content $filePath $content -NoNewline
}

# Update-Funktion für Tags
function Add-TagsSupport {
    param([string]$filePath)
    
    $content = Get-Content $filePath -Raw
    
    # Tags-Anzeige hinzufügen (nach Services oder vor closing </div>)
    $tagsHTML = @"
					
					<?php if ( ! empty( `$event['tags'] ) ) : ?>
						<?php
						`$tags = is_string( `$event['tags'] ) ? json_decode( `$event['tags'], true ) : `$event['tags'];
						if ( is_array( `$tags ) && ! empty( `$tags ) ) :
						?>
						<div class="cts-event-tags">
							<?php foreach ( `$tags as `$tag ) : ?>
								<span class="cts-tag" style="background-color: <?php echo esc_attr( `$tag['color'] ?? '#e5e7eb' ); ?>; color: #fff;">
									<?php echo esc_html( `$tag['name'] ?? '' ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
"@
    
    # Füge nach Services ein (falls vorhanden)
    if ($content -match '<!-- Services -->.*?</div>\s*</div>') {
        $content = $content -replace '(<!-- Services -->.*?</div>\s*</div>)', "`$1`n$tagsHTML"
    }
    # Sonst vor </article> oder </div> der Event-Card
    elseif ($content -match '</article>') {
        $content = $content -replace '(\s*</article>)', "`n$tagsHTML`$1"
    }
    elseif ($content -match '</(div class="cts-event-card)') {
        $content = $content -replace '(\s*</(div class="cts-event-card))', "`n$tagsHTML`$1"
    }
    
    Set-Content $filePath $content -NoNewline
}

# Templates updaten
$successCount = 0
$errorCount = 0

foreach ($template in $templateUpdates.Keys) {
    $fullPath = Join-Path $templatesDir $template
    
    Write-Host "Updating: $template" -ForegroundColor Cyan
    
    if (!(Test-Path $fullPath)) {
        Write-Host "  ✗ Datei nicht gefunden!" -ForegroundColor Red
        $errorCount++
        continue
    }
    
    try {
        # Backup erstellen
        Backup-Template $fullPath
        
        # Toggles hinzufügen
        $update = $templateUpdates[$template]
        if ($update.missing_toggles.Count -gt 0) {
            Add-ToggleSupport $fullPath $update.missing_toggles
            Write-Host "  ✓ Toggles hinzugefügt: $($update.missing_toggles -join ', ')" -ForegroundColor Green
        }
        
        # Tags hinzufügen
        if ($update.add_tags) {
            Add-TagsSupport $fullPath
            Write-Host "  ✓ Tags-Unterstützung hinzugefügt" -ForegroundColor Green
        }
        
        $successCount++
    }
    catch {
        Write-Host "  ✗ Fehler: $_" -ForegroundColor Red
        $errorCount++
    }
    
    Write-Host ""
}

Write-Host "=== Update abgeschlossen ===" -ForegroundColor Cyan
Write-Host "Erfolgreich: $successCount" -ForegroundColor Green
Write-Host "Fehler: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Gray" })
Write-Host ""

if ($successCount -gt 0) {
    Write-Host "HINWEIS: Backup-Dateien (.bak) wurden erstellt." -ForegroundColor Yellow
    Write-Host "         Prüfe die Änderungen und lösche die Backups manuell." -ForegroundColor Yellow
}
