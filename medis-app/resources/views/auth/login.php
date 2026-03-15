<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        :root {
            --brand: #389B5B;
            --brand-dark: #319755;
            --bg: #edf7f1;
            --text: #1a2438;
            --muted: #72819a;
            --input-border: #d4dceb;
            --panel: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at 20% 20%, #f7fcf9 0%, #eef8f2 50%, #e5f2ea 100%);
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: var(--text);
            padding: 20px;
        }

        .wrapper {
            width: min(1120px, 100%);
            min-height: 720px;
            border-radius: 16px;
            overflow: hidden;
            background: var(--panel);
            box-shadow: 0 18px 48px rgba(12, 28, 77, 0.16);
            display: grid;
            grid-template-columns: 46% 54%;
        }

        .left {
            position: relative;
            background: #319755;
            display: grid;
            grid-template-rows: 58% 42%;
        }

        .left-top {
            background-image: url('https://images.unsplash.com/photo-1537368910025-700350fe46c7?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
        }

        .left-bottom {
            background: linear-gradient(180deg, #389B5B 0%, #319755 100%);
            color: #fff;
            padding: 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 22px;
        }

        .welcome-box {
            border-left: 3px solid rgba(255, 255, 255, 0.85);
            padding: 14px 0 14px 22px;
            background: rgba(20, 94, 50, 0.25);
        }

        .welcome-box h2 {
            margin: 0 0 10px;
            font-size: clamp(1.6rem, 1.9vw, 2rem);
            line-height: 1.3;
            font-weight: 700;
        }

        .welcome-box p {
            margin: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.02rem;
            line-height: 1.5;
        }

        .right {
            padding: clamp(34px, 5vw, 62px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
        }

        .form-shell {
            width: min(460px, 100%);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .brand img {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #d8e0f0;
            background: #fff;
        }

        .brand-fallback {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            background: var(--brand);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
        }

        .brand span {
            font-size: 1.9rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--brand);
        }

        h1 {
            margin: 0;
            font-size: 2.15rem;
            letter-spacing: -0.02em;
        }

        .subtitle {
            margin: 10px 0 30px;
            color: var(--muted);
            font-size: 1rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input {
            width: 100%;
            border: 1px solid var(--input-border);
            border-radius: 10px;
            padding: 13px 14px;
            font-size: 1rem;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .input:focus {
            border-color: #8bcfa4;
            box-shadow: 0 0 0 3px rgba(56, 155, 91, 0.18);
        }

        .field { margin-bottom: 18px; }

        .password-wrap {
            position: relative;
        }

        .toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #8090ac;
            font-size: 0.9rem;
            cursor: pointer;
            padding: 4px;
        }

        .extra {
            display: flex;
            justify-content: flex-end;
            margin: 6px 0 22px;
        }

        .extra a,
        .signup a {
            color: var(--brand-dark);
            text-decoration: none;
            font-weight: 600;
        }

        .extra a:hover,
        .signup a:hover { text-decoration: underline; }

        .btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #389B5B 0%, #319755 100%);
            color: #fff;
            font-size: 1.06rem;
            font-weight: 700;
            padding: 13px 16px;
            cursor: pointer;
            box-shadow: 0 7px 20px rgba(49, 151, 85, 0.35);
            transition: transform .15s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .signup {
            margin-top: 24px;
            text-align: center;
            color: #5d6c87;
        }

        .error {
            background: #ffe8e8;
            border: 1px solid #f7bcbc;
            color: #a31a1a;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 18px;
            font-size: 0.95rem;
        }

        @media (max-width: 940px) {
            .wrapper {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .left {
                grid-template-rows: 240px 1fr;
            }

            .right {
                padding: 30px 24px 34px;
            }
        }
    </style>
</head>
<body>
<?php
    $defaultLogoUrl = function_exists('asset') ? (string) asset('uploads/medis logo.png') : '/uploads/medis logo.png';
    $clinicLogoUrl = isset($clinicLogoUrl) && $clinicLogoUrl !== '' ? $clinicLogoUrl : $defaultLogoUrl;
    $clinicName = isset($clinicName) && $clinicName !== '' ? $clinicName : 'Clinic System';

    $esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    $oldInput = static function (string $key, string $default = '') {
        return function_exists('old') ? (string) old($key, $default) : $default;
    };

    $routeUrl = static function (string $name, string $fallback = '#') {
        return function_exists('route') ? (string) route($name) : $fallback;
    };

    $csrf = function_exists('csrf_token') ? (string) csrf_token() : '';
    $hasErrors = isset($errors) && method_exists($errors, 'any') && $errors->any();
    $firstError = $hasErrors ? (string) $errors->first() : '';
?>
<div class="wrapper">
    <section class="left">
        <div class="left-top"></div>
        <div class="left-bottom">
            <div class="brand" style="margin:0;">
                <?php if ($clinicLogoUrl): ?>
                    <img src="<?php echo $esc($clinicLogoUrl); ?>" alt="Clinic Logo">
                <?php else: ?>
                    <span class="brand-fallback">C</span>
                <?php endif; ?>
                <span style="color:#fff;font-size:2rem;"><?php echo $esc($clinicName); ?></span>
            </div>
            <div class="welcome-box">
                <h2>Welcome to <?php echo $esc($clinicName); ?> Management System</h2>
                <p>Cloud-based streamline healthcare management with a centralized, user-friendly platform.</p>
            </div>
        </div>
    </section>

    <section class="right">
        <div class="form-shell">
            <div class="brand">
                <?php if ($clinicLogoUrl): ?>
                    <img src="<?php echo $esc($clinicLogoUrl); ?>" alt="Clinic Logo">
                <?php else: ?>
                    <span class="brand-fallback">C</span>
                <?php endif; ?>
                <span><?php echo $esc($clinicName); ?></span>
            </div>

            <h1>Login</h1>
            <p class="subtitle">Enter your credentials to login to your account</p>

            <?php if ($hasErrors): ?>
                <div class="error"><?php echo $esc($firstError); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $esc($routeUrl('login.store')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc($csrf); ?>">
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" class="input" type="text" value="<?php echo $esc($oldInput('username')); ?>" placeholder="Enter your username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input id="password" name="password" class="input" type="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle" id="togglePassword">Show</button>
                    </div>
                </div>

                <div class="extra">
                    <a href="<?php echo $esc($routeUrl('password.request')); ?>">Forgot Password?</a>
                </div>

                <button class="btn" type="submit">Sign In</button>

                <p class="signup">Don't have an account? <a href="#">Sign Up</a></p>
            </form>
        </div>
    </section>
</div>

<script>
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    toggleBtn.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        toggleBtn.textContent = isPassword ? 'Hide' : 'Show';
    });
</script>
</body>
</html>

