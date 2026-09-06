<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - St. Bilfrid Development Corporation</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .login-wrapper {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: var(--bg-main);
            background-image: 
                radial-gradient(at 20% 20%, rgba(239, 68, 68, 0.12) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(56, 189, 248, 0.08) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.9) 0px, transparent 100%);
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 40px 36px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 35px rgba(239, 68, 68, 0.15);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary-gradient);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            width: 56px;
            height: 56px;
            background: var(--primary-gradient);
            border-radius: var(--radius-md);
            display: grid;
            place-items: center;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
        }

        .login-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .login-title span {
            color: var(--primary-red);
        }

        .login-subtitle {
            font-size: 0.825rem;
            color: var(--text-muted);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 14px;
        }

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-secondary);
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
            color: var(--text-muted);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            pointer-events: none;
        }

        .login-input {
            width: 100%;
            padding: 13px 14px 13px 42px;
            background: rgba(0, 0, 0, 0.45);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 0.925rem;
            transition: all 0.2s ease;
        }

        .login-input:focus {
            outline: none;
            border-color: var(--primary-red);
            background: rgba(0, 0, 0, 0.65);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
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
            color: var(--text-secondary);
            cursor: pointer;
            user-select: none;
        }

        .remember-checkbox {
            accent-color: var(--primary-red);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--primary-gradient);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            font-weight: 700;
            font-family: var(--font-sans);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 18px rgba(239, 68, 68, 0.35);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-helper-box {
            margin-top: 24px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.12);
            border-radius: var(--radius-md);
            text-align: center;
        }

        .btn-quick-fill {
            background: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-quick-fill:hover {
            background: rgba(56, 189, 248, 0.22);
            border-color: #38bdf8;
        }

        .login-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="login-logo">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l8-4v18"></path>
                        <path d="M19 21V11l-6-3"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                        <path d="M9 17h1"></path>
                    </svg>
                </div>
                <h1 class="login-title" style="font-size: 1.45rem;">St. Bilfrid <span>Dev. Corp.</span></h1>
                <div class="login-subtitle">St. Bilfrid Development Corporation</div>
                <div class="security-badge">
                    Enterprise Construction Management Portal
                </div>
            </div>

            @if($errors->any())
                <div class="login-error">
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert-success" style="margin-bottom: 20px; font-size: 0.85rem; padding: 10px 14px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="login-form-group">
                    <label class="login-label" for="email">
                        <span>Admin Email Address</span>
                    </label>
                    <div class="input-icon-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="emailInput" 
                            class="login-input" 
                            placeholder="admin@newconstuc.firm" 
                            value="{{ old('email', 'admin@newconstuc.firm') }}" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="login-form-group">
                    <label class="login-label" for="password">
                        <span>Master Password</span>
                        <button type="button" onclick="togglePassword()" style="background:none; border:none; color:var(--accent-blue); font-size:0.75rem; cursor:pointer; font-weight:600;">
                            Show/Hide
                        </button>
                    </label>
                    <div class="input-icon-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
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
                        <span>Keep administrator logged in</span>
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <span>Secure Sign In</span>
                    <span>&rarr;</span>
                </button>
            </form>

            <div class="demo-helper-box" style="text-align: left; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1);">
                <div style="font-size: 0.8rem; font-weight: 700; color: #f8fafc; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span>1-Click Fast Login Credentials:</span>
                    <span style="font-size: 0.7rem; color: var(--text-muted); font-weight: normal;">Click to auto-fill</span>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Master Admin -->
                    <button type="button" class="btn-quick-fill" onclick="autoFillAdmin()" style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 8px 12px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5; text-align: left;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.8rem;">Master Administrator</div>
                            <div style="font-family: var(--font-mono); font-size: 0.725rem; opacity: 0.8;">admin@newconstuc.firm &bull; admin123</div>
                        </div>
                        <span style="font-size: 0.75rem; background: rgba(239,68,68,0.25); padding: 3px 8px; border-radius: 4px;">Fill &rarr;</span>
                    </button>

                    <!-- Roofing Account -->
                    <button type="button" class="btn-quick-fill" onclick="autoFillRoofing()" style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 8px 12px; background: rgba(245, 158, 11, 0.1); border-color: rgba(245, 158, 11, 0.3); color: #fcd34d; text-align: left;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.8rem;">Roofing Transfer Officer</div>
                            <div style="font-family: var(--font-mono); font-size: 0.725rem; opacity: 0.8;">roofing@newconstuc.firm &bull; roofing123</div>
                        </div>
                        <span style="font-size: 0.75rem; background: rgba(245,158,11,0.25); padding: 3px 8px; border-radius: 4px;">Fill &rarr;</span>
                    </button>

                    <!-- Windows & Doors Account -->
                    <button type="button" class="btn-quick-fill" onclick="autoFillWindowsDoors()" style="display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 8px 12px; background: rgba(56, 189, 248, 0.1); border-color: rgba(56, 189, 248, 0.3); color: #38bdf8; text-align: left;">
                        <div>
                            <div style="font-weight: 700; font-size: 0.8rem;">Windows & Doors Transfer Officer</div>
                            <div style="font-family: var(--font-mono); font-size: 0.725rem; opacity: 0.8;">windows.doors@newconstuc.firm &bull; windows123</div>
                        </div>
                        <span style="font-size: 0.75rem; background: rgba(56,189,248,0.25); padding: 3px 8px; border-radius: 4px;">Fill &rarr;</span>
                    </button>
                </div>
            </div>
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

        function autoFillAdmin() {
            document.getElementById('emailInput').value = 'admin@newconstuc.firm';
            document.getElementById('passwordInput').value = 'admin123';
        }

        function autoFillRoofing() {
            document.getElementById('emailInput').value = 'roofing@newconstuc.firm';
            document.getElementById('passwordInput').value = 'roofing123';
        }

        function autoFillWindowsDoors() {
            document.getElementById('emailInput').value = 'windows.doors@newconstuc.firm';
            document.getElementById('passwordInput').value = 'windows123';
        }
    </script>
</body>
</html>
