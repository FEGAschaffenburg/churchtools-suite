# Git-Repository für ChurchTools Suite Monorepo initialisieren
#
#   .\scripts\init-git-repo.ps1
#   .\scripts\init-git-repo.ps1 -CloneFirst   # klont GitHub-Repo nach -TargetPath
#
# Danach: .\scripts\release-and-deploy-github.ps1 -AutoVersion

param(
    [string]$GitHubRepo = "FEGAschaffenburg/churchtools-suite",
    [string]$DefaultBranch = "main",
    [switch]$CloneFirst,
    [string]$TargetPath = "C:\dev\churchtools-suite"
)

$ErrorActionPreference = "Stop"
$repoPath = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path

if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    throw "git nicht gefunden."
}

function Test-GitRepo {
    param([string]$Path)
    return Test-Path (Join-Path $Path ".git")
}

if ($CloneFirst) {
    if (Test-Path $TargetPath) {
        if (Test-GitRepo $TargetPath) {
            Write-Host "Bereits ein Git-Repo: $TargetPath" -ForegroundColor Yellow
        } else {
            throw "Ordner existiert, ist aber kein Git-Repo: $TargetPath"
        }
    } else {
        $parent = Split-Path $TargetPath -Parent
        if (-not (Test-Path $parent)) {
            New-Item -ItemType Directory -Path $parent -Force | Out-Null
        }
        Write-Host "Clone https://github.com/$GitHubRepo.git -> $TargetPath" -ForegroundColor Cyan
        git clone "https://github.com/$GitHubRepo.git" $TargetPath
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }
    Write-Host ""
    Write-Host "Kopiere lokale Dateien aus:" -ForegroundColor Yellow
    Write-Host "  $repoPath"
    Write-Host "nach:"
    Write-Host "  $TargetPath"
    Write-Host "(robocopy oder manuell, dann im Clone release-and-deploy ausführen)" -ForegroundColor Gray
    exit 0
}

Set-Location $repoPath

if (Test-GitRepo $repoPath) {
    Write-Host "Git-Repo existiert bereits: $repoPath" -ForegroundColor Green
    git remote -v
    git status -sb
    exit 0
}

Write-Host "=== Git init: $repoPath ===" -ForegroundColor Cyan

git init -b $DefaultBranch
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$remoteUrl = "https://github.com/$GitHubRepo.git"
$existingRemote = git remote 2>$null
if ($existingRemote -match 'origin') {
    git remote set-url origin $remoteUrl
} else {
    git remote add origin $remoteUrl
}

Write-Host "Remote: $remoteUrl" -ForegroundColor Gray

git add -A
$status = git status --porcelain
if ($status) {
    git commit -m "Initial commit: ChurchTools Suite Monorepo"
    Write-Host "Initial commit erstellt." -ForegroundColor Green
} else {
    Write-Host "Keine Dateien zum Committen." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Nächste Schritte:" -ForegroundColor Cyan
Write-Host "  1. gh auth login"
Write-Host "  2. git pull origin $DefaultBranch --rebase   # falls Remote schon Commits hat"
Write-Host "  3. .\scripts\release-and-deploy-github.ps1 -AutoVersion"
Write-Host ""
Write-Host "Oder bestehendes Remote-Repo klonen:" -ForegroundColor Gray
Write-Host "  .\scripts\init-git-repo.ps1 -CloneFirst -TargetPath C:\dev\churchtools-suite"
