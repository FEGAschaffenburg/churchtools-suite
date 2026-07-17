$ftpServer = "web73.feg.de"
$ftpUser = "aschaffeftpplugin"
$ftpPassword = "T9hob3TWBt@w"
$localPath = "C:\Users\KasseFeg\wp-local\plugin.feg-aschaffenburg.de"

$files = @(
    @{ local = ".links\wp-content\plugins\churchtools-suite\churchtools-suite.php"; remote = "/public_html/plugin.feg-aschaffenburg.de/wp-content/plugins/churchtools-suite/churchtools-suite.php" },
    @{ local = ".links\wp-content\plugins\churchtools-suite\addons\churchtools-suite-elementor\churchtools-suite-elementor.php"; remote = "/public_html/plugin.feg-aschaffenburg.de/wp-content/plugins/churchtools-suite/addons/churchtools-suite-elementor/churchtools-suite-elementor.php" },
    @{ local = ".links\wp-content\plugins\churchtools-suite\addons\churchtools-suite-posts-sync\churchtools-suite-posts-sync.php"; remote = "/public_html/plugin.feg-aschaffenburg.de/wp-content/plugins/churchtools-suite/addons/churchtools-suite-posts-sync/churchtools-suite-posts-sync.php" }
)

Write-Host "Starte FTP-Upload..." -ForegroundColor Cyan

foreach ($file in $files) {
    $fullLocalPath = Join-Path $localPath $file.local
    
    if (Test-Path $fullLocalPath) {
        try {
            $ftpUri = "ftp://$ftpServer" + $file.remote
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.UseBinary = $true
            $ftpRequest.KeepAlive = $false

            $fileContent = [System.IO.File]::ReadAllBytes($fullLocalPath)
            $requestStream = $ftpRequest.GetRequestStream()
            $requestStream.Write($fileContent, 0, $fileContent.Length)
            $requestStream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()
            
            Write-Host "OK: $(Split-Path $file.local -Leaf)" -ForegroundColor Green
        }
        catch {
            Write-Host "ERR: $_" -ForegroundColor Red
        }
    }
}

Write-Host "FERTIG" -ForegroundColor Green
