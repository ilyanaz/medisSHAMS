<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        :root {
            --brand: #389B5B;
            --brand-light: #319755;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #d9e3d8;
            --danger: #c33838;
            --ok-bg: #e9f8ee;
            --ok-text: #1e6c3c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: #f7faf8;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .layout {
            width: min(1080px, 100%);
            min-height: 620px;
            display: grid;
            grid-template-columns: 48% 52%;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 18px 42px rgba(22, 45, 31, 0.12);
        }

        .left {
            display: grid;
            place-items: center;
            padding: 36px;
            background: #f2f7f3;
        }

        .left img {
            width: min(420px, 100%);
            border-radius: 14px;
        }

        .right {
            padding: 58px 44px;
            display: flex;
            align-items: center;
        }

        .panel {
            width: min(420px, 100%);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .brand img {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            object-fit: cover;
            background: #fff;
            border: 1px solid #dbe8db;
        }

        .brand .dot {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .brand span {
            font-size: 1.35rem;
            color: var(--brand);
            font-weight: 700;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 2rem;
        }

        .desc {
            margin: 0 0 26px;
            color: var(--muted);
            line-height: 1.5;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 13px 14px;
            font-size: 1rem;
            outline: none;
        }

        input:focus {
            border-color: #8bcfa4;
            box-shadow: 0 0 0 3px rgba(56, 155, 91, 0.18);
        }

        .error {
            color: var(--danger);
            margin: 10px 0 0;
            font-size: 0.94rem;
        }

        .ok {
            background: var(--ok-bg);
            color: var(--ok-text);
            border: 1px solid #bde6cb;
            padding: 10px 12px;
            border-radius: 10px;
            margin: 0 0 14px;
            font-size: 0.94rem;
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 10px;
            padding: 12px;
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-light) 100%);
            color: #fff;
            margin-top: 18px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 16px rgba(56, 155, 91, 0.28);
        }

        .back {
            margin-top: 22px;
            text-align: center;
        }

        .back a {
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
        }

        .back a:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .right { padding: 36px 24px 30px; }
            .left { padding: 24px; }
        }
    </style>
</head>
<body>
<?php
    $clinicLogoUrl = isset($clinicLogoUrl) && $clinicLogoUrl !== '' ? $clinicLogoUrl : null;
    $clinicName = isset($clinicName) && $clinicName !== '' ? $clinicName : 'Clinic System';

    $esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    $oldInput = static function (string $key, string $default = '') {
        return function_exists('old') ? (string) old($key, $default) : $default;
    };

    $routeUrl = static function (string $name, string $fallback = '#') {
        return function_exists('route') ? (string) route($name) : $fallback;
    };

    $csrf = function_exists('csrf_token') ? (string) csrf_token() : '';
    $statusMessage = function_exists('session') ? (string) (session('status') ?? '') : '';
    $hasIdentityError = isset($errors) && method_exists($errors, 'has') && $errors->has('identity');
    $identityError = $hasIdentityError ? (string) $errors->first('identity') : '';
?>
<div class="layout">
    <section class="left">
        <img src="https://img.freepik.com/free-vector/forgot-password-concept-illustration_114360-1123.jpg?w=740" alt="Forgot Password Illustration">
    </section>

    <section class="right">
        <div class="panel">
            <div class="brand">
                <?php if ($clinicLogoUrl): ?>
                    <img src="<?php echo $esc($clinicLogoUrl); ?>" alt="Clinic Logo">
                <?php else: ?>
                    <span class="dot">C</span>
                <?php endif; ?>
                <span><?php echo $esc($clinicName); ?></span>
            </div>

            <h1>Forgot Password</h1>
            <p class="desc">Enter your username or email and we'll help you continue password reset.</p>

            <?php if ($statusMessage !== ''): ?>
                <div class="ok"><?php echo $esc($statusMessage); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo $esc($routeUrl('password.email')); ?>">
                <input type="hidden" name="_token" value="<?php echo $esc($csrf); ?>">
                <label for="identity">Username / Email</label>
                <input id="identity" name="identity" type="text" value="<?php echo $esc($oldInput('identity')); ?>" placeholder="example@domain.com or username" required>
                <?php if ($hasIdentityError): ?>
                    <p class="error"><?php echo $esc($identityError); ?></p>
                <?php endif; ?>
                <button type="submit" class="btn">Submit</button>
            </form>

            <p class="back"><a href="<?php echo $esc($routeUrl('login')); ?>">&#x2039; Back to Login</a></p>
        </div>
    </section>
</div>
</body>
</html>
