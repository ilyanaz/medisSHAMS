<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Clinic</title>
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
$clinic = $clinicRecord ?? (object) [];
?>
<style>
.page{display:grid;gap:18px}.page-head h1{margin:0;font-size:1.9rem}.page-head p{margin:6px 0 0;color:#6b7280}.grid{display:grid;grid-template-columns:1.15fr .85fr;gap:18px}.card{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:20px}.card h2{margin:0 0 16px;font-size:1.15rem}.fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field{display:grid;gap:6px}.field span{font-size:.86rem;color:#6b7280}.field strong{font-size:1rem;color:#111827}.field.full{grid-column:1/-1}.logo-preview{border:1px dashed #cbd5e1;border-radius:18px;min-height:240px;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden}.logo-preview img{max-width:100%;max-height:220px;object-fit:contain}.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;border:1px solid #d1d5db;border-radius:12px;padding:10px 14px;background:#fff;color:#374151;font-size:.92rem}.btn.primary{background:#389B5B;border-color:#389B5B;color:#fff}@media (max-width:980px){.grid{grid-template-columns:1fr}.fields{grid-template-columns:1fr}}
</style>
<div class="page">
    <div class="page-head">
        <h1>Admin Clinic</h1>
        <p>Review clinic information based on the <code>clinic</code> table.</p>
    </div>

    <div class="grid">
        <section class="card">
            <h2>Clinic Information</h2>
            <div class="fields">
                <div class="field"><span>Clinic ID</span><strong><?php echo $esc($clinic->clinic_id ?? '-'); ?></strong></div>
                <div class="field"><span>Clinic Name</span><strong><?php echo $esc($clinic->clinic_name ?? ($clinicName ?? '-')); ?></strong></div>
                <div class="field"><span>Telephone</span><strong><?php echo $esc($clinic->clinic_telephone ?? '-'); ?></strong></div>
                <div class="field"><span>Fax</span><strong><?php echo $esc($clinic->clinic_fax ?? '-'); ?></strong></div>
                <div class="field"><span>Email</span><strong><?php echo $esc($clinic->clinic_email ?? '-'); ?></strong></div>
                <div class="field"><span>Username</span><strong><?php echo $esc($clinic->clinic_username ?? '-'); ?></strong></div>
                <div class="field"><span>Postcode</span><strong><?php echo $esc($clinic->clinic_postcode ?? '-'); ?></strong></div>
                <div class="field"><span>District</span><strong><?php echo $esc($clinic->clinic_district ?? '-'); ?></strong></div>
                <div class="field"><span>State</span><strong><?php echo $esc($clinic->clinic_state ?? '-'); ?></strong></div>
                <div class="field full"><span>Address</span><strong><?php echo $esc($clinic->clinic_address ?? '-'); ?></strong></div>
            </div>
            <div class="actions">
                <a class="btn primary" href="<?php echo $esc(route('settings')); ?>">Open Asset Settings</a>
                <a class="btn" href="<?php echo $esc(route('admin.dashboard')); ?>">Back to Dashboard</a>
            </div>
        </section>

        <section class="card">
            <h2>Clinic Logo</h2>
            <div class="logo-preview">
                <?php if (! empty($clinicLogoUrl)): ?>
                    <img src="<?php echo $esc($clinicLogoUrl); ?>" alt="Clinic Logo">
                <?php else: ?>
                    <span>No clinic logo available.</span>
                <?php endif; ?>
            </div>
            <div class="actions">
                <a class="btn" href="<?php echo $esc(route('profile')); ?>">View Profile</a>
                <a class="btn" href="<?php echo $esc(route('account.settings')); ?>">Account Settings</a>
            </div>
        </section>
    </div>
</div>
<?php medis_render_navigation_end(); ?>
</body>
</html>