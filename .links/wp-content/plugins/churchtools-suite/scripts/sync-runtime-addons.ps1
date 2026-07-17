param(
    [switch]$DryRun
)

Write-Host "=== Sync Runtime Addons (Monorepo -> wp-content/plugins) ===" -ForegroundColor Cyan

$scriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$repoRoot = Resolve-Path (Join-Path $scriptDir "..") | Select-Object -ExpandProperty Path
$pluginsRoot = Resolve-Path (Join-Path $repoRoot "..") | Select-Object -ExpandProperty Path

$mappings = @(
    @{
        Name = 'Elementor';
        Source = Join-Path $repoRoot 'addons\churchtools-suite-elementor';
        Target = Join-Path $pluginsRoot 'churchtools-suite-elementor';
    },
    @{
        Name = 'Posts Sync';
        Source = Join-Path $repoRoot 'addons\churchtools-suite-posts-sync';
        Target = Join-Path $pluginsRoot 'churchtools-suite-posts-sync';
    }
)

function Invoke-AddonSync {
    param(
        [Parameter(Mandatory = $true)]
        [hashtable]$Map,
        [switch]$DryRunMode
    )

    $name = $Map.Name
    $source = $Map.Source
    $target = $Map.Target

    if (-not (Test-Path $source)) {
        throw "Source nicht gefunden: $source"
    }

    if (-not (Test-Path $target)) {
        if ($DryRunMode) {
            Write-Host "[DRY-RUN] Zielordner würde erstellt: $target" -ForegroundColor Yellow
        } else {
            New-Item -Path $target -ItemType Directory -Force | Out-Null
            Write-Host "Zielordner erstellt: $target" -ForegroundColor Gray
        }
    }

    Write-Host "Synchronisiere: $name" -ForegroundColor Yellow
    Write-Host "  Source: $source" -ForegroundColor Gray
    Write-Host "  Target: $target" -ForegroundColor Gray

    $robocopyArgs = @(
        $source,
        $target,
        '/MIR',
        '/R:1',
        '/W:1',
        '/NFL',
        '/NDL',
        '/NJH',
        '/NJS',
        '/NP',
        '/XD', '.git', '.github', 'scripts', 'tests', 'node_modules', '.vscode', '.idea',
        '/XF', '*.backup-*', '*.zip', '*.log', '.DS_Store'
    )

    if ($DryRunMode) {
        $robocopyArgs += '/L'
    }

    & robocopy @robocopyArgs | Out-Host
    $exitCode = $LASTEXITCODE

    if ($exitCode -gt 7) {
        throw "Robocopy fehlgeschlagen für $name (ExitCode=$exitCode)"
    }

    if ($DryRunMode) {
        Write-Host "[DRY-RUN] $name geprüft." -ForegroundColor Green
    } else {
        Write-Host "$name synchronisiert." -ForegroundColor Green
    }
}

try {
    foreach ($mapping in $mappings) {
        Invoke-AddonSync -Map $mapping -DryRunMode:$DryRun
    }

    if ($DryRun) {
        Write-Host "=== Dry-Run abgeschlossen ===" -ForegroundColor Cyan
    } else {
        Write-Host "=== Sync abgeschlossen ===" -ForegroundColor Cyan
    }
}
catch {
    Write-Host "❌ Fehler: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
