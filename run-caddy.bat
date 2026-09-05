@echo off
setlocal enabledelayedexpansion
title NewConstuc.FIRM - Caddy Web Server Launcher

echo ==============================================================================
echo     NewConstuc.FIRM - Caddy Web Server Launcher
echo ==============================================================================
echo.

cd /d "%~dp0"

:: 1. Locate PHP-CGI Executable
set "PHP_CGI_EXE="
if exist "C:\xampp\php\php-cgi.exe" (
    set "PHP_CGI_EXE=C:\xampp\php\php-cgi.exe"
) else if exist "C:\php\php-cgi.exe" (
    set "PHP_CGI_EXE=C:\php\php-cgi.exe"
) else (
    for /f "delims=" %%i in ('where php-cgi.exe 2^>nul') do (
        set "PHP_CGI_EXE=%%i"
    )
)

if "%PHP_CGI_EXE%"=="" (
    echo [ERROR] Could not find php-cgi.exe.
    echo Please ensure PHP/XAMPP is installed at C:\xampp\php or added to your system PATH.
    echo.
    pause
    exit /b 1
)

:: 2. Locate Caddy Executable
set "CADDY_EXE="
for /f "delims=" %%i in ('where caddy.exe 2^>nul') do (
    set "CADDY_EXE=%%i"
)

if "%CADDY_EXE%"=="" (
    for /f "delims=" %%i in ('dir /b /s "%LOCALAPPDATA%\Microsoft\WinGet\Packages\*caddy.exe" 2^>nul') do (
        set "CADDY_EXE=%%i"
    )
)

if "%CADDY_EXE%"=="" if exist "C:\caddy\caddy.exe" (
    set "CADDY_EXE=C:\caddy\caddy.exe"
)

if "%CADDY_EXE%"=="" (
    echo [ERROR] Could not find caddy.exe.
    echo You can install it quickly using winget:
    echo     winget install --id CaddyServer.Caddy --accept-package-agreements --accept-source-agreements
    echo.
    pause
    exit /b 1
)

echo [OK] Using PHP-CGI: "%PHP_CGI_EXE%"
echo [OK] Using Caddy:   "%CADDY_EXE%"
echo.

:: 3. Configure Windows FastCGI environment
set PHP_FCGI_MAX_REQUESTS=0
set PHP_FCGI_CHILDREN=8

:: 4. Start PHP FastCGI Server on Port 9000 in background
echo [1/3] Starting PHP FastCGI server on 127.0.0.1:9000...
start "NewConstuc.FIRM - PHP FastCGI" /min "%PHP_CGI_EXE%" -b 127.0.0.1:9000

:: Wait a brief moment for FastCGI to bind
timeout /t 1 /nobreak >nul

:: 5. Open Browser
echo [2/3] Opening Google Chrome / Default Browser at http://localhost:8000 ...
start "" "http://localhost:8000"

:: 6. Launch Caddy Server in Foreground
echo [3/3] Starting Caddy Server on http://localhost:8000 ...
echo ------------------------------------------------------------------------------
echo Server is LIVE! Press Ctrl+C in this window to stop Caddy and FastCGI.
echo ------------------------------------------------------------------------------
echo.

"%CADDY_EXE%" run --config "%~dp0Caddyfile"

:: 7. Cleanup PHP-CGI on exit
echo.
echo Stopping PHP FastCGI background workers...
taskkill /F /IM php-cgi.exe >nul 2>&1
echo Goodbye!
pause
