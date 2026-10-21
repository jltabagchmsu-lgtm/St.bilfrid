<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - St. Bilfrid Development Corporation</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif);
            min-height: 100vh;
            background: #0b1120;
            color: #0f172a;
            overflow-x: hidden;
        }

        .login-wrapper {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            position: relative;
            background: 
                linear-gradient(135deg, rgba(11, 17, 32, 0.78) 0%, rgba(15, 23, 42, 0.88) 100%),
                url('/images/construction_bg.jpg') center center / cover no-repeat fixed;
        }

        /* Ambient light glow */
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 20%;
            left: 50%;
            transform: translateX(-50%);
            width: 520px;
            height: 380px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.22) 0%, rgba(245, 158, 11, 0.12) 40%, transparent 70%);
            pointer-events: none;
            filter: blur(55px);
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 470px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 44px 38px 38px 38px;
            box-shadow: 
                0 25px 60px -15px rgba(0, 0, 0, 0.55),
                0 0 0 1px rgba(255, 255, 255, 0.2),
                0 10px 30px rgba(0, 0, 0, 0.25);
            position: relative;
            z-index: 2;
            overflow: hidden;
            animation: cardFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes cardFadeUp {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 50%, #f59e0b 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-logo {
            width: 62px;
            height: 62px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin: 0 auto 16px auto;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: transform 0.25s ease;
        }

        .login-logo:hover {
            transform: scale(1.05) rotate(1deg);
        }

        .login-title {
            font-size: 1.55rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .login-title span {
            color: #ef4444;
        }

        .login-subtitle {
            font-size: 0.775rem;
            color: #64748b;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-weight: 700;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #dc2626;
            padding: 5px 14px;
            border-radius: 9999px;
            font-size: 0.725rem;
            font-weight: 700;
            margin-top: 14px;
            letter-spacing: 0.02em;
        }

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.825rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            pointer-events: none;
            z-index: 5;
            transition: color 0.2s ease;
        }

        .input-icon-wrapper:focus-within .input-icon {
            color: #ef4444;
        }

        .login-input,
        input.login-input,
        .input-icon-wrapper input {
            width: 100%;
            padding: 13px 14px 13px 44px !important;
            padding-left: 44px !important;
            background: #ffffff !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px;
            color: #0f172a !important;
            font-family: inherit;
            font-size: 0.925rem;
            font-weight: 500;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }

        .login-input::placeholder {
            color: #94a3b8;
        }

        .login-input:focus,
        .input-icon-wrapper input:focus {
            outline: none;
            border-color: #ef4444 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12) !important;
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            font-size: 0.85rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            user-select: none;
            font-weight: 600;
            font-size: 0.825rem;
        }

        .remember-checkbox {
            accent-color: #ef4444;
            width: 16px;
            height: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 18px rgba(239, 68, 68, 0.4);
            letter-spacing: 0.02em;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.55);
            background: linear-gradient(135deg, #f87171 0%, #dc2626 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-error {
            background: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .login-footer-info {
            text-align: center;
            margin-top: 24px;
            font-size: 0.775rem;
            color: rgba(255, 255, 255, 0.65);
            letter-spacing: 0.02em;
            z-index: 2;
            position: relative;
            line-height: 1.6;
        }

        .login-footer-info strong {
            color: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="login-logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-3"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                        <path d="M9 17h1"></path>
                    </svg>
                </div>
                <h1 class="login-title">St. Bilfrid <span>Dev. Corp.</span></h1>
                <div class="login-subtitle">St. Bilfrid Development Corporation</div>
                <div class="security-badge">
                    Enterprise Construction Management Portal
                </div>
            </div>

            @if($errors->any())
                <div class="login-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert-success" style="margin-bottom: 20px; font-size: 0.85rem; padding: 12px 16px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #059669; font-weight: 600;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="login-form-group">
                    <label class="login-label" for="email">
                        <span>Corporate Email Address</span>
                    </label>
                    <div class="input-icon-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="emailInput" 
                            class="login-input" 
                            placeholder="name@newconstuc.firm" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="login-form-group">
                    <label class="login-label" for="password">
                        <span>Password</span>
                        <button type="button" onclick="togglePassword()" style="background:none; border:none; color:#2563eb; font-size:0.75rem; cursor:pointer; font-weight:700;">
                            Show/Hide
                        </button>
                    </label>
                    <div class="input-icon-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input 
                            type="password" 
                            name="password" 
                            id="passwordInput" 
                            class="login-input" 
                            placeholder="••••••••" 
                            required
                        >
                    </div>
                </div>

                <div class="login-options">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" class="remember-checkbox" checked>
                        <span>Remember my session on this device</span>
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <span>Sign In to Portal</span>
                    <span>&rarr;</span>
                </button>
            </form>
        </div>

        <div class="login-footer-info">
            <strong>St. Bilfrid Development Corporation</strong> &bull; Construction Operations & Engineering Portal<br>
            <span>Confidential & Proprietary Enterprise System &bull; All Rights Reserved</span>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('passwordInput');
            if (pwd.type === 'password') {
                pwd.type = 'text';
            } else {
                pwd.type = 'password';
            }
        }
    </script>
</body>
</html>
