@echo off
title NewConstuc.FIRM - Server Launcher
cd /d "%~dp0"

echo ==============================================================================
echo     NewConstuc.FIRM - Construction Firm System Launcher
echo ==============================================================================
echo.
echo Select Web Server Engine:
echo   [1] Caddy Web Server + FastCGI (Recommended - High Performance)
echo   [2] Laravel Artisan Built-in Server (Classic Development)
echo.
set /p choice="Enter choice [1/2] (Default: 1): "

if "%choice%"=="2" (
    echo.
    echo Opening Google Chrome at http://127.0.0.1:8000 ...
    start "" "http://127.0.0.1:8000"
    echo Starting Artisan server on http://127.0.0.1:8000 ...
    "C:\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
    pause
) else (
    call "%~dp0run-caddy.bat"
)
