param(
    [Parameter(Mandatory = $false)]
    [string]$FtpHost = "ftp.feg.de",
    
    [Parameter(Mandatory = $false)]
    [string]$FtpUser = "",
    
    [Parameter(Mandatory = $false)]
    [string]$FtpPassword = "",
    
    [Parameter(Mandatory = $false)]
    [string]$RemotePath = "/web/wp-content/plugins/churchtools-suite-demo"
)

$ScriptDir = Split-Path -Path $MyInvocation.MyCommand.Definition -Parent
$PluginRoot = Resolve-Path (Join-Path $ScriptDir "..") | Select-Object -ExpandProperty Path

Write-Host "=== ChurchTools Suite Demo - FTP Deployment ===" -ForegroundColor Cyan
Write-Host "FTP Host: $FtpHost"
Write-Host "Plugin: churchtools-suite-demo"
Write-Host ""

# Prompt for credentials if not provided
if ([string]::IsNullOrEmpty($FtpUser)) {
    $FtpUser = Read-Host "FTP Username"
}
if ([string]::IsNullOrEmpty($FtpPassword)) {
    $securePass = Read-Host "FTP Password" -AsSecureString
    $FtpPassword = [Runtime.InteropServices.Marshal]::PtrToStringAuto(
        [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePass))
}

# Check if WinSCP is available
$winscpPath = "C:\Program Files (x86)\WinSCP\WinSCP.com"
if (-not (Test-Path $winscpPath)) {
    # Try alternative path
    $winscpPath = "C:\Program Files\WinSCP\WinSCP.com"
}

$useWinSCP = Test-Path $winscpPath

if ($useWinSCP) {
    Write-Host "Using WinSCP for FTP upload..." -ForegroundColor Green
    Write-Host ""
    
    # Create WinSCP script
    $scriptContent = @"
open ftp://${FtpUser}:${FtpPassword}@${FtpHost}/
option batch abort
option confirm off
lcd "$PluginRoot"
cd $RemotePath
rm *
put * -filemask="|.git/;.github/;scripts/;*.zip;*.log;*.backup-*;add-*.php;check-*.php;create-*.php;find-*.php;fix-*.php;remove-*.php;reset-*.php;show-*.php;test-*.php;update-*.php;verify-*.php;watch-*.php;refresh-*.php;content207.txt"
exit
"@
    
    $scriptFile = Join-Path $env:TEMP "winscp-deploy-$(Get-Date -Format 'yyyyMMddHHmmss').txt"
    $scriptContent | Out-File -FilePath $scriptFile -Encoding ASCII
    
    Write-Host "Uploading files..." -ForegroundColor Gray
    & $winscpPath /script=$scriptFile /log="$env:TEMP\winscp-deploy.log"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "[SUCCESS] Deployment successful!" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "[ERROR] Deployment failed. Check log: $env:TEMP\winscp-deploy.log" -ForegroundColor Red
        Get-Content "$env:TEMP\winscp-deploy.log" | Select-Object -Last 20
        exit $LASTEXITCODE
    }
    
    Remove-Item $scriptFile -ErrorAction SilentlyContinue
    
} else {
    Write-Host "Using built-in .NET FTP client..." -ForegroundColor Yellow
    Write-Host ""
    
    # Exclude patterns
    $excludePatterns = @(
        '*.git*', 'scripts*', '*.zip', '*.log', '*.backup-*', 
        'add-*.php', 'check-*.php', 'create-*.php', 'find-*.php', 
        'fix-*.php', 'remove-*.php', 'reset-*.php', 'show-*.php', 
        'test-*.php', 'update-*.php', 'verify-*.php', 'watch-*.php', 
        'refresh-*.php', 'content207.txt'
    )
    
    function Upload-FtpFile {
        param(
            [string]$LocalPath,
            [string]$RemotePath,
            [string]$FtpHost,
            [System.Net.NetworkCredential]$Credentials
        )
        
        try {
            $ftpUri = "ftp://$FtpHost$RemotePath"
            $request = [System.Net.FtpWebRequest]::Create($ftpUri)
            $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $request.Credentials = $Credentials
            $request.UseBinary = $true
            $request.UsePassive = $true
            
            $fileContent = [System.IO.File]::ReadAllBytes($LocalPath)
            $request.ContentLength = $fileContent.Length
            
            $requestStream = $request.GetRequestStream()
            $requestStream.Write($fileContent, 0, $fileContent.Length)
            $requestStream.Close()
            
            $response = $request.GetResponse()
            $response.Close()
            
            return $true
        } catch {
            Write-Host "[ERROR] Failed to upload $RemotePath : $_" -ForegroundColor Red
            return $false
        }
    }
    
    function Create-FtpDirectory {
        param(
            [string]$RemotePath,
            [string]$FtpHost,
            [System.Net.NetworkCredential]$Credentials
        )
        
        try {
            $ftpUri = "ftp://$FtpHost$RemotePath"
            $request = [System.Net.FtpWebRequest]::Create($ftpUri)
            $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $request.Credentials = $Credentials
            $request.UsePassive = $true
            
            $response = $request.GetResponse()
            $response.Close()
            
            return $true
        } catch {
            # Directory might already exist
            return $false
        }
    }
    
    $credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
    
    # Get all files to upload
    $files = Get-ChildItem -Path $PluginRoot -Recurse -File | Where-Object {
        $relativePath = $_.FullName.Substring($PluginRoot.Length)
        $shouldExclude = $false
        foreach ($pattern in $excludePatterns) {
            if ($relativePath -like $pattern) {
                $shouldExclude = $true
                break
            }
        }
        -not $shouldExclude
    }
    
    $totalFiles = $files.Count
    $uploadedFiles = 0
    $failedFiles = 0
    
    Write-Host "Uploading $totalFiles files..." -ForegroundColor Gray
    
    foreach ($file in $files) {
        $relativePath = $file.FullName.Substring($PluginRoot.Length).Replace('\', '/')
        $remoteFilePath = "$RemotePath$relativePath"
        $remoteDir = Split-Path $remoteFilePath -Parent
        
        # Create directory if needed
        if ($remoteDir -ne $RemotePath) {
            Create-FtpDirectory -RemotePath $remoteDir -FtpHost $FtpHost -Credentials $credentials | Out-Null
        }
        
        Write-Host "  Uploading: $relativePath" -NoNewline
        
        if (Upload-FtpFile -LocalPath $file.FullName -RemotePath $remoteFilePath -FtpHost $FtpHost -Credentials $credentials) {
            Write-Host " [OK]" -ForegroundColor Green
            $uploadedFiles++
        } else {
            Write-Host " [FAILED]" -ForegroundColor Red
            $failedFiles++
        }
    }
    
    Write-Host ""
    Write-Host "Upload summary:" -ForegroundColor Cyan
    Write-Host "  Total files: $totalFiles" -ForegroundColor Gray
    Write-Host "  Uploaded: $uploadedFiles" -ForegroundColor Green
    Write-Host "  Failed: $failedFiles" -ForegroundColor Red
    
    if ($failedFiles -eq 0) {
        Write-Host ""
        Write-Host "[SUCCESS] Deployment successful!" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "[WARNING] Deployment completed with errors" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Plugin deployed to: https://plugin.feg-aschaffenburg.de/wp-admin/plugins.php"
