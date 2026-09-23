# =========================================================================
# ST. BILFRID CMS - AUTOMATED WHITE-BOX TESTING POWERSHELL RUNNER
# =========================================================================

param (
    [string]$Filter = "",
    [switch]$Detail,
    [switch]$NoExport
)

$argsList = @()
if ($Filter) {
    $argsList += "--filter=$Filter"
}
if ($Detail) {
    $argsList += "--detail"
}
if ($NoExport) {
    $argsList += "--no-export"
}

Write-Host "Launching Whitebox Testing Suite..." -ForegroundColor Cyan
php run_whitebox.php $argsList
