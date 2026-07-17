param(
    [switch]$DryRun
)

Write-Host "=== ChurchTools Suite Test-Update Release ===" -ForegroundColor Cyan

$repoPath = "c:\Users\nauma\OneDrive\laragon\www\feg-clone\wp-content\plugins\churchtools-suite"
Set-Location $repoPath

$syncScript = Join-Path $repoPath "scripts\sync-runtime-addons.ps1"
if (Test-Path $syncScript) {
    Write-Host "Synchronisiere lokale Runtime-Addon-Ordner..." -ForegroundColor Yellow

    if ($DryRun) {
        & $syncScript -DryRun
    } else {
        & $syncScript
    }

    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Runtime-Addon-Sync fehlgeschlagen" -ForegroundColor Red
        exit 1
    }
}

$mainPluginFile = Join-Path $repoPath "churchtools-suite.php"
$elementorPluginFile = Join-Path $repoPath "addons/churchtools-suite-elementor/churchtools-suite-elementor.php"
$postsSyncPluginFile = Join-Path $repoPath "addons/churchtools-suite-posts-sync/churchtools-suite-posts-sync.php"

if (-not (Test-Path $mainPluginFile)) {
    Write-Host "❌ Hauptplugin-Datei nicht gefunden: $mainPluginFile" -ForegroundColor Red
    exit 1
}

function Get-VersionFromFile {
    param(
        [Parameter(Mandatory = $true)]
        [string]$FilePath,
        [Parameter(Mandatory = $true)]
        [string]$Pattern
    )

    $content = Get-Content $FilePath -Raw
    $match = [regex]::Match($content, $Pattern)
    if (-not $match.Success) {
        throw "Version konnte in $FilePath nicht gelesen werden."
    }

    return $match.Groups[1].Value
}

function Increment-BuildVersion {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Version
    )

    $parts = $Version.Split('.')
    if ($parts.Count -lt 4) {
        while ($parts.Count -lt 4) {
            $parts += '0'
        }
    }

    for ($i = 0; $i -lt $parts.Count; $i++) {
        if (-not [int]::TryParse($parts[$i], [ref]([int]$null))) {
            throw "Ungültige Versionsnummer: $Version"
        }
    }

    $parts[3] = ([int]$parts[3] + 1).ToString()
    return ($parts -join '.')
}

function Update-MainPluginVersion {
    param(
        [Parameter(Mandatory = $true)]
        [string]$FilePath,
        [Parameter(Mandatory = $true)]
        [string]$NewVersion
    )

    $content = Get-Content $FilePath -Raw

    $content = [regex]::Replace(
        $content,
        '(?m)^(\s*\*\s*Version:\s*)([^\r\n]+)$',
        ('$1' + $NewVersion)
    )

    $content = [regex]::Replace(
        $content,
        "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'[^']+'\s*\);",
        "define( 'CHURCHTOOLS_SUITE_VERSION', '$NewVersion' );"
    )

    Set-Content -Path $FilePath -Value $content -Encoding UTF8
}

try {
    $currentMainVersion = Get-VersionFromFile -FilePath $mainPluginFile -Pattern "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'([^']+)'\s*\);"
    $newMainVersion = Increment-BuildVersion -Version $currentMainVersion

    $elementorVersion = Get-VersionFromFile -FilePath $elementorPluginFile -Pattern "define\(\s*'CTS_ELEMENTOR_VERSION'\s*,\s*'([^']+)'\s*\);"
    $postsSyncVersion = Get-VersionFromFile -FilePath $postsSyncPluginFile -Pattern "define\(\s*'CTS_POSTS_SYNC_VERSION'\s*,\s*'([^']+)'\s*\);"

    Write-Host "Aktuelle Hauptplugin-Version: $currentMainVersion"
    Write-Host "Neue Hauptplugin-Version:     $newMainVersion" -ForegroundColor Green
    Write-Host "Elementor Addon-Version:      $elementorVersion"
    Write-Host "Posts Sync Addon-Version:     $postsSyncVersion"

    if ($DryRun) {
        Write-Host "DryRun aktiv: Keine Dateien geändert, kein Release erstellt." -ForegroundColor Yellow
        exit 0
    }

    Update-MainPluginVersion -FilePath $mainPluginFile -NewVersion $newMainVersion

    Write-Host "Starte Monorepo-Release..." -ForegroundColor Yellow
    & .\scripts\auto-create-releases.ps1 `
        -MainVersion $newMainVersion `
        -ElementorVersion $elementorVersion `
        -PostsSyncVersion $postsSyncVersion

    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Release-Erstellung fehlgeschlagen" -ForegroundColor Red
        exit 1
    }

    Write-Host "✅ Test-Update Release erstellt: v$newMainVersion" -ForegroundColor Green
}
catch {
    Write-Host "❌ Fehler: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
