# Switch all ChurchTools Suite repos to their default branches
# Handles locked files and directories gracefully

Write-Host "=== Switching to Default Branches ===" -ForegroundColor Cyan
Write-Host ""

# Function to force-delete directory with retries
function Remove-Locked {
    param($Path)
    if (Test-Path $Path) {
        try {
            # Try unlocking via handle.exe if available
            if (Get-Command handle.exe -ErrorAction SilentlyContinue) {
                handle.exe $Path -nobanner | Out-Null
            }
            
            # Force deletion
            Remove-Item -Path $Path -Recurse -Force -ErrorAction Stop
            Write-Host "  ✅ Removed: $Path" -ForegroundColor Green
        } catch {
            Write-Host "  ⚠️ Could not remove $Path - may be locked" -ForegroundColor Yellow
            # Try via cmd
            cmd /c "rmdir /s /q `"$Path`"" 2>$null
        }
    }
}

# 1. ChurchTools Suite → main
Write-Host "[1/3] ChurchTools Suite → main" -ForegroundColor Yellow
Set-Location "c:\Users\nauma\OneDrive\laragon\www\feg-clone\wp-content\plugins\churchtools-suite"

# Clean problematic directories
Remove-Locked "includes\functions"
Remove-Locked "docs"
Remove-Locked "admin\views\addons-page.php"

# Reset Git state
git reset --hard HEAD 2>&1 | Out-Null
git clean -fd 2>&1 | Out-Null

# Fetch and checkout
git fetch origin main 2>&1 | Out-Null
git checkout -f main 2>&1 | Out-Null
git reset --hard origin/main 2>&1 | Out-Null

$branch = git branch --show-current
$commit = git log --oneline -1
Write-Host "  ✅ Branch: $branch" -ForegroundColor Green
Write-Host "  📝 Commit: $commit" -ForegroundColor White
Write-Host ""

# 2. ChurchTools Suite Demos → master
Write-Host "[2/3] ChurchTools Suite Demos → master" -ForegroundColor Yellow
Set-Location "c:\Users\nauma\OneDrive\laragon\www\plugin-homepage\wp-content\plugins\churchtools-suite-demo"

# Clean
Remove-Locked "docs"
git reset --hard HEAD 2>&1 | Out-Null
git clean -fd 2>&1 | Out-Null

# Fetch and checkout
git fetch origin master 2>&1 | Out-Null
git checkout -f master 2>&1 | Out-Null
git reset --hard origin/master 2>&1 | Out-Null

$branch = git branch --show-current
$commit = git log --oneline -1
Write-Host "  ✅ Branch: $branch" -ForegroundColor Green
Write-Host "  📝 Commit: $commit" -ForegroundColor White
Write-Host ""

# 3. ChurchTools Suite Elementor → master (already correct)
Write-Host "[3/3] ChurchTools Suite Elementor → master" -ForegroundColor Yellow
Set-Location "c:\Users\nauma\OneDrive\laragon\www\feg-clone\wp-content\plugins\churchtools-suite-elementor"

$branch = git branch --show-current
$commit = git log --oneline -1
Write-Host "  OK Branch: $branch" -ForegroundColor Green
Write-Host "  Last Commit: $commit" -ForegroundColor White
Write-Host ""

Write-Host "=== OK Alle Repositories auf Default-Branches ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "VS Code wird jetzt automatisch die Änderungen erkennen." -ForegroundColor White
Write-Host "Falls nötig: Fenster neu laden (Ctrl+Shift+P → 'Reload Window')" -ForegroundColor White
