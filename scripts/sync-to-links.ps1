param(
    [string[]]$Paths,
    [switch]$DryRun
)

$scriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$repoRoot = Resolve-Path (Join-Path $scriptDir "..") | Select-Object -ExpandProperty Path
$runtimeRoot = Join-Path $repoRoot ".links\wp-content\plugins\churchtools-suite"

if (-not (Test-Path $runtimeRoot)) {
    Write-Host "Runtime-Ziel nicht gefunden: $runtimeRoot" -ForegroundColor Red
    Write-Host "Starte zuerst wp-env oder pruefe deine .links-Struktur." -ForegroundColor Yellow
    exit 1
}

function Get-RelativePath {
    param(
        [Parameter(Mandatory = $true)]
        [string]$BasePath,
        [Parameter(Mandatory = $true)]
        [string]$FullPath
    )

    $baseUri = [System.Uri]((Resolve-Path $BasePath).Path + [System.IO.Path]::DirectorySeparatorChar)
    $fullUri = [System.Uri]((Resolve-Path $FullPath).Path)
    return [System.Uri]::UnescapeDataString($baseUri.MakeRelativeUri($fullUri).ToString()).Replace('/', '\\')
}

function Sync-SinglePath {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Path
    )

    $candidate = if ([System.IO.Path]::IsPathRooted($Path)) { $Path } else { Join-Path $repoRoot $Path }

    if (-not (Test-Path $candidate)) {
        throw "Pfad nicht gefunden: $Path"
    }

    $resolved = (Resolve-Path $candidate).Path
    if (-not $resolved.StartsWith($repoRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
        throw "Pfad liegt ausserhalb des Repos: $resolved"
    }

    $relative = Get-RelativePath -BasePath $repoRoot -FullPath $resolved
    $target = Join-Path $runtimeRoot $relative

    if ((Get-Item $resolved).PSIsContainer) {
        if ($DryRun) {
            Write-Host "[DRY-RUN] Ordner sync: $relative" -ForegroundColor Yellow
            return
        }

        New-Item -Path $target -ItemType Directory -Force | Out-Null
        $rcArgs = @(
            $resolved,
            $target,
            '/MIR',
            '/R:1',
            '/W:1',
            '/NFL',
            '/NDL',
            '/NJH',
            '/NJS',
            '/NP'
        )
        & robocopy @rcArgs | Out-Host
        if ($LASTEXITCODE -gt 7) {
            throw "Robocopy fehlgeschlagen fuer $relative (ExitCode=$LASTEXITCODE)"
        }
        Write-Host "Ordner synchronisiert: $relative" -ForegroundColor Green
        return
    }

    if ($DryRun) {
        Write-Host "[DRY-RUN] Datei sync: $relative" -ForegroundColor Yellow
        return
    }

    New-Item -Path (Split-Path -Path $target -Parent) -ItemType Directory -Force | Out-Null
    Copy-Item -Path $resolved -Destination $target -Force
    Write-Host "Datei synchronisiert: $relative" -ForegroundColor Green
}

try {
    Write-Host "=== Sync to .links Runtime ===" -ForegroundColor Cyan
    Write-Host "Repo:    $repoRoot" -ForegroundColor Gray
    Write-Host "Runtime: $runtimeRoot" -ForegroundColor Gray

    if ($Paths -and $Paths.Count -gt 0) {
        foreach ($p in $Paths) {
            Sync-SinglePath -Path $p
        }

        Write-Host "Fertig: Gezielter Sync abgeschlossen." -ForegroundColor Cyan
        exit 0
    }

    $robocopyArgs = @(
        $repoRoot,
        $runtimeRoot,
        '/MIR',
        '/R:1',
        '/W:1',
        '/NFL',
        '/NDL',
        '/NJH',
        '/NJS',
        '/NP',
        '/XD', '.git', '.links', 'node_modules', '.backup', '.github-release', '.tmp', 'tmp',
        '/XF', '*.zip', '*.log'
    )

    if ($DryRun) {
        $robocopyArgs += '/L'
    }

    & robocopy @robocopyArgs | Out-Host
    $exitCode = $LASTEXITCODE

    if ($exitCode -gt 7) {
        throw "Robocopy fehlgeschlagen (ExitCode=$exitCode)"
    }

    if ($DryRun) {
        Write-Host "Fertig: Dry-Run abgeschlossen." -ForegroundColor Cyan
    } else {
        Write-Host "Fertig: Vollsync abgeschlossen." -ForegroundColor Cyan
    }
}
catch {
    Write-Host "Fehler: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
