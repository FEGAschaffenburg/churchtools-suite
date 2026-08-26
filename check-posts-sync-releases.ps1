$response = Invoke-WebRequest -Uri 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite-posts-sync/releases?per_page=30' -ErrorAction Stop
$releases = $response.Content | ConvertFrom-Json

Write-Host "Posts Sync Releases:" -ForegroundColor Cyan
$releases | ForEach-Object {
    $assetCount = $_.assets | Where-Object { $_.name -like '*.zip' } | Measure-Object | Select-Object -ExpandProperty Count
    Write-Host "  Tag: $($_.tag_name) | ZIPs: $assetCount"
    if ($assetCount -gt 0) {
        $_.assets | Where-Object { $_.name -like '*.zip' } | ForEach-Object {
            Write-Host "    - $($_.name)"
        }
    }
}
