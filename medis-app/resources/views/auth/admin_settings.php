<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
</head>
<body>
<?php
require __DIR__ . '/navigation.php';
$esc = static fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
medis_render_navigation_start([
    'clinicName' => $clinicName ?? 'Medis SHAMS',
    'clinicLogoUrl' => $clinicLogoUrl ?? null,
    'username' => $username ?? 'Admin',
    'active' => 'settings',
]);
$userRoleCounts = $userRoleCounts ?? [];
?>
<style>
.page{display:grid;gap:18px}.page-head h1{margin:0;font-size:1.9rem}.page-head p{margin:6px 0 0;color:#6b7280}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px}.card h2{margin:0 0 10px;font-size:1.15rem}.card p{margin:0;color:#6b7280}.count{display:block;margin-top:12px;font-size:2rem;font-weight:700;color:#111827}.shortcut-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.shortcut{border:1px solid #e5e7eb;border-radius:18px;background:#fafafa;padding:16px;text-decoration:none;color:#111827}.shortcut strong{display:block}.shortcut span{display:block;margin-top:6px;color:#6b7280;font-size:.9rem}@media (max-width:980px){.grid{grid-template-columns:1fr}.shortcut-grid{grid-template-columns:1fr}}
</style>
<div class="page">
    <div class="page-head">
        <h1>Admin Settings</h1>
        <p>Central shortcuts for account, document assets, clinic setup, and system master pages.</p>
    </div>

    <div class="grid">
        <section class="card">
            <h2>Admin Users</h2>
            <p>Total users with the Admin role in the <code>users</code> table.</p>
            <span class="count"><?php echo $esc(number_format((int) ($userRoleCounts['Admin'] ?? 0))); ?></span>
        </section>
        <section class="card">
            <h2>Doctor Users</h2>
            <p>Total users with the Doctor role.</p>
            <span class="count"><?php echo $esc(number_format((int) ($userRoleCounts['Doctor'] ?? 0))); ?></span>
        </section>
        <section class="card">
            <h2>Clinic Users</h2>
            <p>Total users with the Clinic role.</p>
            <span class="count"><?php echo $esc(number_format((int) ($userRoleCounts['Clinic'] ?? 0))); ?></span>
        </section>
    </div>

    <section class="card">
        <h2>Admin Shortcuts</h2>
        <div class="shortcut-grid">
            <a class="shortcut" href="<?php echo $esc(route('settings')); ?>"><strong>Document Assets</strong><span>Upload or delete report header and signature.</span></a>
            <a class="shortcut" href="<?php echo $esc(route('account.settings')); ?>"><strong>Account Settings</strong><span>Change password and profile picture.</span></a>
            <a class="shortcut" href="<?php echo $esc(route('profile')); ?>"><strong>Profile</strong><span>Review doctor profile information.</span></a>
            <a class="shortcut" href="<?php echo $esc(route('admin.clinic')); ?>"><strong>Clinic Master</strong><span>Review the clinic master information and logo.</span></a>
            <a class="shortcut" href="<?php echo $esc(route('admin.company')); ?>"><strong>Company Master</strong><span>Open the company administration list.</span></a>
            <a class="shortcut" href="<?php echo $esc(route('admin.employee')); ?>"><strong>Employee Master</strong><span>Open the employee administration list.</span></a>
        </div>
    </section>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>