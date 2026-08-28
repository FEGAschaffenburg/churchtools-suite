# ChurchTools Suite — Git-Repo, Commit, Push, ZIPs, GitHub-Release
#
# Git-Repo anlegen (einmalig):
#   .\scripts\init-git-repo.ps1
#
# Release (Versionen aus Plugin-Dateien):
#   .\scripts\release-and-deploy-github.ps1 -AutoVersion
#
# Release (Versionen manuell):
#   .\scripts\release-and-deploy-github.ps1 `
#     -MainVersion "1.2.1.3" -ElementorVersion "0.6.28" -PostsSyncVersion "0.1.7"

param(
    [string]$MainVersion,
    [string]$ElementorVersion,
    [string]$PostsSyncVersion,
    [switch]$AutoVersion,
    [switch]$InitRepo,
    [switch]$SkipGit,
    [switch]$SkipPush,
    [switch]$SkipRelease,
    [string]$CommitMessage,
    [string]$GitHubRepo = "FEGAschaffenburg/churchtools-suite",
    [string]$DefaultBranch = "main"
)

$ErrorActionPreference = "Stop"
$repoPath = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$workspacePath = (Resolve-Path (Join-Path $repoPath "..")).Path
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

function Ensure-GitRepo {
    if (Test-Path (Join-Path $repoPath ".git")) {
        return
    }
    if ($InitRepo) {
        Write-Host "Initialisiere Git-Repo..." -ForegroundColor Yellow
        & (Join-Path $PSScriptRoot "init-git-repo.ps1") -GitHubRepo $GitHubRepo -DefaultBranch $DefaultBranch
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
        return
    }
    throw @"
Kein Git-Repository in:
  $repoPath

Einmalig ausführen:
  .\scripts\init-git-repo.ps1

oder dieses Skript mit -InitRepo starten.
"@
}

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw "git nicht gefunden."
}
if (-not $SkipRelease -and -not (Get-Command gh -ErrorAction SilentlyContinue)) {
    throw "GitHub CLI (gh) nicht gefunden. Installieren: https://cli.github.com/"
}

Ensure-GitRepo

if ($AutoVersion -or -not $MainVersion) {
    $MainVersion = Get-PluginVersion `
        -FilePath (Join-Path $repoPath "churchtools-suite.php") `
        -Pattern "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'([^']+)'\s*\);"
    $ElementorVersion = Get-PluginVersion `
        -FilePath (Join-Path $workspacePath "churchtools-suite-elementor\churchtools-suite-elementor.php") `
        -Pattern "define\(\s*'CTS_ELEMENTOR_VERSION'\s*,\s*'([^']+)'\s*\);"
    $PostsSyncVersion = Get-PluginVersion `
        -FilePath (Join-Path $workspacePath "churchtools-suite-posts-sync\churchtools-suite-posts-sync.php") `
        -Pattern "define\(\s*'CTS_POSTS_SYNC_VERSION'\s*,\s*'([^']+)'\s*\);"
}

if (-not $MainVersion -or -not $ElementorVersion -or -not $PostsSyncVersion) {
    throw "Versionen fehlen. Nutze -AutoVersion oder -MainVersion/-ElementorVersion/-PostsSyncVersion."
}

Write-Host "=== Release & Deploy: v$MainVersion ===" -ForegroundColor Cyan
Write-Host "Repo:     $repoPath"
Write-Host "GitHub:   $GitHubRepo"
Write-Host "main:     $MainVersion | elementor: $ElementorVersion | posts-sync: $PostsSyncVersion"
Write-Host ""

$definedVersion = Get-PluginVersion `
    -FilePath (Join-Path $repoPath "churchtools-suite.php") `
    -Pattern "define\(\s*'CHURCHTOOLS_SUITE_VERSION'\s*,\s*'([^']+)'\s*\);"
if ($definedVersion -ne $MainVersion) {
    throw "churchtools-suite.php hat Version '$definedVersion', Parameter ist '$MainVersion'."
}

if (-not $CommitMessage) {
    $notesFile = Join-Path $repoPath "release-notes-v$MainVersion.md"
    if (Test-Path $notesFile) {
        $CommitMessage = "Release v$MainVersion"
    } else {
        $CommitMessage = "Release v$MainVersion"
    }
}

if (-not $SkipGit) {
    Write-Host "[1/3] Git commit..." -ForegroundColor Yellow
    git add -A
    $status = git status --porcelain
    if (-not $status) {
        Write-Host "  Keine Änderungen." -ForegroundColor Gray
    } else {
        git commit -m $CommitMessage
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }

    if (-not $SkipPush) {
        Write-Host "[2/3] Git push..." -ForegroundColor Yellow
        $branch = git rev-parse --abbrev-ref HEAD 2>$null
        if (-not $branch) { $branch = $DefaultBranch }
        git push -u origin $branch
        if ($LASTEXITCODE -ne 0) {
            Write-Host "  Hinweis: Bei neuem Repo ggf. zuerst: git pull origin $DefaultBranch --rebase" -ForegroundColor Yellow
            exit $LASTEXITCODE
        }
    }
} else {
    Write-Host "[1-2/3] Git übersprungen (-SkipGit)" -ForegroundColor Gray
}

if ($SkipRelease) {
    Write-Host "[3/3] GitHub-Release übersprungen (-SkipRelease)" -ForegroundColor Gray
    exit 0
}

Write-Host "[3/3] ZIPs + GitHub-Release..." -ForegroundColor Yellow
& (Join-Path $PSScriptRoot "auto-create-releases.ps1") `
    -MainVersion $MainVersion `
    -ElementorVersion $ElementorVersion `
    -PostsSyncVersion $PostsSyncVersion

if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$tag = "v$MainVersion"
Write-Host ""
Write-Host "Fertig: https://github.com/$GitHubRepo/releases/tag/$tag" -ForegroundColor Green
