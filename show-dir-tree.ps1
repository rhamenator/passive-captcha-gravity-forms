Function Get-Tree($path, $indent = "") {
    Get-ChildItem -Path $path | Where-Object { $_.Name -notmatch "node_modules" } | ForEach-Object {
        Write-Output "$indent|- $_"
        If ($_.PSIsContainer) { Get-Tree $_.FullName "$indent  " }
    }
}
Get-Tree .
