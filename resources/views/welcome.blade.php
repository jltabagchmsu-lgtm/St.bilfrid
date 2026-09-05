@extends('layouts.app')

@section('title', 'Laravel 10 - Application Foundation')

@section('content')
<section class="hero">
    <div class="hero-badge">Environment Configured</div>
    <h1 class="hero-title">
        The PHP Framework for <br><span class="gradient-text">Web Artisans</span>
    </h1>
    <p class="hero-subtitle">
        Your Laravel project foundation is fully scaffolded and configured for local development with PHP 8.1 and SQLite.
    </p>
</section>

<div class="card-grid">
    <!-- Card 1: System Status -->
    <div class="glass-card">
        <div class="card-icon" style="font-size: 0.75rem; font-weight: 800; color: #38bdf8;">ENV</div>
        <h2 class="card-title">Environment Specifications</h2>
        <p class="card-desc">Runtime and system configuration details.</p>
        
        <div class="info-table">
            <div class="info-row">
                <span class="info-label">Laravel Version</span>
                <span class="info-val">{{ $systemInfo['laravel_version'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">PHP Version</span>
                <span class="info-val">{{ $systemInfo['php_version'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Environment</span>
                <span class="info-val">{{ $systemInfo['environment'] }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Database</span>
                <span class="info-val">{{ $systemInfo['db_connection'] }} ({{ $systemInfo['db_status'] }})</span>
            </div>
        </div>
    </div>

    <!-- Card 2: Quick Start Commands -->
    <div class="glass-card">
        <div class="card-icon" style="font-size: 0.75rem; font-weight: 800; color: #f59e0b;">CLI</div>
        <h2 class="card-title">Development Commands</h2>
        <p class="card-desc">Run Artisan CLI commands via PHP binary.</p>
        
        <div class="code-box">
            <span id="cmd1">& "C:\xampp\php\php.exe" artisan serve</span>
            <button class="copy-btn" data-target="cmd1">Copy</button>
        </div>

        <div class="code-box">
            <span id="cmd2">& "C:\xampp\php\php.exe" artisan migrate</span>
            <button class="copy-btn" data-target="cmd2">Copy</button>
        </div>

        <div class="code-box">
            <span id="cmd3">& "C:\xampp\php\php.exe" artisan route:list</span>
            <button class="copy-btn" data-target="cmd3">Copy</button>
        </div>
    </div>

    <!-- Card 3: Architecture Guide -->
    <div class="glass-card">
        <div class="card-icon" style="font-size: 0.75rem; font-weight: 800; color: #10b981;">DIR</div>
        <h2 class="card-title">Project Structure</h2>
        <p class="card-desc">Key directory pathways for customization.</p>
        
        <div class="info-table">
            <div class="info-row">
                <span class="info-label">Routes</span>
                <span class="info-val">routes/web.php</span>
            </div>
            <div class="info-row">
                <span class="info-label">Controllers</span>
                <span class="info-val">app/Http/Controllers</span>
            </div>
            <div class="info-row">
                <span class="info-label">Views</span>
                <span class="info-val">resources/views</span>
            </div>
            <div class="info-row">
                <span class="info-label">Models</span>
                <span class="info-val">app/Models</span>
            </div>
        </div>
    </div>
</div>
@endsection
