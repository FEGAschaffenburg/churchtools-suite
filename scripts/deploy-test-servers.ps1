#!/usr/bin/env powershell
<#
.SYNOPSIS
    ChurchTools Suite - Schnell-Deploy auf Test-Servern
    
.DESCRIPTION
    Erstellt ZIP, deployed auf test-servern und führt Syntax-Checks durch
    
.PARAMETER Version
    Plugin-Version (z.B. 1.0.3.20)
    
.PARAMETER Target
    Ziel: 'plugin-test', 'test2-test', oder 'both'
    
.EXAMPLE
    .\deploy-test-servers.ps1 -Version "1.0.3.20" -Target "plugin-test"
    
.NOTES
    SSH muss bereits konfiguriert sein (~/.ssh/config)
#>

param(
    [Parameter(Mandatory=$false)]
    [string]$Version,
    
    [Parameter(Mandatory=$false)]
    [ValidateSet('plugin-test', 'test2-test', 'both')]
    [string]$Target = 'both'
)

# Farben
$Green = [ConsoleColor]::Green
$Red = [ConsoleColor]::Red
$Yellow = [ConsoleColor]::Yellow
$Cyan = [ConsoleColor]::Cyan

function Write-Title {
    param([string]$Text)
    Write-Host "`n╔════════════════════════════════════════╗" -ForegroundColor $Cyan
    Write-Host "║ $($Text.PadRight(36)) ║" -ForegroundColor $Cyan
    Write-Host "╚════════════════════════════════════════╝`n" -ForegroundColor $Cyan
}

function Write-Success {
    param([string]$Text)
    Write-Host "✓ $Text" -ForegroundColor $Green
}

function Write-Error {
    param([string]$Text)
    Write-Host "✗ $Text" -ForegroundColor $Red
}

Write-Title "ChurchTools Suite - Test Deploy"

# Wenn keine Version angegeben, interaktiv fragen
if (-not $Version) {
    $Version = Read-Host "Version eingeben (z.B. 1.0.3.20)"
}

$ZipFile = "C:\privat\churchtools-suite-$Version.zip"
$ProjectRoot = "$PSScriptRoot\.."

# Phase 1: ZIP erstellen
Write-Host "Phase 1: ZIP erstellen" -ForegroundColor $Yellow

if (Test-Path $ZipFile) {
    Write-Success "ZIP existiert bereits: $ZipFile"
} else {
    Write-Host "Erstelle ZIP..."
    & "$ProjectRoot\scripts\create-wp-zip.ps1" -Version $Version
    
    if (Test-Path $ZipFile) {
        Write-Success "ZIP erstellt: $(Get-Item $ZipFile | Select-Object -ExpandProperty Name)"
    } else {
        Write-Error "ZIP konnte nicht erstellt werden"
        exit 1
    }
}

# Phase 2: Deploy-Funktion
function Deploy-Server {
    param(
        [string]$Target,
        [string]$ZipPath
    )
    
    Write-Host "`nDeploy auf $Target..." -ForegroundColor $Yellow
    
    $Commands = @(
        "cd ~",
        "rm -rf churchtools-suite",
        "unzip -q churchtools-suite-$Version.zip",
        "rm churchtools-suite-$Version.zip",
        "php -l churchtools-suite/churchtools-suite.php",
        "grep 'Version:' churchtools-suite/churchtools-suite.php"
    ) -join " && "
    
    # SCP
    Write-Host "Upload..."
    scp $ZipPath "$Target`:~/churchtools-suite-$Version.zip" 2>&1 | Select-String -Pattern "error|Error" | ForEach-Object {
        Write-Error $_
    }
    
    # Execute
    Write-Host "Extract und test..."
    ssh $Target $Commands 2>&1 | ForEach-Object {
        if ($_ -match "^No syntax errors") {
            Write-Success "Syntax OK"
        } elseif ($_ -match "Version:") {
            Write-Host "  $_" -ForegroundColor $Green
        } elseif ($_ -match "error|Error|✗") {
            Write-Error $_
        } else {
            Write-Host "  $_"
        }
    }
    
    Write-Success "$Target deployment complete"
}

# Phase 3: Deploy
Write-Host "`nPhase 2: Deploy" -ForegroundColor $Yellow

if ($Target -eq 'plugin-test' -or $Target -eq 'both') {
    Deploy-Server "plugin-test" $ZipFile
}

if ($Target -eq 'test2-test' -or $Target -eq 'both') {
    Deploy-Server "test2-test" $ZipFile
}

# Summary
Write-Title "Deploy Summary"
Write-Success "Deployment erfolgreich abgeschlossen"
Write-Host "`nNächste Schritte:" -ForegroundColor $Yellow
Write-Host "  1. Teste die Funktionalität auf den Servern"
Write-Host "  2. Bei OK: git push && git tag && GitHub Release"
Write-Host "  3. Production Deploy durchführen`n"
