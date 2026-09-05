<#
.SYNOPSIS
    Starts NewConstuc.FIRM on Caddy Web Server with PHP FastCGI.
.DESCRIPTION
    Launches PHP-CGI FastCGI backend on 127.0.0.1:9000, runs Caddy server on http://localhost:8000,
    and opens the browser.
#>

[CmdletBinding()]
param(
    [int]$Port = 8000,
    [switch]$NoBrowser
)

$ErrorActionPreference = "Stop"
Set-Location -Path $PSScriptRoot

Write-Host "==============================================================================" -ForegroundColor Cyan
Write-Host "     NewConstuc.FIRM - Caddy Web Server Launcher (PowerShell)               " -ForegroundColor Cyan
Write-Host "==============================================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Discover php-cgi.exe
$phpCgi = $null
$phpCgiCandidates = @(
    "C:\xampp\php\php-cgi.exe",
    "C:\php\php-cgi.exe",
    (Get-Command php-cgi.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source)
)

foreach ($cand in $phpCgiCandidates) {
    if ($cand -and (Test-Path $cand)) {
        $phpCgi = $cand
        break
    }
}

if (-not $phpCgi) {
    Write-Error "Could not find php-cgi.exe! Please install XAMPP (C:\xampp\php) or add PHP to your PATH."
    return
}

# 2. Discover caddy.exe
$caddyExe = $null
$caddyCandidates = @(
    (Get-Command caddy.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source),
    (Get-ChildItem -Path "$env:LOCALAPPDATA\Microsoft\WinGet\Packages" -Filter "caddy.exe" -Recurse -Depth 3 -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName -First 1),
    "C:\caddy\caddy.exe"
)

foreach ($cand in $caddyCandidates) {
    if ($cand -and (Test-Path $cand)) {
        $caddyExe = $cand
        break
    }
}

if (-not $caddyExe) {
    Write-Error "Could not find caddy.exe! Install it with: winget install --id CaddyServer.Caddy --accept-package-agreements --accept-source-agreements"
    return
}

Write-Host "[OK] PHP-CGI: $phpCgi" -ForegroundColor Green
Write-Host "[OK] Caddy:   $caddyExe" -ForegroundColor Green
Write-Host ""

# 3. Configure FastCGI environment
$env:PHP_FCGI_MAX_REQUESTS = "0"
$env:PHP_FCGI_CHILDREN = "8"

# 4. Start PHP-CGI process
Write-Host "[1/3] Starting PHP FastCGI server on 127.0.0.1:9000..." -ForegroundColor Yellow
$phpProcess = Start-Process -FilePath $phpCgi -ArgumentList "-b 127.0.0.1:9000" -PassThru -WindowStyle Minimized

# 5. Open browser
if (-not $NoBrowser) {
    Write-Host "[2/3] Opening browser at http://localhost:$Port ..." -ForegroundColor Yellow
    Start-Process "http://localhost:$Port"
}

# 6. Launch Caddy
Write-Host "[3/3] Starting Caddy web server..." -ForegroundColor Green
Write-Host "------------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "Server is LIVE at http://localhost:$Port - Press Ctrl+C to stop." -ForegroundColor White
Write-Host "------------------------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host ""

try {
    & $caddyExe run --config "$PSScriptRoot\Caddyfile"
} finally {
    Write-Host "`nStopping PHP FastCGI..." -ForegroundColor Yellow
    if ($phpProcess -and -not $phpProcess.HasExited) {
        $phpProcess.Kill()
    }
    Stop-Process -Name "php-cgi" -Force -ErrorAction SilentlyContinue
    Write-Host "Server stopped successfully." -ForegroundColor Green
}
