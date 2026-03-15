<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <style>
        :root {
            --brand: #389B5B;
            --brand-light: #319755;
            --text: #1f2937;
            --muted: #6b7280;
            --panel: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: radial-gradient(circle at top right, #eff9f2, #f8fbf9 55%);
        }

        .card {
            width: min(520px, 100%);
            background: var(--panel);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 16px 40px rgba(16, 36, 24, 0.14);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .brand img {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #d8e9db;
        }

        .brand .dot {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            font-weight: 700;
        }

        .brand span {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--brand);
        }

        h1 { margin: 0 0 8px; font-size: 1.9rem; }
        p { margin: 0 0 22px; color: var(--muted); line-height: 1.5; }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 11px 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--brand) 0%, var(--brand-light) 100%);
            color: #fff;
            box-shadow: 0 8px 16px rgba(56, 155, 91, 0.25);
        }

        .btn-light {
            background: #eef3ef;
            color: #334155;
        }
    </style>
</head>
<body>
<?php
    $defaultLogoUrl = function_exists('asset') ? (string) asset('uploads/medis logo.png') : '/uploads/medis logo.png';
    $clinicLogoUrl = isset($clinicLogoUrl) && $clinicLogoUrl !== '' ? $clinicLogoUrl : $defaultLogoUrl;
    $clinicName = isset($clinicName) && $clinicName !== '' ? $clinicName : 'Clinic System';
    $username = isset($username) && $username !== '' ? $username : 'User';
    $esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="card">
    <div class="brand">
        <?php if ($clinicLogoUrl): ?>
            <img src="<?php echo $esc($clinicLogoUrl); ?>" alt="Clinic Logo">
        <?php else: ?>
            <span class="dot">C</span>
        <?php endif; ?>
        <span><?php echo $esc($clinicName); ?></span>
    </div>

    <h1>Logout</h1>
    <p>Hi <?php echo $esc($username); ?>, are you sure you want to logout from your account?</p>

    <div class="actions">
        <form method="POST" action="<?php echo $esc(route('logout')); ?>">
            <input type="hidden" name="_token" value="<?php echo $esc(csrf_token()); ?>">
            <button type="submit" class="btn btn-primary">Yes, Logout</button>
        </form>
        <a class="btn btn-light" href="<?php echo $esc(route('dashboard')); ?>">Cancel</a>
    </div>
</div>
</body>
</html>

