# wp-env setup & verification for plugin.feg-aschaffenburg.de
# Run in PowerShell: .\scripts\wp-env-setup.ps1

$ErrorActionPreference = "Continue"
Set-Location $PSScriptRoot\..

$log = Join-Path (Get-Location) "wp-env-diagnostics.log"
Remove-Item $log -ErrorAction SilentlyContinue

function Run-Step($title, [scriptblock]$block) {
    Write-Host "`n>>> $title" -ForegroundColor Cyan
    Add-Content $log "`n========== $title ==========`n"
    & $block *>&1 | ForEach-Object {
        Write-Host $_
        Add-Content $log $_
    }
    Add-Content $log "`n[exit code: $LASTEXITCODE]`n"
}

Write-Host "wp-env setup – plugin.feg-aschaffenburg.de" -ForegroundColor Green
Write-Host "Log: $log`n"

Run-Step "STEP 1: node_modules" {
    if (Test-Path node_modules) { "node_modules: OK" }
    else {
        "Installing npm dependencies..."
        npm install
    }
}

Run-Step "STEP 2: env:stop" { npm run env:stop }
Run-Step "STEP 3: env:start (can take several minutes)" { npm run env:start }
Run-Step "STEP 4: wp plugin list" { npx wp-env run cli wp plugin list }
Run-Step "STEP 5: wp theme list" { npx wp-env run cli wp theme list }
Run-Step "STEP 6: siteurl" { npx wp-env run cli wp option get siteurl }
Run-Step "STEP 7: docker ps" { docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" }

Write-Host "`nDone. Log saved to: $log" -ForegroundColor Green
Write-Host "Site: http://localhost:8887/wp-admin (admin / password)" -ForegroundColor Yellow
